<?php
require '../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT network, currency, wallet_address, instructions 
                           FROM payment_wallets 
                           WHERE network = 'TRC-20' AND currency = 'USDT' AND is_active = 1 
                           LIMIT 1");
    $stmt->execute();
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($wallet) {
        echo json_encode([
            'success' => true,
            'wallet' => $wallet
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Wallet not found'
        ]);
    }
} catch (PDOException $e) {
    error_log("Wallet fetch error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve wallet address'
    ]);
}
exit;
?>
