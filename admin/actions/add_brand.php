<?php
// admin/actions/add_brand.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error','Invalid request.'); redirect(SITE_URL . '/admin/brands.php');
}
$name   = trim($_POST['name']   ?? '');
$slug   = trim($_POST['slug']   ?? '');
$status = in_array($_POST['status']??'', ['active','inactive']) ? $_POST['status'] : 'active';
if (!$name) { setFlash('error','Brand name required.'); redirect(SITE_URL . '/admin/brands.php'); }
if (!$slug) { $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name)); $slug = trim($slug,'-'); }
$base=$slug; $i=1;
while (dbFetchOne("SELECT id FROM brands WHERE slug=?",[$slug])) { $slug=$base.'-'.$i++; }
dbExecute("INSERT INTO brands (name,slug,status) VALUES (?,?,?)",[$name,$slug,$status]);
setFlash('success',"Brand '$name' added.");
redirect(SITE_URL . '/admin/brands.php');
