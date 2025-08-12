<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

header('Content-Type: application/json');

// Load Squarespace credentials from .env
$api_token = getenv('SQUARESPACE_API_TOKEN') ?: '';

// Validate credentials
if ($api_token === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Missing Squarespace API token in .env']);
    exit;
}

// Squarespace Commerce API endpoint
$url = 'https://api.squarespace.com/1.0/commerce/products';

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $api_token,
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
        echo json_encode(['error' => 'Squarespace API error', 'status' => $statusCode, 'body' => $response]);
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