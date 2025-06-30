<?php
// Start session to maintain any existing session data
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Connection Error</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind = {
      config: {
        theme: {
          extend: {
            colors: {
              primary: '#1E3A8A',
              secondary: '#F2E49C'
            }
          }
        }
      }
    };
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #F9FBF4 0%, #F5F7F0 100%);
      min-height: 100vh;
    }
    .code-block {
      background: #f5f5f5;
      padding: 1rem;
      border-radius: 0.5rem;
      overflow-x: auto;
      font-family: monospace;
    }
  </style>
</head>
<body class="flex flex-col items-center p-4 md:p-8">
  <div class="w-full max-w-4xl">
    <a href="index.html" class="inline-flex items-center text-gray-600 hover:text-primary transition-colors mb-6">
      <i class="ri-arrow-left-line mr-2"></i>
      <span>Back to Home</span>
    </a>
    
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
      <div class="flex items-center mb-6">
        <div class="bg-red-100 p-3 rounded-full mr-4">
          <i class="ri-database-2-line text-red-500 text-2xl"></i>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Database Connection Error</h1>
      </div>
      
      <div class="mb-8">
        <p class="text-gray-600 mb-4">We're having trouble connecting to the database. Our diagnostics show that the PHP MySQL PDO driver is not enabled on this server.</p>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="ri-information-line text-yellow-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                <strong>Technical Details:</strong> The PDO MySQL driver is missing. This is required for database operations.
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <h2 class="text-xl font-semibold text-gray-800 mb-4">How to Fix This in XAMPP</h2>
      
      <div class="space-y-6 mb-8">
        <div>
          <h3 class="font-medium text-gray-800 mb-2">1. Edit php.ini file</h3>
          <p class="text-gray-600 mb-2">Locate your php.ini file at C:\xampp\php\php.ini and open it in a text editor.</p>
        </div>
        
        <div>
          <h3 class="font-medium text-gray-800 mb-2">2. Enable the PDO MySQL Extension</h3>
          <p class="text-gray-600 mb-2">Find and uncomment these lines by removing the semicolon (;) at the beginning:</p>
          <div class="code-block">
            ;extension=pdo_mysql<br>
            ;extension=mysqli
          </div>
          <p class="text-gray-600 mt-2">They should look like this after uncommenting:</p>
          <div class="code-block">
            extension=pdo_mysql<br>
            extension=mysqli
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-gray-800 mb-2">3. Restart Apache</h3>
          <p class="text-gray-600 mb-2">Open XAMPP Control Panel and restart the Apache service:</p>
          <ol class="list-decimal list-inside space-y-1 text-gray-600 ml-4">
            <li>Click "Stop" next to Apache</li>
            <li>Wait a few seconds</li>
            <li>Click "Start" to restart Apache</li>
          </ol>
        </div>
        
        <div>
          <h3 class="font-medium text-gray-800 mb-2">4. Verify MySQL is running</h3>
          <p class="text-gray-600">Make sure the MySQL service is running in XAMPP Control Panel. If not, click "Start" next to MySQL.</p>
        </div>
        
        <div>
          <h3 class="font-medium text-gray-800 mb-2">5. Check if database exists</h3>
          <p class="text-gray-600 mb-2">Make sure the 'sales_spy' database exists. You can create it by importing the SQL file through phpMyAdmin:</p>
          <ol class="list-decimal list-inside space-y-1 text-gray-600 ml-4">
            <li>Open <a href="http://localhost/phpmyadmin" class="text-primary hover:underline">http://localhost/phpmyadmin</a></li>
            <li>Click "New" to create a new database</li>
            <li>Enter "sales_spy" as the database name and click "Create"</li>
            <li>Select the "sales_spy" database</li>
            <li>Click the "Import" tab</li>
            <li>Click "Choose File" and select the sales_spy.sql file</li>
            <li>Click "Go" to import the database structure</li>
          </ol>
        </div>
      </div>
      
      <div class="flex flex-col md:flex-row gap-4">
        <a href="check_mysql.php" class="bg-primary text-white py-2 px-4 rounded-md hover:bg-opacity-90 transition-all text-center">
          Run Diagnostics
        </a>
        <a href="signup.html?form=signup" class="border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 transition-all text-center">
          Try Again
        </a>
      </div>
    </div>
  </div>
</body>
</html> 