<?php
require 'db.php';
require 'vendor/autoload.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);

        // Return reset link (replace this later with email sending)
        echo "Reset link: <a href='reset_password.php?token=$token' target='_blank'>Click to reset password</a>";
    } else {
        echo "❌ Email not found in our system.";
    }
}
?>
