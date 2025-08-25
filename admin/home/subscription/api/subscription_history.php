<?php
require '../../../config/db.php';
require 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    // Get user info
    $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Get subscription history from security logs and transactions
    $historyStmt = $pdo->prepare("
        SELECT 
            'security_log' as source,
            event_type,
            details,
            created_at,
            CASE 
                WHEN event_type LIKE '%suspended%' THEN 'suspended'
                WHEN event_type LIKE '%paused%' THEN 'paused'
                WHEN event_type LIKE '%cancelled%' THEN 'cancelled'
                WHEN event_type LIKE '%resumed%' THEN 'active'
                WHEN event_type LIKE '%plan_changed%' THEN 'active'
                ELSE 'unknown'
            END as status
        FROM security_logs 
        WHERE user_id = ? 
        AND (event_type LIKE '%subscription%' OR event_type LIKE '%plan_%')
        
        UNION ALL
        
        SELECT 
            'transaction' as source,
            CONCAT('Payment ', status) as event_type,
            CONCAT('Amount: $', amount, ' - TXID: ', txid) as details,
            created_at,
            CASE 
                WHEN status = 'success' THEN 'active'
                WHEN status = 'pending' THEN 'pending'
                ELSE 'failed'
            END as status
        FROM transactions 
        WHERE user_id = ?
        
        ORDER BY created_at DESC
        LIMIT 20
    ");
    
    $historyStmt->execute([$userId, $userId]);
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the history for display
    $formattedHistory = [];
    foreach ($history as $item) {
        $details = '';
        if ($item['source'] === 'security_log' && $item['details']) {
            $detailsArray = json_decode($item['details'], true);
            if ($detailsArray) {
                if (isset($detailsArray['reason']) && $detailsArray['reason']) {
                    $details = 'Reason: ' . $detailsArray['reason'];
                }
                if (isset($detailsArray['new_plan'])) {
                    $details = 'Changed to: ' . ucfirst($detailsArray['new_plan']);
                }
                if (isset($detailsArray['duration'])) {
                    $details .= ($details ? ' | ' : '') . 'Duration: ' . $detailsArray['duration'];
                }
            }
        } elseif ($item['source'] === 'transaction') {
            $details = $item['details'];
        }

        $formattedHistory[] = [
            'event_type' => $item['event_type'],
            'details' => $details,
            'created_at' => $item['created_at'],
            'status' => $item['status']
        ];
    }

    // Get current subscription info
    $currentSubStmt = $pdo->prepare("
        SELECT plan_name, status, start_date, end_date, credits_remaining, leads_balance 
        FROM subscriptions 
        WHERE user_id = ? 
        ORDER BY start_date DESC 
        LIMIT 1
    ");
    $currentSubStmt->execute([$userId]);
    $currentSub = $currentSubStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'user' => $user,
        'current_subscription' => $currentSub,
        'history' => $formattedHistory
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch subscription history: ' . $e->getMessage()
    ]);
}