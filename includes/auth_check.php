<?php

function is_logged_in() {
    // Start the session before any other output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the admin_id session variable is NOT set
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_agent']) || !isset($_SESSION['ip_address'])) {
    session_destroy();
    header("Location: ../../signup.php?form=login&status=session_expired");
    exit;
}
    
  
    // Here we'll check against a simple last activity timestamp
    $session_timeout = 1800; // 30 minutes
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
        // Expire the session
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        header("Location: /login.php?expired=1");
        exit;
    }

    // Update the last activity time for the current request
    $_SESSION['LAST_ACTIVITY'] = time();

    // If all checks pass, the user is logged in
    return true;
}
