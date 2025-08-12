<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

header('Content-Type: application/json');

// Load WordPress/WooCommerce credentials from environment
$api_url = rtrim(getenv('WORDPRESS_API_URL') ?: '', '/');
$consumer_key = getenv('WORDPRESS_CONSUMER_KEY') ?: '';
$consumer_secret = getenv('WORDPRESS_CONSUMER_SECRET') ?: '';

if ($api_url === '' || $consumer_key === '' || $consumer_secret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Missing WordPress/WooCommerce credentials in .env']);
    exit;
}

// WooCommerce REST API endpoint
$url = $api_url . '/wp-json/wc/v3/products';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . base64_encode($consumer_key . ':' . $consumer_secret),
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
        echo json_encode(['error' => 'WooCommerce API error', 'status' => $statusCode, 'body' => $response]);
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