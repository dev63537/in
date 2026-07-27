<?php
// admin/actions/add_product.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request.'); redirect(SITE_URL . '/admin/products.php');
}

$errors = [];
$name   = trim($_POST['name'] ?? '');
$slug   = trim($_POST['slug'] ?? '');
$sku    = trim($_POST['sku']  ?? '');

if (!$name)                              $errors[] = 'Product name is required.';
if ((float)($_POST['price'] ?? 0) <= 0) $errors[] = 'Valid price is required.';
if ((int)($_POST['stock'] ?? -1) < 0)   $errors[] = 'Stock cannot be negative.';

// Auto-generate slug
if (!$slug && $name) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $slug = trim($slug, '-');
}

// Ensure slug is unique
$baseSlug = $slug; $i = 1;
while (dbFetchOne("SELECT id FROM products WHERE slug = ?", [$slug])) {
    $slug = $baseSlug . '-' . $i++;
}

// Auto-generate SKU if empty
if (!$sku) {
    $sku = 'DSH-' . strtoupper(substr(uniqid(), -6));
}

// Check duplicate SKU
if (dbFetchOne("SELECT id FROM products WHERE sku = ?", [$sku])) {
    $sku = $sku . '-' . rand(10,99);
}

if ($errors) {
    foreach ($errors as $e) setFlash('error', $e);
    redirect(SITE_URL . '/admin/product_form.php');
}

// ── Handle Main Image Upload ──────────────────────────────
$imageUrl = trim($_POST['image_url'] ?? '');
if (!empty($_FILES['main_image']['name'])) {
    $up = uploadImage($_FILES['main_image']);
    if (isset($up['error'])) { setFlash('error', $up['error']); redirect(SITE_URL . '/admin/product_form.php'); }
    $imageUrl = UPLOAD_URL . $up['filename'];
}

// ── Insert Product ────────────────────────────────────────
$sql = "INSERT INTO products
    (sku, category_id, brand_id, subcategory_id, name, slug, gender, material,
     description, short_description, price, sale_price, cost_price,
     stock, low_stock_alert, sizes, colors, tags,
     is_featured, is_trending, is_new_arrival, is_best_seller, featured,
     meta_title, meta_description, image, status, created_at)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

$params = [
    $sku,
    (int)($_POST['category_id']    ?? 0) ?: null,
    (int)($_POST['brand_id']       ?? 0) ?: null,
    (int)($_POST['subcategory_id'] ?? 0) ?: null,
    $name, $slug,
    in_array($_POST['gender'] ?? '', ['men','women','kids','unisex']) ? $_POST['gender'] : 'unisex',
    trim($_POST['material'] ?? ''),
    trim($_POST['description'] ?? ''),
    trim($_POST['short_description'] ?? ''),
    (float)($_POST['price']      ?? 0),
    (float)($_POST['sale_price'] ?? 0),
    (float)($_POST['cost_price'] ?? 0),
    (int)($_POST['stock']           ?? 0),
    (int)($_POST['low_stock_alert'] ?? 5),
    trim($_POST['sizes']  ?? 'XS,S,M,L,XL,XXL'),
    trim($_POST['colors'] ?? 'Black,White'),
    trim($_POST['tags']   ?? ''),
    isset($_POST['is_featured'])    ? 1 : 0,
    isset($_POST['is_trending'])    ? 1 : 0,
    isset($_POST['is_new_arrival']) ? 1 : 0,
    isset($_POST['is_best_seller']) ? 1 : 0,
    isset($_POST['is_featured'])    ? 1 : 0,
    trim($_POST['meta_title']       ?? ''),
    trim($_POST['meta_description'] ?? ''),
    $imageUrl,
    in_array($_POST['status'] ?? 'active', ['active','inactive']) ? $_POST['status'] : 'active',
];

try {
    dbExecute($sql, $params);
    $productId = dbLastId();
} catch (Exception $ex) {
    setFlash('error', 'Database error: ' . (DEBUG_MODE ? $ex->getMessage() : 'Could not save product.'));
    redirect(SITE_URL . '/admin/product_form.php');
}

// ── Handle Gallery Images ─────────────────────────────────
if (!empty($_FILES['gallery_images']['name'][0])) {
    $sort = 0;
    for ($i = 0; $i < min(6, count($_FILES['gallery_images']['name'])); $i++) {
        if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $file = [
            'name'     => $_FILES['gallery_images']['name'][$i],
            'type'     => $_FILES['gallery_images']['type'][$i],
            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
            'error'    => $_FILES['gallery_images']['error'][$i],
            'size'     => $_FILES['gallery_images']['size'][$i],
        ];
        $up = uploadImage($file);
        if (isset($up['success'])) {
            dbExecute("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?,?,?)",
                      [$productId, $up['filename'], $sort++]);
        }
    }
}

setFlash('success', "Product '{$name}' added successfully!");
redirect(SITE_URL . '/admin/products.php');
