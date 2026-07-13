<?php
// admin/actions/bulk_product.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request.'); redirect(SITE_URL . '/admin/products.php');
}

$action = $_POST['action'] ?? '';
$ids    = array_map('intval', (array)($_POST['ids'] ?? []));
$ids    = array_filter($ids); // remove zeros

if (empty($ids)) { setFlash('error', 'No products selected.'); redirect(SITE_URL . '/admin/products.php'); }

$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    switch ($action) {
        case 'activate':
            dbExecute("UPDATE products SET status='active' WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' product(s) activated.');
            break;

        case 'deactivate':
            dbExecute("UPDATE products SET status='inactive' WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' product(s) deactivated.');
            break;

        case 'delete':
            // Clean gallery files from disk before deleting DB rows
            $imgs = dbFetchAll("SELECT image_path FROM product_images WHERE product_id IN ($placeholders)", $ids);
            foreach ($imgs as $img) {
                $f = UPLOAD_DIR . $img['image_path'];
                if (file_exists($f)) @unlink($f);
            }
            dbExecute("DELETE FROM products WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' product(s) deleted.');
            break;

        default:
            setFlash('error', 'Unknown bulk action.');
    }
} catch (Exception $ex) {
    setFlash('error', 'Bulk action failed: ' . (DEBUG_MODE ? $ex->getMessage() : ''));
}

redirect(SITE_URL . '/admin/products.php');
