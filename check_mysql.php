<?php
// Define that we're running from CLI
define('RUNNING_FROM_CLI', true);

echo "PHP Version: " . phpversion() . "\n\n";

echo "Checking PDO drivers:\n";
if (class_exists('PDO')) {
    $drivers = PDO::getAvailableDrivers();
    echo "Available PDO drivers: " . implode(', ', $drivers) . "\n";
    
    if (in_array('mysql', $drivers)) {
        echo "MySQL PDO driver is available.\n";
    } else {
        echo "MySQL PDO driver is NOT available. Please enable it in your PHP configuration.\n";
    }
} else {
    echo "PDO is not available. Please enable it in your PHP configuration.\n";
}

echo "\nChecking MySQL connection:\n";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sales_spy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to MySQL database '$dbname'.\n";
    
    // Check if users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Users table exists.\n";
        
        // Check if auth_token column exists
        $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'auth_token'");
        if ($stmt->rowCount() > 0) {
            echo "auth_token column exists in users table.\n";
        } else {
            echo "auth_token column does NOT exist in users table.\n";
        }
        
        // Check if auth_token_expiry column exists
        $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'auth_token_expiry'");
        if ($stmt->rowCount() > 0) {
            echo "auth_token_expiry column exists in users table.\n";
        } else {
            echo "auth_token_expiry column does NOT exist in users table.\n";
        }
    } else {
        echo "Users table does NOT exist.\n";
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?> 