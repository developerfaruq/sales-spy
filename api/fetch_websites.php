<?php
require_once '../config/db.php';

if (USE_MOCK_DATA) {
    echo "Inserting mock website data...\n";

    $mockWebsites = [
        [
            'name' => 'WordPress Blog',
            'url' => 'wordpress-example.com',
            'platform' => 'WordPress',
            'category' => 'Blog, Business',
            'country' => 'US',
            'creation_date' => '2023-05-15'
        ],
        [
            'name' => 'Wix Portfolio',
            'url' => 'wix-portfolio.com',
            'platform' => 'Wix',
            'category' => 'Portfolio, Creative',
            'country' => 'UK',
            'creation_date' => '2023-08-21'
        ],
        [
            'name' => 'Squarespace Store',
            'url' => 'squarespace-store.com',
            'platform' => 'Squarespace',
            'category' => 'E-commerce, Fashion',
            'country' => 'CA',
            'creation_date' => '2024-01-10'
        ],
        [
            'name' => 'GoDaddy Business',
            'url' => 'godaddy-business.com',
            'platform' => 'GoDaddy',
            'category' => 'Business, Services',
            'country' => 'AU',
            'creation_date' => '2023-11-05'
        ],
        [
            'name' => 'Webflow Agency',
            'url' => 'webflow-agency.io',
            'platform' => 'Webflow',
            'category' => 'Agency, Design',
            'country' => 'FR',
            'creation_date' => '2024-02-28'
        ],
        [
            'name' => 'Weebly Blog',
            'url' => 'weebly-blog.com',
            'platform' => 'Weebly',
            'category' => 'Blog, Personal',
            'country' => 'DE',
            'creation_date' => '2023-07-11'
        ],
        [
            'name' => 'Namecheap Site',
            'url' => 'namecheap-site.org',
            'platform' => 'Namecheap',
            'category' => 'Non-profit, Organization',
            'country' => 'ES',
            'creation_date' => '2023-09-30'
        ],
        [
            'name' => 'Hostinger Education',
            'url' => 'hostinger-education.com',
            'platform' => 'Hostinger',
            'category' => 'Education, Courses',
            'country' => 'IN',
            'creation_date' => '2024-03-15'
        ],
        [
            'name' => 'HostGator Magazine',
            'url' => 'hostgator-magazine.com',
            'platform' => 'HostGator',
            'category' => 'Magazine, News',
            'country' => 'BR',
            'creation_date' => '2023-10-05'
        ],
        [
            'name' => 'Bluehost Tech',
            'url' => 'bluehost-tech.com',
            'platform' => 'Bluehost',
            'category' => 'Technology, Software',
            'country' => 'JP',
            'creation_date' => '2024-01-25'
        ]
    ];

    // Clear existing records if needed
    // $pdo->exec("TRUNCATE TABLE websites");

    foreach ($mockWebsites as $website) {
        $stmt = $pdo->prepare("
            INSERT INTO websites (
                name, url, platform, category, country, creation_date
            ) VALUES (
                :name, :url, :platform, :category, :country, :creation_date
            )
        ");

        $stmt->execute([
            ':name' => $website['name'],
            ':url' => $website['url'],
            ':platform' => $website['platform'],
            ':category' => $website['category'],
            ':country' => $website['country'],
            ':creation_date' => $website['creation_date']
        ]);
        echo "Inserted: " . $website['name'] . "\n";
    }
    echo "Mock website data insertion complete.\n";
} else {
    echo "Mock website data insertion skipped (USE_MOCK_DATA is false).\n";
} 