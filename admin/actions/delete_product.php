<?php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
$id=(int)($_GET['id']??0);
if($id){dbExecute("DELETE FROM products WHERE id=?",[$id]);setFlash('success','Product deleted.');}
else{setFlash('error','Invalid.');}
redirect(SITE_URL.'/admin/products.php');
