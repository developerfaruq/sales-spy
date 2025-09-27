<?php

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

try {
    $stmt = $pdo->prepare("SELECT logo_path FROM platform_settings WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $logoPath = '/admin/home/settings/'.$row['logo_path'];
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }

?>
