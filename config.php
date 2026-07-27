<?php
// ============================================================
<<<<<<< HEAD
// config.php — Database Configuration
// Update these values after creating your InfinityFree MySQL DB
// ============================================================

define('DB_HOST', 'sql313.infinityfree.com');       // InfinityFree: sql309.infinityfree.com (example)
define('DB_NAME', 'if0_41941799_cshop'); // ← REPLACE with your EXACT DB name from InfinityFree
define('DB_USER', 'if0_41941799');    // e.g. if0_123456789
define('DB_PASS', 'KGmQJFj1LOz8iNK');

define('SITE_NAME', "Gujju Clothing");
define('SITE_URL', 'https://devenra-s.great-site.net'); // NO trailing slash
=======
// configloc.php — LOCAL XAMPP Configuration
// ============================================================
// HOW TO USE:
//   1. Delete configin.php from your XAMPP folder
//   2. Rename THIS file to: config.php
//   Then run the site at: http://localhost/cshop/
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'cshop_local');
define('DB_USER', 'root');
define('DB_PASS', '');              // XAMPP default — no password

define('SITE_NAME', "Gujju Clothing");

define('SITE_URL', 'http://localhost/i%20test'); // NO trailing slash

>>>>>>> origin/master
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

<<<<<<< HEAD
// Error display (set false on production)
define('DEBUG_MODE', true); // Set true temporarily if you need to see errors

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
=======
// Show errors locally for easy debugging
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
>>>>>>> origin/master
}
