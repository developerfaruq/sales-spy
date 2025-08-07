<?php
require '../../../config/db.php';
session_start(); // Start session to access $_SESSION

// Use session user_id or hardcode temporarily
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 25;

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $transactions
]);
