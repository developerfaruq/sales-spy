<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$storeUrl = isset($_GET['store_url']) ? trim((string) $_GET['store_url']) : '';

if ($query === '' && $storeUrl === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?q={search terms} or ?store_url={ebay store url}']);
    exit;
}

$target = $storeUrl !== ''
    ? $storeUrl
    : ('https://www.ebay.com/sch/i.html?_nkw=' . rawurlencode($query));

if (!isAllowedByRobots($target)) {
    http_response_code(403);
    echo json_encode(['error' => 'Blocked by robots.txt']);
    exit;
}

$resp = fetchUrl($target);
if ($resp['status'] >= 400 || $resp['status'] === 0) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch eBay page', 'status' => $resp['status'], 'details' => $resp['error'] ?? null]);
    exit;
}

$html = $resp['body'];
$products = [];

// Extract items from search results by simple patterns
if (preg_match_all('/<li class=\"s-item\"[\s\S]*?<span class=\"s-item__price\">(.*?)<\/span>[\s\S]*?<span class=\"INLINE_FLEX\">[\s\S]*?<span class=\"[^"]*?SECONDARY_INFO[^"]*?\">(.*?)<\/span>/i', $html, $matches)) {
    $titles = [];
    if (preg_match_all('/<div class=\"s-item__title\">\s*<span[^>]*>(.*?)<\/span>/i', $html, $tMatches)) {
        $titles = $tMatches[1];
    }
    $count = min(count($matches[1]), count($titles));
    for ($i = 0; $i < $count; $i++) {
        $price = strip_tags($matches[1][$i]);
        $availability = strip_tags($matches[2][$i]);
        $products[] = [
            'title' => html_entity_decode(strip_tags($titles[$i])),
            'price' => $price,
            'availability' => $availability,
        ];
    }
}

$storeName = $storeUrl !== '' ? (parse_url($storeUrl, PHP_URL_HOST) ?? 'eBay') : 'eBay';
echo json_encode(['items' => normalizeProducts($products, 'eBay', (string) $storeName)]);


