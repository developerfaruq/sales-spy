<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

// Authentication Guard
if (!is_logged_in()) {
    die("Unauthorized access.");
}

// Sanitize and validate inputs
$platform = isset($_GET['platform']) ? filter_var($_GET['platform'], FILTER_SANITIZE_STRING) : '';
$created_after = isset($_GET['created_after']) ? filter_var($_GET['created_after'], FILTER_SANITIZE_STRING) : '';
$created_before = isset($_GET['created_before']) ? filter_var($_GET['created_before'], FILTER_SANITIZE_STRING) : '';
$sort_by = isset($_GET['sort_by']) ? filter_var($_GET['sort_by'], FILTER_SANITIZE_STRING) : 'added_on';
$order = isset($_GET['order']) ? filter_var($_GET['order'], FILTER_SANITIZE_STRING) : 'DESC';
$limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 1000;
$offset = isset($_GET['offset']) ? filter_var($_GET['offset'], FILTER_VALIDATE_INT) : 0;

$allowedSortBy = ['name', 'url', 'platform', 'country', 'category', 'creation_date', 'added_on'];
if (!in_array($sort_by, $allowedSortBy)) {
    $sort_by = 'added_on';
}

$allowedOrder = ['ASC', 'DESC'];
if (!in_array(strtoupper($order), $allowedOrder)) {
    $order = 'DESC';
}

$sql = "SELECT name, url, platform, country, category, creation_date FROM websites WHERE 1=1";
$params = [];

if (!empty($platform)) {
    $sql .= " AND platform = :platform";
    $params[':platform'] = $platform;
}
if (!empty($created_after)) {
    $sql .= " AND creation_date >= :created_after";
    $params[':created_after'] = $created_after;
}
if (!empty($created_before)) {
    $sql .= " AND creation_date <= :created_before";
    $params[':created_before'] = $created_before;
}

$sql .= " ORDER BY $sort_by $order LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        if (in_array($key, [':limit', ':offset'])) {
            $stmt->bindParam($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $val);
        }
    }
    $stmt->execute();
    $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "websites_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, ['name', 'url', 'platform', 'country', 'category', 'creation_date']);

    // Add data rows
    foreach ($websites as $row) {
        fputcsv($output, $row);
    }

    fclose($output);

} catch (PDOException $e) {
    $error_message = 'Database error in export_websites.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    die("Error exporting data.");
}

function is_logged_in() {
    // Placeholder for session-based authentication
    return true;
} 