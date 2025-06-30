<?php
require '../../config/db.php';
session_start();

// Force HTTPS in production (except localhost)
//if (empty($_SERVER['HTTPS']) && $_SERVER['HTTP_HOST'] != 'localhost') {
 //   header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  //  exit();
//}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        header('Location:' .BASE_URL. 'signup.html?form=login&status=empty_fields');
        exit;
    }

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location:' .BASE_URL. 'signup.html?form=login&status=invalid_email');
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Check account lock status
    if ($user && $user['failed_attempts'] >= 5 && strtotime($user['last_failed_attempt']) > time() - 3600) {
        header('Location:' .BASE_URL. 'signup.html?form=login&status=account_locked');
        exit;
    }

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Successful login - reset failed attempts
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE email = ?");
        $stmt->execute([$email]);

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Set up cookie-based authentication fallback
        $use_cookie_fallback = false; // Set to true to enable cookie fallback
        if ($use_cookie_fallback) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET auth_token = ?, auth_token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            // Set secure cookie
            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $httponly = true;
            setcookie('auth_token', $token, [
                'expires' => strtotime('+30 days'),
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => 'Lax'
            ]);
        }

        // Redirect to dashboard
        header('Location:' .BASE_URL. 'home/index.php?status=login_success');
        exit;
    } else {
        // Failed attempt - increment counter if user exists
        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() WHERE email = ?");
            $stmt->execute([$email]);
        }
        
        // Redirect to login page with error message
        header('location: '.BASE_URL. 'signup.html?form=login&status=invalid_credentials');
        exit;
    }
}

// If not a POST request, redirect to login page
header('Location:' .BASE_URL. 'signup.html?form=login');
exit;
?>