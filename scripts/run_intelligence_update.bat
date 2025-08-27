@echo off
REM Shopify Intelligence Auto-Update Script for Windows
REM This batch file should be scheduled to run every hour via Windows Task Scheduler

REM Set the path to your PHP executable and sales-spy directory
SET PHP_PATH=C:\wamp64\bin\php\php8.2.28\php.exe
SET SCRIPT_PATH=C:\wamp64\www\sales-spy\api\cron_shopify_intelligence.php
SET LOG_PATH=C:\wamp64\www\sales-spy\logs\intelligence.log

REM Create logs directory if it doesn't exist
IF NOT EXIST "C:\wamp64\www\sales-spy\logs" 

REM Run the intelligence collection script
echo [%date% %time%] Starting Shopify Intelligence Auto-Update >> "%LOG_PATH%"
"%PHP_PATH%" "%SCRIPT_PATH%" >> "%LOG_PATH%" 2>&1
echo [%date% %time%] Auto-Update completed with exit code %ERRORLEVEL% >> "%LOG_PATH%"

REM Optional: Clean log file if it gets too large (keep last 1000 lines)
FOR /F %%i IN ('TYPE "%LOG_PATH%" ^| FIND /C /V ""') DO SET LINE_COUNT=%%i
IF %LINE_COUNT% GTR 1000 (
    PowerShell -Command "Get-Content '%LOG_PATH%' | Select-Object -Last 1000 | Set-Content '%LOG_PATH%.tmp'; Move-Item '%LOG_PATH%.tmp' '%LOG_PATH%'"
)

exit /b %ERRORLEVEL%