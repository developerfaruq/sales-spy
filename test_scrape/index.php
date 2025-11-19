<?php
require_once "../config/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Spy Admin – Shopify Scraper</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #0b132b;
      color: #fff;
      padding: 40px;
    }
    .card {
      background: #1c2541;
      padding: 20px;
      border-radius: 8px;
      max-width: 700px;
      margin: auto;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    button {
      background: #3a86ff;
      color: #fff;
      border: none;
      padding: 12px 20px;
      margin: 10px 10px 10px 0;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
    }
    button:hover { background: #4361ee; }
    pre {
      background: #111;
      color: #0f0;
      padding: 15px;
      height: 250px;
      overflow-y: auto;
      border-radius: 6px;
      margin-top: 10px;
    }
    .status {
      font-size: 14px;
      color: #5BC0EB;
      margin-bottom: 10px;
    }
    .progress-container {
      background: #111;
      border-radius: 6px;
      overflow: hidden;
      height: 25px;
      margin-top: 15px;
      width: 100%;
    }
    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #3a86ff, #00ff88);
      width: 0%;
      text-align: center;
      color: #000;
      font-weight: bold;
      transition: width 0.3s ease;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2> Sales Spy Control Panel</h2>
<p>Testing stage for shopify,wix.</p>

<div class="status" id="status">🟠 System idle — ready to start scraping.</div>

<button onclick="discoverStores()">🔍 Discover Shopify Stores</button>
<button onclick="startScrape()">🚀 Start Shopify Scrape</button>
<button onclick="startWixScrape()">🧩 Start Wix Scrape</button>
<button onclick="discoverWixStores()">🌍 Discover Wix Stores</button>

<button onclick="stopScrape()" id="stopBtn" style="background:#e63946;">🛑 Stop Scrape</button>

    <div class="progress-container">
      <div class="progress-bar" id="progress-bar">0%</div>
    </div>

    <h3>Logs:</h3>
    <pre id="logs">Waiting for action...</pre>
  </div>

  <script>
  // ✅ Check backend connection
  async function checkConnection() {
    try {
      const res = await fetch('check_connection.php');
      if (res.ok) {
        document.getElementById('status').innerText = '🟢 PHP Scraper service ready';
      } else {
        throw new Error();
      }
    } catch {
      document.getElementById('status').innerText = '🔴 Backend not reachable';
    }
  }

  // ✅ Discover stores
  async function discoverStores() {
    const logs = document.getElementById('logs');
    logs.innerText = 'Discovering new Shopify stores...\n';
    try {
      const res = await fetch('discover_shopify_stores.php');
      const data = await res.json();
      logs.innerText += JSON.stringify(data, null, 2);
    } catch (err) {
      logs.innerText += '❌ Failed to discover stores: ' + err.message;
    }
  }

  // ✅ Start live scraping with progress bar
  async function startScrape() {
    const logs = document.getElementById('logs');
    const bar = document.getElementById('progress-bar');
    logs.innerText = '🚀 Starting scraping process...\n';
    document.getElementById('status').innerText = '🟡 Scraping in progress...';
    bar.style.width = '0%';
    bar.innerText = '0%';

    try {
      const response = await fetch('shopify_scrapper.php');
      const reader = response.body.getReader();
      const decoder = new TextDecoder('utf-8');
      let buffer = '';
      let totalStores = 0;
      let scrapedCount = 0;

      while (true) {
        const { value, done } = await reader.read();
        if (done) break;
        const chunk = decoder.decode(value, { stream: true });
        buffer += chunk;

        // Detect progress lines like "Scraping: https://store..."
        const newLines = chunk.split('\n');
        for (const line of newLines) {
          if (line.includes('Scraping:')) {
            scrapedCount++;
          }
          if (line.includes('Starting scrape for')) {
            const match = line.match(/Starting scrape for (\d+)/i);
            if (match) totalStores = parseInt(match[1]);
          }
        }

        // Update log viewer
        logs.innerText = buffer;
        logs.scrollTop = logs.scrollHeight;

        // Update progress bar
        if (totalStores > 0) {
          const percent = Math.min(100, Math.round((scrapedCount / totalStores) * 100));
          bar.style.width = percent + '%';
          bar.innerText = percent + '%';
        }
      }

      logs.innerText += '\n✅ Scraping completed.\n';
      document.getElementById('status').innerText = '🟢 Scraping completed.';
      bar.style.width = '100%';
      bar.innerText = '100%';
    } catch (err) {
      logs.innerText += '\n❌ Error: ' + err.message;
      document.getElementById('status').innerText = '🔴 Scraping failed.';
    }
  }
  // 🛑 Stop scraping
async function stopScrape() {
  if (!confirm('Are you sure you want to stop the scraper?')) return;
  await fetch('stop_scrape.php');
  document.getElementById('status').innerText = '🛑 Stopping scraper...';
}
// ✅ Trigger Wix Scraper
async function startWixScrape() {
  const logs = document.getElementById('logs');
  logs.innerText = '🚀 Starting Wix scraping process...\n';
  document.getElementById('status').innerText = '🟡 Scraping Wix stores...';

  try {
    const response = await fetch('wix_scraper_trigger.php');
    const data = await response.json();
    logs.innerText += data.message || JSON.stringify(data);
    document.getElementById('status').innerText = '🟢 Wix scraping completed.';
  } catch (err) {
    logs.innerText += '\n❌ Wix scrape failed: ' + err.message;
    document.getElementById('status').innerText = '🔴 Wix scraping failed.';
  }
}
// 🌍 Discover Wix Stores
async function discoverWixStores() {
  const logs = document.getElementById('logs');
  logs.innerText = '🌍 Discovering new Wix stores...\n';
  document.getElementById('status').innerText = '🟡 Searching for Wix stores...';

  try {
    const res = await fetch('discover_wix_stores.php');
    const data = await res.json();
    logs.innerText += data.message || JSON.stringify(data);
    document.getElementById('status').innerText = '🟢 Wix store discovery complete.';
  } catch (err) {
    logs.innerText += '\n❌ Error discovering Wix stores: ' + err.message;
    document.getElementById('status').innerText = '🔴 Discovery failed.';
  }
}




  checkConnection();
  </script>
</body>
</html>
