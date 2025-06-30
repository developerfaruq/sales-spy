<?php
require_once(__DIR__ . '/../config/db.php');

echo "Inserting mock website data...\n";

// First, check if there are already websites in the database
$checkStmt = $pdo->query("SELECT COUNT(*) FROM websites");
$existingCount = $checkStmt->fetchColumn();

if ($existingCount > 0) {
    echo "There are already $existingCount websites in the database. Do you want to continue? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "Aborting website insertion.\n";
        exit;
    }
}

$mockWebsites = [
    [
        'name' => 'Fitness Blog',
        'url' => 'fitnessblog.com',
        'platform' => 'WordPress',
        'category' => 'Health & Fitness',
        'country' => 'US',
        'creation_date' => '2022-03-15',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Creative Design Studio',
        'url' => 'creativestudio.design',
        'platform' => 'Wix',
        'category' => 'Design',
        'country' => 'UK',
        'creation_date' => '2021-11-20',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Artisan Pottery',
        'url' => 'artisanpottery.co',
        'platform' => 'Squarespace',
        'category' => 'Arts & Crafts',
        'country' => 'CA',
        'creation_date' => '2023-01-05',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Tech Solutions Inc',
        'url' => 'techsolutionsinc.com',
        'platform' => 'GoDaddy',
        'category' => 'Technology',
        'country' => 'US',
        'creation_date' => '2020-07-12',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Modern Interior Design',
        'url' => 'moderninterior.design',
        'platform' => 'Webflow',
        'category' => 'Interior Design',
        'country' => 'AU',
        'creation_date' => '2022-09-30',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Family Recipe Collection',
        'url' => 'familyrecipes.net',
        'platform' => 'Weebly',
        'category' => 'Food & Cooking',
        'country' => 'IT',
        'creation_date' => '2021-04-18',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Digital Marketing Agency',
        'url' => 'digitalmarketpro.com',
        'platform' => 'Namecheap',
        'category' => 'Marketing',
        'country' => 'DE',
        'creation_date' => '2023-02-28',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Travel Blog Adventures',
        'url' => 'travelblogadventures.com',
        'platform' => 'Hostinger',
        'category' => 'Travel',
        'country' => 'ES',
        'creation_date' => '2022-06-10',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Pet Care Services',
        'url' => 'petcareservices.org',
        'platform' => 'HostGator',
        'category' => 'Pets & Animals',
        'country' => 'FR',
        'creation_date' => '2021-08-25',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Financial Advisory Group',
        'url' => 'financialadvisory.net',
        'platform' => 'Bluehost',
        'category' => 'Finance',
        'country' => 'SG',
        'creation_date' => '2022-12-05',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Organic Gardening Tips',
        'url' => 'organicgardening.info',
        'platform' => 'WordPress',
        'category' => 'Gardening',
        'country' => 'NZ',
        'creation_date' => '2023-03-20',
        'added_on' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Vintage Clothing Store',
        'url' => 'vintagefashionfinds.com',
        'platform' => 'Wix',
        'category' => 'Fashion',
        'country' => 'JP',
        'creation_date' => '2021-10-15',
        'added_on' => date('Y-m-d H:i:s')
    ]
];

// Check if the websites table exists, if not create it
try {
    $pdo->query("SELECT 1 FROM websites LIMIT 1");
} catch (PDOException $e) {
    echo "Creating websites table...\n";
    
    $createTableSQL = "CREATE TABLE websites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL,
        platform VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        country VARCHAR(50) NOT NULL,
        creation_date DATE NOT NULL,
        added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createTableSQL);
    echo "Websites table created successfully.\n";
}

// Insert the mock websites
$insertCount = 0;
foreach ($mockWebsites as $website) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO websites (
                name, url, platform, category, country, creation_date, added_on
            ) VALUES (
                :name, :url, :platform, :category, :country, :creation_date, :added_on
            )
        ");

        $stmt->execute([
            ':name' => $website['name'],
            ':url' => $website['url'],
            ':platform' => $website['platform'],
            ':category' => $website['category'],
            ':country' => $website['country'],
            ':creation_date' => $website['creation_date'],
            ':added_on' => $website['added_on']
        ]);
        
        echo "Inserted: " . $website['name'] . " (" . $website['platform'] . ")\n";
        $insertCount++;
    } catch (PDOException $e) {
        echo "Error inserting " . $website['name'] . ": " . $e->getMessage() . "\n";
    }
}

echo "Mock website data insertion complete. Inserted $insertCount websites.\n"; 