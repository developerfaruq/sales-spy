<?php
require '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "signup.html?form=login&status=session_expired");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['session_id'])) {
    $session_id = $_POST['session_id'];

    // Remove from DB
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->execute([$_SESSION['user_id'], $session_id]);

    // Optionally: clean up PHP session files if stored in files
    // session_id($session_id);
    // session_start();
    // session_destroy();
}

header("Location: settings.php?status=session_signed_out");
exit;
?>
