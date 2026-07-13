<?php
// ============================================================
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

define('SITE_NAME', "Devendra's Shop");
define('SITE_URL', 'http://localhost/cshop'); // NO trailing slash
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

// Show errors locally for easy debugging
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
