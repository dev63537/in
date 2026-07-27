<?php
// admin/actions/delete_product.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();

if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid CSRF token.'); redirect(SITE_URL . '/admin/products.php');
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid product.'); redirect(SITE_URL . '/admin/products.php'); }

$product = dbFetchOne("SELECT id, name FROM products WHERE id = ?", [$id]);
if (!$product) { setFlash('error', 'Product not found.'); redirect(SITE_URL . '/admin/products.php'); }

try {
    // Remove gallery images from disk (FK cascade handles DB rows, but we clean up files)
    $imgs = dbFetchAll("SELECT image_path FROM product_images WHERE product_id = ?", [$id]);
    foreach ($imgs as $img) {
        $file = UPLOAD_DIR . $img['image_path'];
        if (file_exists($file)) @unlink($file);
    }
    dbExecute("DELETE FROM products WHERE id = ?", [$id]);
    setFlash('success', "Product '{$product['name']}' deleted.");
} catch (Exception $ex) {
    setFlash('error', 'Could not delete product.');
}

redirect(SITE_URL . '/admin/products.php');
