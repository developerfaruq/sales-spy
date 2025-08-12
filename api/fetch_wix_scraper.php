<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$url = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
if ($url === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?url={wix storefront url}']);
    exit;
}

if (!isAllowedByRobots($url)) {
    http_response_code(403);
    echo json_encode(['error' => 'Blocked by robots.txt']);
    exit;
}

$resp = fetchUrl($url);
if ($resp['status'] >= 400 || $resp['status'] === 0) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch Wix page', 'status' => $resp['status'], 'details' => $resp['error'] ?? null]);
    exit;
}

$html = $resp['body'];
$products = [];

// Look for window.__PRELOADED_STATE__
if (preg_match('/window\.__PRELOADED_STATE__\s*=\s*(\{[\s\S]*?\})\s*;\s*<\//', $html, $m)) {
    try {
        $state = decodeJson($m[1]);
        // A common path is state"catalog" or similar; this varies per site
        $items = $state['catalog']['products'] ?? $state['products'] ?? [];
        if (is_array($items)) {
            foreach ($items as $p) {
                $products[] = [
                    'title' => $p['name'] ?? ($p['title'] ?? ''),
                    'price' => $p['priceData']['price'] ?? ($p['price'] ?? ''),
                    'availability' => ($p['stock']['inStock'] ?? $p['inStock'] ?? false) ? 'in_stock' : '',
                ];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}

$storeName = parse_url($url, PHP_URL_HOST) ?: 'Wix';
echo json_encode(['items' => normalizeProducts($products, 'Wix', (string) $storeName)]);


