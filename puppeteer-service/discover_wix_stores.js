const { getJson } = require("serpapi");
const axios = require("axios");
const cheerio = require("cheerio");
const mysql = require("mysql2/promise");
const { URL } = require("url");

// Configuration
const CONFIG = {
  SERP_API_KEY: "83e48694c96c121ee10b2ebfffb0e0ed5e12c65b805652f6d38280c38559a322",
  MAX_STORES: 2000,
  RESULTS_PER_QUERY: 100, // Get more results per query
  ONLY_CUSTOM_DOMAINS: true,
  MAX_RETRIES: 2,
  VERIFICATION_DELAY: 500, // ms between site checks
  SEARCH_DELAY: 2000, // ms between searches
};

// Comprehensive Wix detection
async function detectWixAndGetDomain(url) {
  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
      },
      timeout: 10000,
      maxRedirects: 5,
    });

    const finalUrl = response.request.res.responseUrl || url;
    const html = response.data;
    const $ = cheerio.load(html);

    // Wix detection signatures
    const wixSignatures = {
      meta: $('meta[name="generator"]').attr('content')?.toLowerCase().includes('wix'),
      scripts: $('script[src*="wix"]').length > 0 ||
               $('script[src*="parastorage"]').length > 0 ||
               $('script[src*="wixstatic"]').length > 0,
      links: $('link[href*="wixstatic"]').length > 0,
      html: html.includes('<!-- Wix') || 
            html.includes('wix-code') ||
            html.includes('wixBiSession') ||
            html.includes('WixCodeSDK'),
      attributes: $('[id^="WIX_ADS"]').length > 0 ||
                  $('[data-wix-watermark]').length > 0,
    };

    const isWix = Object.values(wixSignatures).some(v => v === true);
    const isCustomDomain = !finalUrl.includes('wixsite.com');

    return {
      isWix,
      url: finalUrl,
      isCustomDomain,
      signatures: wixSignatures,
    };
  } catch (error) {
    return null;
  }
}

// MASSIVE EXPANDED SEARCH STRATEGIES
const SEARCH_STRATEGIES = {
  // Strategy 1: Direct Wix branding searches
  branding: [
    '"powered by wix"',
    '"built with wix"',
    '"created with wix"',
    '"made on wix"',
    '"wix.com" -site:wix.com -site:wixsite.com',
    '"design by wix"',
    '"website by wix"',
  ],

  // Strategy 2: Industries (EXPANDED)
  industries: [
    'restaurant menu "wix"',
    'photography portfolio "wix"',
    'wedding photography "wix"',
    'real estate "wix"',
    'boutique shop "wix"',
    'fashion store "wix"',
    'jewelry store "wix"',
    'beauty salon "wix"',
    'hair salon "wix"',
    'nail salon "wix"',
    'spa wellness "wix"',
    'fitness gym "wix"',
    'yoga studio "wix"',
    'personal trainer "wix"',
    'consulting services "wix"',
    'marketing agency "wix"',
    'design agency "wix"',
    'law firm "wix"',
    'accounting services "wix"',
    'dental clinic "wix"',
    'medical practice "wix"',
    'veterinary clinic "wix"',
    'pet grooming "wix"',
    'bakery cafe "wix"',
    'coffee shop "wix"',
    'food truck "wix"',
    'catering services "wix"',
    'event planning "wix"',
    'florist flowers "wix"',
    'interior design "wix"',
    'home decor "wix"',
    'furniture store "wix"',
    'art gallery "wix"',
    'music studio "wix"',
    'dance studio "wix"',
    'tutoring education "wix"',
    'daycare childcare "wix"',
    'plumbing services "wix"',
    'electrical services "wix"',
    'cleaning services "wix"',
    'landscaping services "wix"',
    'construction company "wix"',
    'auto repair "wix"',
    'car wash "wix"',
    'tattoo studio "wix"',
    'barber shop "wix"',
  ],

  // Strategy 3: Location-based (targeting major cities)
  locations: [
    '"new york" business "wix" -site:wix.com',
    '"los angeles" business "wix" -site:wix.com',
    '"chicago" business "wix" -site:wix.com',
    '"houston" business "wix" -site:wix.com',
    '"miami" business "wix" -site:wix.com',
    '"london" business "wix" -site:wix.com',
    '"toronto" business "wix" -site:wix.com',
    '"sydney" business "wix" -site:wix.com',
    '"melbourne" business "wix" -site:wix.com',
    '"lagos" business "wix" -site:wix.com',
  ],

  // Strategy 4: Wix Features
  features: [
    '"wix bookings" -site:wix.com -site:wixsite.com',
    '"wix stores" -site:wix.com -site:wixsite.com',
    '"wix blog" -site:wix.com -site:wixsite.com',
    '"wix events" -site:wix.com -site:wixsite.com',
    '"wix restaurants" -site:wix.com -site:wixsite.com',
    '"book online" "wix" -site:wix.com',
    '"schedule appointment" "wix" -site:wix.com',
    '"order online" "wix" -site:wix.com',
  ],

  // Strategy 5: Common Wix Elements
  elements: [
    'intext:"wix website builder" -site:wix.com -site:wixsite.com',
    'inurl:contact "wix" -site:wix.com',
    'inurl:about "wix" -site:wix.com',
    'inurl:services "wix" -site:wix.com',
    'inurl:portfolio "wix" -site:wix.com',
    'inurl:shop "wix" -site:wix.com',
    'inurl:store "wix" -site:wix.com',
  ],

  // Strategy 6: Social proof
  social: [
    '"follow us" "wix" -site:wix.com -site:wixsite.com',
    '"contact us" "wix" -site:wix.com -site:wixsite.com',
    'intext:"© 2024" intext:"wix" -site:wix.com',
    'intext:"© 2023" intext:"wix" -site:wix.com',
    'intext:"all rights reserved" intext:"wix" -site:wix.com',
  ],

  // Strategy 7: TLD-specific
  tlds: [
    'site:.com "wix" -site:wix.com -site:wixsite.com',
    'site:.net "wix" -site:wix.com -site:wixsite.com',
    'site:.co "wix" -site:wix.com -site:wixsite.com',
    'site:.io "wix" -site:wix.com -site:wixsite.com',
    'site:.org "wix" -site:wix.com -site:wixsite.com',
    'site:.us "wix" -site:wix.com -site:wixsite.com',
    'site:.uk "wix" -site:wix.com -site:wixsite.com',
    'site:.ca "wix" -site:wix.com -site:wixsite.com',
    'site:.au "wix" -site:wix.com -site:wixsite.com',
  ],

  // Strategy 8: E-commerce specific
  ecommerce: [
    '"shop now" "wix" -site:wix.com',
    '"add to cart" "wix" -site:wix.com',
    '"buy online" "wix" -site:wix.com',
    '"online store" "wix" -site:wix.com',
    '"free shipping" "wix" -site:wix.com',
  ],

  // Strategy 9: Service-based
  services: [
    '"book now" "wix" -site:wix.com',
    '"get a quote" "wix" -site:wix.com',
    '"schedule consultation" "wix" -site:wix.com',
    '"request appointment" "wix" -site:wix.com',
  ],

  // Strategy 10: Technology footprints
  technical: [
    'intext:"static.wixstatic.com" -site:wix.com',
    'intext:"parastorage" -site:wix.com',
    'intext:"wix-incapsula" -site:wix.com',
  ],
};

