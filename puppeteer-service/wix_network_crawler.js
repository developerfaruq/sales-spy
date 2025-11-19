const axios = require("axios");
const cheerio = require("cheerio");
const mysql = require("mysql2/promise");
const { URL } = require("url");
const https = require("https");

// ==================== CONFIGURATION ====================
const CONFIG = {
  MAX_STORES: 2000,
  TIMEOUT: 10000,
  DELAY: 1500,
  BATCH_SIZE: 5, // Process multiple sites simultaneously
  
  // Better exclusion list
  EXCLUDE_DOMAINS: [
    'facebook.com', 'instagram.com', 'twitter.com', 'x.com',
    'youtube.com', 'tiktok.com', 'linkedin.com', 'pinterest.com',
    'google.com', 'wix.com', 'wixsite.com', 'wixstatic.com',
    'amazon.com', 'ebay.com', 'paypal.com', 'stripe.com',
    'apple.com', 'microsoft.com', 'adobe.com', 'zoom.us',
    'mailchimp.com', 'constantcontact.com', 'messenger.com',
    'whatsapp.com', 'wa.me', 'maps.google', 'goo.gl',
    'yelp.com', 'bbb.org', 'trustpilot.com', 'indeed.com',
    'behance.net', 'dribbble.com', 'awwwards.com', 'discord',
    'etsy.com', 'shopify.com', 'squarespace.com', 'wordpress',
  ],
};

const USER_AGENTS = [
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
];

function getRandomUserAgent() {
  return USER_AGENTS[Math.floor(Math.random() * USER_AGENTS.length)];
}

// ==================== INDUSTRY SEED URLs ====================
// These are popular business directory/listing sites to scrape
const SEED_SOURCES = [
  // Business directories (will check if businesses use Wix)
  'https://www.houzz.com/professionals',
  'https://www.manta.com',
  'https://www.yellowpages.com',
  
  // Industry-specific
  'https://www.archdaily.com',
  'https://www.dezeen.com',
];

// ==================== WIX DETECTION ====================
async function isWixSite(url) {
  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent": getRandomUserAgent(),
        "Accept": "text/html,application/xhtml+xml",
      },
      timeout: CONFIG.TIMEOUT,
      maxRedirects: 3,
      httpsAgent: new https.Agent({ rejectUnauthorized: false }),
    });

    const html = response.data;
    const $ = cheerio.load(html);

    // Multiple Wix detection methods
    const signatures = {
      generator: $('meta[name="generator"]').attr('content')?.toLowerCase().includes('wix'),
      scripts: $('script[src*="wix"]').length > 0 ||
               $('script[src*="parastorage"]').length > 0 ||
               $('script[src*="wixstatic"]').length > 0,
      links: $('link[href*="wixstatic"]').length > 0,
      html: html.includes('static.wixstatic.com') ||
            html.includes('WixCodeSDK') ||
            html.includes('_wix'),
      comments: html.includes('<!-- Wix'),
    };

    const isWix = Object.values(signatures).some(v => v);
    const finalUrl = response.request.res.responseUrl || url;
    const isCustomDomain = !finalUrl.includes('wixsite.com') && !finalUrl.includes('.wixstudio.com');

    if (isWix && isCustomDomain) {
      return {
        url: finalUrl,
        title: $('title').text().trim() || null,
        description: $('meta[name="description"]').attr('content') || null,
      };
    }

    return null;
  } catch (error) {
    return null;
  }
}

// ==================== URL EXTRACTION ====================
function extractDomain(url) {
  try {
    const parsed = new URL(url.startsWith('http') ? url : 'https://' + url);
    return parsed.origin;
  } catch (e) {
    return null;
  }
}

function isExcludedDomain(url) {
  const urlLower = url.toLowerCase();
  return CONFIG.EXCLUDE_DOMAINS.some(domain => urlLower.includes(domain));
}

// ==================== DISCOVERY METHODS ====================

