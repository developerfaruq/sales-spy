<?php
require '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get POST data
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Validate required fields
    if (empty($full_name) || empty($email)) {
        throw new Exception("Full name and email are required");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Start building the update query
    $updates = [];
    $params = [];

    // Add basic info updates
    $updates[] = "full_name = ?";
    $params[] = $full_name;
    
    $updates[] = "email = ?";
    $params[] = $email;
    
    if (!empty($phone)) {
        $updates[] = "phone = ?";
        $params[] = $phone;
    }

    // Handle password update if provided
    if (!empty($current_password) && !empty($new_password)) {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }

        // Validate new password
        if (strlen($new_password) < 8) {
            throw new Exception("New password must be at least 8 characters long");
        }

        // Add password update
        $updates[] = "password = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Add user ID to params
    $params[] = $_SESSION['user_id'];

    // Update user details
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Get updated user data
    $stmt = $pdo->prepare("SELECT id, full_name, email, phone, role, avatar_url FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format avatar_url path
    if (!empty($user['avatar_url'])) {
        $filename = str_replace('uploads/profile_pictures/', '', $user['avatar_url']);
        $user['avatar_url'] = '../uploads/profile_pictures/' . $filename;
    }

    $response['success'] = true;
    $response['message'] = 'User details updated successfully';
    $response['data'] = $user;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 