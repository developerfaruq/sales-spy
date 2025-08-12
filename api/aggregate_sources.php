<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

// Optional .env load for API keys used by API-based scripts called directly
if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

header('Content-Type: application/json');

$platform = isset($_GET['platform']) ? strtolower(trim((string) $_GET['platform'])) : '';
$store = isset($_GET['store']) ? trim((string) $_GET['store']) : '';
$url = isset($_GET['url']) ? trim((string) $_GET['url']) : '';

// Helpers to call internal endpoints and merge items
function callLocalRaw(string $path, array $query): array
{
    $qs = http_build_query($query);
    $full = rtrim(BASE_URL, '/') . '/api/' . ltrim($path, '/');
    if ($qs) {
        $full .= '?' . $qs;
    }
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: SalesSpyAggregator/1.0',
                'Accept: application/json',
            ],
            'timeout' => 20,
        ],
    ]);
    $resp = @file_get_contents($full, false, $context);
    if ($resp === false) {
        return ['status' => 0, 'data' => null];
    }
    $data = json_decode($resp, true);
    return ['status' => 200, 'data' => $data];
}

function normalizeItemsFromApi(string $platform, $data): array
{
    $now = date('c');
    $items = [];
    $platformLower = strtolower($platform);
    switch ($platformLower) {
        case 'shopify':
            $storeName = getenv('SHOPIFY_STORE_DOMAIN') ?: 'Shopify';
            $products = is_array($data) && isset($data['products']) && is_array($data['products']) ? $data['products'] : [];
            foreach ($products as $p) {
                $price = '';
                if (isset($p['variants'][0]['price'])) {
                    $price = (string) $p['variants'][0]['price'];
                }
                $items[] = [
                    'platform' => 'Shopify',
                    'store_name' => $storeName,
                    'product_title' => $p['title'] ?? '',
                    'price' => $price,
                    'availability' => '',
                    'last_updated' => $now,
                ];
            }
            break;
        case 'woocommerce':
        case 'wordpress':
            $apiUrl = getenv('WORDPRESS_API_URL') ?: '';
            $host = $apiUrl ? (parse_url($apiUrl, PHP_URL_HOST) ?: 'WooCommerce') : 'WooCommerce';
            $products = is_array($data) ? $data : [];
            foreach ($products as $p) {
                $items[] = [
                    'platform' => 'WooCommerce',
                    'store_name' => (string) $host,
                    'product_title' => $p['name'] ?? '',
                    'price' => isset($p['price']) ? (string) $p['price'] : '',
                    'availability' => $p['stock_status'] ?? ($p['in_stock'] ?? ''),
                    'last_updated' => $now,
                ];
            }
            break;
        case 'ebay':
            $storeName = 'eBay';
            $products = [];
            if (is_array($data)) {
                if (isset($data['inventoryItems']) && is_array($data['inventoryItems'])) {
                    foreach ($data['inventoryItems'] as $it) {
                        $title = $it['product']['title'] ?? ($it['sku'] ?? '');
                        $availability = $it['availability'] ?? '';
                        $price = $it['product']['aspects']['price'] ?? '';
                        $products[] = [
                            'title' => $title,
                            'price' => $price,
                            'availability' => $availability,
                        ];
                    }
                }
            }
            require_once __DIR__ . '/scraper_utils.php';
            $items = normalizeProducts($products, 'eBay', $storeName);
            break;
        case 'wix':
            $storeName = 'Wix';
            $products = [];
            if (is_array($data)) {
                $list = $data['products'] ?? ($data['items'] ?? []);
                if (is_array($list)) {
                    foreach ($list as $p) {
                        $products[] = [
                            'title' => $p['name'] ?? ($p['title'] ?? ''),
                            'price' => $p['priceData']['price'] ?? ($p['price'] ?? ''),
                            'availability' => ($p['stock']['inStock'] ?? $p['inStock'] ?? false) ? 'in_stock' : '',
                        ];
                    }
                }
            }
            require_once __DIR__ . '/scraper_utils.php';
            $items = normalizeProducts($products, 'Wix', $storeName);
            break;
        case 'squarespace':
            $storeName = 'Squarespace';
            $products = [];
            if (is_array($data)) {
                $list = $data['products'] ?? ($data['items'] ?? ($data['data'] ?? []));
                if (is_array($list)) {
                    foreach ($list as $p) {
                        $products[] = [
                            'title' => $p['name'] ?? ($p['title'] ?? ''),
                            'price' => $p['price']['value'] ?? ($p['price'] ?? ''),
                            'availability' => $p['availability'] ?? '',
                        ];
                    }
                }
            }
            require_once __DIR__ . '/scraper_utils.php';
            $items = normalizeProducts($products, 'Squarespace', $storeName);
            break;
        case 'etsy':
            $storeName = getenv('ETSY_SHOP_ID') ?: 'Etsy';
            $products = [];
            if (is_array($data)) {
                $list = $data['results'] ?? ($data['listings'] ?? []);
                if (is_array($list)) {
                    foreach ($list as $p) {
                        $products[] = [
                            'title' => $p['title'] ?? '',
                            'price' => $p['price']['amount'] ?? ($p['price'] ?? ''),
                            'availability' => ($p['state'] ?? '') === 'active' ? 'active' : ($p['is_active'] ?? ''),
                        ];
                    }
                }
            }
            require_once __DIR__ . '/scraper_utils.php';
            $items = normalizeProducts($products, 'Etsy', $storeName);
            break;
        default:
            $items = [];
    }
    return $items;
}

