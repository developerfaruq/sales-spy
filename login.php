<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ch = curl_init('http://localhost/sales/api/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($data && $data['message'] === "Login successful") {
        $_SESSION['user_id'] = $data['user_id']; // Adjust based on AuthController response
        $_SESSION['fullname'] = $data['fullname']; // Adjust based on AuthController response
        header("Location: dashboard.php");
    } else {
        $message = $data['message'] ?? 'Login failed';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- [Rest of your HTML from previous login.php remains unchanged] -->