<?php
session_start();

// Get error message from session (if any)
$error = $_SESSION['admin_login_error'] ?? null;
unset($_SESSION['admin_login_error']);

// Get success message if exists (e.g., after password reset)
$success = $_SESSION['admin_login_success'] ?? null;
unset($_SESSION['admin_login_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - Sales Spy</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
    }
    .auth-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .input-field {
      transition: all 0.2s ease;
    }
    .input-field:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    .btn-primary {
      background-color: #3b82f6;
      transition: all 0.2s ease;
    }
    .btn-primary:hover {
      background-color: #2563eb;
      transform: translateY(-1px);
    }
    .btn-primary:active {
      transform: translateY(0);
    }
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      color: #64748b;
      font-size: 0.875rem;
    }
    .divider::before, .divider::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid #e2e8f0;
    }
    .divider::before {
      margin-right: 1rem;
    }
    .divider::after {
      margin-left: 1rem;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

  <div class="auth-card w-full max-w-md p-8">
    <!-- Logo/Branding -->
    <div class="flex flex-col items-center mb-8">
      <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-800">Sales Spy</h1>
      <p class="text-gray-500 text-sm mt-1">Competitive Intelligence Dashboard</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded flex items-start">
        <i class="fas fa-exclamation-circle mr-3 mt-0.5 text-red-500"></i>
        <div>
          <p class="font-medium">Login Error</p>
          <p class="text-sm mt-1"><?= htmlspecialchars($error) ?></p>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded flex items-start">
        <i class="fas fa-check-circle mr-3 mt-0.5 text-green-500"></i>
        <div>
          <p class="font-medium">Success</p>
          <p class="text-sm mt-1"><?= htmlspecialchars($success) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" action="auth/login/" class="space-y-5">
      <!-- Email Field -->
      <div class="space-y-1">
        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
        <div class="relative">
          <input type="email" id="email" name="email" required 
                 class="input-field block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none"
                 autocomplete="username">
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-envelope text-gray-400"></i>
          </div>
        </div>
      </div>

      <!-- Password Field -->
      <div class="space-y-1">
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <div class="relative">
          <input type="password" id="password" name="password" required 
                 class="input-field block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none"
                 autocomplete="current-password">
          <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <i class="far fa-eye text-gray-400 hover:text-gray-600" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <!-- Remember Me & Forgot Password -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
          <label for="remember" class="ml-2 block text-sm text-gray-700">Remember this device</label>
        </div>
        <a href="auth/forgot-password" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full btn-primary text-white font-medium py-2.5 px-4 rounded-md shadow">
        Sign In
      </button>

      
      
      
    </form>

    <!-- Footer Note -->
    <div class="mt-8 text-center text-sm text-gray-500">
      <p>Don't have an account? <a href="auth/register" class="text-blue-600 hover:text-blue-500 font-medium">Request access</a></p>
      <p class="mt-2">© <?= date('Y') ?> Sales Spy. All rights reserved.</p>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>