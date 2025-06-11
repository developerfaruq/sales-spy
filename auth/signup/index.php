<?php
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        header('Location:' .BASE_URL. 'signup.html?form=signup&status=empty_fields');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location:' .BASE_URL. 'signup.html?form=signup&status=invalid_email');
        exit;
    }

    // Validate password match
    if ($password !== $confirm_password) {
        header('Location:' .BASE_URL. 'signup.html?form=signup&status=password_mismatch');
        exit;
    }

    // Validate password strength
    if (strlen($password) < 8) {
        header('Location:' .BASE_URL. 'signup.html?form=signup&status=weak_password');
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header('Location:' .BASE_URL.  'signup.html?form=signup&status=email_exists');
        exit;
    }

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone, $hashed_password]);

    header('Location:' .BASE_URL. 'signup.html?form=login&status=signup_success');
    exit;
}

header('Location:' .BASE_URL. 'signup.html?form=signup');
exit;
?>