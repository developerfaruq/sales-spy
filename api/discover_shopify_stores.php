<?php
declare(strict_types=1);

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 10;

// Curated list of popular Shopify stores by category
$storeDatabase = [
    'fashion' => [
        ['name' => 'Gymshark', 'domain' => 'gymshark.com', 'description' => 'Athletic apparel and fitness wear'],
        ['name' => 'Allbirds', 'domain' => 'allbirds.com', 'description' => 'Sustainable footwear'],
        ['name' => 'Fashion Nova', 'domain' => 'fashionnova.com', 'description' => 'Trendy fashion for women'],
        ['name' => 'Bombas', 'domain' => 'bombas.com', 'description' => 'Premium socks and underwear'],
        ['name' => 'Outdoor Voices', 'domain' => 'outdoorvoices.com', 'description' => 'Activewear and athleisure'],
        ['name' => 'Everlane', 'domain' => 'everlane.com', 'description' => 'Sustainable fashion basics'],
        ['name' => 'Rothy\'s', 'domain' => 'rothys.com', 'description' => 'Sustainable shoes and accessories'],
        ['name' => 'Girlfriend Collective', 'domain' => 'girlfriend.com', 'description' => 'Sustainable activewear'],
    ],
    'beauty' => [
        ['name' => 'Glossier', 'domain' => 'glossier.com', 'description' => 'Minimalist beauty and skincare'],
        ['name' => 'Kylie Cosmetics', 'domain' => 'kyliecosmetics.com', 'description' => 'Celebrity makeup brand'],
        ['name' => 'Fenty Beauty', 'domain' => 'fentybeauty.com', 'description' => 'Inclusive beauty by Rihanna'],
        ['name' => 'The Ordinary', 'domain' => 'theordinary.com', 'description' => 'Affordable skincare'],
        ['name' => 'Drunk Elephant', 'domain' => 'drunkelephant.com', 'description' => 'Clinical skincare'],
        ['name' => 'Tatcha', 'domain' => 'tatcha.com', 'description' => 'Japanese-inspired skincare'],
    ],
    'home' => [
        ['name' => 'Brooklinen', 'domain' => 'brooklinen.com', 'description' => 'Luxury bedding and home goods'],
        ['name' => 'Parachute', 'domain' => 'parachutehome.com', 'description' => 'Premium home essentials'],
        ['name' => 'Casper', 'domain' => 'casper.com', 'description' => 'Sleep products and mattresses'],
        ['name' => 'Article', 'domain' => 'article.com', 'description' => 'Modern furniture'],
        ['name' => 'Burrow', 'domain' => 'burrow.com', 'description' => 'Modular furniture'],
        ['name' => 'Tuft & Needle', 'domain' => 'tuftandneedle.com', 'description' => 'Sleep products'],
    ],
    'tech' => [
        ['name' => 'Peak Design', 'domain' => 'peakdesign.com', 'description' => 'Camera accessories and tech gear'],
        ['name' => 'Anker', 'domain' => 'ankerdirect.com', 'description' => 'Phone accessories and chargers'],
        ['name' => 'MVMT', 'domain' => 'mvmtwatches.com', 'description' => 'Modern watches and accessories'],
        ['name' => 'Popsockets', 'domain' => 'popsockets.com', 'description' => 'Phone grips and accessories'],
        ['name' => 'Bellroy', 'domain' => 'bellroy.com', 'description' => 'Premium wallets and tech accessories'],
    ],
    'fitness' => [
        ['name' => 'Mirror', 'domain' => 'mirror.co', 'description' => 'Home fitness technology'],
        ['name' => 'Hydro Flask', 'domain' => 'hydroflask.com', 'description' => 'Water bottles and drinkware'],
        ['name' => 'Theragun', 'domain' => 'theragun.com', 'description' => 'Percussion therapy devices'],
        ['name' => 'Peloton', 'domain' => 'onepeloton.com', 'description' => 'Fitness equipment and classes'],
        ['name' => 'Lululemon', 'domain' => 'lululemon.com', 'description' => 'Athletic apparel'],
    ],
    'jewelry' => [
        ['name' => 'Mejuri', 'domain' => 'mejuri.com', 'description' => 'Fine jewelry for everyday'],
        ['name' => 'Pandora', 'domain' => 'pandora.net', 'description' => 'Charm bracelets and jewelry'],
        ['name' => 'BaubleBar', 'domain' => 'baublebar.com', 'description' => 'Fashion jewelry and accessories'],
        ['name' => 'Gorjana', 'domain' => 'gorjana.com', 'description' => 'California-inspired jewelry'],
    ],
    'food' => [
        ['name' => 'Thrive Market', 'domain' => 'thrivemarket.com', 'description' => 'Organic and natural foods'],
        ['name' => 'Death Wish Coffee', 'domain' => 'deathwishcoffee.com', 'description' => 'Strong coffee blends'],
        ['name' => 'Athletic Greens', 'domain' => 'athleticgreens.com', 'description' => 'Nutritional supplements'],
        ['name' => 'Magic Spoon', 'domain' => 'magicspoon.com', 'description' => 'Protein cereal'],
    ],
    'electronics' => [
        ['name' => 'Nomad', 'domain' => 'nomadgoods.com', 'description' => 'Premium tech accessories'],
        ['name' => 'Secretlab', 'domain' => 'secretlab.co', 'description' => 'Gaming chairs'],
        ['name' => 'Razer', 'domain' => 'razer.com', 'description' => 'Gaming peripherals'],
        ['name' => 'Blue Yeti', 'domain' => 'blueyeti.com', 'description' => 'Microphones and audio'],
    ]
];

// Get stores by category or all categories
$result = [];
if ($category === '' || $category === 'all') {
    // Return all categories with limited stores each
    foreach ($storeDatabase as $cat => $stores) {
        $result[$cat] = array_slice($stores, 0, min($limit, count($stores)));
    }
} else {
    // Return specific category
    if (!isset($storeDatabase[$category])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid category', 
            'available_categories' => array_keys($storeDatabase)
        ]);
        exit;
    }
    $result = array_slice($storeDatabase[$category], 0, $limit);
}

// Add metadata
$response = [
    'stores' => $result,
    'total_categories' => count($storeDatabase),
    'available_categories' => array_keys($storeDatabase)
];

if ($category !== '' && $category !== 'all') {
    $response['category'] = $category;
    $response['total_stores_in_category'] = count($storeDatabase[$category]);
}

echo json_encode($response);
