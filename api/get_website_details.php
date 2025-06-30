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

// Sanitize and validate input
$website_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$website_id) {
    echo json_encode(['error' => 'Invalid website ID.']);
    exit();
}

$sql = "SELECT * FROM websites WHERE id = :id LIMIT 1";
$params = [':id' => $website_id];

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $website = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($website) {
        echo json_encode($website);
    } else {
        echo json_encode(['error' => 'Website not found.']);
    }

} catch (PDOException $e) {
    $error_message = 'Database error in get_website_details.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'An error occurred while fetching website details.']);
} 