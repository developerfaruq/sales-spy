<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Guard
if (!is_logged_in()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized access. Please log in.']);
    exit();
}

// Sanitize and validate inputs
$platform = isset($_GET['platform']) ? filter_var($_GET['platform'], FILTER_SANITIZE_STRING) : '';
$created_after = isset($_GET['created_after']) ? filter_var($_GET['created_after'], FILTER_SANITIZE_STRING) : '';
$created_before = isset($_GET['created_before']) ? filter_var($_GET['created_before'], FILTER_SANITIZE_STRING) : '';
$keyword = isset($_GET['keyword']) ? filter_var($_GET['keyword'], FILTER_SANITIZE_STRING) : '';

$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
$limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 10;
$sort_by = isset($_GET['sort_by']) ? filter_var($_GET['sort_by'], FILTER_SANITIZE_STRING) : 'added_on';
$order = isset($_GET['order']) ? filter_var($_GET['order'], FILTER_SANITIZE_STRING) : 'DESC';

// Ensure valid sort_by and order values to prevent SQL injection
$allowedSortBy = ['name', 'url', 'platform', 'country', 'category', 'creation_date', 'added_on'];
if (!in_array($sort_by, $allowedSortBy)) {
    $sort_by = 'added_on';
}

$allowedOrder = ['ASC', 'DESC'];
if (!in_array(strtoupper($order), $allowedOrder)) {
    $order = 'DESC';
}

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM websites WHERE 1=1";
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
    $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM websites WHERE 1=1";
    $countParams = [];

    if (!empty($platform)) {
        $countSql .= " AND platform = :platform";
        $countParams[':platform'] = $platform;
    }
    if (!empty($created_after)) {
        $countSql .= " AND creation_date >= :created_after";
        $countParams[':created_after'] = $created_after;
    }
    if (!empty($created_before)) {
        $countSql .= " AND creation_date <= :created_before";
        $countParams[':created_before'] = $created_before;
    }
    if (!empty($keyword)) {
        $countSql .= " AND (name LIKE :keyword OR url LIKE :keyword OR category LIKE :keyword)";
        $countParams[':keyword'] = '%' . $keyword . '%';
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalWebsites = $countStmt->fetchColumn();

    $totalPages = ceil($totalWebsites / $limit);

    echo json_encode([
        'websites' => $websites,
        'pagination' => [
            'total_results' => $totalWebsites,
            'total_pages' => $totalPages,
            'page' => $page,
            'limit' => $limit
        ]
    ]);

} catch (PDOException $e) {
    $error_message = 'Database error in filter_websites.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'An error occurred while fetching data.']);
}

// Authentication is now handled by includes/auth_check.php
// No need for a local is_logged_in() function