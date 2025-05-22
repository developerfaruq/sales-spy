<?php
require 'db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid or missing token.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("This reset link is invalid or expired.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->execute([$hashed, $token]);

    echo "✅ Password reset successful! <a href='signup.html?form=login'>Click here to login</a>";
    exit;
}
?>

<!-- Simple HTML form for resetting password -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
<h2>Reset Your Password</h2>
<form method="POST">
    <input type="password" name="password" placeholder="Enter new password" required><br><br>
    <button type="submit">Reset Password</button>
</form>
</body>
</html>
