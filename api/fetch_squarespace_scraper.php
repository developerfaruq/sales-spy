<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$url = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
if ($url === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?url={squarespace storefront url}']);
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
    echo json_encode(['error' => 'Failed to fetch Squarespace page', 'status' => $resp['status'], 'details' => $resp['error'] ?? null]);
    exit;
}

$html = $resp['body'];
$products = [];

// Squarespace often includes a static JSON blob (StaticRenderer)
if (preg_match('/<script[^>]*>\s*StaticRenderer\.render\((\{[\s\S]*?\})\)\s*<\//', $html, $m)) {
    try {
        $state = decodeJson($m[1]);
        $items = $state['items'] ?? $state['products'] ?? [];
        if (is_array($items)) {
            foreach ($items as $p) {
                $products[] = [
                    'title' => $p['name'] ?? ($p['title'] ?? ''),
                    'price' => $p['price']['value'] ?? ($p['price'] ?? ''),
                    'availability' => ($p['availability'] ?? ''),
                ];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}

$storeName = parse_url($url, PHP_URL_HOST) ?: 'Squarespace';
echo json_encode(['items' => normalizeProducts($products, 'Squarespace', (string) $storeName)]);


