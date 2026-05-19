<?php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token']??'')) {
    setFlash('error','Invalid request.'); redirect(SITE_URL.'/admin/products.php');
}
$name=$_POST['name']??''; $price=(float)($_POST['price']??0); $cat=(int)($_POST['category_id']??0);
$sale=(float)($_POST['sale_price']??0); $stock=(int)($_POST['stock']??0);
$desc=$_POST['description']??''; $sizes=$_POST['sizes']??'XS,S,M,L,XL';
$colors=$_POST['colors']??'Black,White'; $tags=$_POST['tags']??'';
$featured=isset($_POST['featured'])?1:0;
$image=trim($_POST['image_url']??'');
if(!empty($_FILES['image_file']['name'])){$up=uploadImage($_FILES['image_file']);if(!empty($up['success']))$image=UPLOAD_URL.$up['filename'];}
$slug=strtolower(preg_replace('/[^a-z0-9]+/','-',$name)).'-'.time();
if(!$name||!$price){setFlash('error','Name and price required.');redirect(SITE_URL.'/admin/products.php');}
dbExecute("INSERT INTO products(category_id,name,slug,description,price,sale_price,stock,sizes,colors,image,tags,featured)VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",[$cat,$name,$slug,$desc,$price,$sale,$stock,$sizes,$colors,$image,$tags,$featured]);
setFlash('success','Product added!'); redirect(SITE_URL.'/admin/products.php');
