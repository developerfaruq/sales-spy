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

    // Execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the export
    $logStmt = $pdo->prepare("INSERT INTO exports (user_id, store_count) VALUES (?, ?)");
    $logStmt->execute([$_SESSION['user_id'], count($stores)]);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stores_export_' . date('Y-m-d') . '.csv"');

    // Create CSV file
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add headers
    fputcsv($output, [
        'Store Name',
        'Domain',
        'Platform',
        'Country',
        'Categories',
        'Product Count',
        'Average Price',
        'Contact Email',
        'Contact Phone',
        'Date Added'
    ]);

    // Add data
    foreach ($stores as $store) {
        fputcsv($output, [
            $store['title'],
            $store['domain'],
            $store['tech_stack'],
            $store['country'],
            $store['categories'],
            $store['product_count'],
            $store['avg_price'],
            $store['contact_email'],
            $store['contact_phone'],
            $store['date_added']
        ]);
    }

    fclose($output);

} catch (PDOException $e) {
    error_log("Export CSV error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 