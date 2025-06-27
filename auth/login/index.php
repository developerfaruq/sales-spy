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

        $session_id = session_id();
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Get geolocation
$geo_json = @file_get_contents("http://ip-api.com/json/" . urlencode($ip_address));
$geo_data = json_decode($geo_json, true);

$city = ($geo_data && $geo_data['status'] === 'success' && !empty($geo_data['city'])) ? $geo_data['city'] : 'Unknown';
$country = ($geo_data && $geo_data['status'] === 'success' && !empty($geo_data['country'])) ? $geo_data['country'] : 'Unknown';

// Check if device already exists (by user_agent + IP)
$stmt = $pdo->prepare("SELECT id FROM user_sessions WHERE user_id = ? AND ip_address = ? AND user_agent = ?");
$stmt->execute([$user['id'], $ip_address, $user_agent]);
$existing = $stmt->fetch();

if ($existing) {
    // Update session ID + last active + location in case IP changed
    $stmt = $pdo->prepare("UPDATE user_sessions SET session_id = ?, last_active = NOW(), city = ?, country = ? WHERE id = ?");
    $stmt->execute([$session_id, $city, $country, $existing['id']]);
} else {
    // Insert new session
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, city, country) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $session_id, $ip_address, $user_agent, $city, $country]);
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
        
header('location: '.BASE_URL.'logout.php');

        header('location: '.BASE_URL. 'signup.html?form=login&status=invalid_credentials');
        exit;
    }
}

// If not a POST request, redirect to login page
header('Location:' .BASE_URL. 'signup.html?form=login');
exit;
?>