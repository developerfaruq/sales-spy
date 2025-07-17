<?php
require '../../config/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$reason = $_POST['reason'];

$stmt = $pdo->prepare("INSERT INTO access_requests (name, email, reason) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $reason]);

session_start();
$_SESSION['admin_login_success'] = "Your request has been submitted.";
header("Location: ../../index.php");
?>
