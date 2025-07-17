<!DOCTYPE html>
<html>
<head>
  <title>Request Access - Sales Spy</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4">Request Admin Access</h2>
    <form method="POST" action="submit.php" class="space-y-4">
      <div>
        <label class="block text-sm">Full Name</label>
        <input type="text" name="name" required class="w-full px-4 py-2 border rounded">
      </div>
      <div>
        <label class="block text-sm">Email Address</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded">
      </div>
      <div>
        <label class="block text-sm">Reason</label>
        <textarea name="reason" rows="3" class="w-full px-4 py-2 border rounded" required></textarea>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Submit Request</button>
    </form>
  </div>
</body>
</html>
