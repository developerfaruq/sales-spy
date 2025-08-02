<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "signup.php?form=login&status=session_expired");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['session_id'])) {
    $session_id = $_POST['session_id'];
    $current_session = session_id();

    // Don't allow user to delete their current session
    if ($session_id !== $current_session) {
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id = ?");
        $stmt->execute([$_SESSION['user_id'], $session_id]);
    }
}

header("Location: settings.php?status=session_signed_out");
exit;
?>
