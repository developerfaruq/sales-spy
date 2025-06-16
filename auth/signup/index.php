<?php
require '../../config/db.php';
session_start();

// Function to generate a secure API key
function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    // Validation: Check for empty fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=empty_fields');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=invalid_email');
        exit;
    }

    // Validate phone format (digits, optional + or -, 10-20 characters)
    if (!preg_match('/^[0-9+\-\s]{10,20}$/', $phone)) {
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=invalid_phone');
        exit;
    }

    // Validate password match
    if ($password !== $confirm_password) {
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=password_mismatch');
        exit;
    }

    // Validate password strength (minimum 8 characters)
    if (strlen($password) < 8) {
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=weak_password');
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Check for duplicate email or phone
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            header('Location: ' . BASE_URL . 'signup.html?form=signup&status=duplicate_email_or_phone');
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, created_at, credits) VALUES (?, ?, ?, ?, 'user', NOW(), 500)");
        $stmt->execute([$full_name, $email, $phone, $hashed_password]);
        $user_id = $pdo->lastInsertId();

        // Generate and store API key (no expiration)
        $api_key = generateApiKey();
        $stmt = $pdo->prepare("INSERT INTO api_keys (user_id, api_key, is_active, created_at) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$user_id, $api_key]);

        // Commit transaction
        $pdo->commit();

        // Redirect to login with success message and API key
        header('Location: ' . BASE_URL . 'signup.html?form=login&status=signup_success');
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        header('Location: ' . BASE_URL . 'signup.html?form=signup&status=database_error');
        exit;
    }
}

// If not a POST request, redirect to signup page
header('Location: ' . BASE_URL . 'signup.html?form=signup');
exit;
?>