<?php
require '../../../config/db.php';
session_start();

header('Content-Type: application/json');

// Basic session auth
if (!isset($_SESSION['admin_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}

// CSRF check helper
function verify_csrf($token) {
	return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function get_settings(PDO $pdo) {
	$stmt = $pdo->prepare('SELECT * FROM platform_settings WHERE id = 1 LIMIT 1');
	$stmt->execute();
	$settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
	echo json_encode(['success' => true, 'settings' => $settings]);
}

function save_settings(PDO $pdo, array $payload) {
	if (!verify_csrf($payload['csrf_token'] ?? '')) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
		return;
	}
	$sql = "UPDATE platform_settings SET platform_name = ?, payment_crypto_only = ?, payment_multiple_wallets = ?, payment_require_verification = ?, notify_payment_success = ?, notify_payment_failed = ?, notify_subscription_renewal = ?, notify_wallet_connection = ?, system_maintenance_mode = ?, system_allow_registration = ?, admin_session_timeout = ?, security_require_2fa = ?, security_ip_restriction = ? WHERE id = 1";
	$params = [
		trim((string)($payload['platform_name'] ?? 'Sales-Spy')),
		(int)($payload['payment_crypto_only'] ?? 0),
		(int)($payload['payment_multiple_wallets'] ?? 0),
		(int)($payload['payment_require_verification'] ?? 0),
		(int)($payload['notify_payment_success'] ?? 0),
		(int)($payload['notify_payment_failed'] ?? 0),
		(int)($payload['notify_subscription_renewal'] ?? 0),
		(int)($payload['notify_wallet_connection'] ?? 0),
		(int)($payload['system_maintenance_mode'] ?? 0),
		(int)($payload['system_allow_registration'] ?? 0),
		max(5, min(240, (int)($payload['admin_session_timeout'] ?? 30))),
		(int)($payload['security_require_2fa'] ?? 0),
		(int)($payload['security_ip_restriction'] ?? 0),
	];
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	echo json_encode(['success' => true]);
}

function reset_defaults(PDO $pdo, string $csrf) {
	if (!verify_csrf($csrf)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
		return;
	}
	$pdo->exec("UPDATE platform_settings SET platform_name='Sales-Spy', payment_crypto_only=0, payment_multiple_wallets=1, payment_require_verification=0, notify_payment_success=1, notify_payment_failed=1, notify_subscription_renewal=1, notify_wallet_connection=0, system_maintenance_mode=0, system_allow_registration=1, admin_session_timeout=30, security_require_2fa=0, security_ip_restriction=0 WHERE id=1");
	echo json_encode(['success' => true]);
}

function handle_upload(PDO $pdo, string $field, string $csrf) {
	if (!verify_csrf($csrf)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
		return;
	}
	if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'No file uploaded']);
		return;
	}
	$allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/x-icon' => 'ico'];
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
	finfo_close($finfo);
	if (!isset($allowed[$mime])) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid file type']);
		return;
	}
	if ($_FILES['file']['size'] > 2 * 1024 * 1024) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'File too large']);
		return;
	}
	$ext = $allowed[$mime];
	$dir = __DIR__ . '/uploads';
	if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
	$filename = $field . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
	$dest = $dir . '/' . $filename;
	if (!move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
		http_response_code(500);
		echo json_encode(['success' => false, 'error' => 'Failed to store file']);
		return;
	}
	// Store relative path for web use
	$relative = 'api/uploads/' . $filename;
	$stmt = $pdo->prepare("UPDATE platform_settings SET {$field}_path = ? WHERE id = 1");
	$stmt->execute([$relative]);
	echo json_encode(['success' => true, 'path' => $relative]);
}

function change_password(PDO $pdo, array $payload) {
	if (!verify_csrf($payload['csrf_token'] ?? '')) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
		return;
	}
	$new = (string)($payload['newPassword'] ?? '');
	if (strlen($new) < 8) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
		return;
	}
	$hash = password_hash($new, PASSWORD_BCRYPT);
	$stmt = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
	$stmt->execute([$hash, $_SESSION['admin_id']]);
	echo json_encode(['success' => true]);
}

