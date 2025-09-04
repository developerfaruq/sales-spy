<?php
require '../../../config/db.php';
require 'auth_check.php';

header('Content-Type: application/json');

try {
    // Get all subscriptions with user details
    $stmt = $pdo->query("
        SELECT 
            s.id,
            s.user_id,
            s.plan_name,
            s.status,
            s.start_date,
            s.end_date,
            s.is_active,
            s.leads_balance,
            u.full_name,
            u.email,
            u.created_at as user_created_at
        FROM subscriptions s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.start_date DESC
    ");
    
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $statsQuery = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE account_status = 'active') as total_users,
            (SELECT COUNT(*) FROM subscriptions WHERE status = 'active') as active_subscriptions,
            (SELECT COUNT(*) FROM subscriptions WHERE status = 'expired') as expired_subscriptions,
            (SELECT COUNT(*) FROM subscriptions WHERE status = 'cancelled') as cancelled_subscriptions,
            (SELECT COUNT(*) FROM subscriptions WHERE status = 'paused') as paused_subscriptions
    ");
    
    $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
    
    // Calculate monthly revenue
    $revenueQuery = $pdo->query("
        SELECT 
            SUM(CASE 
                WHEN s.plan_name = 'pro' THEN 50 
                WHEN s.plan_name = 'enterprise' THEN 150 
                
            END) as monthly_revenue
        FROM subscriptions s 
        WHERE s.status = 'active'
    ");
    
    $revenue = $revenueQuery->fetch(PDO::FETCH_ASSOC);
    $stats['monthly_revenue'] = $revenue['monthly_revenue'] ?? 0;

    echo json_encode([
        'success' => true,
        'subscriptions' => $subscriptions,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch subscriptions: ' . $e->getMessage()
    ]);
}