<?php
session_start();

// Get user ID before destroying session (for auth token removal)
$user_id = $_SESSION['user_id'] ?? null;

// Clear session data
session_unset();
session_destroy();

// Clear auth token cookie if it exists
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // If we have the user ID, also invalidate the token in the database
    if ($user_id) {
        require_once '../config/db.php';
        try {
            $stmt = $pdo->prepare("UPDATE users SET auth_token = NULL, auth_token_expiry = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            // Just log the error, don't expose it
            error_log("Error invalidating auth token: " . $e->getMessage());
        }
    }
}

// Redirect to login page
require_once '../config/db.php';
header('Location: ' . BASE_URL . 'signup.html?form=login&status=logged_out');
exit;
