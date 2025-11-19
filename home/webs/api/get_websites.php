<?php
// Prevent any output before JSON
ob_start();

// Enable error reporting but log to file instead of display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Clear any output that might have been generated
ob_clean();

// Log the request for debugging
error_log("GET request received: " . print_r($_GET, true));

// Database configuration
$host = 'localhost';
$dbname = 'sales_spy';
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get query parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Filter parameters
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
    $country = isset($_GET['country']) ? $_GET['country'] : '';
    $language = isset($_GET['language']) ? $_GET['language'] : '';
    $indexed_date = isset($_GET['indexed_date']) ? $_GET['indexed_date'] : '';
    
    // Build WHERE clause
    $where = ["discovered = 1"];
    $params = [];
    
    if (!empty($keyword)) {
        $where[] = "(store_name LIKE :keyword OR store_url LIKE :keyword OR description LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }
    
    if (!empty($platform)) {
        // Match both exact platform name and partial matches
        $where[] = "(theme_name LIKE :platform OR theme_name = :platform_exact)";
        $params[':platform'] = "%$platform%";
        $params[':platform_exact'] = $platform;
    }
    
    if (!empty($country)) {
        $where[] = "country = :country";
        $params[':country'] = $country;
    }
    
    if (!empty($indexed_date)) {
        $where[] = "DATE(last_scraped) = :indexed_date";
        $params[':indexed_date'] = $indexed_date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM wix_stores WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get paginated data
    $sql = "SELECT 
                id,
                store_name,
                store_url,
                description,
                email,
                phone,
                country,
                theme_name as platform,
                last_scraped as indexed_date,
                created_at as domain_reg_date,
                social_facebook,
                social_instagram,
                social_twitter,
                social_tiktok,
                youtube
            FROM wix_stores 
            WHERE $whereClause
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind filter parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind pagination parameters
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedWebsites = [];
    foreach ($websites as $site) {
        $formattedWebsites[] = [
            'id' => $site['id'],
            'domain' => $site['store_url'],
            'name' => $site['store_name'],
            'platform' => $site['platform'] ?: 'Wix',
            'title' => $site['store_name'],
            'description' => $site['description'],
            'language' => 'EN', // You may want to detect this or add a column
            'country' => $site['country'],
            'email' => $site['email'],
            'phone' => $site['phone'],
            'tags' => [], // You may want to generate tags from description or add a tags column
            'domain_reg_date' => $site['domain_reg_date'] ? date('Y-m-d', strtotime($site['domain_reg_date'])) : null,
            'indexed_date' => $site['indexed_date'] ? date('Y-m-d', strtotime($site['indexed_date'])) : null,
            'facebook' => $site['social_facebook'],
            'instagram' => $site['social_instagram'],
            'twitter' => $site['social_twitter'],
            'tiktok' => $site['social_tiktok'],
            'youtube' => $site['youtube'],
            // Additional fields for detail panel
            'owner' => '', // Add this column to your database if needed
            'traffic' => '', // Add this column to your database if needed
            'hosting' => 'Wix',
            'whatsapp' => '' // Add this column to your database if needed
        ];
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'data' => $formattedWebsites,
        'pagination' => [
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalRecords / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Ensure only JSON is output
ob_end_flush();