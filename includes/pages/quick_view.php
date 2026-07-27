<?php
// pages/quick_view.php — AJAX endpoint for Quick View modal
require_once __DIR__ . '/../includes/functions.php';
startSession();

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$p = dbFetchOne(
    "SELECT p.id, p.name, p.price, p.sale_price, p.image, p.short_description,
            p.sizes, p.colors, p.tags, p.stock,
            c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.id = ? AND p.status = 'active'",
    [$id]
);

if (!$p) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Track recently viewed in session
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
$_SESSION['recently_viewed'] = array_values(array_unique(
    array_merge([$p['id']], $_SESSION['recently_viewed'])
));
if (count($_SESSION['recently_viewed']) > 10) {
    $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);
}

// Parse sizes & colors
$sizes  = !empty($p['sizes'])  ? array_map('trim', explode(',', $p['sizes']))  : [];
$colors = !empty($p['colors']) ? array_map('trim', explode(',', $p['colors'])) : [];

echo json_encode([
    'id'                => $p['id'],
    'name'              => $p['name'],
    'price'             => $p['price'],
    'sale_price'        => $p['sale_price'],
    'image'             => $p['image'],
    'short_description' => $p['short_description'] ?? '',
    'sizes'             => $sizes,
    'colors'            => $colors,
    'category_name'     => $p['category_name'] ?? '',
    'stock'             => (int)$p['stock'],
]);
