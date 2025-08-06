<?php
require '../../../config/db.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get parameters from request
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $payment_type_filter = isset($_GET['payment_type']) ? $_GET['payment_type'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $amount_min = isset($_GET['amount_min']) ? floatval($_GET['amount_min']) : null;
    $amount_max = isset($_GET['amount_max']) ? floatval($_GET['amount_max']) : null;

    // Build WHERE clause
    $where_conditions = ['user_id = ?'];
    $params = [$user_id];

    // Status filter
    if (!empty($status_filter) && in_array($status_filter, ['pending', 'success', 'failed'])) {
        $where_conditions[] = 'status = ?';
        $params[] = $status_filter;
    }

    // Payment type filter
    if (!empty($payment_type_filter)) {
        $where_conditions[] = 'payment_type LIKE ?';
        $params[] = '%' . $payment_type_filter . '%';
    }

    // Date range filter
    if (!empty($date_from)) {
        $where_conditions[] = 'DATE(created_at) >= ?';
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $where_conditions[] = 'DATE(created_at) <= ?';
        $params[] = $date_to;
    }

    // Amount range filter
    if ($amount_min !== null) {
        $where_conditions[] = 'amount >= ?';
        $params[] = $amount_min;
    }
    if ($amount_max !== null) {
        $where_conditions[] = 'amount <= ?';
        $params[] = $amount_max;
    }

    // Search filter (transaction ID or amount)
    if (!empty($search)) {
        $where_conditions[] = '(txid LIKE ? OR CAST(amount AS CHAR) LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM transactions $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get transactions with pagination
    $sql = "SELECT id, txid, payment_type, amount, status, created_at 
            FROM transactions 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format transactions for frontend
    $formatted_transactions = array_map(function ($tx) {
        // Map payment types to display names
        $payment_type_display = $tx['payment_type'];
        if (strtolower($tx['payment_type']) === 'crypto' || strtolower($tx['payment_type']) === 'crupto') {
            $payment_type_display = 'Cryptocurrency';
        } elseif (strtolower($tx['payment_type']) === 'card') {
            $payment_type_display = 'Credit Card';
        }

        // Map status to display format
        $status_display = ucfirst($tx['status']);
        if ($tx['status'] === 'success') {
            $status_display = 'Success';
        } elseif ($tx['status'] === 'pending') {
            $status_display = 'Pending';
        } elseif ($tx['status'] === 'failed') {
            $status_display = 'Failed';
        }

        return [
            'id' => 'TXN-' . str_pad($tx['id'], 6, '0', STR_PAD_LEFT),
            'db_id' => $tx['id'],
            'txid' => $tx['txid'],
            'payment_type' => $payment_type_display,
            'payment_type_raw' => $tx['payment_type'],
            'amount' => floatval($tx['amount']),
            'status' => $status_display,
            'status_raw' => $tx['status'],
            'created_at' => $tx['created_at'],
            'date' => date('M j, Y', strtotime($tx['created_at'])),
            'time' => date('H:i T', strtotime($tx['created_at'])),
            'formatted_amount' => '$' . number_format($tx['amount'], 2),
            'description' => $payment_type_display . ' Payment'
        ];
    }, $transactions);

    // Calculate pagination info
    $total_pages = ceil($total_records / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formatted_transactions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $limit,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ],
        'filters' => [
            'status' => $status_filter,
            'payment_type' => $payment_type_filter,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'search' => $search,
            'amount_min' => $amount_min,
            'amount_max' => $amount_max
        ]
    ]);
} catch (PDOException $e) {
    error_log("Transaction fetch error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching transactions'
    ]);
}
