<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$platform = isset($_GET['platform']) ? $_GET['platform'] : '';
$created_after = isset($_GET['created_after']) ? $_GET['created_after'] : '';
$created_before = isset($_GET['created_before']) ? $_GET['created_before'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // Build the query
    $query = "SELECT * FROM stores WHERE 1=1";
    $params = [];

    if ($platform) {
        $query .= " AND tech_stack LIKE ?";
        $params[] = "%$platform%";
    }

    if ($created_after) {
        $query .= " AND date_added >= ?";
        $params[] = $created_after;
    }

    if ($created_before) {
        $query .= " AND date_added <= ?";
        $params[] = $created_before;
    }

    // Add pagination
    $query .= " ORDER BY date_added DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // Execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countQuery = str_replace("SELECT *", "SELECT COUNT(*)", $query);
    $countQuery = preg_replace('/LIMIT.*$/', '', $countQuery);
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute(array_slice($params, 0, -2));
    $total = $stmt->fetchColumn();

    // Log the search
    $logStmt = $pdo->prepare("INSERT INTO search_logs (user_id, filters_used) VALUES (?, ?)");
    $filters = json_encode([
        'platform' => $platform,
        'created_after' => $created_after,
        'created_before' => $created_before
    ]);
    $logStmt->execute([$_SESSION['user_id'], $filters]);

    // Return the results
    echo json_encode([
        'stores' => $stores,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Filter stores error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 