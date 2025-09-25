<?php
require '../../../config/db.php';
require 'auth_check.php';

header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$email = $input['email'] ?? null;
$plan = $input['plan'] ?? null;
$duration = $input['duration'] ?? '1month';
$notes = $input['notes'] ?? '';
$send_email = $input['send_email'] ?? false;

// Validation
if (!$email || !$plan) {
    echo json_encode(['success' => false, 'message' => 'Email and plan are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate plan exists in database and get credits
function getValidPlansAndCredits($pdo) {
    $stmt = $pdo->prepare("SELECT plan_name, credits_per_month FROM plans WHERE is_active = 1");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $plans;
}

$valid_plans = getValidPlansAndCredits($pdo);

if (!array_key_exists($plan, $valid_plans)) {
    echo json_encode(['success' => false, 'message' => 'Invalid plan selected or plan is not active']);
    exit;
}

$credits = $valid_plans[$plan];

// Email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer - adjust path if needed
require_once '../../../../vendor/autoload.php';

function sendWelcomeEmail($userEmail, $userName, $plan, $duration, $startDate, $endDate, $credits) {
    try {
        // Load email config
        $emailConfig = require '../../../config/email_config.php';
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $emailConfig['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $emailConfig['smtp']['username'];
        $mail->Password = $emailConfig['smtp']['password'];
        $mail->SMTPSecure = $emailConfig['smtp']['encryption'];
        $mail->Port = $emailConfig['smtp']['port'];
        $mail->CharSet = $emailConfig['settings']['charset'];
        $mail->Timeout = $emailConfig['settings']['timeout'];
        $mail->WordWrap = $emailConfig['settings']['word_wrap'];
        
        // Enable debug output if needed
        $mail->SMTPDebug = $emailConfig['settings']['debug'];
        
        // Recipients
        $mail->setFrom($emailConfig['smtp']['from_email'], $emailConfig['smtp']['from_name']);
        $mail->addAddress($userEmail, $userName);
        $mail->addReplyTo($emailConfig['templates']['suspension']['support_email'], 'Sales-Spy Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Sales-Spy - Your ' . ucfirst($plan) . ' Subscription is Active!';
        
        // Format dates
        $formattedStartDate = date('F j, Y', strtotime($startDate));
        $formattedEndDate = $endDate ? date('F j, Y', strtotime($endDate)) : 'Lifetime';
        
        // Create email body
        $mail->Body = getWelcomeEmailTemplate($userName, $plan, $duration, $formattedStartDate, $formattedEndDate, $credits, $emailConfig);
        
        // Alternative text body
        $mail->AltBody = getWelcomeEmailTextTemplate($userName, $plan, $duration, $formattedStartDate, $formattedEndDate, $credits);
        
        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function getWelcomeEmailTemplate($userName, $plan, $duration, $startDate, $endDate, $credits, $config) {
    $planDetails = [
        'free' => ['name' => 'Free Plan', 'color' => '#6B7280'],
        'basic' => ['name' => 'Basic Plan', 'color' => '#F59E0B'],
        'pro' => ['name' => 'Pro Plan', 'color' => '#3B82F6'],
        'enterprise' => ['name' => 'Enterprise Plan', 'color' => '#8B5CF6']
    ];
    
    $planInfo = $planDetails[$plan] ?? ['name' => ucfirst($plan) . ' Plan', 'color' => '#6B7280'];
    $companyName = $config['templates']['suspension']['company_name'];
    $websiteUrl = $config['templates']['suspension']['website_url'];
    $supportEmail = $config['templates']['suspension']['support_email'];
    
    $emailBody = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Welcome to {$companyName}</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); color: white; padding: 40px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
            .content { padding: 40px 30px; }
            .welcome-text { font-size: 18px; color: #374151; margin-bottom: 30px; line-height: 1.6; }
            .plan-card { background: #f8fafc; border: 2px solid {$planInfo['color']}; border-radius: 12px; padding: 25px; margin: 25px 0; }
            .plan-title { color: {$planInfo['color']}; font-size: 20px; font-weight: 600; margin-bottom: 15px; }
            .plan-details { display: grid; gap: 10px; }
            .plan-detail { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
            .plan-detail:last-child { border-bottom: none; }
            .detail-label { font-weight: 500; color: #6b7280; }
            .detail-value { font-weight: 600; color: #1f2937; }
            .cta-section { text-align: center; margin: 35px 0; }
            .cta-button { 
                display: inline-block; padding: 15px 30px; background: {$planInfo['color']}; 
                color: white; text-decoration: none; border-radius: 8px; font-weight: 600; 
                font-size: 16px; transition: all 0.3s ease;
            }
            .cta-button:hover { opacity: 0.9; transform: translateY(-2px); }
            .features { background: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; }
            .features h3 { color: #1f2937; margin-bottom: 15px; }
            .feature-list { list-style: none; padding: 0; margin: 0; }
            .feature-list li { padding: 8px 0; color: #4b5563; position: relative; padding-left: 25px; }
            .feature-list li:before { content: '✓'; color: #10b981; font-weight: bold; position: absolute; left: 0; }
            .footer { background-color: #f3f4f6; padding: 30px; text-align: center; color: #6b7280; }
            .footer a { color: {$planInfo['color']}; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to {$companyName}!</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Your subscription is now active</p>
            </div>
            
            <div class='content'>
                <div class='welcome-text'>
                    <p>Hi {$userName},</p>
                    <p>Welcome to {$companyName}! We're excited to have you on board. Your {$planInfo['name']} subscription has been successfully activated and you're ready to start exploring all our premium features.</p>
                </div>
                
                <div class='plan-card'>
                    <h2 class='plan-title'>{$planInfo['name']} Details</h2>
                    <div class='plan-details'>
                        <div class='plan-detail'>
                            <span class='detail-label'>Plan Type:</span>
                            <span class='detail-value'>{$planInfo['name']}</span>
                        </div>
                        <div class='plan-detail'>
                            <span class='detail-label'>Duration:</span>
                            <span class='detail-value'>" . ucfirst(str_replace(['1month', '3months', '6months', '1year'], ['1 Month', '3 Months', '6 Months', '1 Year'], $duration)) . "</span>
                        </div>
                        <div class='plan-detail'>
                            <span class='detail-label'>Start Date:</span>
                            <span class='detail-value'>{$startDate}</span>
                        </div>
                        <div class='plan-detail'>
                            <span class='detail-label'>End Date:</span>
                            <span class='detail-value'>{$endDate}</span>
                        </div>
                        <div class='plan-detail'>
                            <span class='detail-label'>Credits Available:</span>
                            <span class='detail-value'>" . number_format($credits) . " credits</span>
                        </div>
                    </div>
                </div>
                
                <div class='features'>
                    <h3>What's included in your {$planInfo['name']}:</h3>
                    <ul class='feature-list'>";
                    
    // Add plan-specific features
    switch($plan) {
        case 'free':
            $features = [
                number_format($credits) . " monthly search credits",
                "Basic product research tools",
                "Standard support",
                "Access to community forum"
            ];
            break;
        case 'basic':
            $features = [
                "Everything in Free plan",
                number_format($credits) . " monthly search credits",
                "Advanced filtering options",
                "Email support",
                "Export data to CSV"
            ];
            break;
        case 'pro':
            $features = [
                "Everything in Basic plan", 
                number_format($credits) . " monthly search credits",
                "Advanced analytics dashboard",
                "Priority support",
                "API access",
                "Custom reports"
            ];
            break;
        case 'enterprise':
            $features = [
                "Everything in Pro plan",
                number_format($credits) . "+ monthly search credits",
                "Dedicated account manager",
                "24/7 phone support",
                "Custom integrations",
                "Advanced team features"
            ];
            break;
        default:
            $features = [
                number_format($credits) . " monthly search credits",
                "Access to Sales-Spy platform"
            ];
    }
    
    foreach($features as $feature) {
        $emailBody .= "<li>{$feature}</li>";
    }
    
    $emailBody .= "
                    </ul>
                </div>
                
                <div class='cta-section'>
                    <a href='{$websiteUrl}' class='cta-button'>Start Exploring Sales-Spy</a>
                </div>
                
                <div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                    <h3 style='color: #92400e; margin: 0 0 10px 0;'>Getting Started Tips:</h3>
                    <ul style='color: #92400e; margin: 0; padding-left: 20px;'>
                        <li>Complete your profile setup for better results</li>
                        <li>Explore our tutorial section to maximize your experience</li>
                        <li>Join our community forum to connect with other users</li>
                    </ul>
                </div>
                
                <p style='color: #6b7280; font-size: 14px; line-height: 1.6;'>
                    If you have any questions or need assistance getting started, don't hesitate to reach out to our support team at 
                    <a href='mailto:{$supportEmail}' style='color: {$planInfo['color']};'>{$supportEmail}</a>. 
                    We're here to help you succeed!
                </p>
            </div>
            
            <div class='footer'>
                <p><strong>{$companyName}</strong></p>
                <p>This email was sent because a subscription was created for your account.</p>
                <p>
                    <a href='{$websiteUrl}'>Visit Website</a> | 
                    <a href='mailto:{$supportEmail}'>Contact Support</a>
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    return $emailBody;
}

function getWelcomeEmailTextTemplate($userName, $plan, $duration, $startDate, $endDate, $credits) {
    return "Welcome to Sales-Spy!

Hi {$userName},

Your " . ucfirst($plan) . " subscription has been successfully activated!

Subscription Details:
- Plan: " . ucfirst($plan) . " Plan
- Duration: " . ucfirst(str_replace(['1month', '3months', '6months', '1year'], ['1 Month', '3 Months', '6 Months', '1 Year'], $duration)) . "
- Start Date: {$startDate}
- End Date: {$endDate}
- Credits Available: " . number_format($credits) . " credits

You can now access all the premium features included in your plan. Visit our website to get started.

If you have any questions, please contact our support team.

Best regards,
The Sales-Spy Team";
}

// Get admin info for logging
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$admin_name = $admin ? $admin['name'] : 'Unknown';

try {
    $pdo->beginTransaction();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User with this email does not exist in the system');
    }
    
    $user_id = $user['id'];
    $user_name = $user['full_name'];
    
    // Check if user already has an active subscription
    $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status IN ('active', 'trial')");
    $stmt->execute([$user_id]);
    $existing_sub = $stmt->fetch();
    
    if ($existing_sub) {
        throw new Exception('User already has an active subscription');
    }
    
    // Calculate subscription details
    $start_date = date('Y-m-d H:i:s');
    $end_date = null;
    
    // Calculate end date based on duration (only for paid plans)
    if ($plan !== 'free') {
        switch ($duration) {
            case '1month':
                $end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
                break;
            case '3months':
                $end_date = date('Y-m-d H:i:s', strtotime('+3 months'));
                break;
            case '6months':
                $end_date = date('Y-m-d H:i:s', strtotime('+6 months'));
                break;
            case '1year':
                $end_date = date('Y-m-d H:i:s', strtotime('+1 year'));
                break;
            case 'lifetime':
                $end_date = null; // Lifetime subscription
                break;
        }
    }
    
    // Credits are already fetched from database above
    
    // Insert or update subscription
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (
            user_id, plan_name, status, start_date, end_date, 
            credits_total, credits_remaining, is_active, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            plan_name = VALUES(plan_name),
            status = VALUES(status),
            start_date = VALUES(start_date),
            end_date = VALUES(end_date),
            credits_total = VALUES(credits_total),
            credits_remaining = VALUES(credits_remaining),
            is_active = VALUES(is_active),
            updated_at = NOW()
    ");
    
    $status = ($plan === 'free') ? 'active' : 'active';
    $is_active = 1;
    
    $stmt->execute([
        $user_id, $plan, $status, $start_date, $end_date,
        $credits, $credits, $is_active
    ]);
    
    // Log the admin action
    $details = json_encode([
        'admin_id' => $admin_id,
        'admin_name' => $admin_name,
        'action' => 'subscription_created',
        'user_id' => $user_id,
        'user_name' => $user_name,
        'user_email' => $email,
        'plan' => $plan,
        'duration' => $duration,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'credits' => $credits,
        'notes' => $notes
    ]);
    
    // Log in security logs
    $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        'subscription_created_by_admin',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
    
    // Log in admin actions table
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$admin_id, 'subscription_created', $user_id, $details]);
    
    // Log in subscription history
    $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, 'created', $details, $admin_id]);
    
    // Send welcome email if requested
    $email_result = null;
    if ($send_email) {
        $email_result = sendWelcomeEmail($email, $user_name, $plan, $duration, $start_date, $end_date, $credits);
        
        // Log email sending attempt
        /*$email_log_details = json_encode([
            'recipient' => $email,
            'subject' => 'Welcome email for new subscription',
            'success' => $email_result['success'],
            'error' => $email_result['error'] ?? null,
            'admin_id' => $admin_id
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO email_logs (user_id, email_type, recipient, subject, status, details, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user_id,
            'subscription_welcome',
            $email,
            'Welcome to Sales-Spy - Your ' . ucfirst($plan) . ' Subscription is Active!',
            $email_result['success'] ? 'sent' : 'failed',
            $email_log_details
        ]);*/
    }
    
    $pdo->commit();
    
    $response = [
        'success' => true, 
        'message' => "Subscription created successfully for $email",
        'subscription' => [
            'user_id' => $user_id,
            'plan' => $plan,
            'duration' => $duration,
            'credits' => $credits,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]
    ];
    
    // Add email status to response if email was requested
    if ($send_email) {
        $response['email_sent'] = $email_result['success'];
        if (!$email_result['success']) {
            $response['email_error'] = $email_result['error'];
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Create subscription error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>