<?php
require_once 'config/db.php';

// Set headers for plain text output
header('Content-Type: text/plain');

// Simulate a request to filter_stores.php
$url = BASE_URL . 'api/filter_stores.php';

// Use cURL to make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Execute the request
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Output request info
echo "Request URL: {$url}\n";
echo "HTTP Status: {$info['http_code']}\n\n";

// Output raw response
echo "Raw Response:\n";
echo $response;
echo "\n\n";

// Try to decode the JSON and check for errors
echo "JSON Validation:\n";
$json_data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    
    // Additional debugging - check for UTF-8 BOM and other common issues
    $first_chars = substr($response, 0, 20);
    $hex_chars = bin2hex($first_chars);
    echo "First 20 characters (hex): {$hex_chars}\n";
    
    // Check for specific characters that might cause issues
    echo "Character analysis:\n";
    for ($i = 0; $i < min(100, strlen($response)); $i++) {
        $char = $response[$i];
        $ord = ord($char);
        if ($ord < 32 || $ord > 127) {
            echo "Position {$i}: Non-printable character (ASCII {$ord}, hex: " . bin2hex($char) . ")\n";
        }
    }
} else {
    echo "JSON is valid.\n";
    echo "Data structure: " . print_r(array_keys($json_data), true) . "\n";
}