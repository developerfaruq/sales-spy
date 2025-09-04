# Shopify Intelligence Auto-Update Setup

This document explains how to set up automatic hourly data collection for Shopify intelligence data.

## Windows Task Scheduler Setup

### Step 1: Open Task Scheduler
1. Press `Win + R`, type `taskschd.msc`, and press Enter
2. Or search for "Task Scheduler" in the Start menu

### Step 2: Create Basic Task
1. In the right panel, click "Create Basic Task..."
2. Name: `Shopify Intelligence Auto Update`
3. Description: `Automatically collects Shopify competitor intelligence data every hour`

### Step 3: Set Trigger
1. Select "Daily"
2. Start date: Today
3. Start time: Any time (e.g., 9:00 AM)
4. Recur every: 1 days

### Step 4: Set Action
1. Select "Start a program"
2. Program/script: `C:\wamp64\www\sales-spy\scripts\run_intelligence_update.bat`
3. Start in: `C:\wamp64\www\sales-spy\scripts`

### Step 5: Advanced Settings
1. After creating the task, right-click on it and select "Properties"
2. Go to "Triggers" tab, select your trigger, and click "Edit"
3. Check "Repeat task every: 1 hour"
4. Set "for a duration of: 1 day"
5. Check "Enabled"

### Step 6: Configure Additional Settings
1. In "General" tab:
   - Check "Run whether user is logged on or not"
   - Check "Run with highest privileges"
2. In "Settings" tab:
   - Check "Allow task to be run on demand"
   - Check "If the running task does not end when requested, force it to stop"
   - Set "Stop the task if it runs longer than: 30 minutes"

## Manual Setup (Alternative)

If you prefer not to use Task Scheduler, you can manually trigger updates:

### Via Command Line
```cmd
cd C:\wamp64\www\sales-spy\scripts
run_intelligence_update.bat
```

### Via Browser (Dashboard Auto-Update)
1. Go to your e-commerce dashboard
2. Toggle "Shopify Intelligence" mode
3. Click "Auto Update" button
4. The system will collect fresh data and update every hour while the browser is open

## Verification

### Check Logs
View the log file at: `C:\laragon\www\sales-spy\logs\intelligence.log`

### Check Database
```sql
SELECT COUNT(*) as total_products, 
       COUNT(DISTINCT store_domain) as unique_stores,
       MAX(scraped_at) as last_update
FROM shopify_intelligence;
```

### Check Dashboard
1. Go to the e-commerce dashboard
2. Toggle "Shopify Intelligence" mode
3. You should see data from popular Shopify stores

## Troubleshooting

### Common Issues
1. **PHP Path Error**: Update the PHP_PATH in `run_intelligence_update.bat` to match your Laragon installation
2. **Permission Error**: Make sure the task runs with elevated privileges
3. **Network Issues**: Check internet connection and verify API endpoints are accessible

### Manual Test
Test the script manually:
```cmd
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe C:\laragon\www\sales-spy\api\cron_shopify_intelligence.php
```

## Data Collection Details

The system automatically:
- Rotates through different categories each hour
- Collects data from 2 categories per hour (6 stores total)
- Cleans data older than 30 days
- Maintains collection statistics
- Rate limits requests to be respectful to target sites

Categories rotated:
- Fashion, Beauty, Home, Tech, Fitness, Jewelry, Food, Electronics