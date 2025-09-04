1. Project Overview

Name: Sales-Spy

Goal: Aggregate sales/product data from multiple platforms (Shopify, WooCommerce, Etsy, eBay, Wix, Squarespace).

Approach: Hybrid — use APIs where available, fall back to scrapers when not.

Core Features:

Centralized aggregator API (aggregate_sources.php).

Scrapers for Etsy, eBay, WooCommerce, Wix, Squarespace.

Normalized product schema.

Caching, pagination, and rate limiting.

Frontend dashboard (currently HTML, later migrating to React).

🔹 2. Code Rules
Backend (PHP)

Use PDO prepared statements for all database queries.

Sanitize all inputs ($_GET, $_POST, query params).

Follow unified JSON response format:

{
  "status": "success|error",
  "message": "human readable text",
  "data": { ... }
}


Implement error handling with structured logs.

Frontend (HTML/React Migration)

Use relative links, not placeholders (#).

Navigation must map to real pages (e.g., Dashboard-ecc.html).

Keep markup modular (sections/components).

Plan for React migration: separate logic from presentation.

🔹 3. Security Rules

Never commit secrets (API keys, DB passwords). Use .env.

Escape all outputs in HTML to prevent XSS.

Add API authentication (JWT or API Key system).

Apply rate limiting per user/platform.

Sanitize error responses (no file paths/stack traces in production).

🔹 4. Performance Rules

Prefer Redis (or Memcached) over file-based cache.

Use pagination for large datasets.

Avoid blocking scrapers — prepare for async/queue processing.

Optimize DB with proper indexes on common queries.

🔹 5. Workflow Rules

Confirm file path before editing.

Provide diffs/patches or clear code blocks when changing.

Document What was wrong → What was changed → How to test.

Test locally before marking an issue as fixed.

🔹 6. Navigation Rules

E-commerce button → Dashboard-ecc.html

Other nav links should map to existing pages or be disabled until implemented.

Use consistent folder structure for pages:

/home
/dashboard
/api
/assets


No dead links or placeholders left in production builds.

🔹 7. Testing Rules

Add unit tests (PHPUnit) for scrapers and APIs.

Add frontend test cases (later in React, using Jest).

Manual tests after each fix:

Clear cache (Ctrl+Shift+R in browser, or clear PHP cache).

Restart server (php -S localhost:8000 or Apache/Nginx restart).

Confirm changes are visible in UI and API.

🔹 8. Deployment Rules

Use Docker for consistent environment.

Environment-specific configs (.env.development, .env.production).

CI/CD pipeline with automated testing before deployment.

Monitoring + logging for scrapers and APIs.

✅ Summary:

Keep the code secure, modular, and scalable.

Always test navigation, API responses, and scraper outputs before confirming a fix.

Prepare for React migration by keeping frontend modular.