// Get all search queries
function getAllSearchQueries() {
  const allQueries = [];
  
  for (const [strategy, queries] of Object.entries(SEARCH_STRATEGIES)) {
    allQueries.push(...queries.map(q => ({ query: q, strategy })));
  }
  
  // Shuffle to get diverse results
  return allQueries.sort(() => Math.random() - 0.5);
}

// Main discovery function
(async () => {
  console.log("🌍 MASSIVE Wix Site Discovery Engine");
  console.log("=".repeat(80));
  console.log(`📋 Configuration:`);
  console.log(`   - Target: ${CONFIG.MAX_STORES} stores`);
  console.log(`   - Results per query: ${CONFIG.RESULTS_PER_QUERY}`);
  console.log(`   - Only custom domains: ${CONFIG.ONLY_CUSTOM_DOMAINS}`);
  console.log("");

  const db = await mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "sales_spy",
  });

  const discoveredSites = new Map(); // domain -> data
  const failedChecks = new Set(); // domains that failed verification
  const allQueries = getAllSearchQueries();
  
  console.log(`🔍 Total search strategies: ${Object.keys(SEARCH_STRATEGIES).length}`);
  console.log(`🔍 Total search queries: ${allQueries.length}`);
  console.log(`🎯 Estimated potential: ${allQueries.length * CONFIG.RESULTS_PER_QUERY} results\n`);

  let queryCount = 0;
  let totalResultsChecked = 0;

  for (const { query, strategy } of allQueries) {
    // Stop if we've reached our target
    if (discoveredSites.size >= CONFIG.MAX_STORES) {
      console.log(`\n🎉 TARGET REACHED! Found ${discoveredSites.size} stores!`);
      break;
    }

    queryCount++;
    console.log(`\n[${ queryCount}/${allQueries.length}] 🔎 Strategy: ${strategy}`);
    console.log(`Query: ${query}`);
    console.log(`Progress: ${discoveredSites.size}/${CONFIG.MAX_STORES} stores found`);

    try {
      const json = await getJson({
        engine: "google",
        q: query,
        api_key: CONFIG.SERP_API_KEY,
        num: CONFIG.RESULTS_PER_QUERY,
      });

      if (json.organic_results && json.organic_results.length > 0) {
        console.log(`   📄 Found ${json.organic_results.length} search results`);
        
        for (const result of json.organic_results) {
          const link = result.link;
          totalResultsChecked++;
          
          // Skip if already processed or failed
          if (discoveredSites.has(link) || failedChecks.has(link)) continue;
          
          // Skip Wix official sites and wixsite.com
          if (link.includes('wix.com') || link.includes('wixsite.com')) {
            continue;
          }

          // Extract domain
          try {
            const domain = new URL(link).origin;
            
            // Skip if already checked this domain
            if (discoveredSites.has(domain) || failedChecks.has(domain)) {
              continue;
            }
            
            // Check if it's a Wix site
            console.log(`   🔍 [${totalResultsChecked}] Checking: ${domain}`);
            const detection = await detectWixAndGetDomain(domain);
            
            if (detection && detection.isWix && detection.isCustomDomain) {
              discoveredSites.set(domain, {
                url: domain,
                strategy,
                query,
                title: result.title,
                snippet: result.snippet,
              });
              
              console.log(`   ✅ WIX FOUND! ${domain} (${discoveredSites.size}/${CONFIG.MAX_STORES})`);
            } else if (detection && detection.isWix) {
              console.log(`   ⏭️ Wix but not custom domain: ${domain}`);
              failedChecks.add(domain);
            } else {
              console.log(`   ❌ Not Wix: ${domain}`);
              failedChecks.add(domain);
            }
            
            // Small delay to avoid hammering sites
            await new Promise(resolve => setTimeout(resolve, CONFIG.VERIFICATION_DELAY));
            
          } catch (urlError) {
            console.log(`   ⚠️ Invalid URL: ${link}`);
          }
          
          // Stop if we have enough
          if (discoveredSites.size >= CONFIG.MAX_STORES) {
            console.log(`\n🎉 Reached target of ${CONFIG.MAX_STORES} sites!`);
            break;
          }
        }
      } else {
        console.log(`   ⚠️ No results for this query`);
      }
      
      // Rate limiting for SerpAPI
      await new Promise(resolve => setTimeout(resolve, CONFIG.SEARCH_DELAY));
      
    } catch (err) {
      console.log(`   ❌ Search error: ${err.message}`);
      if (err.message.includes('rate limit')) {
        console.log(`   ⏸️ Rate limited, waiting 10 seconds...`);
        await new Promise(resolve => setTimeout(resolve, 10000));
      }
    }
  }

  // Save to database
  console.log("\n" + "=".repeat(80));
  console.log("💾 Saving to database...");
  console.log("=".repeat(80));

  let addedCount = 0;
  let skippedCount = 0;

  for (const [domain, data] of discoveredSites) {
    try {
      const [existing] = await db.query(
        "SELECT id FROM wix_stores WHERE store_url = ?",
        [data.url]
      );

      if (existing.length === 0) {
        await db.query(
          `INSERT INTO wix_stores (store_url, store_name, description, discovered) 
           VALUES (?, ?, ?, 1)`,
          [data.url, data.title || null, data.snippet || null]
        );
        
        console.log(`✅ Added: ${data.url}`);
        addedCount++;
      } else {
        console.log(`⏭️ Exists: ${data.url}`);
        skippedCount++;
      }
    } catch (err) {
      console.log(`⚠️ DB Error for ${data.url}: ${err.message}`);
    }
  }

  // Statistics by strategy
  console.log("\n" + "=".repeat(80));
  console.log("📊 Results by Strategy");
  console.log("=".repeat(80));

  const strategyStats = {};
  for (const [domain, data] of discoveredSites) {
    strategyStats[data.strategy] = (strategyStats[data.strategy] || 0) + 1;
  }

  // Sort by count
  const sortedStats = Object.entries(strategyStats).sort((a, b) => b[1] - a[1]);
  
  for (const [strategy, count] of sortedStats) {
    const percentage = ((count / discoveredSites.size) * 100).toFixed(1);
    console.log(`${strategy.padEnd(20)} : ${count.toString().padStart(4)} sites (${percentage}%)`);
  }

  console.log("\n" + "=".repeat(80));
  console.log("🏁 DISCOVERY COMPLETE!");
  console.log("=".repeat(80));
  console.log(`✅ New sites added: ${addedCount}`);
  console.log(`⏭️ Duplicates skipped: ${skippedCount}`);
  console.log(`🌐 Total custom domains found: ${discoveredSites.size}`);
  console.log(`🔍 Total sites checked: ${totalResultsChecked}`);
  console.log(`❌ Failed verifications: ${failedChecks.size}`);
  console.log(`📊 Conversion rate: ${((discoveredSites.size / totalResultsChecked) * 100).toFixed(2)}%`);
  console.log(`💰 Queries used: ${queryCount}/${allQueries.length}`);

  await db.end();
})();