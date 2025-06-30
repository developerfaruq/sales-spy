<?php
require_once 'config/db.php';

// Ensure we have a database connection
if (!isset($pdo)) {
    die("<h1>Database Schema Update</h1><p style='color: red;'>Error: Database connection failed. Check your database configuration.</p>");
}

echo "<h1>Database Schema Update</h1>";

try {
    // Check if auth_token column exists in users table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'auth_token'");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Add auth_token column
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token VARCHAR(255) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added auth_token column to users table.</p>";
    } else {
        echo "<p>auth_token column already exists.</p>";
    }

    // Check if auth_token_expiry column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'auth_token_expiry'");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Add auth_token_expiry column
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token_expiry DATETIME DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added auth_token_expiry column to users table.</p>";
    } else {
        echo "<p>auth_token_expiry column already exists.</p>";
    }

    echo "<p style='color: green;'>Database schema update completed successfully.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error updating database schema: " . $e->getMessage() . "</p>";
}

echo "<p><a href='" . BASE_URL . "'>Return to Home</a></p>";
?> 