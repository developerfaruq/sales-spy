<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Guard
if (!is_logged_in()) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access. Please log in.']);
    exit();
}

// Sanitize and validate inputs
$platform = isset($_GET['platform']) ? filter_var($_GET['platform'], FILTER_SANITIZE_STRING) : '';
$created_after = isset($_GET['created_after']) ? filter_var($_GET['created_after'], FILTER_SANITIZE_STRING) : '';
$created_before = isset($_GET['created_before']) ? filter_var($_GET['created_before'], FILTER_SANITIZE_STRING) : '';
$keyword = isset($_GET['keyword']) ? filter_var($_GET['keyword'], FILTER_SANITIZE_STRING) : '';

$sql = "SELECT name, url, platform, category, country, creation_date, added_on FROM websites WHERE 1=1";
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

if (!empty($keyword)) {
    $sql .= " AND (name LIKE :keyword OR url LIKE :keyword OR category LIKE :keyword)";
    $params[':keyword'] = '%' . $keyword . '%';
}

$sql .= " ORDER BY added_on DESC";

try {
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => &$val) {
        if (strpos($key, 'keyword') !== false) {
            $stmt->bindParam($key, $val, PDO::PARAM_STR);
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
    fputcsv($output, ['name', 'url', 'platform', 'category', 'country', 'creation_date', 'added_on']);

    // Add data rows
    foreach ($websites as $row) {
        fputcsv($output, $row);
    }

    fclose($output);

} catch (PDOException $e) {
    $error_message = 'Database error in export_websites.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'An error occurred while exporting data.']);
}

// Authentication is now handled by includes/auth_check.php
// No need for a local is_logged_in() function