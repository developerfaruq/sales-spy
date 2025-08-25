<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : 'discover';
$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$store = isset($_GET['store']) ? trim((string) $_GET['store']) : '';
$url = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 10;

// Available actions: discover, scrape, collect (bulk), trending
switch ($action) {
    case 'discover':
        // Use the discover system
        $discoverUrl = "http://localhost/sales-spy/api/discover_shopify_stores.php";
        $params = [];
        if ($category !== '') $params['category'] = $category;
        if ($limit !== 10) $params['limit'] = $limit;
        
        if (!empty($params)) {
            $discoverUrl .= '?' . http_build_query($params);
        }
        
        $resp = file_get_contents($discoverUrl);
        echo $resp;
        break;
        
    case 'scrape':
        // Scrape a specific store
        if ($store === '' && $url === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Provide store domain or URL for scraping']);
            exit;
        }
        
        $scrapeUrl = "http://localhost/sales-spy/api/fetch_shopify_scraper.php";
        $params = [];
        if ($store !== '') $params['store'] = $store;
        if ($url !== '') $params['url'] = $url;
        
        $scrapeUrl .= '?' . http_build_query($params);
        $resp = file_get_contents($scrapeUrl);
        echo $resp;
        break;
        
    case 'collect':
        // Bulk collection: discover stores and scrape them
        try {
            $results = [];
            
            // Get stores from discovery
            $discoverUrl = "http://localhost/sales-spy/api/discover_shopify_stores.php";
            if ($category !== '') {
                $discoverUrl .= "?category=" . urlencode($category) . "&limit=" . $limit;
            } else {
                $discoverUrl .= "?limit=" . $limit;
            }
            
            $discoverResp = file_get_contents($discoverUrl);
            $storesData = json_decode($discoverResp, true);
            
            if (!$storesData || !isset($storesData['stores'])) {
                throw new Exception('Failed to discover stores');
            }
            
            $storesToScrape = [];
            if (isset($storesData['stores'][0]['domain'])) {
                // Single category response
                $storesToScrape = $storesData['stores'];
            } else {
                // Multi-category response
                foreach ($storesData['stores'] as $categoryStores) {
                    $storesToScrape = array_merge($storesToScrape, $categoryStores);
                }
            }
            
            // Don't limit the stores - scrape all discovered stores
            $storesToScrape = array_slice($storesToScrape, 0, count($storesToScrape));
            
            foreach ($storesToScrape as $storeInfo) {
                $domain = $storeInfo['domain'];
                
                // Scrape the store
                $scrapeUrl = "http://localhost/sales-spy/api/fetch_shopify_scraper.php?store=" . urlencode($domain);
                $scrapeResp = file_get_contents($scrapeUrl);
                $scrapeData = json_decode($scrapeResp, true);
                
                if ($scrapeData && isset($scrapeData['items'])) {
                    $results[] = [
                        'store_info' => $storeInfo,
                        'products' => $scrapeData['items'],
                        'total_products' => count($scrapeData['items']),
                        'scraped_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Store in database
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO competitor_data (store_domain, store_name, product_data, scraped_at) 
                            VALUES (?, ?, ?, NOW())
                            ON DUPLICATE KEY UPDATE 
                            product_data = VALUES(product_data), 
                            scraped_at = VALUES(scraped_at)
                        ");
                        $stmt->execute([
                            $domain,
                            $storeInfo['name'],
                            json_encode($scrapeData['items'])
                        ]);
                    } catch (PDOException $e) {
                        // Table might not exist, continue without storing
                    }
                }
                
                // Rate limiting
                usleep(1000000); // 1 second delay between stores
            }
            
            echo json_encode([
                'action' => 'collect',
                'collected_stores' => count($results),
                'data' => $results,
                'total_products_found' => array_sum(array_column($results, 'total_products'))
            ]);
            
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Collection failed: ' . $e->getMessage()]);
        }
        break;
        
    case 'trending':
        // Get trending products from database
        try {
            $stmt = $pdo->prepare("
                SELECT store_domain, store_name, product_data, scraped_at 
                FROM competitor_data 
                WHERE scraped_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY scraped_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $trending = [];
            foreach ($results as $row) {
                $products = json_decode($row['product_data'], true);
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $trending[] = [
                            'store' => $row['store_name'],
                            'domain' => $row['store_domain'],
                            'product' => $product,
                            'scraped_at' => $row['scraped_at']
                        ];
                    }
                }
            }
            
            echo json_encode([
                'action' => 'trending',
                'total_entries' => count($trending),
                'data' => array_slice($trending, 0, $limit)
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'action' => 'trending',
                'error' => 'Database not available: ' . $e->getMessage(),
                'data' => []
            ]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid action',
            'available_actions' => ['discover', 'scrape', 'collect', 'trending'],
            'examples' => [
                'discover' => '?action=discover&category=fashion&limit=5',
                'scrape' => '?action=scrape&store=gymshark.com',
                'collect' => '?action=collect&category=beauty&limit=3',
                'trending' => '?action=trending&limit=20'
            ]
        ]);
}

// Create competitor_data table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS competitor_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_domain VARCHAR(255) UNIQUE,
            store_name VARCHAR(255),
            product_data LONGTEXT,
            scraped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_scraped_at (scraped_at),
            INDEX idx_store_domain (store_domain)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    // Ignore table creation errors
}