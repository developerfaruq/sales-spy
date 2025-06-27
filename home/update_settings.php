<?php
require '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "signup.html?form=login&status=session_expired");
    exit;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get user data
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.phone, u.role, u.created_at, u.credits, u.profile_picture
        FROM users u
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // Ensure profile_picture is a valid URL path
    if (!empty($user['profile_picture'])) {
        // Remove any existing uploads/profile_pictures/ prefix to avoid duplication
        $filename = str_replace('uploads/profile_pictures/', '', $user['profile_picture']);
        $user['profile_picture'] = '../uploads/profile_pictures/' . $filename;
    } else {
        $user['profile_picture'] = null;
    }

    // Get user's subscription details
    $stmt = $pdo->prepare("SELECT plan_name, credits_total, start_date, end_date, is_active FROM subscriptions WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare response data (remove sessions)
    $response['data'] = [
        'user' => $user,
        'subscription' => $subscription,
        'credits' => $subscription['credits_total']
    ];
    $response['success'] = true;
    $response['message'] = 'User data fetched successfully';

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
