<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    // In a real application, you would check if a user session variable exists
    // e.g., return isset($_SESSION['user_id']);
    // For now, returning true for development purposes.
    return true;
} 