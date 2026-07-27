<?php
// admin/actions/delete_brand.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
if (!verifyCsrf($_GET['csrf_token'] ?? '')) {
    setFlash('error','Invalid CSRF.'); redirect(SITE_URL . '/admin/brands.php');
}
$id = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error','Invalid brand.'); redirect(SITE_URL . '/admin/brands.php'); }
$b = dbFetchOne("SELECT * FROM brands WHERE id=?",[$id]);
if (!$b) { setFlash('error','Brand not found.'); redirect(SITE_URL . '/admin/brands.php'); }
// Unlink brand from products
dbExecute("UPDATE products SET brand_id=NULL WHERE brand_id=?",[$id]);
dbExecute("DELETE FROM brands WHERE id=?",[$id]);
setFlash('success',"Brand '{$b['name']}' deleted.");
redirect(SITE_URL . '/admin/brands.php');
