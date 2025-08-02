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
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=empty_fields');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=invalid_email');
        exit;
    }

    // Validate phone format
    if (!preg_match('/^[0-9+\-\s]{10,20}$/', $phone)) {
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=invalid_phone');
        exit;
    }

    // Validate password match
    if ($password !== $confirm_password) {
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=password_mismatch');
        exit;
    }

    // Validate password strength (minimum 8 characters)
    if (strlen($password) < 8) {
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=weak_password');
        exit;
    }

    try {
        // Get user's IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Check how many accounts this IP has already created
        $ipCount = $pdo->prepare("SELECT COUNT(*) FROM users WHERE ip_address = ?");
        $ipCount->execute([$ip_address]);
        $existingAccounts = $ipCount->fetchColumn();

        // Deny registration if more than 2 accounts already created from this IP
        if ($existingAccounts >= 2) {
            header('Location: ' . BASE_URL . 'signup.php?form=signup&status=ip_blocked');
            exit;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Check for duplicate email or phone
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            header('Location: ' . BASE_URL . 'signup.php?form=signup&status=duplicate_email_or_phone');
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user with IP address and created_at
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, ip_address, created_at) VALUES (?, ?, ?, ?, 'user', ?, NOW())");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $ip_address]);
        $user_id = $pdo->lastInsertId();

        // Insert default free subscription
        $insertSubscription = $pdo->prepare("
            INSERT INTO subscriptions (user_id, plan_name, credits_remaining, credits_total, leads_balance, is_active)
            VALUES (?, 'free', 1000, 1000, 1000, 1)
        ");
        $insertSubscription->execute([$user_id]);

        // Generate and store API key
        $api_key = generateApiKey();
        $stmt = $pdo->prepare("INSERT INTO api_keys (user_id, api_key, is_active, created_at) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$user_id, $api_key]);

        // Commit transaction
        $pdo->commit();

        // Redirect to login with success message
        header('Location: ' . BASE_URL . 'signup.php?form=login&status=signup_success');
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        header('Location: ' . BASE_URL . 'signup.php?form=signup&status=database_error');
        exit;
    }
}

// If not a POST request, redirect to signup page
header('Location: ' . BASE_URL . 'signup.php?form=signup');
exit;
?>
