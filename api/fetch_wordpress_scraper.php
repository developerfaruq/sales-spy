<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$base = isset($_GET['url']) ? rtrim((string) $_GET['url'], '/') : '';
if ($base === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?url={wordpress site base url}']);
    exit;
}

$storeName = parse_url($base, PHP_URL_HOST) ?: 'WooCommerce';

// Try Store API first (no auth): /wp-json/wc/store/products
$storeApi = $base . '/wp-json/wc/store/products';
if (isAllowedByRobots($storeApi)) {
    $resp = fetchUrl($storeApi);
    if ($resp['status'] === 200) {
        try {
            $data = decodeJson($resp['body']);
            $items = [];
            foreach ($data as $p) {
                $items[] = [
                    'title' => $p['name'] ?? '',
                    'price' => $p['prices']['price'] ?? '',
                    'availability' => ($p['is_in_stock'] ?? false) ? 'in_stock' : 'out_of_stock',
                ];
            }
            echo json_encode(['items' => normalizeProducts($items, 'WooCommerce', (string) $storeName)]);
            exit;
        } catch (Throwable $e) {
            // fall through to REST v3 attempt
        }
    }
}

// Try REST v3 public (some sites expose public products): /wp-json/wc/v3/products
$v3 = $base . '/wp-json/wc/v3/products';
if (isAllowedByRobots($v3)) {
    $resp2 = fetchUrl($v3);
    if ($resp2['status'] === 200) {
        try {
            $data = decodeJson($resp2['body']);
            $items = [];
            foreach ($data as $p) {
                $items[] = [
                    'title' => $p['name'] ?? '',
                    'price' => $p['price'] ?? '',
                    'availability' => ($p['stock_status'] ?? '') ?: (($p['in_stock'] ?? false) ? 'in_stock' : ''),
                ];
            }
            echo json_encode(['items' => normalizeProducts($items, 'WooCommerce', (string) $storeName)]);
            exit;
        } catch (Throwable $e) {
            // fall through to scrape
        }
    }
}

// Fallback: fetch home page and try to detect product JSON (theme dependent)
$home = $base;
if (!isAllowedByRobots($home)) {
    http_response_code(403);
    echo json_encode(['error' => 'Blocked by robots.txt']);
    exit;
}
$resp3 = fetchUrl($home);
if ($resp3['status'] >= 400 || $resp3['status'] === 0) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch site', 'status' => $resp3['status']]);
    exit;
}

echo json_encode(['items' => normalizeProducts([], 'WooCommerce', (string) $storeName)]);


