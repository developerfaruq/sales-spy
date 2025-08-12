<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env if present, but scrapers do not require credentials
if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

/**
 * Fetch a URL with a browser-like User-Agent and short delay to avoid being blocked.
 * Returns ['status' => int, 'body' => string, 'headers' => array].
 */
function fetchUrl(string $url, int $timeoutSeconds = 15): array
{
    // Small delay between requests
    usleep(200000); // 200ms

    $ch = curl_init();
    $headers = [];
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
        CURLOPT_TIMEOUT => $timeoutSeconds,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36 SalesSpyBot/1.0',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.8',
        ],
        CURLOPT_HEADERFUNCTION => function ($curl, $headerLine) use (&$headers) {
            $len = strlen($headerLine);
            $parts = explode(':', $headerLine, 2);
            if (count($parts) === 2) {
                $headers[strtolower(trim($parts[0]))][] = trim($parts[1]);
            }
            return $len;
        },
    ]);

    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($body === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'status' => 0,
            'body' => '',
            'headers' => $headers,
            'error' => 'cURL error: ' . $error,
        ];
    }
    curl_close($ch);
    return ['status' => $status, 'body' => (string) $body, 'headers' => $headers];
}

/**
 * Very simple robots.txt checker for User-agent: * rules.
 * Returns true if the given URL path is allowed.
 */
function isAllowedByRobots(string $url): bool
{
    $parts = parse_url($url);
    if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
        return true; // be permissive if malformed
    }
    $robotsUrl = $parts['scheme'] . '://' . $parts['host'] . '/robots.txt';
    $response = fetchUrl($robotsUrl);
    if ($response['status'] !== 200 || trim($response['body']) === '') {
        return true; // assume allowed if robots not accessible
    }
    $lines = preg_split('/\r?\n/', $response['body']);
    $applies = false;
    $disallows = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (stripos($line, 'User-agent:') === 0) {
            $ua = trim(substr($line, strlen('User-agent:')));
            $applies = ($ua === '*' || stripos($ua, 'SalesSpyBot') !== false);
            continue;
        }
        if ($applies && stripos($line, 'Disallow:') === 0) {
            $path = trim(substr($line, strlen('Disallow:')));
            $disallows[] = $path;
        }
    }
    $path = $parts['path'] ?? '/';
    foreach ($disallows as $rule) {
        if ($rule === '') {
            continue; // empty disallow means allow all
        }
        if ($rule === '/') {
            return false;
        }
        if (str_starts_with($path, $rule)) {
            return false;
        }
    }
    return true;
}

/**
 * Normalize a list of product-like arrays to the expected structure.
 * Each input item may be a partial associative array; missing values will be filled.
 */
function normalizeProducts(array $items, string $platform, string $storeName): array
{
    $now = date('c');
    $normalized = [];
    foreach ($items as $item) {
        $normalized[] = [
            'platform' => $platform,
            'store_name' => $item['store_name'] ?? $storeName,
            'product_title' => $item['product_title'] ?? ($item['title'] ?? ''),
            'price' => isset($item['price']) ? (string) $item['price'] : '',
            'availability' => $item['availability'] ?? ($item['in_stock'] ?? ''),
            'last_updated' => $item['last_updated'] ?? $now,
        ];
    }
    return $normalized;
}

/**
 * Try to decode JSON safely and return array or throw.
 */
function decodeJson(string $json): array
{
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('JSON decode error: ' . json_last_error_msg());
    }
    return is_array($data) ? $data : [];
}


