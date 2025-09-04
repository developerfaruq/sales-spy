<?php
// api/users.php

require '../../config/db.php';
session_start();

if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $_SESSION['admin_id'] = $_COOKIE['admin_remember'];
    // Optionally fetch admin info again from DB
}

// Redirect to login if still not authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../");
    exit;
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php'; // Adjust path as needed

// Load email configuration
$emailConfig = require '../../config/email_config.php'; // Adjust path as needed

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetUsers();
            break;
        case 'POST':
            handleUserActions();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

function handleGetUsers() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $planFilter = isset($_GET['plan']) ? trim($_GET['plan']) : 'all';
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($planFilter !== 'all') {
        $whereConditions[] = "COALESCE(s.plan_name, 'free') = ?";
        $params[] = $planFilter;
    }
    
    if ($statusFilter !== 'all') {
        if ($statusFilter === 'active') {
            $whereConditions[] = "(u.account_status = 'active' OR u.account_status IS NULL) AND (u.is_disabled = 0 OR u.is_disabled IS NULL)";
        } elseif ($statusFilter === 'suspended') {
            $whereConditions[] = "(u.account_status = 'locked' OR u.is_disabled = 1)";
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(DISTINCT u.id) as total 
                 FROM users u 
                 LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
                 $whereClause";
    
    try {
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        echo json_encode(['error' => 'Count query failed: ' . $e->getMessage()]);
        return;
    }
    
    // Get users with pagination
    $sql = "SELECT DISTINCT u.id, u.full_name, u.email, u.phone, u.created_at, 
                   COALESCE(u.account_status, 'active') as account_status, 
                   COALESCE(u.is_disabled, 0) as is_disabled, 
                   u.profile_picture,
                   COALESCE(s.plan_name, 'free') as plan_name, 
                   s.status as subscription_status, 
                   COALESCE(s.leads_balance, 0) as leads_balance,
                   CASE 
                       WHEN (COALESCE(u.account_status, 'active') = 'active' AND COALESCE(u.is_disabled, 0) = 0) THEN 'active'
                       ELSE 'suspended'
                   END as user_status
            FROM users u 
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
            $whereClause
            ORDER BY u.created_at DESC 
            LIMIT $offset, $limit";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Users query failed: ' . $e->getMessage()]);
        return;
    }
    
    // Format users data
    $formattedUsers = array_map(function($user) {
        $initials = getInitials($user['full_name']);
        
        return [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'plan' => ucfirst($user['plan_name']),
            'planColor' => getPlanColor($user['plan_name']),
            'wallet' => 'Not Connected',
            'walletColor' => 'text-gray-600 bg-gray-100',
            'joined' => date('M d, Y', strtotime($user['created_at'])),
            'status' => ucfirst($user['user_status']),
            'statusColor' => $user['user_status'] === 'active' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600',
            'initials' => $initials,
            'profile_picture' => $user['profile_picture'],
            'leads_balance' => $user['leads_balance']
        ];
    }, $users);
    
    echo json_encode([
        'users' => $formattedUsers,
        'total' => intval($total),
        'page' => $page,
        'limit' => $limit,
        'totalPages' => ceil($total / $limit),
        'success' => true
    ]);
}

