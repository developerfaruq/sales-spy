<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Assuming this file exists for auth check

header('Content-Type: application/json');

// Authentication Guard
if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Sanitize and validate inputs
$platform = isset($_GET['platform']) ? filter_var($_GET['platform'], FILTER_SANITIZE_STRING) : '';
$created_after = isset($_GET['created_after']) ? filter_var($_GET['created_after'], FILTER_SANITIZE_STRING) : '';
$created_before = isset($_GET['created_before']) ? filter_var($_GET['created_before'], FILTER_SANITIZE_STRING) : '';
$keyword = isset($_GET['keyword']) ? filter_var($_GET['keyword'], FILTER_SANITIZE_STRING) : '';
$sns_email = isset($_GET['sns_email']) ? filter_var($_GET['sns_email'], FILTER_SANITIZE_STRING) : '';

$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
$limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 10;
$sort_by = isset($_GET['sort_by']) ? filter_var($_GET['sort_by'], FILTER_SANITIZE_STRING) : 'date_added';
$order = isset($_GET['order']) ? filter_var($_GET['order'], FILTER_SANITIZE_STRING) : 'DESC';

// Ensure valid sort_by and order values to prevent SQL injection
$allowedSortBy = ['domain', 'title', 'tech_stack', 'country', 'product_count', 'avg_price', 'date_added'];
if (!in_array($sort_by, $allowedSortBy)) {
    $sort_by = 'date_added';
}

$allowedOrder = ['ASC', 'DESC'];
if (!in_array(strtoupper($order), $allowedOrder)) {
    $order = 'DESC';
}

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM stores WHERE 1=1";
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

// Add pagination and sorting
$sql .= " ORDER BY ".$sort_by." ".$order." LIMIT :limit OFFSET :offset";
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
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM stores WHERE 1=1";
    $countParams = [];

    if (!empty($platform)) {
        $countSql .= " AND tech_stack = :platform";
        $countParams[':platform'] = $platform;
    }
    if (!empty($created_after)) {
        $countSql .= " AND date_added >= :created_after";
        $countParams[':created_after'] = $created_after;
    }
    if (!empty($created_before)) {
        $countSql .= " AND date_added <= :created_before";
        $countParams[':created_before'] = $created_before;
    }
    if (!empty($keyword)) {
        $countSql .= " AND (domain LIKE :keyword OR title LIKE :keyword OR description LIKE :keyword OR categories LIKE :keyword)";
        $countParams[':keyword'] = '%' . $keyword . '%';
    }
    if (!empty($sns_email)) {
        switch ($sns_email) {
            case 'facebook':
                $countSql .= " AND facebook_url != ''";
                break;
            case 'instagram':
                $countSql .= " AND instagram_url != ''";
                break;
            case 'twitter':
                $countSql .= " AND twitter_url != ''";
                break;
            case 'youtube':
                $countSql .= " AND youtube_url != ''";
                break;
            case 'tiktok':
                $countSql .= " AND tiktok_url != ''";
                break;
            case 'pinterest':
                $countSql .= " AND pinterest_url != ''";
                break;
            case 'email':
                $countSql .= " AND contact_email != ''";
                break;
        }
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalStores = $countStmt->fetchColumn();

    $totalPages = ceil($totalStores / $limit);

    echo json_encode([
        'stores' => $stores,
        'pagination' => [
            'total_results' => $totalStores,
            'total_pages' => $totalPages,
            'page' => $page,
            'limit' => $limit
        ]
    ]);

} catch (PDOException $e) {
    $error_message = 'Database error in filter_stores.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'An error occurred while fetching data.']);
}

function is_logged_in() {
    // This is a placeholder. Implement actual session-based authentication.
    // For development, we'll assume true. In production, check $_SESSION['user_id'] etc.
    return true; // For now, always true for development/testing
} 