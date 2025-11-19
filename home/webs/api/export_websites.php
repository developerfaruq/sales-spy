<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="websites_export_' . date('Y-m-d') . '.csv"');
header('Access-Control-Allow-Origin: *');

// Database configuration
$host = 'localhost';
$dbname = 'sales_spy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get filter parameters
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
    $country = isset($_GET['country']) ? $_GET['country'] : '';
    $indexed_date = isset($_GET['indexed_date']) ? $_GET['indexed_date'] : '';
    
    // Build WHERE clause
    $where = ["discovered = 1"];
    $params = [];
    
    if (!empty($keyword)) {
        $where[] = "(store_name LIKE :keyword OR store_url LIKE :keyword OR description LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }
    
    if (!empty($platform)) {
        $where[] = "theme_name LIKE :platform";
        $params[':platform'] = "%$platform%";
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
    
    // Get data
    $sql = "SELECT 
                store_name as 'Website Name',
                store_url as 'Domain',
                theme_name as 'Platform',
                description as 'Description',
                country as 'Country',
                email as 'Email',
                phone as 'Phone',
                created_at as 'Domain Registration Date',
                last_scraped as 'Indexed Date',
                social_facebook as 'Facebook',
                social_instagram as 'Instagram',
                social_twitter as 'Twitter',
                social_tiktok as 'TikTok',
                youtube as 'YouTube'
            FROM wix_stores 
            WHERE $whereClause
            ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    $firstRow = true;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($firstRow) {
            fputcsv($output, array_keys($row));
            $firstRow = false;
        }
        fputcsv($output, $row);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}