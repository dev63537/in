<?php
// admin/actions/edit_brand.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error','Invalid request.'); redirect(SITE_URL . '/admin/brands.php');
}
$id     = (int)($_POST['id'] ?? 0);
$name   = trim($_POST['name']   ?? '');
$slug   = trim($_POST['slug']   ?? '');
$status = in_array($_POST['status']??'', ['active','inactive']) ? $_POST['status'] : 'active';
if (!$id || !$name) { setFlash('error','Invalid data.'); redirect(SITE_URL . '/admin/brands.php'); }
if (!$slug) { $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name)); $slug = trim($slug,'-'); }
$base=$slug; $i=1;
while (dbFetchOne("SELECT id FROM brands WHERE slug=? AND id!=?",[$slug,$id])) { $slug=$base.'-'.$i++; }
dbExecute("UPDATE brands SET name=?,slug=?,status=? WHERE id=?",[$name,$slug,$status,$id]);
setFlash('success',"Brand updated.");
redirect(SITE_URL . '/admin/brands.php');
