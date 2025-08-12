<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

header('Content-Type: application/json');

// Load Shopify credentials from environment
$api_key = getenv('SHOPIFY_API_KEY') ?: '';
$api_secret = getenv('SHOPIFY_API_SECRET') ?: '';
$access_token = getenv('SHOPIFY_ACCESS_TOKEN') ?: '';
$store_domain = getenv('SHOPIFY_STORE_DOMAIN') ?: '';

// Validate credentials
if ($api_key === '' || $api_secret === '' || $access_token === '' || $store_domain === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Missing Shopify credentials in .env file']);
    exit;
}

// Initialize cURL request
$url = "https://{$store_domain}/admin/api/2023-07/products.json";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'X-Shopify-Access-Token: ' . $access_token,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

// Execute request
try {
    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($statusCode >= 400) {
        http_response_code($statusCode);
        echo json_encode(['error' => 'Shopify API error', 'status' => $statusCode, 'body' => $response]);
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