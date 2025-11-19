const express = require("express");
const puppeteer = require("puppeteer");
const bodyParser = require("body-parser");
const cors = require("cors");
const mysql = require("mysql2/promise");

const app = express();
app.use(cors());
app.use(bodyParser.json());

// ================================
//  DATABASE CONNECTION
// ================================
let db;
(async () => {
  db = await mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "sales_spy",
  });
  console.log("✅ Connected to MySQL Database");
})();

// ================================
//  SCRAPER ROUTE
// ================================
app.post("/scrape", async (req, res) => {
  const { storeUrl } = req.body;
  if (!storeUrl) return res.status(400).send("Missing storeUrl");

  res.setHeader("Content-Type", "text/plain; charset=utf-8");
  const log = (msg) => {
    res.write(msg + "\n");
    console.log(msg);
  };

  log(`🛒 Scraping: ${storeUrl}`);

  try {
    const browser = await puppeteer.launch({
      headless: true,
      args: ["--no-sandbox", "--disable-setuid-sandbox"],
    });
    const page = await browser.newPage();

    // Step 1️⃣ Visit store homepage
    try {
      await page.goto(storeUrl, { waitUntil: "domcontentloaded", timeout: 60000 });
      log(`✅ Connected to ${storeUrl}`);
    } catch (e) {
      log(`❌ Failed to open ${storeUrl}`);
      await browser.close();
      res.end();
      return;
    }

    // Step 2️⃣ Extract Shopify data
    let storeData = {};
    try {
      storeData = await page.evaluate(() => {
        const s = window.Shopify || {};
        return {
          shop: s.shop || document.location.hostname,
          currency: s.currency?.active || "",
          country: s.country || "",
          theme: s.theme?.name || "",
          email: s.email || "",
        };
      });
      log(`🏪 Store Data: ${JSON.stringify(storeData)}`);
    } catch {
      log("⚠️ Could not extract Shopify metadata");
    }

    // Step 3️⃣ Extract contact info (email, phone, socials)
 const contactInfo = await page.evaluate(() => {
  const html = document.body.innerHTML;
  const text = document.body.innerText;
  const anchors = Array.from(document.querySelectorAll("a")).map(a => a.href).join(" ");

  const extract = (regex, source) => {
    const match = source.match(regex);
    return match ? match[0] : "";
  };

  const email = extract(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i, text);
  let phone = extract(/(\+?\d[\d\s().-]{8,}\d)/i, text); // at least 8 digits
  if (!phone) {
    phone = anchors.match(/tel:([+0-9\s().-]+)/i)?.[1] || "";
  }

  const instagram = anchors.match(/instagram\.com\/[A-Za-z0-9_.-]+/i)?.[0] || "";
  const facebook = anchors.match(/facebook\.com\/[A-Za-z0-9_.-]+/i)?.[0] || "";
  const tiktok = anchors.match(/tiktok\.com\/@[A-Za-z0-9_.-]+/i)?.[0] || "";
  const twitter = anchors.match(/twitter\.com\/[A-Za-z0-9_.-]+/i)?.[0] || "";

  return { email, phone, instagram, facebook, tiktok, twitter };
});

    log(`📞 Contact Info: ${JSON.stringify(contactInfo)}`);

    // Step 4️⃣ Save store metadata
    await db.execute(
      `UPDATE shopify_stores
       SET country = ?, currency = ?, contact_email = ?, phone = ?, instagram = ?, facebook = ?, tiktok = ?, twitter = ?, theme_name = ?, last_scraped = NOW()
       WHERE store_url = ?`,
      [
        storeData.country,
        storeData.currency,
        contactInfo.email || storeData.email,
        contactInfo.phone,
        contactInfo.instagram,
        contactInfo.facebook,
        contactInfo.tiktok,
        contactInfo.twitter,
        storeData.theme,
        storeUrl,
      ]
    );

    // Step 5️⃣ Attempt to load /products.json
    let products = [];
    try {
      const jsonUrl = storeUrl.replace(/\/$/, "") + "/products.json?limit=250";
      log(`📦 Fetching: ${jsonUrl}`);

      const response = await page.goto(jsonUrl, { timeout: 30000 });
      const json = await response.json();

      if (json && json.products && json.products.length) {
        products = json.products;
        log(`✅ Found ${products.length} products`);
      } else {
        log(`⚠️ No products found on ${storeUrl}`);
      }
    } catch (err) {
      log(`❌ Failed to fetch /products.json — skipping.`);
    }

    // Step 6️⃣ Save each product
    let saved = 0;
    for (const p of products) {
      const handle = p.handle || null;
      const name = p.title || "Untitled";
      const desc = p.body_html || "";
      const vendor = p.vendor || "";
      const productType = p.product_type || "";
      const tags = Array.isArray(p.tags) ? p.tags.join(",") : p.tags || "";
      const price = p.variants?.[0]?.price || 0;
      const image = p.images?.[0]?.src || "";
      const createdAt = p.created_at ? new Date(p.created_at) : null;
      const updatedAt = p.updated_at ? new Date(p.updated_at) : null;
      const variants = JSON.stringify(p.variants || []);
      const rawJson = JSON.stringify(p);

      try {
        await db.execute(
          `INSERT INTO shopify_products 
           (store_url, product_name, handle, description, vendor, product_type, tags, price, image_url, created_at, updated_at, variants, raw_json)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
           ON DUPLICATE KEY UPDATE 
             updated_at = VALUES(updated_at),
             price = VALUES(price),
             image_url = VALUES(image_url),
             raw_json = VALUES(raw_json)`,
          [storeUrl, name, handle, desc, vendor, productType, tags, price, image, createdAt, updatedAt, variants, rawJson]
        );
        saved++;
      } catch {
        log(`⚠️ Skip duplicate or failed insert for ${handle}`);
      }
    }

    // Step 7️⃣ Update store stats
    await db.execute(
      `UPDATE shopify_stores 
       SET total_products = ?, updated_at = NOW()
       WHERE store_url = ?`,
      [products.length, storeUrl]
    );

    await browser.close();
    log(`💾 Saved ${saved}/${products.length} products for ${storeUrl}`);
    log("✅ Done!");
    res.end();
  } catch (err) {
    console.error(err);
    res.write("❌ Scrape failed: " + err.message);
    res.end();
  }
});

// ================================
//  TEST ROUTE
// ================================
app.get("/", (req, res) => {
  res.send("Puppeteer scraper is alive ✅");
});

// ================================
//  SERVER START
// ================================
const PORT = 4000;
app.listen(PORT, () => console.log(`🚀 Puppeteer scraper running on port ${PORT}`));
