<?php
session_start();
require '../../config/db.php';

if (isset($_POST['remember'])) {
    // Set a long-lasting cookie
    setcookie("admin_remember", $admin['id'], time() + (86400 * 30), "/"); // 30 days
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        $_SESSION['admin_login_error'] = "Invalid email or password.";
        header("Location: ../../index.php");
        exit;
    }

    // Success: start admin session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];

    header("Location: ../../home/");
    exit;
}



?>
