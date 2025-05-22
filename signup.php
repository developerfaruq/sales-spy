<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        header("Location: signup.html?form=signup&status=error");
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header("Location: signup.html?form=signup&status=email_exists");
        exit;
    }

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone, $hashed_password]);

    header("Location: signup.html?form=login&status=signup_success");
    exit;
}
?>
