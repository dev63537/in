<?php
// ============================================================
// config.php — LIVE INFINITYFREE Configuration
// ============================================================
// HOW TO USE:
//   1. Open your InfinityFree Control Panel (vPanel)
//   2. Go to "MySQL Databases" to find your exact Host, Username, and Password
//   3. Replace the values below with your actual details
// ============================================================

// 1. UPDATE THESE 4 LINES WITH YOUR INFINITYFREE DETAILS:
define('DB_HOST', 'sqlXXX.infinityfree.com'); // e.g. sql108.infinityfree.com
define('DB_NAME', 'if0_41941799_cshop');      // Your vPanel Database Name
define('DB_USER', 'if0_41941799');            // Your vPanel Username
define('DB_PASS', 'YOUR_VPANEL_PASSWORD');    // Your vPanel Password (Find this in vPanel)

define('SITE_NAME', "Gujju Clothing");

define('SITE_URL', 'https://localhost/i%20test');

define('SITE_EMAIL', 'info@gujjuclothing.com');
define('CURRENCY', '₹');
define('CURRENCY_CODE', 'INR');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Show errors locally for easy debugging (Keep FALSE on live server for security)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
