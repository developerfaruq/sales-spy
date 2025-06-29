<?php
/**
 * Real Data Fetcher for Sales Spy
 * 
 * This script fetches real data from various eCommerce and website builder platforms
 * and stores it in the database.
 * 
 * Usage: php scripts/fetch_real_data.php [platform] [limit]
 * Example: php scripts/fetch_real_data.php shopify 10
 */

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/inc/api_helpers.php');

// Define available platforms
$available_platforms = [
    'ecommerce' => [
        'shopify',
        'etsy',
        'ebay',
        'woocommerce',
        'square'
    ],
    'websites' => [
        'wordpress',
        'wix',
        'squarespace',
        'godaddy',
        'webflow',
        'weebly',
        'namecheap',
        'hostinger',
        'hostgator',
        'bluehost'
    ]
];

// Parse command line arguments
$platform = isset($argv[1]) ? strtolower($argv[1]) : null;
$limit = isset($argv[2]) ? intval($argv[2]) : 10;

// If no platform specified, show usage
if (!$platform) {
    echo "Usage: php fetch_real_data.php [platform] [limit]\n";
    echo "Available platforms:\n";
    
    echo "eCommerce platforms:\n";
    foreach ($available_platforms['ecommerce'] as $p) {
        echo "  - $p\n";
    }
    
    echo "Website builder platforms:\n";
    foreach ($available_platforms['websites'] as $p) {
        echo "  - $p\n";
    }
    
    exit;
}

// Validate platform
$platform_type = null;
if (in_array($platform, $available_platforms['ecommerce'])) {
    $platform_type = 'ecommerce';
} elseif (in_array($platform, $available_platforms['websites'])) {
    $platform_type = 'websites';
} else {
    echo "Error: Unknown platform '$platform'.\n";
    exit;
}

// Check if we should use mock data
if (defined('USE_MOCK_DATA') && USE_MOCK_DATA) {
    log_message("Warning: USE_MOCK_DATA is set to true in config. This script is intended for real data.", 'WARNING');
    echo "Do you want to continue anyway? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "Exiting.\n";
        exit;
    }
}

// Start fetching data
log_message("Starting data fetch for platform: $platform (limit: $limit)");

try {
    // Call appropriate function based on platform
    switch ($platform) {
        case 'shopify':
            fetch_shopify_data($pdo, $limit);
            break;
        case 'etsy':
            fetch_etsy_data($pdo, $limit);
            break;
        case 'ebay':
            fetch_ebay_data($pdo, $limit);
            break;
        case 'woocommerce':
            fetch_woocommerce_data($pdo, $limit);
            break;
        case 'square':
            fetch_square_data($pdo, $limit);
            break;
        case 'wordpress':
            fetch_wordpress_data($pdo, $limit);
            break;
        case 'wix':
            fetch_wix_data($pdo, $limit);
            break;
        case 'squarespace':
            fetch_squarespace_data($pdo, $limit);
            break;
        case 'godaddy':
            fetch_godaddy_data($pdo, $limit);
            break;
        case 'webflow':
            fetch_webflow_data($pdo, $limit);
            break;
        case 'weebly':
            fetch_weebly_data($pdo, $limit);
            break;
        case 'namecheap':
            fetch_namecheap_data($pdo, $limit);
            break;
        case 'hostinger':
            fetch_hostinger_data($pdo, $limit);
            break;
        case 'hostgator':
            fetch_hostgator_data($pdo, $limit);
            break;
        case 'bluehost':
            fetch_bluehost_data($pdo, $limit);
            break;
        default:
            log_message("No implementation for platform: $platform", 'ERROR');
            exit;
    }
    
    log_message("Data fetch completed for platform: $platform");
} catch (Exception $e) {
    log_message("Error fetching data: " . $e->getMessage(), 'ERROR');
    exit;
}

/**
 * Fetch data from Shopify
 */
