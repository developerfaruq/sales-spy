const axios = require("axios");
const cheerio = require("cheerio");
const mysql = require("mysql2/promise");

// ==================== CONFIGURATION ====================
const CONFIG = {
  MAX_RETRIES: 3,
  RETRY_DELAY: 5000, // 5 seconds
  REQUEST_TIMEOUT: 30000, // 30 seconds
  RATE_LIMIT_DELAY: 3000, // 3 seconds between requests
  BATCH_SIZE: 2000, // Number of stores to process
};

// ==================== HELPER FUNCTIONS ====================

// Language detection from meta tags and content
function detectLanguage(html, $) {
  const htmlLang = $('html').attr('lang');
  if (htmlLang) {
    return htmlLang.substring(0, 2).toLowerCase();
  }
  
  const metaLang = $('meta[http-equiv="content-language"]').attr('content') ||
                   $('meta[name="language"]').attr('content');
  if (metaLang) {
    return metaLang.substring(0, 2).toLowerCase();
  }
  
  const ogLocale = $('meta[property="og:locale"]').attr('content');
  if (ogLocale) {
    return ogLocale.substring(0, 2).toLowerCase();
  }
  
  const bodyText = $('body').text().toLowerCase();
  const languagePatterns = {
    'en': /\b(the|and|for|you|with|this|that|from|have|more|will|your)\b/g,
    'es': /\b(el|la|de|que|en|los|del|por|para|una|con)\b/g,
    'fr': /\b(le|la|de|et|les|des|un|une|dans|pour|que)\b/g,
    'de': /\b(der|die|und|in|den|von|zu|das|mit|sich|des)\b/g,
    'it': /\b(il|di|e|la|per|in|un|da|che|con|non)\b/g,
    'pt': /\b(o|de|e|a|em|os|do|da|para|com|um)\b/g,
  };
  
  let maxMatches = 0;
  let detectedLang = 'en';
  
  for (const [lang, pattern] of Object.entries(languagePatterns)) {
    const matches = (bodyText.match(pattern) || []).length;
    if (matches > maxMatches) {
      maxMatches = matches;
      detectedLang = lang;
    }
  }
  
  return detectedLang;
}

