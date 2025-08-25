<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/scraper_utils.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
$platform = isset($_GET['platform']) ? trim((string) $_GET['platform']) : 'shopify';
$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$created_after = isset($_GET['created_after']) ? trim((string) $_GET['created_after']) : '';
$created_before = isset($_GET['created_before']) ? trim((string) $_GET['created_before']) : '';
$auto_update = isset($_GET['auto_update']) ? (bool) $_GET['auto_update'] : false;

$offset = ($page - 1) * $limit;

try {
    // Auto-update: Trigger intelligence collection if requested
    if ($auto_update) {
        // Get fresh data from popular stores
        $collectUrl = "http://localhost/sales-spy/api/shopify_intelligence.php?action=collect&category=" . urlencode($category ?: 'fashion') . "&limit=3";
        $collectResp = @file_get_contents($collectUrl);
        if ($collectResp) {
            $collectData = json_decode($collectResp, true);
            // Collection was triggered, data should be in database now
        }
    }

    // Create shopify_intelligence table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS shopify_intelligence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_domain VARCHAR(255),
            store_name VARCHAR(255),
            product_title VARCHAR(500),
            price DECIMAL(10,2),
            availability VARCHAR(50),
            product_type VARCHAR(255),
            vendor VARCHAR(255),
            tags TEXT,
            scraped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            category VARCHAR(100),
            INDEX idx_scraped_at (scraped_at),
            INDEX idx_store_domain (store_domain),
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Query to get aggregated store data from scraped intelligence
    $whereConditions = [];
    $params = [];
    
    if ($platform === 'shopify') {
        // For Shopify intelligence data
        $whereConditions[] = "store_domain IS NOT NULL";
    }
    
    if ($category !== '') {
        $whereConditions[] = "category = ?";
        $params[] = $category;
    }
    
    if ($created_after !== '') {
        $whereConditions[] = "scraped_at >= ?";
        $params[] = $created_after . ' 00:00:00';
    }
    
    if ($created_before !== '') {
        $whereConditions[] = "scraped_at <= ?";
        $params[] = $created_before . ' 23:59:59';
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get aggregated store data
    $sql = "
        SELECT 
            store_domain as domain,
            store_name as title,
            'Shopify' as tech_stack,
            CONCAT('Competitive intelligence data from ', store_name) as description,
            'EN' as language,
            'Various' as country,
            COUNT(DISTINCT product_title) as product_count,
            ROUND(AVG(price), 2) as avg_price,
            'USD' as currency,
            '' as contact_email,
            '' as contact_phone,
            '' as whatsapp_number,
            0 as facebook_ads_count,
            MAX(scraped_at) as date_added,
            '' as shipping_destinations,
            '' as payment_methods,
            '' as facebook_url,
            '' as instagram_url,
            '' as twitter_url,
            '' as youtube_url,
            '' as tiktok_url,
            '' as pinterest_url,
            GROUP_CONCAT(DISTINCT tags SEPARATOR ', ') as tags_list
        FROM shopify_intelligence 
        $whereClause
        GROUP BY store_domain, store_name
        ORDER BY MAX(scraped_at) DESC, product_count DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    // Bind parameters manually to ensure correct types
    $paramIndex = 1;
    foreach ($params as $i => $param) {
        if ($i === count($params) - 2) {
            // This is the limit parameter
            $stmt->bindValue($paramIndex, (int) $param, PDO::PARAM_INT);
        } elseif ($i === count($params) - 1) {
            // This is the offset parameter
            $stmt->bindValue($paramIndex, (int) $param, PDO::PARAM_INT);
        } else {
            // Regular string parameter
            $stmt->bindValue($paramIndex, $param, PDO::PARAM_STR);
        }
        $paramIndex++;
    }
    $stmt->execute();
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countSql = "
        SELECT COUNT(DISTINCT store_domain) as total
        FROM shopify_intelligence 
        $whereClause
    ";
    $countParams = array_slice($params, 0, -2); // Remove limit and offset
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalStores = (int) $countStmt->fetchColumn();

    // If no data in database, populate with some initial data
    if ($totalStores === 0 && $auto_update) {
        // Trigger initial data collection
        $categories = ['fashion', 'beauty', 'tech', 'home', 'fitness'];
        foreach ($categories as $cat) {
            $collectUrl = "http://localhost/sales-spy/api/shopify_intelligence.php?action=collect&category=$cat&limit=10";
            $collectResp = @file_get_contents($collectUrl);
            if ($collectResp) {
                $collectData = json_decode($collectResp, true);
                if ($collectData && isset($collectData['data'])) {
                    foreach ($collectData['data'] as $storeData) {
                        if (isset($storeData['products']) && is_array($storeData['products'])) {
                            foreach ($storeData['products'] as $product) {
                                $insertSql = "
                                    INSERT INTO shopify_intelligence 
                                    (store_domain, store_name, product_title, price, availability, product_type, vendor, tags, category, scraped_at)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                ";
                                try {
                                    $insertStmt = $pdo->prepare($insertSql);
                                    $insertStmt->execute([
                                        $storeData['store_info']['domain'] ?? '',
                                        $storeData['store_info']['name'] ?? '',
                                        $product['product_title'] ?? '',
                                        isset($product['price']) ? (float) $product['price'] : 0,
                                        $product['availability'] ?? '',
                                        $product['product_type'] ?? '',
                                        $product['vendor'] ?? '',
                                        $product['tags'] ?? '',
                                        $cat
                                    ]);
                                } catch (PDOException $e) {
                                    // Ignore duplicate entries
                                }
                            }
                        }
                    }
                }
            }
            // Rate limiting between categories
            usleep(500000); // 0.5 second delay
        }
        
        // Re-run the query after populating data
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($countParams);
        $totalStores = (int) $countStmt->fetchColumn();
    }

    $totalPages = $totalStores > 0 ? ceil($totalStores / $limit) : 1;

    // Format the response
    $response = [
        'stores' => $stores,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_stores' => $totalStores,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ],
        'filters' => [
            'platform' => $platform,
            'category' => $category,
            'created_after' => $created_after,
            'created_before' => $created_before
        ],
        'last_updated' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'stores' => [],
        'pagination' => [
            'page' => 1,
            'limit' => $limit,
            'total_stores' => 0,
            'total_pages' => 1,
            'has_next' => false,
            'has_prev' => false
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'stores' => [],
        'pagination' => [
            'page' => 1,
            'limit' => $limit,
            'total_stores' => 0,
            'total_pages' => 1,
            'has_next' => false,
            'has_prev' => false
        ]
    ]);
}