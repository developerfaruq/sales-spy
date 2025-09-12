<?php
require '../config/db.php'; 
session_start();

// Get the current session ID before destroying it
$session_id = session_id();

// Delete the session from the database
$stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_id = ?");
$stmt->execute([$session_id]);

// Destroy PHP session
session_unset();
session_destroy();

// Redirect to login/signup page
header('Location: ../signup.php');
exit;
?>
