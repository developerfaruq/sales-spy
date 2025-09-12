<?php
require '../../config/db.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle submission
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        exit("❌ Passwords do not match.");
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
    $stmt->execute([$hashed, $token]);

    echo "✅ Password reset successfully. <a href= 'https://sales-spy.test/signup.php?form=login'>Login</a>";
    exit;
}

// Show form if token is valid and not expired
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    exit("❌ Invalid or expired token.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Reset Your Password</h2>
    <form method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <div class="mb-4">
        <label class="block text-gray-600">New Password</label>
        <input type="password" name="password" class="w-full border p-2 rounded mt-1" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-600">Confirm Password</label>
        <input type="password" name="confirm_password" class="w-full border p-2 rounded mt-1" required>
      </div>
      <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Reset Password</button>
    </form>
  </div>
</body>
</html>
