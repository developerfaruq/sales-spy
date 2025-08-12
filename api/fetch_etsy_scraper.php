<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$shop = isset($_GET['shop']) ? trim((string) $_GET['shop']) : '';
$urlParam = isset($_GET['url']) ? trim((string) $_GET['url']) : '';

if ($shop === '' && $urlParam === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?shop={etsy-shop-name} or ?url={etsy-shop-url}']);
    exit;
}

// Build URL
if ($urlParam !== '') {
    $shopUrl = $urlParam;
} else {
    $shopUrl = 'https://www.etsy.com/shop/' . rawurlencode($shop);
}

if (!isAllowedByRobots($shopUrl)) {
    http_response_code(403);
    echo json_encode(['error' => 'Blocked by robots.txt']);
    exit;
}

$resp = fetchUrl($shopUrl);
if ($resp['status'] >= 400 || $resp['status'] === 0) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch Etsy shop page', 'status' => $resp['status'], 'details' => $resp['error'] ?? null]);
    exit;
}

$html = $resp['body'];

// Basic extraction: Etsy often embeds JSON in <script type="application/ld+json"> or window.__etsy__
$products = [];
try {
    if (preg_match_all('/<script type=\"application\/ld\+json\">(.*?)<\/script>/si', $html, $matches)) {
        foreach ($matches[1] as $json) {
            $data = decodeJson(html_entity_decode($json));
            if (isset($data['@type']) && $data['@type'] === 'ItemList' && isset($data['itemListElement']) && is_array($data['itemListElement'])) {
                foreach ($data['itemListElement'] as $el) {
                    $item = $el['item'] ?? [];
                    if (!is_array($item)) continue;
                    $products[] = [
                        'title' => $item['name'] ?? '',
                        'price' => $item['offers']['price'] ?? '',
                        'availability' => $item['offers']['availability'] ?? '',
                    ];
                }
            }
        }
    }
} catch (Throwable $e) {
    // ignore and fallback to empty list
}

$storeName = $shop !== '' ? $shop : parse_url($shopUrl, PHP_URL_PATH);
$normalized = normalizeProducts($products, 'Etsy', (string) $storeName);
echo json_encode(['items' => $normalized]);


