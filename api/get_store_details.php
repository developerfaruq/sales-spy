<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Assuming this file exists for auth check

header('Content-Type: application/json');

// Authentication Guard
if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Sanitize and validate input
$store_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$store_id) {
    echo json_encode(['error' => 'Invalid store ID.']);
    exit();
}

$sql = "SELECT * FROM stores WHERE id = :id LIMIT 1";
$params = [':id' => $store_id];

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($store) {
        echo json_encode($store);
    } else {
        echo json_encode(['error' => 'Store not found.']);
    }

} catch (PDOException $e) {
    $error_message = 'Database error in get_store_details.php: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/error.log', date('Y-m-d H:i:s') . ' - ' . $error_message . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'An error occurred while fetching store details.']);
}

function is_logged_in() {
    // This is a placeholder. Implement actual session-based authentication.
    // For development, we'll assume true. In production, check $_SESSION['user_id'] etc.
    return true; // For now, always true for development/testing
} 