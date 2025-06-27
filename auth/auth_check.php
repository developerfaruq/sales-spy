<?php 
require '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html?form=login&status=session_expired");
    exit;
}
// Update last_active
$stmt = $pdo->prepare("UPDATE user_sessions SET last_active = NOW() WHERE session_id = ?");
$stmt->execute([session_id()]);

?>