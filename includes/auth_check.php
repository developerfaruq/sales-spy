<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is logged in using either session or cookie-based authentication.
 * 
 * @return bool True if the user is logged in, false otherwise.
 */
function is_logged_in() {
    // Primary check: Session-based authentication
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    // Fallback: Cookie-based authentication (if enabled)
    $use_cookie_fallback = false; // Set to true to enable cookie fallback

    if ($use_cookie_fallback && isset($_COOKIE['auth_token'])) {
        // This is a fallback method for environments where sessions might not work
        $token = $_COOKIE['auth_token'];
        
        // Verify the token against the database
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE auth_token = ? AND auth_token_expiry > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Set session variables to restore the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                return true;
            }
        } catch (PDOException $e) {
            // Log error but don't expose it
            error_log("Auth token verification error: " . $e->getMessage());
        }
    }
    
    // Development mode has been removed
    // All requests must have proper authentication
    
    return false;
} 