function handleUserActions() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }
    
    $action = $input['action'] ?? '';
    $userId = $input['userId'] ?? '';
    
    if (empty($action) || empty($userId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing action or user ID']);
        return;
    }
    
    switch ($action) {
        case 'suspend':
            suspendUser($userId, $input);
            break;
        case 'unsuspend':
            unsuspendUser($userId, $input);
            break;
        case 'delete':
            deleteUser($userId, $input);
            break;
        case 'getUserDetails':
            getUserDetails($userId);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function suspendUser($userId, $input) {
    global $pdo;
    
    $reason = $input['reason'] ?? '';
    $duration = $input['duration'] ?? 'indefinite';
    $sendNotification = $input['sendNotification'] ?? false;
    
    try {
        $pdo->beginTransaction();
        
        // Get user details first
        $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Update user status
        if ($duration === 'indefinite') {
            $stmt = $pdo->prepare("UPDATE users SET account_status = 'locked', is_disabled = 1 WHERE id = ?");
            $stmt->execute([$userId]);
        } else {
            $unlockTime = calculateUnlockTime($duration);
            $stmt = $pdo->prepare("UPDATE users SET account_status = 'locked', is_disabled = 1, unlock_time = ? WHERE id = ?");
            $stmt->execute([$unlockTime, $userId]);
        }
        
        // Log security event
        try {
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, details, created_at) VALUES (?, 'account_suspended', ?, NOW())");
            $stmt->execute([$userId, json_encode(['reason' => $reason, 'duration' => $duration])]);
        } catch (Exception $e) {
            // Continue if logging fails
        }
        
        $pdo->commit();
        
        // Send email notification if requested
        $emailSent = false;
        if ($sendNotification) {
            $emailSent = sendSuspensionEmail($user['email'], $user['full_name'], $reason, $duration);
            if (!$emailSent) {
                error_log("Failed to send suspension email to user ID: $userId");
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'User suspended successfully',
            'emailSent' => $emailSent
        ]);
    } catch (Exception $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function unsuspendUser($userId, $input) {
    global $pdo;
    
    $reason = $input['reason'] ?? '';
    $sendNotification = $input['sendNotification'] ?? false;
    
    try {
        $pdo->beginTransaction();
        
        // Get user details first
        $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Update user status
        $stmt = $pdo->prepare("UPDATE users SET account_status = 'active', is_disabled = 0, unlock_time = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Log security event
        try {
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, details, created_at) VALUES (?, 'account_unsuspended', ?, NOW())");
            $stmt->execute([$userId, json_encode(['reason' => $reason])]);
        } catch (Exception $e) {
            // Continue if logging fails
        }
        
        $pdo->commit();
        
        // Send email notification if requested
        $emailSent = false;
        if ($sendNotification) {
            $emailSent = sendUnsuspensionEmail($user['email'], $user['full_name'], $reason);
            if (!$emailSent) {
                error_log("Failed to send unsuspension email to user ID: $userId");
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'User unsuspended successfully',
            'emailSent' => $emailSent
        ]);
    } catch (Exception $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function deleteUser($userId, $input) {
    global $pdo;
    
    $confirmation = $input['confirmation'] ?? '';
    
    if ($confirmation !== 'DELETE') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid confirmation']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if user exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$userId]);
        if (!$checkStmt->fetch()) {
            throw new Exception('User not found');
        }
        
        // Check if user has active subscriptions
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM subscriptions WHERE user_id = ? AND status = 'active'");
            $stmt->execute([$userId]);
            $activeSubscriptions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($activeSubscriptions > 0) {
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete user with active subscriptions']);
                return;
            }
        } catch (Exception $e) {
            // Continue if table doesn't exist
        }
        
        // Delete related data first
        $tables = [
            'user_sessions', 'security_logs', 'search_logs', 'exports', 
            'leads', 'campaigns', 'api_keys', 'subscriptions', 
            'user_2fa', 'user_stats', 'user_tokens'
        ];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->prepare("DELETE FROM $table WHERE user_id = ?");
                $stmt->execute([$userId]);
            } catch (Exception $e) {
                // Continue if table doesn't exist
            }
        }
        
        // Finally delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getUserDetails($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   COALESCE(s.plan_name, 'free') as plan_name, 
                   s.status as subscription_status, 
                   COALESCE(s.leads_balance, 0) as leads_balance
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
            WHERE u.id = ?
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        // Get recent transactions
        $transactions = [];
        try {
            $stmt = $pdo->prepare("
                SELECT txid, amount, status, created_at, payment_type
                FROM transactions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$userId]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Continue if transactions table doesn't exist
        }
        
        // Get total paid amount
        $totalPaid = 0;
        try {
            $stmt = $pdo->prepare("SELECT SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total FROM transactions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPaid = $result['total'] ?? 0;
        } catch (Exception $e) {
            // Continue if transactions table doesn't exist
        }
        
        $user['transactions'] = $transactions;
        $user['total_paid'] = number_format($totalPaid, 2);
        $user['initials'] = getInitials($user['full_name']);
        $user['joined'] = date('M d, Y', strtotime($user['created_at']));
        
        echo json_encode(['user' => $user, 'success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Email Functions
function sendSuspensionEmail($email, $fullName, $reason, $duration) {
    global $emailConfig;
    
    try {
        $mail = new PHPMailer(true);
        
        // Configure SMTP
        configureMailer($mail);
        
        // Recipients
        $mail->setFrom($emailConfig['smtp']['from_email'], $emailConfig['smtp']['from_name']);
        $mail->addAddress($email, $fullName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailConfig['templates']['suspension']['subject'];
        
        $durationText = getDurationText($duration);
        $suspensionReason = !empty($reason) ? $reason : 'Administrative action';
        
        $mail->Body = getSuspensionEmailTemplate($fullName, $suspensionReason, $durationText);
        $mail->AltBody = getSuspensionEmailTextVersion($fullName, $suspensionReason, $durationText);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Suspension email failed: " . $e->getMessage());
        return false;
    }
}

function sendUnsuspensionEmail($email, $fullName, $reason) {
    global $emailConfig;
    
    try {
        $mail = new PHPMailer(true);
        
        // Configure SMTP
        configureMailer($mail);
        
        // Recipients
        $mail->setFrom($emailConfig['smtp']['from_email'], $emailConfig['smtp']['from_name']);
        $mail->addAddress($email, $fullName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailConfig['templates']['unsuspension']['subject'];
        
        $reactivationReason = !empty($reason) ? $reason : 'Administrative decision';
        
        $mail->Body = getUnsuspensionEmailTemplate($fullName, $reactivationReason);
        $mail->AltBody = getUnsuspensionEmailTextVersion($fullName, $reactivationReason);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Unsuspension email failed: " . $e->getMessage());
        return false;
    }
}

function configureMailer($mail) {
    global $emailConfig;
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $emailConfig['smtp']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $emailConfig['smtp']['username'];
    $mail->Password = $emailConfig['smtp']['password'];
    $mail->SMTPSecure = $emailConfig['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $emailConfig['smtp']['port'];
    
    // Additional settings
    $mail->Timeout = $emailConfig['settings']['timeout'];
    $mail->SMTPDebug = $emailConfig['settings']['debug'];
    $mail->CharSet = $emailConfig['settings']['charset'];
    $mail->WordWrap = $emailConfig['settings']['word_wrap'];
}

function getSuspensionEmailTemplate($fullName, $reason, $duration) {
    global $emailConfig;
    
    $config = $emailConfig['templates']['suspension'];
    $companyName = $config['company_name'];
    $supportEmail = $config['support_email'];
    $websiteUrl = $config['website_url'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Account Suspension Notice</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background-color: #fff; padding: 30px; border: 1px solid #dee2e6; }
            .footer { background-color: #f8f9fa; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6c757d; }
            .alert { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 4px; margin: 20px 0; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; color: #dc3545;'>Account Suspension Notice</h1>
            </div>
            <div class='content'>
                <h2>Dear " . htmlspecialchars($fullName) . ",</h2>
                
                <p>We are writing to inform you that your $companyName account has been temporarily suspended.</p>
                
                <div class='alert'>
                    <strong>Reason for suspension:</strong> " . htmlspecialchars($reason) . "<br>
                    <strong>Duration:</strong> " . htmlspecialchars($duration) ."
                </div>
                
                <p>During this suspension period, you will not be able to access your account or use our services.</p>
                
                <h3>What happens next?</h3>
                <ul>
                    <li>Your account data remains secure and intact</li>
                    <li>Active subscriptions are paused during the suspension</li>
                    <li>You will be notified when your account is reactivated</li>
                </ul>
                
                <p>If you believe this suspension was made in error or if you have any questions, please contact our support team immediately.</p>
                
                <a href='mailto:$supportEmail' class='btn'>Contact Support</a>
                
                <p>We appreciate your understanding and look forward to restoring your access soon.</p>
                
                <p>Best regards,<br>
                <strong>$companyName Support Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated message from $companyName. Please do not reply to this email.</p>
                <p>&copy; 2025 $companyName. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function getUnsuspensionEmailTemplate($fullName, $reason) {
    global $emailConfig;
    
    $config = $emailConfig['templates']['unsuspension'];
    $companyName = $config['company_name'];
    $supportEmail = $config['support_email'];
    $loginUrl = $config['login_url'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Account Reactivated</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d4edda; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background-color: #fff; padding: 30px; border: 1px solid #dee2e6; }
            .footer { background-color: #f8f9fa; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6c757d; }
            .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; color: #28a745;'>Account Reactivated!</h1>
            </div>
            <div class='content'>
                <h2>Welcome back, " . htmlspecialchars($fullName) . "!</h2>
                
                <div class='success'>
                    <strong>Good news!</strong> Your $companyName account has been reactivated and is now fully functional.
                </div>
                
                <p><strong>Reason for reactivation:</strong> " . htmlspecialchars($reason) . "</p>
                
                <p>You can now:</p>
                <ul>
                    <li>Access your dashboard and all features</li>
                    <li>Resume your active subscriptions</li>
                    <li>Continue using all $companyName services</li>
                    <li>Access your previously saved data and settings</li>
                </ul>
                
                <p>We apologize for any inconvenience caused during the suspension period and appreciate your patience.</p>
                
                <a href='$loginUrl' class='btn'>Login to Your Account</a>
                
                <p>If you experience any issues accessing your account or have any questions, please don't hesitate to contact our support team.</p>
                
                <p>Thank you for being a valued member of $companyName!</p>
                
                <p>Best regards,<br>
                <strong>$companyName Support Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated message from $companyName. Please do not reply to this email.</p>
                <p>&copy; 2025 $companyName. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function getSuspensionEmailTextVersion($fullName, $reason, $duration) {
    global $emailConfig;
    
    $companyName = $emailConfig['templates']['suspension']['company_name'];
    $supportEmail = $emailConfig['templates']['suspension']['support_email'];
    
    return "
Account Suspension Notice - $companyName

Dear $fullName,

We are writing to inform you that your $companyName account has been temporarily suspended.

Reason for suspension: $reason
Duration: $duration

During this suspension period, you will not be able to access your account or use our services.

What happens next?
- Your account data remains secure and intact
- Active subscriptions are paused during the suspension
- You will be notified when your account is reactivated

If you believe this suspension was made in error or if you have any questions, please contact our support team at $supportEmail.

We appreciate your understanding and look forward to restoring your access soon.

Best regards,
$companyName Support Team

This is an automated message from $companyName. Please do not reply to this email.
© 2025 $companyName. All rights reserved.
    ";
}

function getUnsuspensionEmailTextVersion($fullName, $reason) {
    global $emailConfig;
    
    $companyName = $emailConfig['templates']['unsuspension']['company_name'];
    $supportEmail = $emailConfig['templates']['unsuspension']['support_email'];
    $loginUrl = $emailConfig['templates']['unsuspension']['login_url'];
    
    return "
Account Reactivated - $companyName

Welcome back, $fullName!

Good news! Your $companyName account has been reactivated and is now fully functional.

Reason for reactivation: $reason

You can now:
- Access your dashboard and all features
- Resume your active subscriptions
- Continue using all $companyName services
- Access your previously saved data and settings

We apologize for any inconvenience caused during the suspension period and appreciate your patience.

Login to your account: $loginUrl

If you experience any issues accessing your account or have any questions, please don't hesitate to contact our support team at $supportEmail.

Thank you for being a valued member of $companyName!

Best regards,
$companyName Support Team

This is an automated message from $companyName. Please do not reply to this email.
© 2025 $companyName. All rights reserved.
    ";
}

function getDurationText($duration) {
    switch ($duration) {
        case '1day':
            return '1 day';
        case '1week':
            return '1 week';
        case '1month':
            return '1 month';
        case 'indefinite':
        default:
            return 'Indefinite (until further notice)';
    }
}

function getPlanColor($plan) {
    switch (strtolower($plan)) {
        case 'basic':
            return 'bg-gray-100 text-gray-600';
        case 'pro':
            return 'bg-blue-50 text-blue-600';
        case 'enterprise':
            return 'bg-purple-50 text-purple-600';
        case 'premium':
            return 'bg-orange-50 text-orange-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
}

function calculateUnlockTime($duration) {
    switch ($duration) {
        case '1day':
            return date('Y-m-d H:i:s', strtotime('+1 day'));
        case '1week':
            return date('Y-m-d H:i:s', strtotime('+1 week'));
        case '1month':
            return date('Y-m-d H:i:s', strtotime('+1 month'));
        default:
            return null;
    }
}

function getInitials($fullName) {
    $initials = '';
    $nameParts = explode(' ', trim($fullName));
    foreach ($nameParts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    return substr($initials, 0, 2);
}
?>