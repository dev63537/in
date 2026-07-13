<?php
// ============================================================
// configin.php — INFINITYFREE LIVE Hosting Configuration
// ============================================================
// HOW TO USE:
//   1. Delete configloc.php from your InfinityFree htdocs folder
//   2. Rename THIS file to: config.php
//   Then visit: https://yourname.great-site.net/
// ============================================================
// ⚠ FILL IN YOUR REAL VALUES BELOW BEFORE UPLOADING!
//   Get DB_HOST, DB_NAME, DB_USER from:
//   InfinityFree Control Panel → MySQL Databases
// ============================================================

define('DB_HOST', 'sql313.infinityfree.com');    // ← REPLACE with your exact hostname from panel
define('DB_NAME', 'if0_41941799_cshop');         // ← REPLACE with your exact DB name
define('DB_USER', 'if0_41941799');               // ← REPLACE with your exact DB user
define('DB_PASS', 'KGmQJFj1LOz8iNK');           // ← REPLACE with your DB password

define('SITE_NAME', "Devendra's Shop");
define('SITE_URL', 'https://devenra-s.great-site.net'); // ← REPLACE with your subdomain. NO trailing slash
define('SITE_EMAIL', 'info@devendras.com');
define('CURRENCY', '₹');
define('CURRENCY_CODE', 'INR');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ALWAYS false on live/production — never show errors to visitors
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
