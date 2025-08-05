<?php
require '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "signup.php?form=login&status=session_expired");
    exit;
}

// Delete all sessions for this user
$stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Destroy current session
session_destroy();

// Redirect to login
header("Location: " . BASE_URL . "signup.php?form=login&status=all_signed_out");
exit;
?>
