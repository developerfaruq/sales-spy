<?php

require '../../../config/db.php';
require '../../../home/subscription/api/auth_check.php';

 
    
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once '../../../../vendor/autoload.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}


$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_transactions':
            getTransactions($pdo);
            break;
        case 'update_transaction_status':
            updateTransactionStatus($pdo);
            break;
        case 'get_transaction_details':
            getTransactionDetails($pdo);
            break;
        case 'export_transactions':
            exportTransactions($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getTransactions($pdo) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $date_filter = $_GET['date_filter'] ?? '7days';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE conditions
    $where_conditions = [];
    $params = [];
    
    // Status filter
    if ($status !== 'all') {
        $where_conditions[] = "t.status = ?";
        $params[] = $status;
    }
    
    // Search filter
    if (!empty($search)) {
        $where_conditions[] = "(t.order_id LIKE ? OR u.full_name LIKE ? OR t.txid LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Date filter
    if ($date_filter !== 'custom') {
        $date_condition = getDateCondition($date_filter);
        if ($date_condition) {
            $where_conditions[] = $date_condition;
        }
    }
    
    $where_sql = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "
        SELECT COUNT(*) 
        FROM transactions t 
        LEFT JOIN users u ON t.user_id = u.id 
        $where_sql
    ";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    
    // Get transactions
    $sql = "
        SELECT 
            t.id,
            t.order_id,
            t.user_id,
            u.full_name as user_name,
            u.email as user_email,
            t.txid,
            t.amount,
            t.status,
            t.created_at,
            t.screenshot_path
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        $where_sql
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);

    // Bind dynamic filters
    foreach ($params as $i => $value) {
        $stmt->bindValue($i + 1, $value);
    }

    // Bind LIMIT and OFFSET explicitly as integers
    $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data and determine plan based on amount
    foreach ($transactions as &$transaction) {
        // Get plan based on transaction amount from database
        $plan_info = getPlanByAmount($pdo, $transaction['amount']);
        $transaction['plan_name'] = $plan_info['plan_name'];
        $transaction['plan_display_name'] = ucfirst($plan_info['plan_name']);
        
        $transaction['formatted_date'] = date('M d, Y H:i', strtotime($transaction['created_at']));
        $transaction['short_txid'] = substr($transaction['txid'], 0, 10) . '...' . substr($transaction['txid'], -6);
        
        // Generate user initials
        $names = explode(' ', $transaction['user_name'] ?? '');
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        $transaction['user_initials'] = substr($initials, 0, 2);
        
        // Generate user color
        $colors = [
            'bg-blue-100 text-blue-600',
            'bg-green-100 text-green-600',
            'bg-red-100 text-red-600',
            'bg-purple-100 text-purple-600',
            'bg-orange-100 text-orange-600',
            'bg-pink-100 text-pink-600',
            'bg-yellow-100 text-yellow-600',
            'bg-indigo-100 text-indigo-600',
            'bg-teal-100 text-teal-600',
            'bg-gray-100 text-gray-600'
        ];
        $transaction['user_color'] = $colors[$transaction['user_id'] % count($colors)];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function updateTransactionStatus($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $transaction_id = $input['transaction_id'] ?? null;
    $new_status = $input['status'] ?? null;
    
    if (!$transaction_id || !$new_status) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        return;
    }
    
    if (!in_array($new_status, ['pending', 'success', 'failed'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }
    
    // Get platform notification settings
    $notification_settings = getPlatformNotificationSettings($pdo);
    
    $pdo->beginTransaction();
    
    try {
        // Update transaction status
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $transaction_id]);
        
        // Get transaction details
        $stmt = $pdo->prepare("
            SELECT t.*, u.email, u.full_name 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            // Determine plan based on amount from database
            $plan_info = getPlanByAmount($pdo, $transaction['amount']);
            
            // Handle subscription logic based on status
            $email_sent = false;
            
            if ($new_status === 'success') {
                // Update or create subscription
                updateUserSubscription($pdo, $transaction['user_id'], $plan_info);
                
                // Send success notification email if enabled
                if ($notification_settings['notify_payment_success']) {
                    $email_sent = sendPaymentNotification($transaction, $plan_info, 'success');
                }
                
                // Log admin action
                logAdminAction($pdo, $_SESSION['admin_id'], 'transaction_approved', $transaction['user_id'], [
                    'transaction_id' => $transaction_id,
                    'amount' => $transaction['amount'],
                    'plan' => $plan_info['plan_name'],
                    'user_name' => $transaction['full_name'],
                    'user_email' => $transaction['email'],
                    'notification_enabled' => $notification_settings['notify_payment_success'] ? 'yes' : 'no',
                    'notification_sent' => $email_sent ? 'yes' : 'no'
                ]);
            } elseif ($new_status === 'failed') {
                // Send failure notification email if enabled
                if ($notification_settings['notify_payment_failed']) {
                    $email_sent = sendPaymentNotification($transaction, $plan_info, 'failed');
                }
                
                // Log admin action
                logAdminAction($pdo, $_SESSION['admin_id'], 'transaction_rejected', $transaction['user_id'], [
                    'transaction_id' => $transaction_id,
                    'amount' => $transaction['amount'],
                    'plan' => $plan_info['plan_name'],
                    'user_name' => $transaction['full_name'],
                    'user_email' => $transaction['email'],
                    'notification_enabled' => $notification_settings['notify_payment_failed'] ? 'yes' : 'no',
                    'notification_sent' => $email_sent ? 'yes' : 'no'
                ]);
            }
        }
        
        $pdo->commit();
        
        // Response message based on notification settings
        $message = 'Transaction updated successfully';
        if ($new_status === 'success') {
            if ($notification_settings['notify_payment_success']) {
                $message .= $email_sent ? ' and success notification sent to user' : ' but failed to send notification email';
            } else {
                $message .= ' (success notifications disabled)';
            }
        } elseif ($new_status === 'failed') {
            if ($notification_settings['notify_payment_failed']) {
                $message .= $email_sent ? ' and failure notification sent to user' : ' but failed to send notification email';
            } else {
                $message .= ' (failure notifications disabled)';
            }
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getTransactionDetails($pdo) {
    $transaction_id = $_GET['id'] ?? null;
    
    if (!$transaction_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Transaction ID required']);
        return;
    }
    
    $sql = "
        SELECT 
            t.*,
            u.full_name as user_name,
            u.email as user_email
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        http_response_code(404);
        echo json_encode(['error' => 'Transaction not found']);
        return;
    }
    
    // Add plan info based on amount 
    $plan_info = getPlanByAmount($pdo, $transaction['amount']);
    $transaction['plan_name'] = $plan_info['plan_name'];
    
    echo json_encode(['success' => true, 'data' => $transaction]);
}

function exportTransactions($pdo) {
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $date_filter = $_GET['date_filter'] ?? '7days';
    
    // Build WHERE conditions (same as getTransactions)
    $where_conditions = [];
    $params = [];
    
    if ($status !== 'all') {
        $where_conditions[] = "t.status = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(t.order_id LIKE ? OR u.full_name LIKE ? OR t.txid LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($date_filter !== 'custom') {
        $date_condition = getDateCondition($date_filter);
        if ($date_condition) {
            $where_conditions[] = $date_condition;
        }
    }
    
    $where_sql = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            t.order_id,
            u.full_name as user_name,
            t.amount,
            t.txid,
            t.created_at,
            t.status
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        $where_sql
        ORDER BY t.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add plan info for each transaction
    foreach ($transactions as &$transaction) {
        $plan_info = getPlanByAmount($pdo, $transaction['amount']);
        $transaction['plan_name'] = $plan_info['plan_name'];
    }
    
    // Return data for CSV export
    echo json_encode(['success' => true, 'data' => $transactions]);
}

function getDateCondition($date_filter) {
    switch ($date_filter) {
        case 'today':
            return "DATE(t.created_at) = CURDATE()";
        case 'yesterday':
            return "DATE(t.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        case '7days':
            return "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30days':
            return "t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        default:
            return null;
    }
}

// NEW: Get platform notification settings
function getPlatformNotificationSettings($pdo) {
    $stmt = $pdo->prepare("
        SELECT notify_payment_success, notify_payment_failed, platform_name
        FROM platform_settings 
        WHERE id = 1
    ");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Default settings if not found
    if (!$settings) {
        return [
            'notify_payment_success' => 1,
            'notify_payment_failed' => 1,
            'platform_name' => 'Sales-Spy'
        ];
    }
    
    return $settings;
}


function sendPaymentNotification($transaction, $plan_info, $status) {
 
   
    
    try {
        // Get platform settings for platform name
        global $pdo;
        $platform_settings = getPlatformNotificationSettings($pdo);
        $platform_name = $platform_settings['platform_name'];
        
        // Get email configuration from database or config file
        $email_config = getEmailConfig($pdo);
        
        $user_name = $transaction['full_name'];
        $amount = $transaction['amount'];
        $order_id = $transaction['order_id'];
        $plan_name = ucfirst($plan_info['plan_name']);
        
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $email_config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_config['smtp_username'];
        $mail->Password   = $email_config['smtp_password'];
        $mail->SMTPSecure = ($email_config['smtp_encryption'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $email_config['smtp_port'];
        
        // Recipients
        $mail->setFrom($email_config['from_email'], $platform_name);
        $mail->addAddress($transaction['email'], $user_name);
        $mail->addReplyTo($email_config['reply_to_email'], $platform_name);
        
        // Content
        $mail->isHTML(true);
        
        if ($status === 'success') {
            $mail->Subject = "Payment Confirmed - $platform_name Subscription";
            $mail->Body = "
                <html>
                <head>
                    <title>Payment Confirmation</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f8f9fa; }
                        .details-box { background-color: white; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Payment Confirmed!</h2>
                        </div>
                        <div class='content'>
                            <p>Dear $user_name,</p>
                            <p>Your payment has been successfully processed and your subscription is now active.</p>
                            
                            <div class='details-box'>
                                <h3>Payment Details:</h3>
                                <p><strong>Order ID:</strong> $order_id</p>
                                <p><strong>Amount:</strong> $amount</p>
                                <p><strong>Plan:</strong> $plan_name</p>
                                <p><strong>Status:</strong> <span style='color: #28a745;'>Active</span></p>
                            </div>
                            
                            <p>You can now access all features included in your $plan_name plan.</p>
                            <p>Thank you for choosing $platform_name!</p>
                        </div>
                        <div class='footer'>
                            <p>Best regards,<br>The $platform_name Team</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
        } else { // failed
            $mail->Subject = "Payment Failed - $platform_name";
            $mail->Body = "
                <html>
                <head>
                    <title>Payment Failed</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f8f9fa; }
                        .details-box { background-color: white; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Payment Failed</h2>
                        </div>
                        <div class='content'>
                            <p>Dear $user_name,</p>
                            <p>Unfortunately, we were unable to process your payment for your $platform_name subscription.</p>
                            
                            <div class='details-box'>
                                <h3>Payment Details:</h3>
                                <p><strong>Order ID:</strong> $order_id</p>
                                <p><strong>Amount:</strong> $amount</p>
                                <p><strong>Plan:</strong> $plan_name</p>
                                <p><strong>Status:</strong> <span style='color: #dc3545;'>Failed</span></p>
                            </div>
                            
                            <p>Please check your payment details and try again, or contact our support team for assistance.</p>
                        </div>
                        <div class='footer'>
                            <p>Best regards,<br>The $platform_name Team</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
        }
        
        // Send the email
        $mail->send();
        
        return true;
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// NEW: Get email configuration
function getEmailConfig($pdo) {
    // You can either store email config in database or use a config file
    // Option 1: From database (if you have an email_settings table)
    $config_path = '../../../config/email_config.php';
    $emailConfig = require $config_path;
    
   
    return [
        'smtp_host' => $emailConfig['smtp']['host'], // Change to your SMTP server
        'smtp_username' => $emailConfig['smtp']['username'], // Change to your email
        'smtp_password' =>  $emailConfig['smtp']['password'], // Change to your password/app password
        'smtp_port' => $emailConfig['smtp']['port'],
        'smtp_encryption' => $emailConfig['smtp']['encryption'], // or 'ssl'
        'from_email' => $emailConfig['smtp']['username'],
        'reply_to_email' => 'support@yourdomain.com'
    ];
}

// Dynamic plan determination based on database
function getPlanByAmount($pdo, $amount) {
    $amount = floatval($amount);
    
    // Get all active plans ordered by monthly price
    $stmt = $pdo->prepare("
        SELECT plan_name, monthly_price, credits_per_month 
        FROM plans 
        WHERE is_active = 1 
        ORDER BY monthly_price ASC
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Default to the first plan (usually free/lowest)
    $selected_plan = $plans[0] ?? ['plan_name' => 'free', 'monthly_price' => 0, 'credits_per_month' => 0];
    
    // Find the appropriate plan based on amount
    foreach ($plans as $plan) {
        if ($amount >= floatval($plan['monthly_price'])) {
            $selected_plan = $plan;
        }
    }
    
    return $selected_plan;
}

// Use plan info from database
function updateUserSubscription($pdo, $user_id, $plan_info) {
    if (!$plan_info) return;
    
    // Calculate end date (assuming monthly subscription)
    $end_date = $plan_info['plan_name'] === 'free' ? null : date('Y-m-d H:i:s', strtotime('+1 month'));
    
    // First deactivate existing subscriptions
    $stmt = $pdo->prepare("UPDATE subscriptions SET is_active = 0, status = 'expired' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Insert new subscription using credits_per_month from plans table
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (user_id, plan_name, credits_remaining, credits_total, start_date, end_date, status, is_active) 
        VALUES (?, ?, ?, ?, NOW(), ?, 'active', 1)
    ");
    
    $stmt->execute([
        $user_id,
        strtolower($plan_info['plan_name']),
        $plan_info['credits_per_month'],
        $plan_info['credits_per_month'],
        $end_date
    ]);
}

function logAdminAction($pdo, $admin_id, $action_type, $target_user_id, $details) {
    $stmt = $pdo->prepare("
        INSERT INTO admin_actions (admin_id, action_type, target_user_id, target_type, details, ip_address, user_agent) 
        VALUES (?, ?, ?, 'payment', ?, ?, ?)
    ");
    
    $stmt->execute([
        $admin_id,
        $action_type,
        $target_user_id,
        json_encode($details),
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}
?>