<?php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token']??'')) {
    setFlash('error','Invalid request.'); redirect(SITE_URL.'/admin/products.php');
}
$id=(int)($_POST['id']??0); $name=$_POST['name']??''; $price=(float)($_POST['price']??0);
$cat=(int)($_POST['category_id']??0); $sale=(float)($_POST['sale_price']??0);
$stock=(int)($_POST['stock']??0); $desc=$_POST['description']??'';
$sizes=$_POST['sizes']??''; $colors=$_POST['colors']??''; $tags=$_POST['tags']??'';
$status=in_array($_POST['status']??'',['active','inactive'])?$_POST['status']:'active';
$image=trim($_POST['image_url']??'');
if(!empty($_FILES['image_file']['name'])){$up=uploadImage($_FILES['image_file']);if(!empty($up['success']))$image=UPLOAD_URL.$up['filename'];}
if(!$id||!$name){setFlash('error','Invalid.');redirect(SITE_URL.'/admin/products.php');}
dbExecute("UPDATE products SET category_id=?,name=?,description=?,price=?,sale_price=?,stock=?,sizes=?,colors=?,tags=?,status=?,image=CASE WHEN ?='' THEN image ELSE ? END WHERE id=?",[$cat,$name,$desc,$price,$sale,$stock,$sizes,$colors,$tags,$status,$image,$image,$id]);
setFlash('success','Product updated!'); redirect(SITE_URL.'/admin/products.php');
