<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Assuming this file exists for auth check

// Authentication Guard
if (!is_logged_in()) {
    die("Unauthorized access.");
}

// Sanitize and validate inputs
$platform = isset($_GET['platform']) ? filter_var($_GET['platform'], FILTER_SANITIZE_STRING) : '';
$created_after = isset($_GET['created_after']) ? filter_var($_GET['created_after'], FILTER_SANITIZE_STRING) : '';
$created_before = isset($_GET['created_before']) ? filter_var($_GET['created_before'], FILTER_SANITIZE_STRING) : '';
$keyword = isset($_GET['keyword']) ? filter_var($_GET['keyword'], FILTER_SANITIZE_STRING) : '';
$sns_email = isset($_GET['sns_email']) ? filter_var($_GET['sns_email'], FILTER_SANITIZE_STRING) : '';

$sql = "SELECT domain, title, tech_stack as platform, country, categories as category, date_added as creation_date FROM stores WHERE 1=1";
$params = [];

if (!empty($platform)) {
    $sql .= " AND tech_stack = :platform";
    $params[':platform'] = $platform;
}

if (!empty($created_after)) {
    $sql .= " AND date_added >= :created_after";
    $params[':created_after'] = $created_after;
}

if (!empty($created_before)) {
    $sql .= " AND date_added <= :created_before";
    $params[':created_before'] = $created_before;
}

if (!empty($keyword)) {
    $sql .= " AND (domain LIKE :keyword OR title LIKE :keyword OR description LIKE :keyword OR categories LIKE :keyword)";
    $params[':keyword'] = '%' . $keyword . '%';
}

if (!empty($sns_email)) {
    switch ($sns_email) {
        case 'facebook':
            $sql .= " AND facebook_url != ''";
            break;
        case 'instagram':
            $sql .= " AND instagram_url != ''";
            break;
        case 'twitter':
            $sql .= " AND twitter_url != ''";
            break;
        case 'youtube':
            $sql .= " AND youtube_url != ''";
            break;
        case 'tiktok':
            $sql .= " AND tiktok_url != ''";
            break;
        case 'pinterest':
            $sql .= " AND pinterest_url != ''";
            break;
        case 'email':
            $sql .= " AND contact_email != ''";
            break;
        // Add more cases as needed for other SNS platforms
    }
}

$sql .= " ORDER BY date_added DESC";

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
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "ecommerce_stores_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, ['name', 'url', 'platform', 'country', 'category', 'creation_date']);

    // Add data rows
    foreach ($stores as $row) {
        fputcsv($output, $row);
    }

    fclose($output);

} catch (PDOException $e) {
    $error_message = 'Database error in export_csv.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    die("Error exporting data.");
}

function is_logged_in() {
    // This is a placeholder. Implement actual session-based authentication.
    // For development, we'll assume true. In production, check $_SESSION['user_id'] etc.
    return true; // For now, always true for development/testing
} 