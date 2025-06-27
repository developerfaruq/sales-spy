<?php
require '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "signup.html?form=login&status=session_expired");
    exit;
}

// Delete all user sessions except current
$stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Destroy current session too
session_destroy();

header("Location: " . BASE_URL . "signup.html?form=login&status=all_signed_out");
exit;
?>
