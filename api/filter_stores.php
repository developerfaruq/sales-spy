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
$platform = isset($_GET['platform']) ? trim(strip_tags($_GET['platform'])) : '';
$created_after = isset($_GET['created_after']) ? trim(strip_tags($_GET['created_after'])) : '';
$created_before = isset($_GET['created_before']) ? trim(strip_tags($_GET['created_before'])) : '';
$keyword = isset($_GET['keyword']) ? trim(strip_tags($_GET['keyword'])) : '';
$sns_email = isset($_GET['sns_email']) ? trim(strip_tags($_GET['sns_email'])) : '';

$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
$limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 10;
$sort_by = isset($_GET['sort_by']) ? trim(strip_tags($_GET['sort_by'])) : 'date_added';
$order = isset($_GET['order']) ? trim(strip_tags($_GET['order'])) : 'DESC';

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

    // Mapping backend keys to frontend keys
    foreach ($stores as &$store) {
        $store['facebook'] = $store['facebook_url'] ?? '';
        $store['twitter'] = $store['twitter_url'] ?? '';
        $store['instagram'] = $store['instagram_url'] ?? '';
        $store['linkedin'] = $store['linkedin_url'] ?? '';
        $store['pinterest'] = $store['pinterest_url'] ?? '';
    }

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
            'page' => $page,
            'limit' => $limit,
            'total_stores' => $totalStores,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ]
    ]);

} catch (PDOException $e) {
    $error_message = 'Database error in filter_stores.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'An error occurred while fetching data.']);
}

// is_logged_in() function is already defined in auth_check.php