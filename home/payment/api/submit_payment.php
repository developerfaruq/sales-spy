<?php
session_start();
require_once '../../../config/db.php'; // assumes $pdo is already available

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$txid = $_POST['txid'] ?? '';
$amount = $_POST['amount'] ?? '';
$plan_name = $_POST['plan_name'] ?? '';
$payment_type = 'crypto';
$status = 'pending';
$created_at = date('Y-m-d H:i:s');

// Basic validation
if (empty($txid) || empty($amount)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing TXID or amount."]);
    exit;
}

// ✅ Check if TXID already exists
try {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE txid = :txid");
    $checkStmt->execute([':txid' => $txid]);
    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "This TXID has already been used."]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error during TXID check: " . $e->getMessage()]);
    exit;
}

// Handle screenshot upload
$screenshot_path = null;
if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/payment_screenshots/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('ss_') . '_' . basename($_FILES['screenshot']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetPath)) {
        $screenshot_path = $targetPath;
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to upload screenshot."]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No valid screenshot uploaded."]);
    exit;
}

// Insert transaction into database
try {
    $stmt = $pdo->prepare("INSERT INTO transactions 
        (user_id, txid, payment_type, amount, status, created_at, screenshot_path) 
        VALUES (:user_id, :txid, :payment_type, :amount, :status, :created_at, :screenshot_path)");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':txid' => $txid,
        ':payment_type' => $payment_type,
        ':amount' => $amount,
        ':status' => $status,
        ':created_at' => $created_at,
        ':screenshot_path' => $screenshot_path
    ]);

    echo json_encode(["success" => true, "message" => "Transaction submitted successfully and awaiting admin approval."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
