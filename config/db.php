<?php
$servername = "localhost";
$username ="root";
$password = "";
$dbname = "sales_spy";

// Defining base url
define("BASE_URL", "http://localhost/sales-spy/");

// Check if PDO MySQL driver is available
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    die("Error: PDO MySQL driver is not available. Please enable it in your PHP configuration.");
}

// Database connection
try{
  $pdo = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
}catch(PDOException $e){
  // For web requests, redirect to an error page
  if (!defined('RUNNING_FROM_CLI') || !RUNNING_FROM_CLI) {
    header("Location: " . BASE_URL . "signup.html?form=signup&status=database_error");
    exit;
  } else {
    // For CLI scripts, just output the error
    echo "Database connection failed: ".$e->getMessage();
    exit;
  }
}

// Mock data configuration
define("USE_MOCK_DATA", true);