// Method 1: Random Domain Generator (try common patterns)
async function generateRandomDomains() {
  console.log("\n🎲 METHOD 1: Trying common domain patterns...");
  const discovered = [];
  
  const prefixes = ['my', 'the', 'studio', 'creative', 'design', 'shop'];
  const middles = ['beauty', 'photo', 'art', 'fashion', 'food', 'fitness', 'yoga'];
  const suffixes = ['studio', 'co', 'shop', 'store', 'boutique', 'agency'];
  const tlds = ['.com', '.co', '.net', '.io'];
  
  // Generate combinations
  const attempts = [];
  for (let i = 0; i < 50; i++) {
    const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    const middle = middles[Math.floor(Math.random() * middles.length)];
    const suffix = suffixes[Math.floor(Math.random() * suffixes.length)];
    const tld = tlds[Math.floor(Math.random() * tlds.length)];
    
    attempts.push(`https://${prefix}${middle}${suffix}${tld}`);
    attempts.push(`https://${middle}${suffix}${tld}`);
  }
  
  // Check in batches
  for (let i = 0; i < attempts.length; i += CONFIG.BATCH_SIZE) {
    const batch = attempts.slice(i, i + CONFIG.BATCH_SIZE);
    const results = await Promise.all(
      batch.map(url => isWixSite(url))
    );
    
    results.forEach((result, idx) => {
      if (result) {
        console.log(`   ✅ Found: ${batch[idx]}`);
        discovered.push(result);
      }
    });
    
    await new Promise(r => setTimeout(r, CONFIG.DELAY));
    
    if (discovered.length >= 50) break;
  }
  
  console.log(`   📊 Found ${discovered.length} sites from random generation`);
  return discovered;
}

// Method 2: TLD Enumeration
async function enumerateTLDs() {
  console.log("\n🌐 METHOD 2: Checking alternative TLDs of existing sites...");
  const discovered = [];
  
  // This will use your existing sites and check other TLDs
  return discovered; // Placeholder
}

// Method 3: Similar Domain Names
async function findSimilarDomains(seedDomains) {
  console.log("\n🔍 METHOD 3: Finding variations of successful domains...");
  const discovered = [];
  
  for (const seedUrl of seedDomains.slice(0, 10)) {
    try {
      const parsed = new URL(seedUrl);
      const domain = parsed.hostname.replace('www.', '');
      const [name, tld] = domain.split('.');
      
      // Try variations
      const variations = [
        `https://${name}shop.${tld}`,
        `https://${name}store.${tld}`,
        `https://${name}studio.${tld}`,
        `https://the${name}.${tld}`,
        `https://my${name}.${tld}`,
        `https://${name}.co`,
        `https://${name}.io`,
        `https://${name}.net`,
      ];
      
      for (const variant of variations) {
        const result = await isWixSite(variant);
        if (result) {
          console.log(`   ✅ Found variant: ${variant}`);
          discovered.push(result);
        }
        await new Promise(r => setTimeout(r, CONFIG.DELAY));
      }
      
    } catch (e) {}
    
    if (discovered.length >= 100) break;
  }
  
  console.log(`   📊 Found ${discovered.length} sites from variations`);
  return discovered;
}

// Method 4: Geographic Expansion
async function findGeographicVariants() {
  console.log("\n🗺️ METHOD 4: Searching geographic business names...");
  const discovered = [];
  
  const cities = ['newyork', 'london', 'paris', 'tokyo', 'sydney', 'miami', 'chicago', 'la', 'toronto', 'berlin'];
  const industries = ['beauty', 'salon', 'photo', 'studio', 'fitness', 'yoga', 'cafe', 'restaurant', 'boutique'];
  
  const attempts = [];
  cities.forEach(city => {
    industries.forEach(industry => {
      attempts.push(`https://${city}${industry}.com`);
      attempts.push(`https://${industry}${city}.com`);
      attempts.push(`https://${city}-${industry}.com`);
    });
  });
  
  // Check in batches
  for (let i = 0; i < attempts.length; i += CONFIG.BATCH_SIZE) {
    const batch = attempts.slice(i, i + CONFIG.BATCH_SIZE);
    const results = await Promise.all(
      batch.map(url => isWixSite(url))
    );
    
    results.forEach((result, idx) => {
      if (result) {
        console.log(`   ✅ Found: ${batch[idx]}`);
        discovered.push(result);
      }
    });
    
    await new Promise(r => setTimeout(r, CONFIG.DELAY));
    
    if (discovered.length >= 100) break;
  }
  
  console.log(`   📊 Found ${discovered.length} sites from geographic search`);
  return discovered;
}

