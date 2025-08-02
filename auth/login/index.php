<?php
require '../../config/db.php';
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
        $current_time = new DateTime();

        // Check if account is disabled
        if ($user['account_status'] === 'disabled') {
            header('Location:' . BASE_URL . 'signup.php?form=login&status=account_disabled');
            exit;
        }

        // Handle auto-unlock if account is locked
        if ($user['account_status'] === 'locked') {
            if (!empty($user['unlock_time'])) {
                $unlock_time = new DateTime($user['unlock_time']);
                if ($current_time >= $unlock_time) {
                    // Unlock account after 1 hour
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
                $stmt = $pdo->prepare("UPDATE user_sessions SET session_id = ?, last_active = NOW(), city = ?, country = ? WHERE id = ?");
                $stmt->execute([$session_id, $city, $country, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, city, country) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $session_id, $ip_address, $user_agent, $city, $country]);
            }

            header('Location:' . BASE_URL . 'home/index.php?status=login_success');
            exit;
        } else {
            // Wrong password - increment failed_attempts
            $new_failed = $user['failed_attempts'] + 1;

            if ($new_failed >= 5) {
                $unlock_time = $current_time->modify('+1 hour')->format('Y-m-d H:i:s');
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_failed_attempt = NOW(), account_status = 'locked', unlock_time = ? WHERE email = ?");
                $stmt->execute([$new_failed, $unlock_time, $email]);
                header('Location:' . BASE_URL . 'signup.php?form=login&status=account_locked_pword');
            } else {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, last_failed_attempt = NOW() WHERE email = ?");
                $stmt->execute([$new_failed, $email]);
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
