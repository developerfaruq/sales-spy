<?php 
require '../config/db.php';
session_start();

// Check if session variables exist
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_agent']) || !isset($_SESSION['ip_address'])) {
    session_destroy();
    header("Location: ../signup.php?form=login&status=session_expired");
    exit;
}

$user_id = $_SESSION['user_id'];
$session_id = session_id();

// Validate session from DB
$stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_id = ?");
$stmt->execute([$user_id, $session_id]);
$session_valid = $stmt->fetch();

if (!$session_valid) {
    session_destroy();
    header("Location: ../signup.php?form=login&status=session_revoked");
    exit;
}

//Update last_active timestamp
$stmt = $pdo->prepare("UPDATE user_sessions SET last_active = NOW() WHERE session_id = ?");
$stmt->execute([$session_id]);
?>
