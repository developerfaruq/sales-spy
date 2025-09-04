<?php
require '../../../config/db.php';
session_start();

if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $_SESSION['admin_id'] = $_COOKIE['admin_remember'];
    // Optionally fetch admin info again from DB
}

// Redirect to login if still not authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../");
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            // Get wallet information
            $stmt = $pdo->query("SELECT * FROM payment_wallets WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($wallet) {
                echo json_encode([
                    'success' => true,
                    'wallet' => $wallet
                ]);
            } else {
                // Return default wallet if none exists
                echo json_encode([
                    'success' => true,
                    'wallet' => [
                        'id' => null,
                        'network' => 'TRC-20',
                        'currency' => 'USDT',
                        'wallet_address' => '',
                        'instructions' => 'Send only USDT (TRC-20) to this address. Minimum confirmation required: 1.',
                        'is_active' => 1
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Update or create wallet information
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            // Validate required fields
            if (empty($input['wallet_name']) || empty($input['wallet_address'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Wallet name and address are required']);
                exit;
            }
            
            // Validate wallet address format (basic validation)
            $wallet_address = trim($input['wallet_address']);
            if (strlen($wallet_address) < 20 || strlen($wallet_address) > 50) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid wallet address format']);
                exit;
            }
            
            $wallet_name = trim($input['wallet_name']);
            $network = $input['token_standard'] ?? 'TRC-20';
            $currency = $input['blockchain'] ?? 'USDT';
            $instructions = "Send only $currency ($network) to this address. Minimum confirmation required: 1.";
            
            $pdo->beginTransaction();
            
            try {
                // Deactivate all existing wallets
                $stmt = $pdo->prepare("UPDATE payment_wallets SET is_active = 0");
                $stmt->execute();
                
                // Check if wallet with this address already exists
                $stmt = $pdo->prepare("SELECT id FROM payment_wallets WHERE wallet_address = ?");
                $stmt->execute([$wallet_address]);
                $existing_wallet = $stmt->fetch();
                
                if ($existing_wallet) {
                    // Update existing wallet
                    $stmt = $pdo->prepare("
                        UPDATE payment_wallets 
                        SET network = ?, currency = ?, instructions = ?, is_active = 1, updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ");
                    $stmt->execute([$network, $currency, $instructions, $existing_wallet['id']]);
                    $wallet_id = $existing_wallet['id'];
                } else {
                    // Create new wallet
                    $stmt = $pdo->prepare("
                        INSERT INTO payment_wallets (network, currency, wallet_address, instructions, is_active) 
                        VALUES (?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([$network, $currency, $wallet_address, $instructions]);
                    $wallet_id = $pdo->lastInsertId();
                }
                
                // Log admin action
                $admin_id = $_SESSION['admin_id'];
                $admin_name = $_SESSION['admin_name'] ?? 'Admin';
                
                $action_details = json_encode([
                    'admin_id' => $admin_id,
                    'admin_name' => $admin_name,
                    'action' => 'wallet_updated',
                    'wallet_name' => $wallet_name,
                    'wallet_address' => $wallet_address,
                    'network' => $network,
                    'currency' => $currency
                ]);
                
                $stmt = $pdo->prepare("
                    INSERT INTO admin_actions (admin_id, action_type, target_type, details, ip_address, user_agent) 
                    VALUES (?, 'wallet_updated', 'system', ?, ?, ?)
                ");
                $stmt->execute([
                    $admin_id,
                    $action_details,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $pdo->commit();
                
                // Return updated wallet info
                $stmt = $pdo->prepare("SELECT * FROM payment_wallets WHERE id = ?");
                $stmt->execute([$wallet_id]);
                $updated_wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Wallet information updated successfully',
                    'wallet' => $updated_wallet
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>