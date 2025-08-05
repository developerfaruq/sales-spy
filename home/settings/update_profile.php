<?php
require 'auth_check.php';
// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

try {
    if (!isset($_POST['profile_picture'])) {
        throw new Exception('No profile picture provided');
    }

    // Get the base64 image data
    $imageData = $_POST['profile_picture'];
    
    // Check if it's a valid base64 image
    if (strpos($imageData, 'data:image') === false) {
        throw new Exception('Invalid image format');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = '../../uploads/profile_pictures/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.jpg';
    $filepath = $uploadDir . $filename;

    // Remove the data URL prefix to get the base64 data
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace('data:image/webp;base64,', '', $imageData);

    // Decode and save the image
    $decodedData = base64_decode($imageData);
    if ($decodedData === false) {
        throw new Exception('Failed to decode image data');
    }

    // Save the file
    if (!file_put_contents($filepath, $decodedData)) {
        throw new Exception('Failed to save image');
    }

    // Update database with new profile picture path
    $relativePath = 'uploads/profile_pictures/' . $filename;
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$relativePath, $_SESSION['user_id']]);

    $response['success'] = true;
    $response['message'] = 'Profile picture updated successfully';
    $response['path'] = $relativePath;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 