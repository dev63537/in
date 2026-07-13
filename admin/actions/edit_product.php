<?php
// admin/actions/edit_product.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request.'); redirect(SITE_URL . '/admin/products.php');
}

$id   = (int)($_POST['id'] ?? 0);
$prod = dbFetchOne("SELECT * FROM products WHERE id = ?", [$id]);
if (!$id || !$prod) { setFlash('error', 'Product not found.'); redirect(SITE_URL . '/admin/products.php'); }

$errors = [];
$name   = trim($_POST['name']  ?? '');
$slug   = trim($_POST['slug']  ?? '');
$sku    = trim($_POST['sku']   ?? $prod['sku']);

if (!$name) $errors[] = 'Product name is required.';
if ((float)($_POST['price'] ?? 0) <= 0) $errors[] = 'Valid price is required.';

// Slug uniqueness (exclude self)
if (!$slug) { $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)); $slug = trim($slug, '-'); }
$baseSlug = $slug; $i = 1;
while (dbFetchOne("SELECT id FROM products WHERE slug = ? AND id != ?", [$slug, $id])) {
    $slug = $baseSlug . '-' . $i++;
}

// SKU uniqueness (exclude self)
if (!$sku) $sku = $prod['sku'];
if (dbFetchOne("SELECT id FROM products WHERE sku = ? AND id != ?", [$sku, $id])) {
    $sku = $sku . '-' . rand(10,99);
}

if ($errors) {
    foreach ($errors as $e) setFlash('error', $e);
    redirect(SITE_URL . '/admin/product_form.php?id=' . $id);
}

// ── Handle Main Image ─────────────────────────────────────
$imageUrl = trim($_POST['image_url'] ?? $prod['image']);
if (!empty($_FILES['main_image']['name'])) {
    $up = uploadImage($_FILES['main_image']);
    if (isset($up['error'])) { setFlash('error', $up['error']); redirect(SITE_URL . '/admin/product_form.php?id=' . $id); }
    $imageUrl = UPLOAD_URL . $up['filename'];
}

// ── Update Product ────────────────────────────────────────
$sql = "UPDATE products SET
    sku=?, category_id=?, brand_id=?, subcategory_id=?, name=?, slug=?,
    gender=?, material=?, description=?, short_description=?,
    price=?, sale_price=?, cost_price=?, stock=?, low_stock_alert=?,
    sizes=?, colors=?, tags=?,
    is_featured=?, is_trending=?, is_new_arrival=?, is_best_seller=?, featured=?,
    meta_title=?, meta_description=?, image=?, status=?
  WHERE id=?";

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
    trim($_POST['sizes']  ?? ''),
    trim($_POST['colors'] ?? ''),
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
    $id,
];

try { dbExecute($sql, $params); }
catch (Exception $ex) {
    setFlash('error', 'DB error: ' . (DEBUG_MODE ? $ex->getMessage() : 'Could not update.'));
    redirect(SITE_URL . '/admin/product_form.php?id=' . $id);
}

// ── Handle New Gallery Images ─────────────────────────────
if (!empty($_FILES['gallery_images']['name'][0])) {
    $sort = (int)(dbFetchOne("SELECT COALESCE(MAX(sort_order),0) AS mx FROM product_images WHERE product_id=?", [$id])['mx'] ?? 0);
    for ($i = 0; $i < min(8, count($_FILES['gallery_images']['name'])); $i++) {
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
                      [$id, $up['filename'], ++$sort]);
        }
    }
}

setFlash('success', "Product '{$name}' updated successfully!");
redirect(SITE_URL . '/admin/products.php');
