<?php
// Start with a simple authentication check
session_start();
$is_admin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
$override_key = "setup123"; // Simple override key for initial setup

// Check if user is admin or has provided the override key
if (!$is_admin && (!isset($_GET["key"]) || $_GET["key"] !== $override_key)) {
    echo "<h1>Access Denied</h1>";
    echo "<p>You must be an admin or provide a valid setup key to access this page.</p>";
    echo "<p><a href=\"signup.html?form=login\">Login</a></p>";
    exit;
}

// Include database configuration
require_once "config/db.php";

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Schema Update</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #1E3A8A; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Database Schema Update</h1>";

try {
    // Check if auth_token column exists in users table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE \"auth_token\"");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Add auth_token column
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token VARCHAR(255) DEFAULT NULL");
        echo "<p class=\"success\">✅ Added auth_token column to users table.</p>";
    } else {
        echo "<p class=\"info\">ℹ️ auth_token column already exists.</p>";
    }

    // Check if auth_token_expiry column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE \"auth_token_expiry\"");
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Add auth_token_expiry column
        $pdo->exec("ALTER TABLE users ADD COLUMN auth_token_expiry DATETIME DEFAULT NULL");
        echo "<p class=\"success\">✅ Added auth_token_expiry column to users table.</p>";
    } else {
        echo "<p class=\"info\">ℹ️ auth_token_expiry column already exists.</p>";
    }

    echo "<p class=\"success\">✅ Database schema update completed successfully.</p>";
    
    // Show current database structure
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Users Table Structure</h2>";
    echo "<pre>";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'];
        if ($column['Null'] === "NO") echo " NOT NULL";
        if ($column['Default'] !== null) echo " DEFAULT \"" . $column['Default'] . "\"";
        echo "\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p class=\"error\">❌ Error updating database schema: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Next Steps</h2>
<p>Now that the database schema has been updated, you can:</p>
<ol>
    <li>Test the session functionality with <a href=\"test_session.php\">test_session.php</a></li>
    <li>Try logging in with your credentials at <a href=\"signup.html?form=login\">the login page</a></li>
    <li>If sessions don\"t work, enable the cookie fallback by setting \$use_cookie_fallback = true in auth/login/index.php and includes/auth_check.php</li>
</ol>

<p><a href=\"" . BASE_URL . "\">Return to Home</a></p>
</body>
</html>";
?>