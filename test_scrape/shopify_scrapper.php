<?php
require_once "../config/db.php";
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

header("Content-Type: text/plain; charset=utf-8");
set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$stopFlag = __DIR__ . '/stop.flag';

// ================================
//  Setup HTTP client
// ================================
$client = new Client([
    'timeout' => 25,
    'verify' => false,
    'headers' => [
        'User-Agent' => 'SalesSpyBot/1.0 (+https://sales-spy.local)'
    ]
]);

// ================================
//  Fetch stores with missing info
// ================================
$stores = $pdo->query("
    SELECT store_url FROM shopify_stores 
    WHERE blocked = 0
      AND (contact_email IS NULL OR contact_email = '' 
        OR country IS NULL OR currency IS NULL OR theme_name IS NULL)
")->fetchAll(PDO::FETCH_COLUMN);

$total = count($stores);

if (!$total) {
    echo "✅ All stores already filled.\n";
    flush();
    exit;
}

echo "Starting scrape for {$total} stores...\n";
flush();

$success = 0;
$failed  = 0;

// ================================
//  MAIN LOOP
// ================================
foreach ($stores as $index => $storeUrl) {
    if (file_exists($stopFlag)) {
        echo "\n🛑 Scraping stopped manually.\n";
        unlink($stopFlag);
        break;
    }

    echo "\n[ " . ($index + 1) . "/{$total} ] 🔍 Scraping: {$storeUrl}\n";
    flush();

    try {
        // Fetch homepage HTML
        $response = $client->get($storeUrl);
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        $text = '';
        try {
            $text = $crawler->filter('body')->text();
        } catch (Exception $e) {
            $text = strip_tags($html);
        }

        // ---------------------------
        // Extract Shopify metadata
        // ---------------------------
        preg_match('/Shopify\.country\s*=\s*"(.*?)"/i', $html, $country);

        // --- Improved Currency Extraction ---
        preg_match('/Shopify\.currency\s*=\s*(?:\{.*?"(active|code)"\s*:\s*["\']([A-Z]{3})["\'].*?\}|["\']([A-Z]{3})["\'])/i', $html, $currencyMatch);
        $currency = $currencyMatch[2] ?? $currencyMatch[3] ?? null;

        // --- Improved Theme Extraction ---
        preg_match('/Shopify\.theme\s*=\s*\{[^}]*["\']name["\']\s*:\s*["\']([^"\']+)["\']/i', $html, $themeMatch);
        $theme = $themeMatch[1] ?? null;

        // fallback meta tags
        if (empty($currency)) {
            preg_match('/<meta[^>]*name=["\']currency["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $metaCurrency);
            $currency = $metaCurrency[1] ?? null;
        }
        if (empty($theme)) {
            preg_match('/<meta[^>]*name=["\']theme["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $metaTheme);
            $theme = $metaTheme[1] ?? null;
        }

        // ---------------------------
        // Extract contact details
        // ---------------------------
        preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $html, $email);
        preg_match('/(\+?\d{1,3}[\s\-().]*\d{2,4}[\s\-().]*\d{3,4}[\s\-().]*\d{3,4})/', $text, $phoneRaw);

        $phone = null;
        if (!empty($phoneRaw[0])) {
            $clean = preg_replace('/[^\d+]/', '', $phoneRaw[0]);
            if (preg_match('/^\+?\d{9,15}$/', $clean)) {
                $phone = $clean;
            }
        }

        preg_match('/instagram\.com\/[A-Za-z0-9_.-]+/i', $html, $insta);
        preg_match('/facebook\.com\/[A-Za-z0-9_.-]+/i', $html, $fb);
        preg_match('/tiktok\.com\/@[A-Za-z0-9_.-]+/i', $html, $tiktok);
        preg_match('/twitter\.com\/[A-Za-z0-9_.-]+/i', $html, $twitter);

        // ---------------------------
        // Try /contact, /about pages if email missing
        // ---------------------------
        if (empty($email[0])) {
            $paths = ['/contact', '/about', '/pages/contact-us', '/pages/about-us'];
            foreach ($paths as $p) {
                try {
                    $sub = $client->get(rtrim($storeUrl, '/') . $p);
                    $page = (string) $sub->getBody();
                    if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $page, $match)) {
                        $email[0] = $match[0];
                        break;
                    }
                } catch (Exception $e) {}
            }
        }

        // ---------------------------
        // Fetch all products (pagination)
        // ---------------------------
        $allProducts = [];
        $pageNum = 1;
        do {
            $jsonUrl = rtrim($storeUrl, '/') . "/products.json?limit=250&page={$pageNum}";
            try {
                $res = $client->get($jsonUrl);
                $data = json_decode($res->getBody(), true);
                $products = $data['products'] ?? [];
                if (empty($products)) break;
                $allProducts = array_merge($allProducts, $products);
                $pageNum++;
            } catch (Exception $e) {
                break;
            }
        } while (count($products) === 250);

        $totalProducts = count($allProducts);

        // ---------------------------
        // Calculate Average Product Price
        // ---------------------------
        $avgPrice = 0;
        if ($totalProducts > 0) {
            $prices = [];
            foreach ($allProducts as $p) {
                if (!empty($p['variants'][0]['price'])) {
                    $prices[] = floatval($p['variants'][0]['price']);
                }
            }
            if (!empty($prices)) {
                $avgPrice = array_sum($prices) / count($prices);
            }
        }

        // ---------------------------
        // Save store info
        // ---------------------------
        $stmt = $pdo->prepare("
            UPDATE shopify_stores 
            SET contact_email = COALESCE(NULLIF(?, ''), contact_email),
                phone = COALESCE(NULLIF(?, ''), phone),
                instagram = COALESCE(NULLIF(?, ''), instagram),
                facebook = COALESCE(NULLIF(?, ''), facebook),
                tiktok = COALESCE(NULLIF(?, ''), tiktok),
                twitter = COALESCE(NULLIF(?, ''), twitter),
                country = COALESCE(NULLIF(?, ''), country),
                currency = COALESCE(NULLIF(?, ''), currency),
                theme_name = COALESCE(NULLIF(?, ''), theme_name),
                total_products = ?,
                avg_product_price = ?,
                last_scraped = NOW()
            WHERE store_url = ?
        ");

        $stmt->execute([
            $email[0] ?? null,
            $phone ?? null,
            $insta[0] ?? null,
            $fb[0] ?? null,
            $tiktok[0] ?? null,
            $twitter[0] ?? null,
            $country[1] ?? null,
            $currency ?? null,
            $theme ?? null,
            $totalProducts,
            $avgPrice,
            $storeUrl
        ]);

        echo "✅ Store updated | Products: {$totalProducts} | Avg Price: {$avgPrice}\n";
        flush();

        // ---------------------------
        // Save products
        // ---------------------------
        if (!empty($allProducts)) {
            $insert = $pdo->prepare("
                INSERT INTO shopify_products 
                    (store_url, product_name, handle, description, vendor, product_type, tags, price, image_url, created_at, updated_at, variants, raw_json)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    price = VALUES(price),
                    updated_at = VALUES(updated_at),
                    image_url = VALUES(image_url),
                    raw_json = VALUES(raw_json)
            ");
            foreach ($allProducts as $p) {
                $insert->execute([
                    $storeUrl,
                    $p['title'] ?? 'Untitled',
                    $p['handle'] ?? null,
                    $p['body_html'] ?? '',
                    $p['vendor'] ?? '',
                    $p['product_type'] ?? '',
                    is_array($p['tags']) ? implode(',', $p['tags']) : ($p['tags'] ?? ''),
                    $p['variants'][0]['price'] ?? 0,
                    $p['images'][0]['src'] ?? '',
                    $p['created_at'] ?? null,
                    $p['updated_at'] ?? null,
                    json_encode($p['variants'] ?? []),
                    json_encode($p)
                ]);
            }
        }

        $success++;
    } catch (Exception $e) {
        echo "❌ Failed: {$storeUrl} — " . $e->getMessage() . "\n";
        $failed++;
    }

    flush();
    sleep(2);
}

echo "\n✅ Done! {$success} succeeded, {$failed} failed.\n";
flush();
