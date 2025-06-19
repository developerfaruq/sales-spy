<?php
require_once __DIR__ . '/../config/db.php';

if (!USE_MOCK_DATA) {
    http_response_code(403);
    echo json_encode(["error" => "Mock data insertion is disabled."]);
    exit;
}

$mockWebsites = [
    ["name" => "WP Design Studio", "url" => "https://wpdesignstudio.com", "platform" => "WordPress", "country" => "USA", "category" => "Web Design", "creation_date" => "2021-03-15"],
    ["name" => "Wixly", "url" => "https://wixly.io", "platform" => "Wix", "country" => "UK", "category" => "Marketing", "creation_date" => "2022-07-10"],
    ["name" => "SquareSpace Creatives", "url" => "https://sscreatives.com", "platform" => "Squarespace", "country" => "Canada", "category" => "Portfolio", "creation_date" => "2020-11-22"],
    ["name" => "GoDaddy Pros", "url" => "https://godaddypros.com", "platform" => "GoDaddy", "country" => "Australia", "category" => "Business", "creation_date" => "2023-01-05"],
    ["name" => "Webflow Wizards", "url" => "https://webflowwizards.com", "platform" => "Webflow", "country" => "Germany", "category" => "Agency", "creation_date" => "2021-09-30"],
    ["name" => "Weebly Works", "url" => "https://weeblyworks.com", "platform" => "Weebly", "country" => "India", "category" => "Ecommerce", "creation_date" => "2022-04-18"],
    ["name" => "Namecheap Ninjas", "url" => "https://namecheapninjas.com", "platform" => "Namecheap", "country" => "Nigeria", "category" => "Blog", "creation_date" => "2020-06-12"],
    ["name" => "Hostinger Hub", "url" => "https://hostingerhub.com", "platform" => "Hostinger", "country" => "Brazil", "category" => "Landing Page", "creation_date" => "2023-02-14"],
    ["name" => "HostGator Heroes", "url" => "https://hostgatorheroes.com", "platform" => "HostGator", "country" => "France", "category" => "Web Design", "creation_date" => "2021-12-01"],
    ["name" => "Bluehost Builders", "url" => "https://bluehostbuilders.com", "platform" => "Bluehost", "country" => "South Africa", "category" => "Portfolio", "creation_date" => "2022-08-25"],
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO websites (name, url, platform, country, category, creation_date) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($mockWebsites as $site) {
        $stmt->execute([
            $site["name"],
            $site["url"],
            $site["platform"],
            $site["country"],
            $site["category"],
            $site["creation_date"]
        ]);
    }
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Mock websites inserted."]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} 