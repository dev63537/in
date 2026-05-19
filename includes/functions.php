<?php
// ============================================================
// includes/functions.php — Global Helper Functions
// ============================================================

require_once __DIR__ . '/db.php';

// Start session safely — compatible with PHP 5.6, 7.x, 8.x
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('DEVENDRAS_SESSION');
        // Use positional args (works on all PHP versions including InfinityFree)
        session_set_cookie_params(SESSION_TIMEOUT, '/', '', false, true);
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login — redirect if not
function requireLogin($redirect = '/auth/login.php') {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . $redirect);
        exit;
    }
}

// Check if admin
function isAdmin() {
    startSession();
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . SITE_URL . "/index.php");
        exit;
    }
}

// Sanitize output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Format price
function formatPrice($amount) {
    return CURRENCY . number_format((float)$amount, 2);
}

// Get cart item count from session
function getCartCount() {
    startSession();
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Get cart total
function getCartTotal() {
    startSession();
    if (!isset($_SESSION['cart'])) return 0;
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Add to cart
function addToCart($product_id, $size, $color, $quantity = 1) {
    startSession();
    $product = dbFetchOne("SELECT id, name, price, sale_price, image FROM products WHERE id = ? AND status = 'active'", [$product_id]);
    if (!$product) return false;

    $price = $product['sale_price'] > 0 ? $product['sale_price'] : $product['price'];
    $key   = $product_id . '_' . $size . '_' . $color;

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $product_id,
            'name'       => $product['name'],
            'price'      => $price,
            'image'      => $product['image'],
            'size'       => $size,
            'color'      => $color,
            'quantity'   => $quantity,
        ];
    }
    return true;
}

// Remove from cart
function removeFromCart($key) {
    startSession();
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        return true;
    }
    return false;
}

// Update cart quantity
function updateCartQuantity($key, $qty) {
    startSession();
    if (isset($_SESSION['cart'][$key])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$key]);
        } else {
            $_SESSION['cart'][$key]['quantity'] = (int)$qty;
        }
        return true;
    }
    return false;
}

// Generate CSRF token
function csrfToken() {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCsrf($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash message setter
function setFlash($type, $message) {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Flash message getter (clears after read)
function getFlash() {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get products with optional filters
function getProducts($limit = 12, $offset = 0, $filters = []) {
    $where   = ["p.status = 'active'"];
    $params  = [];

    if (!empty($filters['category_id'])) {
        $where[] = "p.category_id = ?";
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['min_price'])) {
        $where[] = "p.price >= ?";
        $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = "p.price <= ?";
        $params[] = $filters['max_price'];
    }
    if (!empty($filters['search'])) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['tag'])) {
        $where[] = "p.tags LIKE ?";
        $params[] = '%' . $filters['tag'] . '%';
    }

    $whereSQL = implode(' AND ', $where);
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereSQL
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;

    return dbFetchAll($sql, $params);
}

// Get categories
function getCategories() {
    return dbFetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order, name");
}

// Upload image helper
function uploadImage($file, $dir = null) {
    if (!$dir) $dir = UPLOAD_DIR;
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload failed.'];
    if ($file['size'] > MAX_UPLOAD_SIZE) return ['error' => 'File too large (max 2MB).'];
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return ['error' => 'Invalid file type.'];

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $dest     = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'Could not save file.'];
    return ['success' => true, 'filename' => $filename];
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Paginate helper — returns HTML
function pagination($total, $limit, $page, $url) {
    $pages = ceil($total / $limit);
    if ($pages <= 1) return '';
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = ($i == $page) ? ' active' : '';
        $html .= "<a href=\"{$url}&page={$i}\" class=\"page-btn{$active}\">{$i}</a>";
    }
    $html .= '</div>';
    return $html;
}
