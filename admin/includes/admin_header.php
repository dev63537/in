<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/../../includes/functions.php';
startSession(); requireAdmin();
$adminUser = $_SESSION['user_name'] ?? 'Admin';
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle ?? "Admin — Gujju Clothing") ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin/>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"/>
</head>
<body>
<div class="admin-layout">

<!-- Sidebar -->
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="admin-brand">
    <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="Admin Logo" style="max-height:100px;width:auto;object-fit:contain;" onError="if(this.src.indexOf('png')!=-1){this.src='<?= SITE_URL ?>/assets/images/logo.jpg';}else{this.style.display='none';this.nextElementSibling.style.display='inline-flex';}" />
    <span style="display:none;align-items:center;gap:10px;"><i class="fa fa-gem"></i> Gujju Clothing</span>
  </div>
  <nav class="admin-nav">
    <div class="admin-nav-section">Main</div>
    <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-item <?= $currentAdmin==='index.php'?'active':'' ?>">
      <i class="fa fa-chart-line"></i> Dashboard
    </a>
    <a href="<?= SITE_URL ?>/admin/products.php" class="admin-nav-item <?= $currentAdmin==='products.php'?'active':'' ?>">
      <i class="fa fa-box"></i> Products
    </a>
    <a href="<?= SITE_URL ?>/admin/orders.php" class="admin-nav-item <?= $currentAdmin==='orders.php'?'active':'' ?>">
      <i class="fa fa-shopping-cart"></i> Orders
    </a>
    <a href="<?= SITE_URL ?>/admin/users.php" class="admin-nav-item <?= $currentAdmin==='users.php'?'active':'' ?>">
      <i class="fa fa-users"></i> Customers
    </a>
    <div class="admin-nav-section">Catalogue</div>
    <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-item <?= $currentAdmin==='categories.php'?'active':'' ?>">
      <i class="fa fa-tags"></i> Categories
    </a>
    <a href="<?= SITE_URL ?>/admin/brands.php" class="admin-nav-item <?= $currentAdmin==='brands.php'?'active':'' ?>">
      <i class="fa fa-store"></i> Brands
    </a>
    <a href="<?= SITE_URL ?>/admin/reviews.php" class="admin-nav-item <?= $currentAdmin==='reviews.php'?'active':'' ?>">
      <i class="fa fa-star"></i> Reviews
    </a>
    <a href="<?= SITE_URL ?>/admin/coupons.php" class="admin-nav-item <?= $currentAdmin==='coupons.php'?'active':'' ?>">
      <i class="fa fa-ticket-alt"></i> Coupons
    </a>
    <div class="admin-nav-section">Settings</div>
    <a href="<?= SITE_URL ?>/index.php" target="_blank" class="admin-nav-item">
      <i class="fa fa-external-link-alt"></i> View Store
    </a>
    <a href="<?= SITE_URL ?>/auth/logout.php" class="admin-nav-item" style="color:#e74c3c">
      <i class="fa fa-sign-out-alt"></i> Logout
    </a>
  </nav>
</aside>

<!-- Main -->
<div class="admin-main">
  <div class="admin-topbar">
    <div>
      <button onclick="document.getElementById('admin-sidebar').classList.toggle('open')" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:#333;margin-right:12px">
        <i class="fa fa-bars"></i>
      </button>
      <span class="admin-topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
    </div>
    <div class="admin-topbar-right">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="flash-message flash-<?= e($flash['type']) ?>" style="position:static;font-size:.85rem">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
      <div class="admin-avatar" title="<?= e($adminUser) ?>"><?= strtoupper(substr($adminUser,0,1)) ?></div>
    </div>
  </div>
  <div class="admin-content">
