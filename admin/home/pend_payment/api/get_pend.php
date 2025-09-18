<?php

require '../../../config/db.php';
require '../../subscription/api/auth_check.php';



try {
    // Get pending transactions with user details
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.txid,
            t.amount,
            t.payment_type,
            t.created_at,
            t.order_id,
            t.screenshot_path,
            u.full_name,
            u.email,
            CONCAT(UPPER(LEFT(u.full_name, 1)), UPPER(SUBSTRING(u.full_name, LOCATE(' ', u.full_name) + 1, 1))) as initials,
            p.plan_name
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN plans p ON p.monthly_price = t.amount OR p.yearly_price = t.amount
        WHERE t.status = 'pending'
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for frontend
    $formatted_transactions = [];
    foreach ($transactions as $transaction) {
        $formatted_transactions[] = [
            'id' => $transaction['id'],
            'userId' => $transaction['initials'],
            'userName' => $transaction['full_name'],
            'userEmail' => $transaction['email'],
            'orderId' => $transaction['order_id'],
            'paymentMethod' => ucfirst($transaction['payment_type']),
            'amount' => '$' . number_format($transaction['amount'], 2),
            'rawAmount' => $transaction['amount'],
            'date' => date('M j, Y', strtotime($transaction['created_at'])),
            'transactionId' => $transaction['txid'],
            'screenshot' => $transaction['screenshot_path'] ? '../../../../home/payment/api/' . $transaction['screenshot_path'] : '',
            'planName' => $transaction['plan_name'] ? ucfirst($transaction['plan_name']) : 'Basic'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'transactions' => $formatted_transactions
    ]);
    
} catch (Exception $e) {
    error_log("Get pending payments error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while fetching payments'
    ]);
}
?>