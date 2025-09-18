<?php
require '../../../config/db.php';
require '../../subscription/api/auth_check.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../../vendor/autoload.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['transaction_id'], $input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$transaction_id = $input['transaction_id'];
$action = $input['action'];
$admin_id = $_SESSION['admin_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get transaction details with user info
    $stmt = $pdo->prepare("
        SELECT t.*, u.full_name, u.email, p.plan_name, p.credits_per_month 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id
        LEFT JOIN plans p ON p.monthly_price = t.amount OR p.yearly_price = t.amount
        WHERE t.id = ? AND t.status = 'pending'
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception('Transaction not found or already processed');
    }
    
    // Get admin info for logging
    $stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    $admin_name = $admin['name'] ?? 'Unknown Admin';
    
    if ($action === 'approve') {
        // Approve transaction
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'success' WHERE id = ?");
        $stmt->execute([$transaction_id]);
        
        // Determine the plan based on amount paid
        $plan_name = null;
        $credits = 1000;
        $end_date = null;
        
        // Check which plan matches the paid amount
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE monthly_price = ? OR yearly_price = ? ORDER BY monthly_price ASC LIMIT 1");
        $stmt->execute([$transaction['amount'], $transaction['amount']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($plan) {
            $plan_name = $plan['plan_name'];
            $credits = $plan['credits_per_month'];
            
            // Determine duration (monthly vs yearly)
            $is_yearly = ($transaction['amount'] == $plan['yearly_price']);
            $duration_months = $is_yearly ? 12 : 1;
            
            // Calculate end date
            $end_date = date('Y-m-d H:i:s', strtotime("+{$duration_months} months"));
        }
        
        // Check for existing subscription and delete if found
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ?");
        $stmt->execute([$transaction['user_id']]);
        $existing_subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_subscription) {
            // Log the deletion to subscription_history before deleting
            $stmt = $pdo->prepare("
                INSERT INTO subscription_history (
                    user_id, subscription_id, event_type, old_plan, old_status, 
                    details, admin_id, amount, created_at
                ) VALUES (?, ?, 'deleted_for_upgrade', ?, ?, ?, ?, ?, NOW())
            ");
            $deletion_details = json_encode([
                'reason' => 'Deleted due to new payment approval',
                'transaction_id' => $transaction_id,
                'old_subscription_id' => $existing_subscription['id'],
                'old_plan_name' => $existing_subscription['plan_name'],
                'old_credits_remaining' => $existing_subscription['credits_remaining'],
                'old_start_date' => $existing_subscription['start_date'],
                'old_end_date' => $existing_subscription['end_date'],
                'admin_name' => $admin_name,
                'user_name' => $transaction['full_name'],
                'user_email' => $transaction['email']
            ]);
            
            $stmt->execute([
                $transaction['user_id'],
                $existing_subscription['id'],
                $existing_subscription['plan_name'],
                $existing_subscription['status'],
                $deletion_details,
                $admin_id,
                $transaction['amount']
            ]);
            
            // Now delete the existing subscription
            $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ?");
            $stmt->execute([$transaction['user_id']]);
        }
        
        // Create new subscription
        $stmt = $pdo->prepare("
            INSERT INTO subscriptions (user_id, plan_name, credits_remaining, credits_total, start_date, end_date, status, is_active) 
            VALUES (?, ?, ?, ?, NOW(), ?, 'active', 1)
        ");
        $stmt->execute([
            $transaction['user_id'],
            $plan_name,
            $credits,
            $credits,
            $end_date
        ]);
        
        $new_subscription_id = $pdo->lastInsertId();
        
        // Log the new subscription creation to subscription_history
        $stmt = $pdo->prepare("
            INSERT INTO subscription_history (
                user_id, subscription_id, event_type, new_plan, new_status, 
                details, admin_id, amount, created_at
            ) VALUES (?, ?, 'created', ?, 'active', ?, ?, ?, NOW())
        ");
        $creation_details = json_encode([
            'reason' => 'Created due to payment approval',
            'transaction_id' => $transaction_id,
            'plan_name' => $plan_name,
            'credits_total' => $credits,
            'credits_remaining' => $credits,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => $end_date,
            'duration' => $is_yearly ? '12_months' : '1_month',
            'amount_paid' => $transaction['amount'],
            'admin_name' => $admin_name,
            'user_name' => $transaction['full_name'],
            'user_email' => $transaction['email'],
            'had_existing_subscription' => (bool)$existing_subscription
        ]);
        
        $stmt->execute([
            $transaction['user_id'],
            $new_subscription_id,
            $plan_name,
            $creation_details,
            $admin_id,
            $transaction['amount']
        ]);
        
        // Update user credits
        $stmt = $pdo->prepare("UPDATE users SET credits = ? WHERE id = ?");
        $stmt->execute([$credits, $transaction['user_id']]);
        
        // Log admin action
        $action_details = json_encode([
            'transaction_id' => $transaction_id,
            'amount' => $transaction['amount'],
            'plan' => $plan_name,
            'user_name' => $transaction['full_name'],
            'user_email' => $transaction['email'],
            'new_subscription_id' => $new_subscription_id,
            'had_existing_subscription' => (bool)$existing_subscription,
            'credits_assigned' => $credits
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_actions (admin_id, action_type, target_user_id, target_type, details, ip_address, user_agent) 
            VALUES (?, 'transaction_approved', ?, 'payment', ?, ?, ?)
        ");
        $stmt->execute([
            $admin_id,
            $transaction['user_id'],
            $action_details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        $message = 'Transaction approved and subscription updated successfully';
        
    } elseif ($action === 'decline') {
        // Decline transaction
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE id = ?");
        $stmt->execute([$transaction_id]);
        
        // Log admin action
        $action_details = json_encode([
            'transaction_id' => $transaction_id,
            'amount' => $transaction['amount'],
            'user_name' => $transaction['full_name'],
            'user_email' => $transaction['email'],
            'reason' => 'Declined by admin'
        ]);
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_actions (admin_id, action_type, target_user_id, target_type, details, ip_address, user_agent) 
            VALUES (?, 'transaction_declined', ?, 'payment', ?, ?, ?)
        ");
        $stmt->execute([
            $admin_id,
            $transaction['user_id'],
            $action_details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        $message = 'Transaction declined successfully';
    } else {
        throw new Exception('Invalid action');
    }
    
    // Commit database transaction first
    $pdo->commit();
    
    // Send email after successful database commit
    $email_sent = false;
    $email_error = null;
    
    try {
        if ($action === 'approve') {
            $email_sent = sendTransactionEmail($transaction, 'approved', $plan_name);
        } elseif ($action === 'decline') {
            // Fixed: Pass null as plan_name for declined transactions
            $email_sent = sendTransactionEmail($transaction, 'declined', null);
        }
    } catch (Exception $e) {
        $email_error = $e->getMessage();
        error_log("Email sending failed: " . $email_error);
    }
    
    // Include email status in response for debugging
    $response = [
        'success' => true, 
        'message' => $message,
        'action' => $action,
        'email_sent' => $email_sent
    ];
    
    // Add email error to response if debugging is needed
    if (!$email_sent && $email_error) {
        $response['email_error'] = $email_error;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollback();
    error_log("Payment action error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

function sendTransactionEmail($transaction, $status, $plan_name = null) {
    $mail = new PHPMailer(true);
    
    try {
        // Check if email config file exists
        $config_path = '../../../config/email_config.php';
        if (!file_exists($config_path)) {
            throw new Exception("Email configuration file not found at: $config_path");
        }
        
        $emailConfig = require $config_path;
        
        // Validate email configuration
        if (!isset($emailConfig['smtp']) || 
            !isset($emailConfig['smtp']['host']) || 
            !isset($emailConfig['smtp']['username']) || 
            !isset($emailConfig['smtp']['password']) || 
            !isset($emailConfig['smtp']['port'])) {
            throw new Exception("Invalid email configuration");
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $emailConfig['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $emailConfig['smtp']['username'];
        $mail->Password   = $emailConfig['smtp']['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $emailConfig['smtp']['port'];
        
        // Enable debugging for testing (remove in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom('noreply@sales-spy.com', 'Sales-Spy');
        $mail->addAddress($transaction['email'], $transaction['full_name']);
        $mail->addReplyTo('support@sales-spy.com', 'Sales-Spy Support');
        
        // Content
        $mail->isHTML(true);
        
        $user_name = $transaction['full_name'];
        $amount = $transaction['amount'];
        $order_id = $transaction['order_id'];
        
        if ($status === 'approved') {
            $mail->Subject = 'Payment Approved - Sales-Spy Subscription';
            $mail->Body = "
            <html>
            <head>
                <title>Payment Approved</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #1E3A8A; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { padding: 30px; background: #f9f9f9; }
                    .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                    .button { background: #1E3A8A; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                    .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                    ul { list-style: none; padding: 0; }
                    li { margin: 8px 0; }
                    .status-approved { color: green; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>Sales-Spy</h1>
                        <p style='margin: 5px 0 0 0;'>Payment Approved</p>
                    </div>
                    
                    <div class='content'>
                        <h2 style='color: #1E3A8A;'>Great news, {$user_name}!</h2>
                        
                        <p>Your payment has been successfully approved and your subscription has been activated.</p>
                        
                        <div class='details'>
                            <h3>Payment Details:</h3>
                            <ul>
                                <li><strong>Order ID:</strong> {$order_id}</li>
                                <li><strong>Amount:</strong> \${$amount}</li>
                                <li><strong>Plan:</strong> " . ucfirst($plan_name) . "</li>
                                <li><strong>Status:</strong> <span class='status-approved'>Approved</span></li>
                            </ul>
                        </div>
                        
                        <p>You can now access all the features included in your " . ucfirst($plan_name) . " plan.</p>
                        
                        <div style='text-align: center;'>
                            <a href='https://sales-spy.test/dashboard' class='button'>Access Dashboard</a>
                        </div>
                        
                        <p>If you have any questions, please don't hesitate to contact our support team.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>&copy; 2025 Sales-Spy. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
            
        } else {
            $mail->Subject = 'Payment Update - Sales-Spy';
            $mail->Body = "
            <html>
            <head>
                <title>Payment Update</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { padding: 30px; background: #f9f9f9; }
                    .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                    .button { background: #1E3A8A; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                    .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
                    ul { list-style: none; padding: 0; }
                    li { margin: 8px 0; }
                    .status-declined { color: red; font-weight: bold; }
                    .reasons { list-style: disc; padding-left: 20px; }
                    .reasons li { margin: 4px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0;'>Sales-Spy</h1>
                        <p style='margin: 5px 0 0 0;'>Payment Update</p>
                    </div>
                    
                    <div class='content'>
                        <h2 style='color: #dc3545;'>Payment Update, {$user_name}</h2>
                        
                        <p>We're writing to inform you about the status of your recent payment.</p>
                        
                        <div class='details'>
                            <h3>Payment Details:</h3>
                            <ul>
                                <li><strong>Order ID:</strong> {$order_id}</li>
                                <li><strong>Amount:</strong> \${$amount}</li>
                                <li><strong>Status:</strong> <span class='status-declined'>Declined</span></li>
                            </ul>
                        </div>
                        
                        <p>Unfortunately, we were unable to verify your payment at this time. This could be due to:</p>
                        <ul class='reasons'>
                            <li>Insufficient payment information</li>
                            <li>Payment verification failed</li>
                            <li>Transaction details don't match our records</li>
                        </ul>
                        
                        <p>Please contact our support team if you believe this is an error or if you need assistance with your payment.</p>
                        
                        <div style='text-align: center;'>
                            <a href='mailto:support@sales-spy.com' class='button'>Contact Support</a>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>&copy; 2025 Sales-Spy. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
        }
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage() . " | PHPMailer Error: " . $mail->ErrorInfo);
        throw $e; // Re-throw the exception to be caught in the main code
    }
}
?>