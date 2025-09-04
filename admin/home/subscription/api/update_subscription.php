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

$user_id = $input['user_id'] ?? null;
$action = $input['action'] ?? null;

if (!$user_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Get admin info for logging
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$admin_name = $admin ? $admin['name'] : 'Unknown';

// Get user info
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user_name = $user['full_name'];
$user_email = $user['email'];

try {
    $pdo->beginTransaction();
    
    switch ($action) {
        case 'change_plan':
    $new_plan = $input['plan'] ?? null;
    if (!$new_plan || !in_array($new_plan, ['free', 'pro', 'enterprise'])) {
        throw new Exception('Invalid plan selected');
    }
    
    // Calculate end date based on plan (1 month from now for paid plans)
    $end_date = null;
    if ($new_plan !== 'free') {
        $end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
    }
    
    // Update subscription plan and end date
    $stmt = $pdo->prepare("UPDATE subscriptions SET plan_name = ?, end_date = ? WHERE user_id = ?");
    $stmt->execute([$new_plan, $end_date, $user_id]);
    
    // Update credits based on plan
    $credits_map = [
        'free' => 1000,
        'pro' => 2000,
        'enterprise' => 10000
    ];
    
    $new_credits = $credits_map[$new_plan];
    $stmt = $pdo->prepare("UPDATE subscriptions SET credits_total = ?, credits_remaining = ? WHERE user_id = ?");
    $stmt->execute([$new_credits, $new_credits, $user_id]);
    
    // Log the admin action
    $details = json_encode([
        'admin_id' => $admin_id,
        'admin_name' => $admin_name,
        'action' => 'plan_changed',
        'old_plan' => 'N/A', 
        'new_plan' => $new_plan,
        'end_date' => $end_date,
        'user_name' => $user_name,
        'user_email' => $user_email
    ]);
    
    $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        'plan_changed_by_admin',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
    
    // Log in admin actions table 
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$admin_id, 'subscription_plan_changed', $user_id, $details]);
    
    $message = "Subscription plan changed to {$new_plan} successfully";
    if ($end_date) {
        $message .= " (expires on " . date('Y-m-d', strtotime($end_date)) . ")";
    }
    break;
            
        case 'pause':
            $reason = $input['reason'] ?? '';
            $duration = $input['duration'] ?? '1month';
            
            // Calculate pause end date
            $pause_end = date('Y-m-d H:i:s');
            switch ($duration) {
                case '1week':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+1 week'));
                    break;
                case '2weeks':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+2 weeks'));
                    break;
                case '1month':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+1 month'));
                    break;
                case '3months':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+3 months'));
                    break;
            }
            
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'paused' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_paused',
                'reason' => $reason,
                'duration' => $duration,
                'pause_end' => $pause_end,
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_paused_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_paused', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'paused', $details, $admin_id]);
            
            $message = "Subscription paused successfully";
            break;
            
        case 'cancel':
            $reason = $input['reason'] ?? '';
            
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled', is_active = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_cancelled',
                'reason' => $reason,
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_cancelled_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_cancelled', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'cancelled', $details, $admin_id]);
            
            $message = "Subscription cancelled successfully";
            break;
            
        case 'resume':
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'active', is_active = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_resumed',
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_resumed_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_resumed', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'resumed', $details, $admin_id]);
            
            $message = "Subscription resumed successfully";
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Subscription update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>