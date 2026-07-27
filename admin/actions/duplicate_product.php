<?php
// admin/actions/duplicate_product.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();

if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid CSRF token.'); redirect(SITE_URL . '/admin/products.php');
}

$id = (int)($_GET['id'] ?? 0);
$p  = dbFetchOne("SELECT * FROM products WHERE id = ?", [$id]);
if (!$id || !$p) { setFlash('error', 'Product not found.'); redirect(SITE_URL . '/admin/products.php'); }

// New slug — append '-copy' and make unique
$baseSlug = $p['slug'] . '-copy'; $slug = $baseSlug; $i = 1;
while (dbFetchOne("SELECT id FROM products WHERE slug = ?", [$slug])) { $slug = $baseSlug . '-' . $i++; }

// New unique SKU
$sku = 'DSH-COPY-' . strtoupper(substr(uniqid(), -5));

try {
    dbExecute("INSERT INTO products
        (sku, category_id, brand_id, subcategory_id, name, slug, gender, material,
         description, short_description, price, sale_price, cost_price,
         stock, low_stock_alert, sizes, colors, tags,
         is_featured, is_trending, is_new_arrival, is_best_seller, featured,
         meta_title, meta_description, image, status, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
        [$sku, $p['category_id'], $p['brand_id'] ?? null, $p['subcategory_id'] ?? null,
         'Copy of ' . $p['name'], $slug, $p['gender'] ?? 'unisex', $p['material'] ?? '',
         $p['description'], $p['short_description'] ?? '', $p['price'], $p['sale_price'], $p['cost_price'] ?? 0,
         $p['stock'], $p['low_stock_alert'] ?? 5, $p['sizes'], $p['colors'], $p['tags'],
         0, 0, 0, 0, 0,
         $p['meta_title'] ?? '', $p['meta_description'] ?? '',
         $p['image'], 'inactive']);

    $newId = dbLastId();

    // Copy gallery image references (same physical files, new DB rows)
    $imgs = dbFetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order", [$id]);
    foreach ($imgs as $img) {
        dbExecute("INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES (?,?,?,?)",
                  [$newId, $img['image_path'], $img['alt_text'], $img['sort_order']]);
    }

    setFlash('success', 'Product duplicated. Edit it to make active.');
    redirect(SITE_URL . '/admin/product_form.php?id=' . $newId);
} catch (Exception $ex) {
    setFlash('error', 'Could not duplicate: ' . (DEBUG_MODE ? $ex->getMessage() : ''));
    redirect(SITE_URL . '/admin/products.php');
}