// Method 5: Industry-Specific Keywords
async function findIndustryDomains() {
  console.log("\n🏢 METHOD 5: Searching industry-specific domains...");
  const discovered = [];
  
  const industries = [
    // Service industries
    'plumber', 'electrician', 'painter', 'carpenter', 'landscaper',
    // Creative industries
    'photographer', 'designer', 'artist', 'creative', 'agency',
    // Retail
    'boutique', 'shop', 'store', 'fashion', 'jewelry',
    // Health & Wellness
    'spa', 'salon', 'wellness', 'massage', 'yoga',
    // Food
    'bakery', 'cafe', 'catering', 'restaurant', 'food',
  ];
  
  const modifiers = ['best', 'top', 'pro', 'expert', 'premier', 'luxury', 'local'];
  
  const attempts = [];
  industries.forEach(industry => {
    modifiers.forEach(modifier => {
      attempts.push(`https://${modifier}${industry}.com`);
      attempts.push(`https://${industry}${modifier}.com`);
    });
    // Also try standalone
    attempts.push(`https://the${industry}.com`);
    attempts.push(`https://my${industry}.com`);
  });
  
  // Check in batches
  for (let i = 0; i < attempts.length; i += CONFIG.BATCH_SIZE) {
    const batch = attempts.slice(i, i + CONFIG.BATCH_SIZE);
    const results = await Promise.all(
      batch.map(url => isWixSite(url))
    );
    
    results.forEach((result, idx) => {
      if (result) {
        console.log(`   ✅ Found: ${batch[idx]}`);
        discovered.push(result);
      }
    });
    
    await new Promise(r => setTimeout(r, CONFIG.DELAY));
    
    if (discovered.length >= 200) break;
  }
  
  console.log(`   📊 Found ${discovered.length} sites from industry search`);
  return discovered;
}

// ==================== MAIN FUNCTION ====================
(async () => {
  console.log("🚀 IMPROVED WIX DISCOVERY - Multi-Strategy Approach");
  console.log("=".repeat(80));
  console.log(`Target: ${CONFIG.MAX_STORES} stores\n`);

  const db = await mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "sales_spy",
  });

  const allDiscovered = new Set();
  const discoveredData = new Map();

  // Get existing stores for method 3
  const [existingStores] = await db.query(
    "SELECT store_url FROM wix_stores WHERE discovered = 1 LIMIT 50"
  );
  const seedUrls = existingStores.map(s => s.store_url);

  // Run all discovery methods
  console.log("🎯 Running 5 discovery strategies...\n");

  try {
    // Method 1: Random generation
    const random = await generateRandomDomains();
    random.forEach(site => {
      allDiscovered.add(site.url);
      discoveredData.set(site.url, site);
    });

    if (allDiscovered.size >= CONFIG.MAX_STORES) {
      console.log(`\n🎉 Target reached! Found ${allDiscovered.size} stores`);
    } else {
      // Method 3: Similar domains
      const similar = await findSimilarDomains(seedUrls);
      similar.forEach(site => {
        allDiscovered.add(site.url);
        discoveredData.set(site.url, site);
      });
    }

    if (allDiscovered.size >= CONFIG.MAX_STORES) {
      console.log(`\n🎉 Target reached! Found ${allDiscovered.size} stores`);
    } else {
      // Method 4: Geographic
      const geographic = await findGeographicVariants();
      geographic.forEach(site => {
        allDiscovered.add(site.url);
        discoveredData.set(site.url, site);
      });
    }

    if (allDiscovered.size >= CONFIG.MAX_STORES) {
      console.log(`\n🎉 Target reached! Found ${allDiscovered.size} stores`);
    } else {
      // Method 5: Industry-specific
      const industry = await findIndustryDomains();
      industry.forEach(site => {
        allDiscovered.add(site.url);
        discoveredData.set(site.url, site);
      });
    }

  } catch (error) {
    console.log(`\n⚠️ Error during discovery: ${error.message}`);
  }

  // Save to database
  console.log("\n" + "=".repeat(80));
  console.log("💾 Saving to database...");
  console.log("=".repeat(80));

  let added = 0;
  let skipped = 0;

  for (const url of allDiscovered) {
    const data = discoveredData.get(url);
    
    try {
      const [existing] = await db.query(
        "SELECT id FROM wix_stores WHERE store_url = ?",
        [url]
      );

      if (existing.length === 0) {
        await db.query(
          `INSERT INTO wix_stores (store_url, store_name, description, discovered) 
           VALUES (?, ?, ?, 1)`,
          [url, data.title, data.description]
        );
        console.log(`✅ ${url}`);
        added++;
      } else {
        skipped++;
      }
    } catch (err) {
      console.log(`⚠️ DB Error: ${err.message}`);
    }
  }

  console.log("\n" + "=".repeat(80));
  console.log("🏁 DISCOVERY COMPLETE!");
  console.log("=".repeat(80));
  console.log(`✅ New sites added: ${added}`);
  console.log(`⏭️ Already in database: ${skipped}`);
  console.log(`🌐 Total discovered: ${allDiscovered.size}`);

  await db.end();
})();