$items = [];

// Shopify: API
if ($platform === '' || $platform === 'shopify') {
    $resp = callLocalRaw('fetch_shopify_data.php', []);
    if ($resp['status'] === 200) {
        $items = array_merge($items, normalizeItemsFromApi('shopify', $resp['data']));
    }
}

// WooCommerce: API if creds provided; else scraper with provided URL
if ($platform === '' || $platform === 'woocommerce' || $platform === 'wordpress') {
    $wcApi = callLocalRaw('fetch_wordpress_data.php', []); // requires env; may be empty
    if ($wcApi['status'] === 200 && is_array($wcApi['data'])) {
        $items = array_merge($items, normalizeItemsFromApi('woocommerce', $wcApi['data']));
    }
    if ($url !== '') {
        $wcScrape = callLocalRaw('fetch_wordpress_scraper.php', ['url' => $url]);
        if ($wcScrape['status'] === 200 && isset($wcScrape['data']['items'])) {
            $items = array_merge($items, (array) $wcScrape['data']['items']);
        }
    }
}

// Etsy: API if creds present, and/or scraper using shop name or URL
if ($platform === '' || $platform === 'etsy') {
    if ($store !== '') {
        $etsyS = callLocalRaw('fetch_etsy_scraper.php', ['shop' => $store]);
        if ($etsyS['status'] === 200 && isset($etsyS['data']['items'])) {
            $items = array_merge($items, (array) $etsyS['data']['items']);
        }
    } elseif ($url !== '') {
        $etsyS = callLocalRaw('fetch_etsy_scraper.php', ['url' => $url]);
        if ($etsyS['status'] === 200 && isset($etsyS['data']['items'])) {
            $items = array_merge($items, (array) $etsyS['data']['items']);
        }
    }
    // API version if token present (may be restricted)
    $etsyToken = getenv('ETSY_ACCESS_TOKEN') ?: '';
    $etsyShopId = getenv('ETSY_SHOP_ID') ?: '';
    if ($etsyToken !== '' && $etsyShopId !== '') {
        $etsyApi = callLocalRaw('fetch_etsy_data.php', []);
        if ($etsyApi['status'] === 200 && is_array($etsyApi['data'])) {
            $items = array_merge($items, normalizeItemsFromApi('etsy', $etsyApi['data']));
        }
    }
}

// eBay: API if token present, plus scraper
if ($platform === '' || $platform === 'ebay') {
    $ebayToken = getenv('EBAY_AUTH_TOKEN') ?: '';
    if ($ebayToken !== '') {
        $ebayApi = callLocalRaw('fetch_ebay_data.php', []);
        if ($ebayApi['status'] === 200 && is_array($ebayApi['data'])) {
            $items = array_merge($items, normalizeItemsFromApi('ebay', $ebayApi['data']));
        }
    }
    $scrapeParams = [];
    if ($store !== '') $scrapeParams['store_url'] = $store;
    if ($url !== '') $scrapeParams['store_url'] = $url;
    if ($store !== '' || $url !== '' || $platform === 'ebay') {
        $ebayS = callLocalRaw('fetch_ebay_scraper.php', $scrapeParams);
        if ($ebayS['status'] === 200 && isset($ebayS['data']['items'])) {
            $items = array_merge($items, (array) $ebayS['data']['items']);
        }
    }
}

// Wix: API if token present, plus scraper
if ($platform === '' || $platform === 'wix') {
    $wixToken = getenv('WIX_ACCESS_TOKEN') ?: '';
    if ($wixToken !== '') {
        $wixApi = callLocalRaw('fetch_wix_data.php', []);
        if ($wixApi['status'] === 200 && is_array($wixApi['data'])) {
            $items = array_merge($items, normalizeItemsFromApi('wix', $wixApi['data']));
        }
    }
    if ($url !== '') {
        $wixS = callLocalRaw('fetch_wix_scraper.php', ['url' => $url]);
        if ($wixS['status'] === 200 && isset($wixS['data']['items'])) {
            $items = array_merge($items, (array) $wixS['data']['items']);
        }
    }
}

// Squarespace: API if token present, plus scraper
if ($platform === '' || $platform === 'squarespace') {
    $sqToken = getenv('SQUARESPACE_API_TOKEN') ?: '';
    if ($sqToken !== '') {
        $sqApi = callLocalRaw('fetch_squarespace_data.php', []);
        if ($sqApi['status'] === 200 && is_array($sqApi['data'])) {
            $items = array_merge($items, normalizeItemsFromApi('squarespace', $sqApi['data']));
        }
    }
    if ($url !== '') {
        $sqS = callLocalRaw('fetch_squarespace_scraper.php', ['url' => $url]);
        if ($sqS['status'] === 200 && isset($sqS['data']['items'])) {
            $items = array_merge($items, (array) $sqS['data']['items']);
        }
    }
}

// Optional: filter by store name if provided
if ($store !== '') {
    $needle = strtolower($store);
    $items = array_values(array_filter($items, function ($it) use ($needle) {
        return isset($it['store_name']) && str_contains(strtolower((string) $it['store_name']), $needle);
    }));
}

echo json_encode(['items' => $items]);