// Extract owner/business name from various sources
function extractOwner(html, $) {
  const jsonLdScripts = $('script[type="application/ld+json"]').toArray();
  for (const script of jsonLdScripts) {
    try {
      const content = $(script).html();
      if (content) {
        const data = JSON.parse(content);
        if (data.name && typeof data.name === 'string') {
          return data.name.substring(0, 255);
        }
        if (data.author?.name) {
          return data.author.name.substring(0, 255);
        }
        if (data.publisher?.name) {
          return data.publisher.name.substring(0, 255);
        }
      }
    } catch (e) {
      // Invalid JSON, skip
    }
  }
  
  const author = $('meta[name="author"]').attr('content');
  if (author) {
    return author.substring(0, 255);
  }
  
  const copyrightText = $('footer, [class*="footer"], [class*="copyright"]').text();
  const copyrightMatch = copyrightText.match(/©\s*(?:\d{4}\s+)?([A-Za-z\s&.,']+?)(?:\.|All|Rights|\d{4}|$)/i);
  if (copyrightMatch) {
    return copyrightMatch[1].trim().substring(0, 255);
  }
  
  return null;
}

// Extract traffic estimate
function extractTrafficEstimate(html, $) {
  const socialText = $('body').text();
  const followerMatches = socialText.match(/(\d{1,3}(?:,\d{3})*|\d+)\s*(?:followers|fans|subscribers)/gi);
  
  if (followerMatches && followerMatches.length > 0) {
    const numbers = followerMatches.map(m => {
      const num = m.match(/\d{1,3}(?:,\d{3})*|\d+/)[0].replace(/,/g, '');
      return parseInt(num);
    });
    
    const maxFollowers = Math.max(...numbers);
    const estimatedTraffic = Math.floor(maxFollowers * 0.1);
    
    if (estimatedTraffic > 1000) {
      return `~${(estimatedTraffic / 1000).toFixed(0)}k/mo`;
    } else if (estimatedTraffic > 0) {
      return `~${estimatedTraffic}/mo`;
    }
  }
  
  return null;
}

// Extract WhatsApp from links and text
function extractWhatsApp(html, $) {
  const whatsappLink = $('a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="api.whatsapp"]').first().attr('href');
  
  if (whatsappLink) {
    const numberMatch = whatsappLink.match(/(?:wa\.me\/|phone=)(\+?\d{10,15})/);
    if (numberMatch) {
      return numberMatch[1];
    }
  }
  
  const bodyText = $('body').text();
  const whatsappPatterns = [
    /whatsapp[\s:]*(\+?\d{1,4}[\s.-]?\d{2,4}[\s.-]?\d{3,4}[\s.-]?\d{3,4})/gi,
    /wa[\s:]*(me\/)?(\+?\d{10,15})/gi,
  ];
  
  for (const pattern of whatsappPatterns) {
    const matches = [...bodyText.matchAll(pattern)];
    for (const match of matches) {
      const number = (match[2] || match[1]).replace(/[^\d+]/g, '');
      if (number.length >= 10) {
        return number.startsWith('+') ? number : '+' + number;
      }
    }
  }
  
  return null;
}

// Generate relevant tags based on content
function generateTags(html, $, storeName, description) {
  const tags = new Set();
  
  const categoryKeywords = {
    'Fashion': /fashion|clothing|apparel|dress|wear|style|boutique/gi,
    'Jewelry': /jewelry|jewellery|necklace|ring|bracelet|earring/gi,
    'Beauty': /beauty|cosmetic|makeup|skincare|spa|salon/gi,
    'Food': /food|restaurant|cafe|bakery|catering|cuisine/gi,
    'Tech': /technology|software|app|digital|tech|computer/gi,
    'Art': /art|gallery|artist|design|creative|handmade/gi,
    'Sports': /sport|fitness|gym|athletic|exercise|workout/gi,
    'Home': /home|furniture|decor|interior|house/gi,
    'Kids': /kids|children|baby|toy|parenting/gi,
    'Pet': /pet|dog|cat|animal|veterinary/gi,
    'Travel': /travel|tour|hotel|vacation|trip/gi,
    'Education': /education|course|learning|school|training/gi,
    'Health': /health|medical|wellness|clinic|doctor/gi,
    'Music': /music|band|concert|audio|sound/gi,
    'Photography': /photo|photography|camera|picture/gi,
  };
  
  const fullText = (storeName + ' ' + description + ' ' + $('body').text()).toLowerCase();
  
  for (const [category, pattern] of Object.entries(categoryKeywords)) {
    if (pattern.test(fullText)) {
      tags.add(category);
    }
  }
  
  return Array.from(tags).slice(0, 5).join(',');
}

// Validate phone numbers
function isValidPhone(phone) {
  const cleaned = phone.replace(/[^\d]/g, '');
  return cleaned.length >= 10 && cleaned.length <= 15;
}

// Extract phone from text with improved patterns
function extractPhone(text) {
  const patterns = [
    /\+\d{1,4}[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,4}[\s.-]?\d{0,9}/g,
    /(?:tel|phone|call|contact|mobile)[\s:]*(\+?\d{1,4}[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,4}[\s.-]?\d{0,9})/gi,
    /\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}/g,
  ];

  for (const pattern of patterns) {
    const matches = [...text.matchAll(pattern)];
    for (const match of matches) {
      let phone = match[1] || match[0];
      phone = phone.trim();
      
      if (isValidPhone(phone)) {
        const digits = phone.replace(/[^\d+]/g, '');
        if (digits.startsWith('+')) {
          return digits;
        } else if (digits.length >= 10) {
          return '+' + digits;
        }
      }
    }
  }
  
  return null;
}

// Enhanced product data extraction
function extractProductData(html, $) {
  let products = [];
  
  try {
    const scripts = $('script').toArray();
    
    for (const script of scripts) {
      const content = $(script).html();
      if (!content) continue;
      
      if (content.includes('"products"') || content.includes('"catalog"')) {
        try {
          const priceMatches = [...content.matchAll(/"price":\s*(\d+\.?\d*)/g)];
          if (priceMatches.length > 0) {
            products = priceMatches.map(m => parseFloat(m[1]));
          }
          
          const formattedMatches = [...content.matchAll(/"(?:formattedPrice|displayPrice)":\s*"[^\d]*?([\d,.]+)"/g)];
          if (formattedMatches.length > products.length) {
            products = formattedMatches.map(m => parseFloat(m[1].replace(/,/g, '')));
          }
          
          if (products.length > 0) break;
        } catch (e) {
          // Continue to next method
        }
      }
    }
    
    if (products.length === 0) {
      const priceSelectors = [
        '[data-hook="product-item-price"]',
        '[data-hook="formatted-primary-price"]',
        '.product-price',
        '[class*="ProductPrice"]',
        '[class*="price"]',
        '[data-price]',
        '.price'
      ];
      
      const productPrices = [];
      
      priceSelectors.forEach(selector => {
        $(selector).each((i, elem) => {
          const priceText = $(elem).text() || $(elem).attr('data-price') || '';
          const cleaned = priceText.replace(/[^0-9.,]/g, '');
          const priceMatch = cleaned.match(/[\d,]+\.?\d*/);
          
          if (priceMatch) {
            const price = parseFloat(priceMatch[0].replace(/,/g, ''));
            if (price > 0 && price < 1000000) {
              productPrices.push(price);
            }
          }
        });
      });
      
      if (productPrices.length > 0) {
        products = productPrices;
      }
    }
    
    if (products.length === 0) {
      const productLinks = $('a[href*="/product-page/"], a[href*="/products/"], [data-hook="product-item"]').length;
      return { count: productLinks, avgPrice: null };
    }
    
  } catch (e) {
    console.log(`   ⚠️ Error extracting products: ${e.message}`);
  }
  
  if (products.length > 0) {
    const validPrices = products.filter(p => p > 0 && p < 1000000);
    const avgPrice = validPrices.length > 0 
      ? (validPrices.reduce((a, b) => a + b, 0) / validPrices.length).toFixed(2)
      : null;
    
    return { count: validPrices.length, avgPrice };
  }
  
  return { count: 0, avgPrice: null };
}

// Enhanced email extraction with filtering
function extractEmail(html) {
  const emailMatches = [...html.matchAll(/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,7}\b/g)];
  
  const excludePatterns = [
    'sentry', 'example.com', 'wix.com', 'schema.org', 'localhost',
    'test.com', 'domain.com', 'email.com', 'placeholder'
  ];
  
  for (const match of emailMatches) {
    const email = match[0].toLowerCase();
    
    if (!excludePatterns.some(pattern => email.includes(pattern))) {
      return email;
    }
  }
  
  return null;
}

// Retry wrapper for network requests
async function retryRequest(url, maxRetries = CONFIG.MAX_RETRIES) {
  let lastError;
  
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      console.log(`   🔄 Attempt ${attempt}/${maxRetries}`);
      
      const { data: html } = await axios.get(url, {
        headers: {
          "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
          "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
          "Accept-Language": "en-US,en;q=0.9",
          "Accept-Encoding": "gzip, deflate, br",
          "Connection": "keep-alive",
          "Upgrade-Insecure-Requests": "1",
        },
        timeout: CONFIG.REQUEST_TIMEOUT,
        maxRedirects: 5,
        validateStatus: (status) => status < 500,
      });
      
      return html;
      
    } catch (error) {
      lastError = error;
      console.log(`   ⚠️ Attempt ${attempt} failed: ${error.message}`);
      
      if (attempt < maxRetries) {
        const delay = CONFIG.RETRY_DELAY * attempt;
        console.log(`   ⏳ Waiting ${delay/1000}s before retry...`);
        await new Promise(resolve => setTimeout(resolve, delay));
      }
    }
  }
  
  throw lastError;
}

