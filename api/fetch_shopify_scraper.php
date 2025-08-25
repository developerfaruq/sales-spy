<?php
declare(strict_types=1);

require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$store = isset($_GET['store']) ? trim((string) $_GET['store']) : '';
$urlParam = isset($_GET['url']) ? trim((string) $_GET['url']) : '';

if ($store === '' && $urlParam === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Provide ?store={shopify-domain} or ?url={shopify-store-url}']);
    exit;
}

// Build URL - Try products.json API first
if ($urlParam !== '') {
    $storeUrl = rtrim($urlParam, '/');
    $storeDomain = parse_url($storeUrl, PHP_URL_HOST);
} else {
    $storeDomain = $store;
    if (!str_contains($storeDomain, '.')) {
        $storeDomain = $storeDomain . '.myshopify.com';
    }
    $storeUrl = 'https://' . $storeDomain;
}

$productsApiUrl = $storeUrl . '/products.json';

// Check robots.txt compliance
if (!isAllowedByRobots($productsApiUrl)) {
    http_response_code(403);
    echo json_encode(['error' => 'Blocked by robots.txt']);
    exit;
}

// Try products.json API first (most reliable)
$resp = fetchUrl($productsApiUrl);
$products = [];
$source = 'unknown';

if ($resp['status'] === 200) {
    try {
        $data = decodeJson($resp['body']);
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $product) {
                if (!is_array($product)) continue;
                
                $variants = $product['variants'] ?? [];
                $firstVariant = is_array($variants) && count($variants) > 0 ? $variants[0] : [];
                
                $products[] = [
                    'title' => $product['title'] ?? '',
                    'price' => isset($firstVariant['price']) ? $firstVariant['price'] : '',
                    'availability' => isset($firstVariant['available']) ? 
                        ($firstVariant['available'] ? 'in_stock' : 'out_of_stock') : 'unknown',
                    'product_type' => $product['product_type'] ?? '',
                    'vendor' => $product['vendor'] ?? '',
                    'tags' => is_array($product['tags'] ?? []) ? implode(', ', $product['tags']) : '',
                    'handle' => $product['handle'] ?? '',
                    'created_at' => $product['created_at'] ?? '',
                    'updated_at' => $product['updated_at'] ?? ''
                ];
            }
            $source = 'products_json_api';
        }
    } catch (Throwable $e) {
        // Fall back to HTML scraping
    }
}

// Fallback: HTML scraping if products.json failed
if (empty($products)) {
    $htmlResp = fetchUrl($storeUrl);
    if ($htmlResp['status'] === 200) {
        $html = $htmlResp['body'];
        
        // Try to extract JSON-LD structured data
        try {
            if (preg_match_all('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/si', $html, $matches)) {
                foreach ($matches[1] as $json) {
                    $data = decodeJson(html_entity_decode($json));
                    if (isset($data['@type'])) {
                        if ($data['@type'] === 'Product') {
                            $offers = $data['offers'] ?? [];
                            $products[] = [
                                'title' => $data['name'] ?? '',
                                'price' => $offers['price'] ?? '',
                                'availability' => isset($offers['availability']) ? 
                                    (str_contains($offers['availability'], 'InStock') ? 'in_stock' : 'out_of_stock') : 'unknown',
                                'brand' => $data['brand']['name'] ?? '',
                                'description' => $data['description'] ?? ''
                            ];
                        } elseif ($data['@type'] === 'ItemList' && isset($data['itemListElement'])) {
                            foreach ($data['itemListElement'] as $item) {
                                $product = $item['item'] ?? [];
                                if (!is_array($product)) continue;
                                $offers = $product['offers'] ?? [];
                                $products[] = [
                                    'title' => $product['name'] ?? '',
                                    'price' => $offers['price'] ?? '',
                                    'availability' => isset($offers['availability']) ? 
                                        (str_contains($offers['availability'], 'InStock') ? 'in_stock' : 'out_of_stock') : 'unknown'
                                ];
                            }
                        }
                    }
                }
                if (!empty($products)) {
                    $source = 'html_jsonld';
                }
            }
        } catch (Throwable $e) {
            // Continue to basic HTML parsing
        }
        
        // Basic HTML parsing as last resort
        if (empty($products)) {
            if (preg_match_all('/<h[1-6][^>]*class="[^"]*product[^"]*"[^>]*>(.*?)<\/h[1-6]>/si', $html, $matches)) {
                foreach ($matches[1] as $title) {
                    $products[] = [
                        'title' => trim(strip_tags($title)),
                        'price' => '',
                        'availability' => 'unknown'
                    ];
                }
                $source = 'html_parsing';
            }
        }
    }
}

// Add rate limiting delay to be respectful
usleep(500000); // 0.5 second delay

$normalized = normalizeProducts($products, 'Shopify', $storeDomain);
echo json_encode([
    'items' => $normalized,
    'store_domain' => $storeDomain,
    'total_products' => count($normalized),
    'source' => $source
]);