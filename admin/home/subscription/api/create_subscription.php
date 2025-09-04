<?php
require '../../../config/db.php';
require 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$email = $input['email'] ?? null;
$plan = $input['plan'] ?? null;
$duration = $input['duration'] ?? '1month';
$notes = $input['notes'] ?? '';
$send_email = $input['send_email'] ?? false;

// Validation
if (!$email || !$plan) {
    echo json_encode(['success' => false, 'message' => 'Email and plan are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!in_array($plan, ['free', 'pro', 'enterprise'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid plan selected']);
    exit;
}

// Get admin info for logging
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$admin_name = $admin ? $admin['name'] : 'Unknown';

try {
    $pdo->beginTransaction();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User with this email does not exist in the system');
    }
    
    $user_id = $user['id'];
    $user_name = $user['full_name'];
    
    // Check if user already has an active subscription
    $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status IN ('active', 'trial')");
    $stmt->execute([$user_id]);
    $existing_sub = $stmt->fetch();
    
    if ($existing_sub) {
        throw new Exception('User already has an active subscription');
    }
    
    // Calculate subscription details
    $start_date = date('Y-m-d H:i:s');
    $end_date = null;
    
    // Calculate end date based on duration (only for paid plans)
    if ($plan !== 'free') {
        switch ($duration) {
            case '1month':
                $end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
                break;
            case '3months':
                $end_date = date('Y-m-d H:i:s', strtotime('+3 months'));
                break;
            case '6months':
                $end_date = date('Y-m-d H:i:s', strtotime('+6 months'));
                break;
            case '1year':
                $end_date = date('Y-m-d H:i:s', strtotime('+1 year'));
                break;
            case 'lifetime':
                $end_date = null; // Lifetime subscription
                break;
        }
    }
    
    // Set credits based on plan
    $credits_map = [
        'free' => 1000,
        'pro' => 2000,
        'enterprise' => 10000
    ];
    $credits = $credits_map[$plan];
    
    // Insert or update subscription
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (
            user_id, plan_name, status, start_date, end_date, 
            credits_total, credits_remaining, is_active, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            plan_name = VALUES(plan_name),
            status = VALUES(status),
            start_date = VALUES(start_date),
            end_date = VALUES(end_date),
            credits_total = VALUES(credits_total),
            credits_remaining = VALUES(credits_remaining),
            is_active = VALUES(is_active),
            updated_at = NOW()
    ");
    
    $status = ($plan === 'free') ? 'active' : 'active';
    $is_active = 1;
    
    $stmt->execute([
        $user_id, $plan, $status, $start_date, $end_date,
        $credits, $credits, $is_active
    ]);
    
    // Log the admin action
    $details = json_encode([
        'admin_id' => $admin_id,
        'admin_name' => $admin_name,
        'action' => 'subscription_created',
        'user_id' => $user_id,
        'user_name' => $user_name,
        'user_email' => $email,
        'plan' => $plan,
        'duration' => $duration,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'credits' => $credits,
        'notes' => $notes
    ]);
    
    // Log in security logs
    $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        'subscription_created_by_admin',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
    
    // Log in admin actions table
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$admin_id, 'subscription_created', $user_id, $details]);
    
    // Log in subscription history
    $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, 'created', $details, $admin_id]);
    
    // Send welcome email if requested
    if ($send_email) {
        // Here we will would implement your email sending logic
        // For now, we'll just log it
        error_log("Welcome email should be sent to: $email for plan: $plan");
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Subscription created successfully for $email",
        'subscription' => [
            'user_id' => $user_id,
            'plan' => $plan,
            'duration' => $duration,
            'credits' => $credits,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Create subscription error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>