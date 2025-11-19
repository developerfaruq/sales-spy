<?php
require_once "../config/db.php";
header("Content-Type: application/json");

// 🔑 Your SerpAPI Key — keep this secret!
$apiKey = "83e48694c96c121ee10b2ebfffb0e0ed5e12c65b805652f6d38280c38559a322";

// Keywords to find Shopify stores
$keywords = [
  
  // 🧥 Fashion & Apparel
  // 'fashion', 'clothing', 't-shirts', 'hoodies', 'jeans', 'jackets', 'dresses',
  // 'activewear', 'streetwear', 'luxury fashion', 'men fashion', 'women fashion',
  // 'vintage clothing', 'kids clothing', 'sustainable fashion', 'athleisure',

  // // 👟 Footwear
  // 'sneakers', 'boots', 'sandals', 'heels', 'running shoes', 'sports shoes',
  // 'leather shoes', 'flip flops', 'designer shoes',

  // // 💄 Beauty & Personal Care
  // 'beauty', 'makeup', 'skincare', 'hair care', 'nail polish', 'perfume',
  // 'cosmetics', 'lipstick', 'foundation', 'eyeshadow', 'organic beauty products',

  // // 🏠 Home & Lifestyle
  // 'home decor', 'furniture', 'wall art', 'lighting', 'bedding', 'kitchenware',
  // 'bathroom accessories', 'candles', 'home improvement', 'smart home devices',
  // 'kitchen appliances', 'cookware', 'tableware',

  // // ⌚ Accessories
  // 'watches', 'jewelry', 'bracelets', 'rings', 'necklaces', 'earrings',
  // 'bags', 'wallets', 'belts', 'sunglasses', 'scarves', 'hats',

  // // 🎮 Tech & Gadgets
  // 'gadgets', 'electronics', 'tech accessories', 'wireless chargers',
  // 'smartwatches', 'headphones', 'Bluetooth speakers', 'power banks',
  // 'gaming accessories', 'PC peripherals', 'VR headset', 'wearable tech',

  // // 🧸 Kids & Toys
  // 'toys', 'educational toys', 'baby products', 'kids gifts', 'plush toys',
  // 'learning games', 'board games', 'outdoor toys',

  // // 🧴 Health & Wellness
  // 'fitness equipment', 'yoga', 'supplements', 'protein powder', 'massage tools',
  // 'essential oils', 'wellness products', 'health gadgets',

  // // 🏖️ Sports & Outdoor
  // 'camping gear', 'gym wear', 'swimwear', 'surfing gear', 'hiking boots',
  // 'outdoor equipment', 'sportswear', 'cycling accessories',

  // // 🐶 Pets
  // 'pet supplies', 'dog toys', 'cat accessories', 'pet grooming', 'pet food',

  // // 🍳 Food & Kitchen
  // 'kitchen tools', 'baking accessories', 'coffee accessories', 'cookware sets',
  // 'blenders', 'smart kitchen gadgets', 'food containers',

  // 🎁 Gifts & Misc
  'gift ideas', 'custom gifts', 'personalized items', 'stationery',
  'art supplies', 'collectibles', 'home fragrance', 'seasonal decor'


];

// Prepare insert/update statement for your shopify_stores table
$insert = $pdo->prepare("
  INSERT INTO shopify_stores (store_url, store_name, last_discovered)
  VALUES (?, ?, NOW())
  ON DUPLICATE KEY UPDATE last_discovered = NOW(), updated_at = NOW()
");

$found = [];

foreach ($keywords as $kw) {
  $url = "https://serpapi.com/search.json?engine=google&q=site:myshopify.com+$kw&api_key=$apiKey";
  
  $json = @file_get_contents($url);
  if (!$json) continue;
  
  $data = json_decode($json, true);
  if (!isset($data['organic_results'])) continue;

  foreach ($data['organic_results'] as $res) {
    if (!isset($res['link'])) continue;

   $link = trim($res['link']);
if (strpos($link, 'myshopify.com') === false) continue;

// Extract clean root store URL only
if (preg_match('/https?:\/\/[a-zA-Z0-9\-]+\.myshopify\.com/', $link, $match)) {
  $storeUrl = $match[0];
} else {
  continue;
}


    // Try to derive store name from URL
    $storeName = preg_replace('/https?:\/\/([^\.]+)\.myshopify\.com.*/i', '$1', $storeUrl);
    $storeName = ucfirst(str_replace(['-', '_'], ' ', $storeName));

    // Insert or update store info
    $insert->execute([$storeUrl, $storeName]);
    $found[] = $storeUrl;
  }
}

// ✅ Return clean response
echo json_encode([
  'status' => 'ok',
  'found' => count($found),
  'stores' => array_values(array_unique($found))
]);
?>
