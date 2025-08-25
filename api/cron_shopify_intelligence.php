<?php
declare(strict_types=1);

/**
 * Automated Shopify Intelligence Data Collection Script
 * This script should be run via cron job every hour to keep data fresh
 * 
 * Cron job example (hourly):
 * 0 * * * * /usr/bin/php /path/to/sales-spy/api/cron_shopify_intelligence.php >> /path/to/logs/intelligence.log 2>&1
 */

require_once __DIR__ . '/../config/db.php';

// Set longer execution time for batch processing
set_time_limit(600); // 10 minutes max
ini_set('memory_limit', '256M');

// Log function
function logMessage(string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
}

// Function to call local API endpoints
function callIntelligenceAPI(string $action, array $params = []): array {
    $baseUrl = rtrim(BASE_URL, '/') . '/api/shopify_intelligence.php';
    $params['action'] = $action;
    $url = $baseUrl . '?' . http_build_query($params);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 60,
            'header' => [
                'User-Agent: SalesSpyIntelligenceCron/1.0'
            ]
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['success' => false, 'error' => 'API call failed'];
    }
    
    $data = json_decode($response, true);
    return ['success' => true, 'data' => $data];
}

// Function to clean old data (older than 30 days)
function cleanOldData(PDO $pdo): int {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM shopify_intelligence 
            WHERE scraped_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        logMessage("Error cleaning old data: " . $e->getMessage());
        return 0;
    }
}

// Function to get collection statistics
function getCollectionStats(PDO $pdo): array {
    try {
        $stmt = $pdo->query("
            SELECT 
                COUNT(DISTINCT store_domain) as unique_stores,
                COUNT(*) as total_products,
                AVG(price) as avg_price,
                category,
                COUNT(*) as products_per_category
            FROM shopify_intelligence 
            WHERE scraped_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY category
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logMessage("Error getting stats: " . $e->getMessage());
        return [];
    }
}

// Start execution
logMessage("Starting Shopify Intelligence Auto Collection");

try {
    // Categories to collect data from (rotate to distribute load)
    $categories = ['fashion', 'beauty', 'home', 'tech', 'fitness', 'jewelry', 'food', 'electronics'];
    $currentHour = (int) date('H');
    
    // Rotate categories based on hour to distribute load throughout the day
    $selectedCategories = [
        $categories[$currentHour % count($categories)],
        $categories[($currentHour + 4) % count($categories)]
    ];
    
    logMessage("Selected categories for this hour: " . implode(', ', $selectedCategories));
    
    $totalCollected = 0;
    $totalStores = 0;
    
    // Collect data for selected categories
    foreach ($selectedCategories as $category) {
        logMessage("Collecting data for category: $category");
        
        $result = callIntelligenceAPI('collect', [
            'category' => $category,
            'limit' => 10 // Increased from 3 to get more stores
        ]);
        
        if ($result['success'] && isset($result['data']['collected_stores'])) {
            $storesCollected = $result['data']['collected_stores'];
            $productsFound = $result['data']['total_products_found'] ?? 0;
            
            $totalStores += $storesCollected;
            $totalCollected += $productsFound;
            
            logMessage("Category $category: $storesCollected stores, $productsFound products");
        } else {
            $error = $result['error'] ?? 'Unknown error';
            logMessage("Failed to collect data for $category: $error");
        }
        
        // Add delay between categories to be respectful
        sleep(30); // 30 seconds between categories
    }
    
    // Clean old data
    logMessage("Cleaning old data...");
    $deletedRows = cleanOldData($pdo);
    logMessage("Deleted $deletedRows old records");
    
    // Get and log statistics
    $stats = getCollectionStats($pdo);
    logMessage("Collection statistics for last 24 hours:");
    foreach ($stats as $stat) {
        $category = $stat['category'] ?: 'unknown';
        $products = $stat['products_per_category'];
        $avgPrice = round((float) $stat['avg_price'], 2);
        logMessage("  $category: $products products (avg price: $avgPrice)");
    }
    
    // Update collection metadata
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS intelligence_metadata (
                id INT AUTO_INCREMENT PRIMARY KEY,
                last_collection_run TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                stores_collected INT DEFAULT 0,
                products_collected INT DEFAULT 0,
                categories_processed TEXT,
                status VARCHAR(50) DEFAULT 'success'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO intelligence_metadata 
            (last_collection_run, stores_collected, products_collected, categories_processed, status)
            VALUES (NOW(), ?, ?, ?, 'success')
        ");
        $stmt->execute([
            $totalStores,
            $totalCollected,
            implode(',', $selectedCategories)
        ]);
        
    } catch (PDOException $e) {
        logMessage("Error updating metadata: " . $e->getMessage());
    }
    
    logMessage("Collection completed successfully");
    logMessage("Total: $totalStores stores, $totalCollected products");
    
} catch (Throwable $e) {
    logMessage("Fatal error during collection: " . $e->getMessage());
    
    // Log error to metadata
    try {
        $stmt = $pdo->prepare("
            INSERT INTO intelligence_metadata 
            (last_collection_run, stores_collected, products_collected, categories_processed, status)
            VALUES (NOW(), 0, 0, ?, 'error')
        ");
        $stmt->execute(['error: ' . $e->getMessage()]);
    } catch (PDOException $dbError) {
        logMessage("Could not log error to database: " . $dbError->getMessage());
    }
    
    exit(1);
}

logMessage("Shopify Intelligence Auto Collection completed");
exit(0);