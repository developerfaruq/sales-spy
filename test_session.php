<?php
session_start();
require_once 'config/db.php';

// Display current session status
echo "<h1>Session Test</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// Test setting a session variable
if (isset($_GET['set'])) {
    $_SESSION['test_var'] = "This is a test value set at " . date('Y-m-d H:i:s');
    echo "<p>Session variable set. <a href='test_session.php'>Refresh</a> to see it.</p>";
}

// Test clearing the session
if (isset($_GET['clear'])) {
    session_unset();
    echo "<p>Session cleared. <a href='test_session.php'>Refresh</a> to see it.</p>";
}

// Links for testing
echo "<p><a href='test_session.php?set=1'>Set Test Session Variable</a> | <a href='test_session.php?clear=1'>Clear Session</a></p>";

// Check if session storage is working
echo "<h2>Session Storage Test</h2>";
if (isset($_SESSION['test_var'])) {
    echo "<p style='color: green;'>✅ Session storage is working correctly.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No test variable found. Click 'Set Test Session Variable' to test.</p>";
}

// Check if cookies are enabled
echo "<h2>Cookie Test</h2>";
if (count($_COOKIE) > 0) {
    echo "<p style='color: green;'>✅ Cookies are enabled.</p>";
} else {
    echo "<p style='color: red;'>❌ Cookies appear to be disabled. Sessions may not work correctly.</p>";
}

// Check session configuration
echo "<h2>Session Configuration</h2>";
echo "<pre>";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . (ini_get('session.cookie_secure') ? "Yes" : "No") . "\n";
echo "session.cookie_httponly: " . (ini_get('session.cookie_httponly') ? "Yes" : "No") . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "</pre>";

// Provide guidance
echo "<h2>Troubleshooting</h2>";
echo "<ul>";
echo "<li>If session variables aren't persisting, check your PHP configuration.</li>";
echo "<li>Make sure cookies are enabled in your browser.</li>";
echo "<li>Check file permissions on the session save path.</li>";
echo "<li>Try using a different browser to rule out browser-specific issues.</li>";
echo "</ul>";

// Return link
echo "<p><a href='" . BASE_URL . "'>Return to Home</a></p>";
?> 