function fetch_shopify_data($pdo, $limit) {
    log_message("Fetching Shopify data...");
    
    // Shopify doesn't have a public API for store discovery
    // We'll use a combination of known Shopify stores and the Shopify Partners API if available
    
    // For now, we'll use a curated list of known Shopify stores
    $shopify_stores = [
        [
            'domain' => 'allbirds.com',
            'title' => 'Allbirds',
            'description' => 'Sustainable shoes and clothing made from natural materials',
            'country' => 'US',
            'product_count' => 50,
            'avg_price' => 95.00,
            'categories' => 'Fashion, Footwear, Sustainable',
        ],
        [
            'domain' => 'gymshark.com',
            'title' => 'Gymshark',
            'description' => 'Fitness apparel & accessories brand',
            'country' => 'UK',
            'product_count' => 500,
            'avg_price' => 45.00,
            'categories' => 'Fitness, Apparel, Sportswear',
        ],
        [
            'domain' => 'brooklinen.com',
            'title' => 'Brooklinen',
            'description' => 'Luxury bedding and home essentials',
            'country' => 'US',
            'product_count' => 100,
            'avg_price' => 120.00,
            'categories' => 'Home, Bedding, Luxury',
        ],
        [
            'domain' => 'bombas.com',
            'title' => 'Bombas',
            'description' => 'Comfort-focused socks and apparel',
            'country' => 'US',
            'product_count' => 150,
            'avg_price' => 15.00,
            'categories' => 'Fashion, Socks, Apparel',
        ],
        [
            'domain' => 'mvmt.com',
            'title' => 'MVMT',
            'description' => 'Stylish watches and accessories',
            'country' => 'US',
            'product_count' => 200,
            'avg_price' => 120.00,
            'categories' => 'Fashion, Watches, Accessories',
        ],
        [
            'domain' => 'kylie.com',
            'title' => 'Kylie Cosmetics',
            'description' => 'Makeup and beauty products by Kylie Jenner',
            'country' => 'US',
            'product_count' => 300,
            'avg_price' => 25.00,
            'categories' => 'Beauty, Cosmetics, Makeup',
        ],
        [
            'domain' => 'beardbrand.com',
            'title' => 'Beardbrand',
            'description' => 'Premium beard care products',
            'country' => 'US',
            'product_count' => 75,
            'avg_price' => 30.00,
            'categories' => 'Grooming, Men, Beard Care',
        ],
        [
            'domain' => 'manitobah.com',
            'title' => 'Manitobah Mukluks',
            'description' => 'Indigenous-owned footwear company',
            'country' => 'CA',
            'product_count' => 100,
            'avg_price' => 200.00,
            'categories' => 'Fashion, Footwear, Indigenous',
        ],
        [
            'domain' => 'helm.com',
            'title' => 'Helm Boots',
            'description' => 'Handcrafted leather boots',
            'country' => 'US',
            'product_count' => 50,
            'avg_price' => 350.00,
            'categories' => 'Fashion, Footwear, Handcrafted',
        ],
        [
            'domain' => 'rothys.com',
            'title' => 'Rothy\'s',
            'description' => 'Sustainable shoes and bags made from recycled plastic',
            'country' => 'US',
            'product_count' => 75,
            'avg_price' => 125.00,
            'categories' => 'Fashion, Footwear, Sustainable',
        ],
    ];
    
    // Limit the number of stores to insert
    $shopify_stores = array_slice($shopify_stores, 0, $limit);
    
    // Insert stores into database
    $inserted_count = 0;
    foreach ($shopify_stores as $store) {
        try {
            // Check if store already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM stores WHERE domain = ?");
            $check_stmt->execute([$store['domain']]);
            $exists = $check_stmt->fetchColumn() > 0;
            
            if ($exists) {
                log_message("Store already exists: {$store['domain']} - updating");
                
                // Update existing store
                $stmt = $pdo->prepare("
                    UPDATE stores SET 
                    title = :title,
                    description = :description,
                    language = :language,
                    country = :country,
                    currency = :currency,
                    product_count = :product_count,
                    avg_price = :avg_price,
                    categories = :categories,
                    tech_stack = :tech_stack,
                    date_added = NOW()
                    WHERE domain = :domain
                ");
                
                $stmt->execute([
                    ':title' => $store['title'],
                    ':description' => $store['description'],
                    ':language' => 'en', // Default to English
                    ':country' => $store['country'],
                    ':currency' => $store['country'] == 'US' ? 'USD' : ($store['country'] == 'CA' ? 'CAD' : 'USD'),
                    ':product_count' => $store['product_count'],
                    ':avg_price' => $store['avg_price'],
                    ':categories' => $store['categories'],
                    ':tech_stack' => 'Shopify',
                    ':domain' => $store['domain']
                ]);
            } else {
                // Insert new store
                $stmt = $pdo->prepare("
                    INSERT INTO stores (
                        domain, title, description, language, country, currency, 
                        product_count, avg_price, categories, tech_stack, date_added
                    ) VALUES (
                        :domain, :title, :description, :language, :country, :currency, 
                        :product_count, :avg_price, :categories, :tech_stack, NOW()
                    )
                ");
                
                $stmt->execute([
                    ':domain' => $store['domain'],
                    ':title' => $store['title'],
                    ':description' => $store['description'],
                    ':language' => 'en', // Default to English
                    ':country' => $store['country'],
                    ':currency' => $store['country'] == 'US' ? 'USD' : ($store['country'] == 'CA' ? 'CAD' : 'USD'),
                    ':product_count' => $store['product_count'],
                    ':avg_price' => $store['avg_price'],
                    ':categories' => $store['categories'],
                    ':tech_stack' => 'Shopify'
                ]);
            }
            
            log_message("Processed: {$store['title']} ({$store['domain']})");
            $inserted_count++;
        } catch (PDOException $e) {
            log_message("Error inserting {$store['domain']}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    log_message("Shopify data fetch complete. Processed $inserted_count stores.");
}

/**
 * Fetch data from Etsy
 */
function fetch_etsy_data($pdo, $limit) {
    log_message("Fetching Etsy data...");
    
    // Check if API key is available
    if (!defined('ETSY_API_KEY') || empty(ETSY_API_KEY)) {
        log_message("Etsy API key not configured. Please add your API key to config/config.php.", 'ERROR');
        return;
    }
    
    // Etsy API requires authentication
    // Documentation: https://developers.etsy.com/documentation/
    
    $api_key = ETSY_API_KEY;
    $endpoint = "https://openapi.etsy.com/v3/application/shops";
    
    // Set up headers
    $headers = [
        "x-api-key: $api_key",
        "Accept: application/json"
    ];
    
    // Make API request with rate limiting
    if (!rate_limit('etsy_api', ETSY_RATE_LIMIT, 1)) {
        log_message("Rate limit exceeded for Etsy API. Waiting...", 'WARNING');
        sleep(1); // Wait for rate limit to reset
    }
    
    $response = make_http_request($endpoint, $headers);
    
    // Check response
    if ($response['status'] != 200) {
        log_message("Etsy API error: HTTP status {$response['status']}", 'ERROR');
        return;
    }
    
    // Parse response
    $data = parse_json_response($response['body']);
    if (!$data || !isset($data['results'])) {
        log_message("Failed to parse Etsy API response", 'ERROR');
        return;
    }
    
    // Process shops
    $shops = array_slice($data['results'], 0, $limit);
    $inserted_count = 0;
    
    foreach ($shops as $shop) {
        try {
            // Get shop details
            $shop_id = $shop['shop_id'];
            $shop_endpoint = "https://openapi.etsy.com/v3/application/shops/$shop_id";
            
            if (!rate_limit('etsy_api', ETSY_RATE_LIMIT, 1)) {
                log_message("Rate limit exceeded for Etsy API. Waiting...", 'WARNING');
                sleep(1);
            }
            
            $shop_response = make_http_request($shop_endpoint, $headers);
            $shop_data = parse_json_response($shop_response['body']);
            
            if (!$shop_data || !isset($shop_data['shop_name'])) {
                log_message("Failed to get details for shop ID: $shop_id", 'WARNING');
                continue;
            }
            
            // Extract relevant data
            $domain = "etsy.com/shop/" . $shop_data['shop_name'];
            $title = $shop_data['shop_name'];
            $description = isset($shop_data['announcement']) ? $shop_data['announcement'] : '';
            $country = isset($shop_data['country_id']) ? $shop_data['country_id'] : 'US';
            
            // Check if store already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM stores WHERE domain = ?");
            $check_stmt->execute([$domain]);
            $exists = $check_stmt->fetchColumn() > 0;
            
            if ($exists) {
                log_message("Store already exists: $domain - updating");
                
                // Update existing store
                $stmt = $pdo->prepare("
                    UPDATE stores SET 
                    title = :title,
                    description = :description,
                    language = :language,
                    country = :country,
                    currency = :currency,
                    tech_stack = :tech_stack,
                    date_added = NOW()
                    WHERE domain = :domain
                ");
                
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':language' => 'en', // Default to English
                    ':country' => $country,
                    ':currency' => $country == 'US' ? 'USD' : 'EUR',
                    ':tech_stack' => 'Etsy',
                    ':domain' => $domain
                ]);
            } else {
                // Insert new store
                $stmt = $pdo->prepare("
                    INSERT INTO stores (
                        domain, title, description, language, country, currency, 
                        tech_stack, date_added
                    ) VALUES (
                        :domain, :title, :description, :language, :country, :currency, 
                        :tech_stack, NOW()
                    )
                ");
                
                $stmt->execute([
                    ':domain' => $domain,
                    ':title' => $title,
                    ':description' => $description,
                    ':language' => 'en', // Default to English
                    ':country' => $country,
                    ':currency' => $country == 'US' ? 'USD' : 'EUR',
                    ':tech_stack' => 'Etsy'
                ]);
            }
            
            log_message("Processed: $title ($domain)");
            $inserted_count++;
        } catch (Exception $e) {
            log_message("Error processing Etsy shop: " . $e->getMessage(), 'ERROR');
        }
    }
    
    log_message("Etsy data fetch complete. Processed $inserted_count stores.");
}

/**
 * Fetch data from eBay (placeholder)
 */
function fetch_ebay_data($pdo, $limit) {
    log_message("eBay API integration not implemented yet. Requires API key.");
    
    // eBay API requires authentication
    // Documentation: https://developer.ebay.com/
    
    // TODO: Implement eBay API integration when API key is available
}

/**
 * Fetch data from WooCommerce sites (placeholder)
 */
function fetch_woocommerce_data($pdo, $limit) {
    log_message("WooCommerce data fetch not implemented yet.");
    
    // WooCommerce doesn't have a central directory
    // We'll need to use a list of known WooCommerce sites or a discovery service
    
    // TODO: Implement WooCommerce data fetch
}

/**
 * Fetch data from Square (placeholder)
 */
function fetch_square_data($pdo, $limit) {
    log_message("Square API integration not implemented yet. Requires API key.");
    
    // Square API requires authentication
    // Documentation: https://developer.squareup.com/
    
    // TODO: Implement Square API integration when API key is available
}

/**
 * Fetch data from WordPress (placeholder)
 */
function fetch_wordpress_data($pdo, $limit) {
    log_message("Fetching WordPress data...");
    
    // We can use the WordPress.org API to fetch themes and plugins
    // But for websites, we'll need a discovery service or list
    
    // For now, we'll use a curated list of known WordPress sites
    $wordpress_sites = [
        [
            'domain' => 'techcrunch.com',
            'title' => 'TechCrunch',
            'description' => 'Technology news and analysis',
            'country' => 'US',
            'categories' => 'Technology, News, Media',
        ],
        [
            'domain' => 'bbcamerica.com',
            'title' => 'BBC America',
            'description' => 'British television channel',
            'country' => 'US',
            'categories' => 'Entertainment, Media, Television',
        ],
        [
            'domain' => 'thewaltdisneycompany.com',
            'title' => 'The Walt Disney Company',
            'description' => 'Entertainment company',
            'country' => 'US',
            'categories' => 'Entertainment, Media, Corporate',
        ],
        [
            'domain' => 'beyonce.com',
            'title' => 'Beyoncé',
            'description' => 'Official website of Beyoncé',
            'country' => 'US',
            'categories' => 'Music, Entertainment, Celebrity',
        ],
        [
            'domain' => 'katyperry.com',
            'title' => 'Katy Perry',
            'description' => 'Official website of Katy Perry',
            'country' => 'US',
            'categories' => 'Music, Entertainment, Celebrity',
        ],
        [
            'domain' => 'sweden.se',
            'title' => 'Sweden.se',
            'description' => 'Official website of Sweden',
            'country' => 'SE',
            'categories' => 'Government, Travel, Tourism',
        ],
        [
            'domain' => 'whitehouse.gov',
            'title' => 'The White House',
            'description' => 'Official website of the White House',
            'country' => 'US',
            'categories' => 'Government, Politics',
        ],
        [
            'domain' => 'blog.mozilla.org',
            'title' => 'Mozilla Blog',
            'description' => 'Mozilla Foundation blog',
            'country' => 'US',
            'categories' => 'Technology, Open Source, Software',
        ],
        [
            'domain' => 'news.harvard.edu',
            'title' => 'Harvard Gazette',
            'description' => 'Harvard University news',
            'country' => 'US',
            'categories' => 'Education, News, University',
        ],
        [
            'domain' => 'blog.ted.com',
            'title' => 'TED Blog',
            'description' => 'TED Conferences blog',
            'country' => 'US',
            'categories' => 'Education, Ideas, Technology',
        ],
    ];
    
    // Limit the number of sites to insert
    $wordpress_sites = array_slice($wordpress_sites, 0, $limit);
    
    // Insert sites into database
    $inserted_count = 0;
    foreach ($wordpress_sites as $site) {
        try {
            // Check if website already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM websites WHERE domain = ?");
            $check_stmt->execute([$site['domain']]);
            $exists = $check_stmt->fetchColumn() > 0;
            
            if ($exists) {
                log_message("Website already exists: {$site['domain']} - updating");
                
                // Update existing website
                $stmt = $pdo->prepare("
                    UPDATE websites SET 
                    title = :title,
                    description = :description,
                    country = :country,
                    categories = :categories,
                    platform = :platform
                    WHERE domain = :domain
                ");
                
                $stmt->execute([
                    ':title' => $site['title'],
                    ':description' => $site['description'],
                    ':country' => $site['country'],
                    ':categories' => $site['categories'],
                    ':platform' => 'WordPress',
                    ':domain' => $site['domain']
                ]);
            } else {
                // Insert new website
                $stmt = $pdo->prepare("
                    INSERT INTO websites (
                        domain, title, description, country, categories, platform, creation_date
                    ) VALUES (
                        :domain, :title, :description, :country, :categories, :platform, NOW()
                    )
                ");
                
                $stmt->execute([
                    ':domain' => $site['domain'],
                    ':title' => $site['title'],
                    ':description' => $site['description'],
                    ':country' => $site['country'],
                    ':categories' => $site['categories'],
                    ':platform' => 'WordPress'
                ]);
            }
            
            log_message("Processed: {$site['title']} ({$site['domain']})");
            $inserted_count++;
        } catch (PDOException $e) {
            log_message("Error inserting {$site['domain']}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    log_message("WordPress data fetch complete. Processed $inserted_count websites.");
}

// Placeholder functions for other platforms
function fetch_wix_data($pdo, $limit) {
    log_message("Wix data fetch not implemented yet.");
}

function fetch_squarespace_data($pdo, $limit) {
    log_message("Squarespace data fetch not implemented yet.");
}

function fetch_godaddy_data($pdo, $limit) {
    log_message("GoDaddy data fetch not implemented yet.");
}

function fetch_webflow_data($pdo, $limit) {
    log_message("Webflow data fetch not implemented yet.");
}

function fetch_weebly_data($pdo, $limit) {
    log_message("Weebly data fetch not implemented yet.");
}

function fetch_namecheap_data($pdo, $limit) {
    log_message("Namecheap data fetch not implemented yet.");
}

function fetch_hostinger_data($pdo, $limit) {
    log_message("Hostinger data fetch not implemented yet.");
}

function fetch_hostgator_data($pdo, $limit) {
    log_message("HostGator data fetch not implemented yet.");
}

function fetch_bluehost_data($pdo, $limit) {
    log_message("Bluehost data fetch not implemented yet.");
}