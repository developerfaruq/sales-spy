<?php
header('Content-Type: application/json');
require '../config/db.php';
session_start();

// ====== Session & Auth Validation ======
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_agent']) || !isset($_SESSION['ip_address'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please log in again.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$session_id = session_id();

// Validate session from DB
try {
    $stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->execute([$user_id, $session_id]);
    $session_valid = $stmt->fetch();

    if (!$session_valid) {
        session_destroy();
        echo json_encode([
            'success' => false,
            'message' => 'Session has been revoked. Please log in again.'
        ]);
        exit;
    }

    // Update last_active timestamp
    $stmt = $pdo->prepare("UPDATE user_sessions SET last_active = NOW() WHERE session_id = ?");
    $stmt->execute([$session_id]);

} catch (PDOException $e) {
    error_log("Auth DB error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Authentication error'
    ]);
    exit;
}

// ====== Fetch and Return Plans ======
try {
    $stmt = $pdo->prepare("SELECT 
            plan_name, 
            description, 
            monthly_price, 
            yearly_price, 
            leads_per_month, 
            features, 
            is_popular 
        FROM plans 
        WHERE is_active = 1");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($plans)) {
        echo json_encode([
            'success' => false,
            'message' => 'No active subscription plans available'
        ]);
        exit;
    }

    $processedPlans = [];
    foreach ($plans as $plan) {
        $processedPlans[] = [
            'plan_name' => $plan['plan_name'],
            'description' => $plan['description'],
            'monthly_price' => (float)$plan['monthly_price'],
            'yearly_price' => (float)$plan['yearly_price'],
            'leads_per_month' => (int)$plan['leads_per_month'],
            'features' => json_decode($plan['features'] ?? '[]', true),
            'is_popular' => (bool)$plan['is_popular']
        ];
    }

    echo json_encode([
        'success' => true,
        'plans' => $processedPlans
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Payment plans DB error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve subscription plans'
    ]);
    exit;
}
?>
