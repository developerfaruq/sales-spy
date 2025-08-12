<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

header('Content-Type: application/json');

// Load Etsy credentials from .env
$etsy_api_key = getenv('ETSY_API_KEY') ?: '';
$etsy_access_token = getenv('ETSY_ACCESS_TOKEN') ?: '';
$etsy_shop_id = getenv('ETSY_SHOP_ID') ?: '';

// Validate credentials
if ($etsy_access_token === '' || $etsy_shop_id === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Missing Etsy credentials (ETSY_ACCESS_TOKEN and ETSY_SHOP_ID) in .env']);
    exit;
}

// Etsy API endpoint: active listings for a shop
$url = "https://openapi.etsy.com/v3/application/shops/{$etsy_shop_id}/listings/active";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $etsy_access_token,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

try {
    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($statusCode >= 400) {
        http_response_code($statusCode);
        echo json_encode(['error' => 'Etsy API error', 'status' => $statusCode, 'body' => $response]);
        exit;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
    }

    echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    curl_close($ch);
}