function get_activity(PDO $pdo) {
	// Fetch latest 50 rows from admin_actions with enhanced details
	$stmt = $pdo->prepare("SELECT a.id, a.action_type, a.target_user_id, a.target_type, a.details, a.ip_address, a.user_agent, a.created_at, ad.name AS admin_name
		FROM admin_actions a
		LEFT JOIN admins ad ON ad.id = a.admin_id
		ORDER BY a.created_at DESC
		LIMIT 50");
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$activity = array_map(function ($r) {
		$details = json_decode($r['details'], true) ?: [];
		$action = $r['action_type'];
		
		// Create human-readable action descriptions
		$actionDescriptions = [
			'subscription_created' => 'Created subscription',
			'subscription_paused' => 'Paused subscription',
			'subscription_resumed' => 'Resumed subscription',
			'subscription_cancelled' => 'Cancelled subscription',
			'subscription_plan_changed' => 'Changed subscription plan',
			'transaction_approved' => 'Approved payment',
			'transaction_declined' => 'Declined payment',
			'wallet_updated' => 'Updated wallet settings',
			'user_created' => 'Created user account',
			'user_suspended' => 'Suspended user',
			'user_activated' => 'Activated user',
			'admin_login' => 'Admin login',
			'admin_logout' => 'Admin logout',
			'password_changed' => 'Changed password',
			'settings_updated' => 'Updated settings'
		];
		
		$actionText = $actionDescriptions[$action] ?? ucwords(str_replace('_', ' ', $action));
		
		// Build detailed description based on action type
		$description = $actionText;
		$additionalInfo = [];
		
		if ($r['target_user_id']) {
			$additionalInfo[] = "User ID: {$r['target_user_id']}";
		}
		
		switch ($action) {
			case 'subscription_created':
				$plan = $details['plan'] ?? 'Unknown';
				$duration = $details['duration'] ?? '';
				$credits = $details['credits'] ?? '';
				$description = "Created {$plan} subscription";
				if ($duration) $additionalInfo[] = "Duration: {$duration}";
				if ($credits) $additionalInfo[] = "Credits: " . number_format($credits);
				break;
				
			case 'subscription_paused':
				$reason = $details['reason'] ?? '';
				$duration = $details['duration'] ?? '';
				$description = "Paused subscription";
				if ($reason) $additionalInfo[] = "Reason: {$reason}";
				if ($duration) $additionalInfo[] = "Duration: {$duration}";
				break;
				
			case 'subscription_resumed':
				$description = "Resumed subscription";
				break;
				
			case 'subscription_cancelled':
				$reason = $details['reason'] ?? '';
				$description = "Cancelled subscription";
				if ($reason) $additionalInfo[] = "Reason: {$reason}";
				break;
				
			case 'subscription_plan_changed':
				$oldPlan = $details['old_plan'] ?? 'Unknown';
				$newPlan = $details['new_plan'] ?? 'Unknown';
				$description = "Changed plan from {$oldPlan} to {$newPlan}";
				break;
				
			case 'transaction_approved':
				$amount = $details['amount'] ?? '';
				$plan = $details['plan'] ?? '';
				$description = "Approved payment";
				if ($amount) $additionalInfo[] = "Amount: \${$amount}";
				if ($plan) $additionalInfo[] = "Plan: {$plan}";
				break;
				
			case 'transaction_declined':
				$amount = $details['amount'] ?? '';
				$reason = $details['reason'] ?? '';
				$description = "Declined payment";
				if ($amount) $additionalInfo[] = "Amount: \${$amount}";
				if ($reason) $additionalInfo[] = "Reason: {$reason}";
				break;
				
			case 'wallet_updated':
				$walletName = $details['wallet_name'] ?? '';
				$currency = $details['currency'] ?? '';
				$description = "Updated wallet settings";
				if ($walletName) $additionalInfo[] = "Wallet: {$walletName}";
				if ($currency) $additionalInfo[] = "Currency: {$currency}";
				break;
		}
		
		// Add user info if available
		if (isset($details['user_name'])) {
			$additionalInfo[] = "User: {$details['user_name']}";
		}
		if (isset($details['user_email'])) {
			$additionalInfo[] = "Email: {$details['user_email']}";
		}
		
		// Add IP and browser info
		if ($r['ip_address']) {
			$additionalInfo[] = "IP: {$r['ip_address']}";
		}
		
		// Parse user agent for browser info
		$browserInfo = '';
		if ($r['user_agent']) {
			if (strpos($r['user_agent'], 'Chrome') !== false) {
				$browserInfo = 'Chrome';
			} elseif (strpos($r['user_agent'], 'Firefox') !== false) {
				$browserInfo = 'Firefox';
			} elseif (strpos($r['user_agent'], 'Safari') !== false) {
				$browserInfo = 'Safari';
			} elseif (strpos($r['user_agent'], 'Edge') !== false) {
				$browserInfo = 'Edge';
			} else {
				$browserInfo = 'Unknown Browser';
			}
		}
		
		return [
			'id' => $r['id'],
			'admin_name' => $r['admin_name'],
			'action' => $action,
			'action_text' => $actionText,
			'description' => $description,
			'additional_info' => $additionalInfo,
			'browser' => $browserInfo,
			'details' => $details,
			'created_at' => $r['created_at'],
			'target_type' => $r['target_type'],
			'target_user_id' => $r['target_user_id']
		];
	}, $rows ?: []);
	
	echo json_encode(['success' => true, 'activity' => $activity]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? null;

try {
	if ($method === 'GET') {
		if ($action === 'get_settings') {
			get_settings($pdo);
			return;
		}
		if ($action === 'get_activity') {
			get_activity($pdo);
			return;
		}
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid action']);
		return;
	}
	// POST handlers
	if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
		$payload = json_decode(file_get_contents('php://input'), true) ?: [];
		switch ($payload['action'] ?? '') {
			case 'save_settings':
				save_settings($pdo, $payload);
				break;
			case 'reset_defaults':
				reset_defaults($pdo, (string)($payload['csrf_token'] ?? ''));
				break;
			case 'change_password':
				change_password($pdo, $payload);
				break;
			default:
				http_response_code(400);
				echo json_encode(['success' => false, 'error' => 'Invalid action']);
		}
		return;
	} else {
		// multipart upload
		$action = $_POST['action'] ?? '';
		$csrf = (string)($_POST['csrf_token'] ?? '');
		if ($action === 'upload_logo') { handle_upload($pdo, 'logo', $csrf); return; }
		if ($action === 'upload_favicon') { handle_upload($pdo, 'favicon', $csrf); return; }
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid action']);
		return;
	}
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Server error']);
} 