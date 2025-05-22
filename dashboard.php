<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
<p>You are now logged in.</p>
<a href="logout.php">Logout</a>
</body>
</html>
