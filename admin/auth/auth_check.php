<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $_SESSION['admin_id'] = $_COOKIE['admin_remember'];
    // Optionally fetch admin info again from DB
}

// Redirect to login if still not authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../");
    exit;
}

?>
