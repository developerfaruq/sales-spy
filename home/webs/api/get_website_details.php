<?php
require '../../../config/db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');



try {
  
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid website ID');
    }
    
    $sql = "SELECT 
                id,
                store_name,
                store_url,
                description,
                email,
                phone,
                currency,
                total_products,
                avg_product_price,
                country,
                theme_name,
                last_scraped,
                created_at,
                social_facebook,
                social_instagram,
                social_tiktok,
                social_twitter,
                youtube
            FROM wix_stores 
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$site) {
        throw new Exception('Website not found');
    }
    
    // Format the data
    $data = [
        'id' => $site['id'],
        'domain' => $site['store_url'],
        'name' => $site['store_name'],
        'owner' => '', // Add to database if needed
        'traffic' => '', // Add to database if needed
        'dateAdded' => $site['created_at'] ? date('Y-m-d', strtotime($site['created_at'])) : null,
        'hosting' => $site['theme_name'] ?: 'Wix',
        'tags' => [$site['country'], $site['theme_name']],
        'description' => $site['description'],
        'email' => $site['email'],
        'phone' => $site['phone'],
        'whatsapp' => '', // Add to database if needed
        'regDate' => $site['created_at'] ? date('Y-m-d', strtotime($site['created_at'])) : null,
        'indexedDate' => $site['last_scraped'] ? date('Y-m-d', strtotime($site['last_scraped'])) : null,
        'country' => $site['country'],
        'language' => 'EN', // Detect or add to database
        'facebook' => $site['social_facebook'],
        'instagram' => $site['social_instagram'],
        'youtube' => $site['youtube'],
        'tiktok' => $site['social_tiktok'],
        'twitter' => $site['social_twitter'],
        'domain_reg_date' => $site['created_at'] ? date('Y-m-d', strtotime($site['created_at'])) : null,
        'indexed_date' => $site['last_scraped'] ? date('Y-m-d', strtotime($site['last_scraped'])) : null,
        'platform' => $site['theme_name'] ?: 'Wix',
        'title' => $site['store_name']
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}