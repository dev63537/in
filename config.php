<?php
// ============================================================
// config.php — Database Configuration
// Update these values after creating your InfinityFree MySQL DB
// ============================================================

define('DB_HOST', 'sql313.infinityfree.com');       // InfinityFree: sql309.infinityfree.com (example)
define('DB_NAME', 'if0_41941799_cshop'); // ← REPLACE with your EXACT DB name from InfinityFree
define('DB_USER', 'if0_41941799');    // e.g. if0_123456789
define('DB_PASS', 'KGmQJFj1LOz8iNK');

define('SITE_NAME', "Devendra's Shop");
define('SITE_URL', 'http://devenra-s.great-site.net'); // NO trailing slash
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

// Error display (set false on production)
define('DEBUG_MODE', false); // Set true temporarily if you need to see errors

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
