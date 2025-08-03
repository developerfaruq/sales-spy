<?php
require '../auth/auth_check.php';

header('Content-Type: application/json');

try {
    // Verify that PDO connection is available (from auth_check.php)
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }

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

    // Process each plan
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

} catch (PDOException $e) {
    error_log("Payment plans error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve subscription plans'
    ]);
} catch (Exception $e) {
    error_log("General error in payment.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
exit;
?>