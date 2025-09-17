<?php
require '../../config/db.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];


    if (empty($email) || empty($password)) {
        header('Location:' . BASE_URL . 'signup.php?form=login&status=empty_fields');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location:' . BASE_URL . 'signup.php?form=login&status=invalid_email');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $now = new DateTime('now', new DateTimeZone('UTC'));

        // Check if account is disabled or deleted
        if ($user['account_status'] === 'deleted') {
            header('Location:' . BASE_URL . 'signup.php?form=login&status=account_deleted');
            exit;
        }elseif ($user['account_status'] === 'disabled') {
            header('Location:' . BASE_URL . 'signup.php?form=login&status=account_disabled');
            exit;
        }

        // Handle auto-unlock if account is locked
        if ($user['account_status'] === 'locked') {
            if (!empty($user['unlock_time'])) {
                $unlock_time = new DateTime($user['unlock_time'], new DateTimeZone('UTC'));
                if ($now >= $unlock_time) {
                    // Unlock account
                    $stmt = $pdo->prepare("UPDATE users SET account_status = 'active', failed_attempts = 0, last_failed_attempt = NULL, unlock_time = NULL WHERE email = ?");
                    $stmt->execute([$email]);

                    header('Location:' . BASE_URL . 'signup.php?form=login&status=account_unlocked');
                    exit;
                } else {
                    header('Location:' . BASE_URL . 'signup.php?form=login&status=account_locked_pword');
                    exit;
                }
            } else {
                header('Location:' . BASE_URL . 'signup.php?form=login&status=account_locked_pword');
                exit;
            }
        }

        // Check password
        if (password_verify($password, $user['password'])) {
            // Login success - reset failed attempts
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL, account_status = 'active', unlock_time = NULL WHERE email = ?");
            $stmt->execute([$email]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            $session_id = session_id();
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];

            $geo_json = @file_get_contents("http://ip-api.com/json/" . urlencode($ip_address));
            $geo_data = json_decode($geo_json, true);

            $city = ($geo_data && $geo_data['status'] === 'success' && !empty($geo_data['city'])) ? $geo_data['city'] : 'Unknown';
            $country = ($geo_data && $geo_data['status'] === 'success' && !empty($geo_data['country'])) ? $geo_data['country'] : 'Unknown';

            $stmt = $pdo->prepare("SELECT id FROM user_sessions WHERE user_id = ? AND ip_address = ? AND user_agent = ?");
            $stmt->execute([$user['id'], $ip_address, $user_agent]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE user_sessions SET session_id = ?, last_active = ?, city = ?, country = ? WHERE id = ?");
                $stmt->execute([$session_id, $now->format('Y-m-d H:i:s'), $city, $country, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, city, country) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $session_id, $ip_address, $user_agent, $city, $country]);
            }

            // Send login notification email

$mail = new PHPMailer(true);

try {
    $login_time = $now->format('Y-m-d H:i:s');
    $subject = "New Login to Your Sales-Spy Account";

    $body = "
        <h2>Hello {$user['full_name']},</h2>
        <p>We noticed a new login to your account:</p>
        <ul>
            <li><strong>Time (UTC):</strong> {$login_time}</li>
            <li><strong>IP Address:</strong> {$ip_address}</li>
            <li><strong>Location:</strong> {$city}, {$country}</li>
            <li><strong>Device:</strong> {$user_agent}</li>
        </ul>
        <p>If this was you, you can ignore this message. If not, please change your password immediately.</p>
        <p>— Sales-Spy Security Team</p>
    ";

    $mail->isSMTP();
    $mail->Host       = 'smtp.yourhost.com'; // Your SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your@email.com';
    $mail->Password   = 'your_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('no-reply@sales-spy.com', 'Sales-Spy Security');
    $mail->addAddress($user['email'], $user['full_name']);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
} catch (Exception $e) {
    error_log("Login email could not be sent. Mailer Error: {$mail->ErrorInfo}");
}


            header('Location:' . BASE_URL . 'home/');
            exit;
        } else {
            // Wrong password - increment failed_attempts
            $new_failed = $user['failed_attempts'] + 1;
            $now_str = $now->format('Y-m-d H:i:s');

            if ($new_failed >= 5) {
                $unlock_time = $now->modify('+300 seconds')->format('Y-m-d H:i:s');
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_failed_attempt = ?, account_status = 'locked', unlock_time = ? WHERE email = ?");
                $stmt->execute([$new_failed, $now_str, $unlock_time, $email]);

                header('Location:' . BASE_URL . 'signup.php?form=login&status=account_locked_pword');
            } else {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_failed_attempt = ? WHERE email = ?");
                $stmt->execute([$new_failed, $now_str, $email]);

                header('Location:' . BASE_URL . 'signup.php?form=login&status=invalid_credentials');
            }
            exit;
        }
    } else {
        header('Location:' . BASE_URL . 'signup.php?form=login&status=user_not_found');
        exit;
    }
}

header('Location:' . BASE_URL . 'signup.php?form=login');
exit;