// ==================== MAIN SCRAPING FUNCTION ====================
(async () => {
  console.log("🕵️‍♂️ Starting Enhanced Wix Site Scraper...\n");
  console.log("📋 Configuration:");
  console.log(`   - Max retries: ${CONFIG.MAX_RETRIES}`);
  console.log(`   - Request timeout: ${CONFIG.REQUEST_TIMEOUT/1000}s`);
  console.log(`   - Rate limit: ${CONFIG.RATE_LIMIT_DELAY/1000}s between requests`);
  console.log(`   - Batch size: ${CONFIG.BATCH_SIZE}\n`);

  let db;
  let successCount = 0;
  let failCount = 0;
  
  try {
    db = await mysql.createConnection({
      host: "localhost",
      user: "root",
      password: "",
      database: "sales_spy",
    });

    // DIAGNOSTIC QUERIES - Show what's in the database
    const [totalStores] = await db.query(`SELECT COUNT(*) as total FROM wix_stores`);
    console.log(`📊 Total stores in database: ${totalStores[0].total}`);

    const [discoveredStores] = await db.query(`SELECT COUNT(*) as total FROM wix_stores WHERE discovered = 1`);
    console.log(`📊 Stores with discovered=1: ${discoveredStores[0].total}`);

    const [emptyNameStores] = await db.query(`SELECT COUNT(*) as total FROM wix_stores WHERE (store_name IS NULL OR store_name = '')`);
    console.log(`📊 Stores with empty names: ${emptyNameStores[0].total}`);

    const [scrapedStores] = await db.query(`SELECT COUNT(*) as total FROM wix_stores WHERE scraped = 1`);
    console.log(`📊 Already scraped stores: ${scrapedStores[0].total}\n`);

    // UPDATED QUERY - Scrape all unscraped stores (even if they have names)
    const [stores] = await db.query(
      `SELECT id, store_url FROM wix_stores 
       WHERE (scraped IS NULL OR scraped = 0)
       LIMIT ?`,
      [CONFIG.BATCH_SIZE]
    );

    console.log(`📊 Found ${stores.length} stores to scrape\n`);

    if (stores.length === 0) {
      console.log("ℹ️ No stores found to scrape. All stores may already be scraped or have names.");
      return;
    }

    for (let i = 0; i < stores.length; i++) {
      const store = stores[i];
      const url = store.store_url;
      
      console.log(`\n${'='.repeat(80)}`);
      console.log(`[${i + 1}/${stores.length}] 🔍 Scraping: ${url}`);
      console.log('='.repeat(80));

      try {
        const html = await retryRequest(url);
        const $ = cheerio.load(html);
        
        const bodyText = $('body').text();

        // 🏷️ Store name
        let store_name = $("title").first().text().trim();
        if (!store_name || store_name.length < 2) {
          store_name = $("meta[property='og:site_name']").attr("content") || 
                       $("h1").first().text().trim() ||
                       "Unknown Store";
        }
        store_name = store_name.substring(0, 255);

        // 📝 Description
        const description = (
          $("meta[name='description']").attr("content") ||
          $("meta[property='og:description']").attr("content") ||
          $("p").first().text().trim() ||
          ""
        ).substring(0, 1000);

        // 🌐 Language
        const language = detectLanguage(html, $);

        // 👤 Owner
        const owner = extractOwner(html, $);

        // 📧 Email
        const email = extractEmail(html);

        // 📞 Phone
        const phone = extractPhone(bodyText);

        // 💬 WhatsApp
        const whatsapp = extractWhatsApp(html, $);

        // 📊 Traffic estimate
        const traffic = extractTrafficEstimate(html, $);

        // 🏷️ Tags
        const tags = generateTags(html, $, store_name, description);

        // 💱 Currency detection
        let currency = "USD";
        const currencyMeta = $("meta[property='og:price:currency']").attr("content");
        
        if (currencyMeta) {
          currency = currencyMeta.toUpperCase();
        } else {
          const jsonLdScripts = $('script[type="application/ld+json"]').toArray();
          for (const script of jsonLdScripts) {
            try {
              const content = $(script).html();
              if (content) {
                const data = JSON.parse(content);
                if (data.priceCurrency) {
                  currency = data.priceCurrency;
                  break;
                }
              }
            } catch (e) {}
          }
          
          if (currency === "USD") {
            const currencyMatch = html.match(/"currency":\s*"([A-Z]{3})"/);
            if (currencyMatch) {
              currency = currencyMatch[1];
            }
          }
        }

        // 🌍 Country mapping
        const countryMap = {
          "USD": "United States", "EUR": "Europe", "GBP": "United Kingdom",
          "CAD": "Canada", "AUD": "Australia", "NGN": "Nigeria",
          "ZAR": "South Africa", "JPY": "Japan", "CNY": "China",
          "INR": "India", "BRL": "Brazil", "MXN": "Mexico",
          "RUB": "Russia", "KRW": "South Korea", "TWD": "Taiwan",
          "MYR": "Malaysia", "SGD": "Singapore", "THB": "Thailand",
          "IDR": "Indonesia", "PHP": "Philippines", "VND": "Vietnam",
          "CZK": "Czech Republic", "PLN": "Poland", "HUF": "Hungary",
          "DKK": "Denmark", "SEK": "Sweden", "NOK": "Norway",
          "CHF": "Switzerland", "NZD": "New Zealand", "AED": "UAE"
        };
        const country = countryMap[currency] || null;

        // 🎨 Theme
        const theme_name = "Wix Template";

        // 🌐 Social links
        const social_facebook = $("a[href*='facebook.com']").first().attr("href") || null;
        const social_instagram = $("a[href*='instagram.com']").first().attr("href") || null;
        const social_twitter = $("a[href*='twitter.com'], a[href*='x.com']").first().attr("href") || null;
        const social_tiktok = $("a[href*='tiktok.com']").first().attr("href") || null;
        const youtube = $("a[href*='youtube.com']").first().attr("href") || null;

        // 🛍️ Products
        const productData = extractProductData(html, $);
        const total_products = productData.count;
        const avg_product_price = productData.avgPrice;

        // 💾 Update database
        await db.query(
          `UPDATE wix_stores 
           SET store_name=?, description=?, email=?, phone=?, currency=?, country=?, 
               theme_name=?, social_facebook=?, social_instagram=?, social_twitter=?, 
               social_tiktok=?, youtube=?, total_products=?, avg_product_price=?,
               language=?, owner=?, traffic=?, whatsapp=?, tags=?,
               scraped=1, discovered=1, last_scraped=NOW()
           WHERE id=?`,
          [
            store_name, description, email, phone, currency, country,
            theme_name, social_facebook, social_instagram, social_twitter,
            social_tiktok, youtube, total_products, avg_product_price,
            language, owner, traffic, whatsapp, tags,
            store.id
          ]
        );

        successCount++;

        // Display results
        console.log(`   ✅ ${store_name}`);
        console.log(`   🌐 Language: ${language} | Country: ${country || 'Unknown'}`);
        console.log(`   👤 Owner: ${owner || 'Not found'}`);
        console.log(`   📧 Email: ${email || 'Not found'}`);
        console.log(`   📞 Phone: ${phone || 'Not found'}`);
        console.log(`   💬 WhatsApp: ${whatsapp || 'Not found'}`);
        console.log(`   📊 Traffic: ${traffic || 'Not estimated'}`);
        console.log(`   🏷️ Tags: ${tags || 'None'}`);
        console.log(`   💰 ${currency} | 🛍️ ${total_products} products | Avg: ${avg_product_price || 'N/A'}`);

      } catch (err) {
        failCount++;
        console.log(`   ❌ Failed after ${CONFIG.MAX_RETRIES} attempts: ${err.message}`);
        
        try {
          await db.query(
            `UPDATE wix_stores SET last_scraped=NOW() WHERE id=?`,
            [store.id]
          );
        } catch (dbErr) {
          console.log(`   ⚠️ Could not update failed record: ${dbErr.message}`);
        }
      }

      // Rate limiting
      if (i < stores.length - 1) {
        const delay = CONFIG.RATE_LIMIT_DELAY;
        console.log(`   ⏳ Waiting ${delay/1000}s...`);
        await new Promise(resolve => setTimeout(resolve, delay));
      }
    }

  } catch (err) {
    console.error("\n❌ Fatal error:", err.message);
    console.error(err.stack);
  } finally {
    if (db) await db.end();
    
    console.log("\n" + "=".repeat(80));
    console.log("🏁 Scraping Complete!");
    console.log("=".repeat(80));
    console.log(`✅ Successful: ${successCount}`);
    console.log(`❌ Failed: ${failCount}`);
    if (successCount + failCount > 0) {
      console.log(`📊 Success rate: ${((successCount/(successCount+failCount))*100).toFixed(1)}%`);
    }
  }
})();