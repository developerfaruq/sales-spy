<?php
require '../config/db.php'; // adjust path as needed

$name = "Super Admin";
$email = "admin@gmail.com";
$password = "timlexino";

// Hash the password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $hashed]);

echo "Admin created successfully!";
?>
