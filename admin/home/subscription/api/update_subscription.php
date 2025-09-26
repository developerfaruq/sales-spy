<?php
require '../../../config/db.php';
require 'auth_check.php';
// Email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../../../vendor/autoload.php';
function sendSubscriptionEmail($toEmail, $toName, $type, $details = []) {
    $config = require '../../../config/email_config.php';
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        $mail->CharSet = $config['settings']['charset'];
        $mail->Timeout = $config['settings']['timeout'];
        $mail->WordWrap = $config['settings']['word_wrap'];
        $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        // Beautiful HTML and text templates similar to create_subscription email
        $subjects = [
            'paused' => 'Your Sales-Spy Subscription Has Been Paused',
            'resumed' => 'Welcome Back — Your Sales-Spy Subscription Is Active Again',
            'cancelled' => 'Confirmation: Your Sales-Spy Subscription Has Been Cancelled'
        ];
        $mail->Subject = $subjects[$type] ?? 'Sales-Spy Subscription Update';
        $mail->Body = getActionEmailTemplate($toName, $type, $details, $config);
        $mail->AltBody = getActionEmailTextTemplate($toName, $type, $details, $config);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Subscription email error: ' . $e->getMessage());
        return false;
    }
}

function getActionEmailTemplate($userName, $type, $details, $config) {
    $companyName = $config['templates']['suspension']['company_name'] ?? 'Sales-Spy';
    $websiteUrl = $config['templates']['suspension']['website_url'] ?? '#';
    $supportEmail = $config['templates']['suspension']['support_email'] ?? ($config['smtp']['from_email'] ?? 'support@example.com');
    $palette = [
        'paused' => '#3B82F6',
        'resumed' => '#3B82F6',
        'cancelled' => '#3B82F6'
    ];
    $title = [
        'paused' => 'Your subscription is paused',
        'resumed' => 'Your subscription is now active',
        'cancelled' => 'Your subscription was cancelled'
    ][$type] ?? 'Subscription update';
    $color = $palette[$type] ?? '#3B82F6';

    $reasonLine = !empty($details['reason']) ? '<div class="plan-detail"><span class="detail-label">Reason:</span><span class="detail-value">' . htmlspecialchars($details['reason']) . '</span></div>' : '';
    $durationPretty = '';
    if (!empty($details['duration'])) {
        $map = [
            '1week' => '1 Week',
            '2weeks' => '2 Weeks',
            '1month' => '1 Month',
            '3months' => '3 Months',
            '6months' => '6 Months',
            '1year' => '1 Year'
        ];
        $durationPretty = '<div class="plan-detail"><span class="detail-label">Duration:</span><span class="detail-value">' . htmlspecialchars($map[$details['duration']] ?? $details['duration']) . '</span></div>';
    }
    $pauseEndLine = !empty($details['pause_end']) ? '<div class="plan-detail"><span class="detail-label">Pause Ends:</span><span class="detail-value">' . date('F j, Y', strtotime($details['pause_end'])) . '</span></div>' : '';
    $resumedAtLine = !empty($details['resumed_at']) ? '<div class="plan-detail"><span class="detail-label">Resumed At:</span><span class="detail-value">' . date('F j, Y, g:i A', strtotime($details['resumed_at'])) . '</span></div>' : '';

    $intro = [
        'paused' => "Your subscription has been temporarily paused. You won't lose your data, and you can resume anytime.",
        'resumed' => "Great news! Your subscription is active again. Welcome back to all premium features.",
        'cancelled' => "This is a confirmation that your subscription has been cancelled. We're sorry to see you go."
    ][$type] ?? 'Subscription status updated.';

    $cta = [
        'paused' => ['Contact Support', 'mailto:' . $supportEmail],
        'resumed' => ['Open Sales-Spy', $websiteUrl],
        'cancelled' => ['Visit Sales-Spy', $websiteUrl]
    ][$type];

    $emailBody = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$companyName} Subscription Update</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); color: white; padding: 32px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
            .content { padding: 32px 30px; }
            .welcome-text { font-size: 16px; color: #374151; margin-bottom: 24px; line-height: 1.6; }
            .plan-card { background: #f8fafc; border: 2px solid {$color}; border-radius: 12px; padding: 20px; margin: 20px 0; }
            .plan-title { color: {$color}; font-size: 18px; font-weight: 600; margin-bottom: 12px; }
            .plan-details { display: grid; gap: 10px; }
            .plan-detail { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
            .plan-detail:last-child { border-bottom: none; }
            .detail-label { font-weight: 500; color: #6b7280; }
            .detail-value { font-weight: 600; color: #1f2937; }
            .cta-section { text-align: center; margin: 28px 0; }
            .cta-button { display: inline-block; padding: 12px 24px; background: {$color}; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; }
            .footer { background-color: #f3f4f6; padding: 24px; text-align: center; color: #6b7280; }
            .footer a { color: {$color}; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>{$companyName} — {$title}</h1>
            </div>
            <div class='content'>
                <div class='welcome-text'>
                    <p>Hi " . htmlspecialchars($userName) . ",</p>
                    <p>{$intro}</p>
                </div>
                <div class='plan-card'>
                    <h2 class='plan-title'>Subscription Details</h2>
                    <div class='plan-details'>
                        {$reasonLine}
                        {$durationPretty}
                        {$pauseEndLine}
                        {$resumedAtLine}
                    </div>
                </div>
                <div class='cta-section'>
                    <a href='" . htmlspecialchars($cta[1]) . "' class='cta-button'>" . htmlspecialchars($cta[0]) . "</a>
                </div>
                <p style='color: #6b7280; font-size: 14px; line-height: 1.6;'>
                    Need help? Contact our team at <a href='mailto:{$supportEmail}' style='color: {$color};'>{$supportEmail}</a>.
                </p>
            </div>
            <div class='footer'>
                <p><strong>{$companyName}</strong></p>
                <p>This email is about a subscription status change on your account.</p>
                <p><a href='{$websiteUrl}'>Visit Website</a> | <a href='mailto:{$supportEmail}'>Contact Support</a></p>
            </div>
        </div>
    </body>
    </html>";

    return $emailBody;
}

function getActionEmailTextTemplate($userName, $type, $details, $config) {
    $title = [
        'paused' => 'Subscription Paused',
        'resumed' => 'Subscription Resumed',
        'cancelled' => 'Subscription Cancelled'
    ][$type] ?? 'Subscription Update';
    $lines = [
        'paused' => "Your subscription has been paused.",
        'resumed' => "Your subscription is active again.",
        'cancelled' => "Your subscription was cancelled."
    ][$type] ?? 'Subscription status changed.';
    $reason = !empty($details['reason']) ? "Reason: {$details['reason']}\n" : '';
    $duration = !empty($details['duration']) ? "Duration: {$details['duration']}\n" : '';
    $pauseEnd = !empty($details['pause_end']) ? "Pause Ends: " . date('F j, Y', strtotime($details['pause_end'])) . "\n" : '';
    $resumedAt = !empty($details['resumed_at']) ? "Resumed At: " . date('F j, Y, g:i A', strtotime($details['resumed_at'])) . "\n" : '';

    return "$title

Hi {$userName},

{$lines}
{$reason}{$duration}{$pauseEnd}{$resumedAt}
If you need help, reply to this email.

Sales-Spy Team";
}

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

$user_id = $input['user_id'] ?? null;
$action = $input['action'] ?? null;

if (!$user_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Get admin info for logging
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$admin_name = $admin ? $admin['name'] : 'Unknown';

// Get user info
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user_name = $user['full_name'];
$user_email = $user['email'];

try {
    $pdo->beginTransaction();
    
    switch ($action) {
        case 'change_plan':
    $new_plan = $input['plan'] ?? null;
    if (!$new_plan || !in_array($new_plan, ['free', 'pro', 'enterprise'])) {
        throw new Exception('Invalid plan selected');
    }
    
    // Calculate end date based on plan (1 month from now for paid plans)
    $end_date = null;
    if ($new_plan !== 'free') {
        $end_date = date('Y-m-d H:i:s', strtotime('+1 month'));
    }
    
    // Update subscription plan and end date
    $stmt = $pdo->prepare("UPDATE subscriptions SET plan_name = ?, end_date = ? WHERE user_id = ?");
    $stmt->execute([$new_plan, $end_date, $user_id]);
    
    // Update credits based on plan
    $credits_map = [
        'free' => 1000,
        'pro' => 2000,
        'enterprise' => 10000
    ];
    
    $new_credits = $credits_map[$new_plan];
    $stmt = $pdo->prepare("UPDATE subscriptions SET credits_total = ?, credits_remaining = ? WHERE user_id = ?");
    $stmt->execute([$new_credits, $new_credits, $user_id]);
    
    // Log the admin action
    $details = json_encode([
        'admin_id' => $admin_id,
        'admin_name' => $admin_name,
        'action' => 'plan_changed',
        'old_plan' => 'N/A', 
        'new_plan' => $new_plan,
        'end_date' => $end_date,
        'user_name' => $user_name,
        'user_email' => $user_email
    ]);
    
    $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        'plan_changed_by_admin',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
    
    // Log in admin actions table 
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$admin_id, 'subscription_plan_changed', $user_id, $details]);
    
    $message = "Subscription plan changed to {$new_plan} successfully";
    if ($end_date) {
        $message .= " (expires on " . date('Y-m-d', strtotime($end_date)) . ")";
    }
    break;
            
        case 'pause':
            $reason = $input['reason'] ?? '';
            $duration = $input['duration'] ?? '1month';
            $send_email = array_key_exists('send_email', $input) ? !empty($input['send_email']) : true;
            
            // Calculate pause end date
            $pause_end = date('Y-m-d H:i:s');
            switch ($duration) {
                case '1week':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+1 week'));
                    break;
                case '2weeks':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+2 weeks'));
                    break;
                case '1month':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+1 month'));
                    break;
                case '3months':
                    $pause_end = date('Y-m-d H:i:s', strtotime('+3 months'));
                    break;
            }
            
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'paused' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_paused',
                'reason' => $reason,
                'duration' => $duration,
                'pause_end' => $pause_end,
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_paused_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_paused', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'paused', $details, $admin_id]);
            
            $message = "Subscription paused successfully";
            if ($send_email) {
                try {
                    sendSubscriptionEmail($user_email, $user_name, 'paused', ['reason' => $reason, 'duration' => $duration]);
                } catch (Exception $e) {
                    error_log('Pause email error: ' . $e->getMessage());
                }
            }
            break;
            
        case 'cancel':
            $reason = $input['reason'] ?? '';
            $send_email = array_key_exists('send_email', $input) ? !empty($input['send_email']) : true;
            
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled', is_active = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_cancelled',
                'reason' => $reason,
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_cancelled_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_cancelled', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'cancelled', $details, $admin_id]);
            
            $message = "Subscription cancelled successfully";
            if ($send_email) {
                try {
                    sendSubscriptionEmail($user_email, $user_name, 'cancelled', ['reason' => $reason]);
                } catch (Exception $e) {
                    error_log('Cancel email error: ' . $e->getMessage());
                }
            }
            break;
            
        case 'resume':
            // Update subscription status
            $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'active', is_active = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Log the admin action
            $details = json_encode([
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'action' => 'subscription_resumed',
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
            
            $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event_type, ip_address, user_agent, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                'subscription_resumed_by_admin',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $details
            ]);
            
            // Log in admin actions table
            $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_user_id, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$admin_id, 'subscription_resumed', $user_id, $details]);
            
            // Log in subscription history
            $stmt = $pdo->prepare("INSERT INTO subscription_history (user_id, event_type, details, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, 'resumed', $details, $admin_id]);
            
            $message = "Subscription resumed successfully";
            $send_email = array_key_exists('send_email', $input) ? !empty($input['send_email']) : true;
            if ($send_email) {
                try {
                    sendSubscriptionEmail($user_email, $user_name, 'resumed');
                } catch (Exception $e) {
                    error_log('Resume email error: ' . $e->getMessage());
                }
            }
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Subscription update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>