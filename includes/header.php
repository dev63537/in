<?php
// ============================================================
// includes/header.php — Global Site Header / Navbar
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
startSession();
$cartCount  = getCartCount();
$categories = getCategories();
$flash      = getFlash();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= e($metaDesc ?? "Gujju Clothing — Premium Fashion for Every Occasion. Shop Trendy Clothing Online.") ?>" />
  <meta name="keywords" content="clothing store, fashion, trendy clothes, online shopping" />
  <meta name="theme-color" content="#0f0f0f" />
  <title><?= e($pageTitle ?? "Gujju Clothing — Premium Fashion") ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Faculty+Glyphic&family=Marcellus&display=swap" rel="stylesheet" />

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css" />

  <?php if (isset($extraCSS)): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= e($extraCSS) ?>" />
  <?php endif; ?>
</head>
<body>

<!-- ===== FLASH MESSAGE ===== -->
<?php if ($flash): ?>
  <div class="flash-message flash-<?= e($flash['type']) ?>" id="flash-msg">
    <i class="fa <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= e($flash['message']) ?>
    <button class="flash-close" onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button>
  </div>
<?php endif; ?>

<!-- ===== NAVBAR ===== -->
<nav class="navbar" id="navbar">
  <!-- ===== ANNOUNCEMENT BAR ===== -->
  <div class="announcement-bar" style="background-color: var(--primary, #c9a96e); color: #fff; text-align: center; padding: 8px 35px; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; width: 100%; position: relative;">
    FREE SHIPPING ON ALL ORDERS OVER <?= CURRENCY ?>999
    <button onclick="this.parentElement.style.display='none';" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; padding: 0;">&times;</button>
  </div>
  <div class="nav-container">

    <!-- Logo -->
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
      <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= e(SITE_NAME) ?>" class="site-logo-img" style="max-height:100px;width:auto;object-fit:contain;" onError="if(this.src.indexOf('png')!=-1){this.src='<?= SITE_URL ?>/assets/images/logo.jpg';}else{this.style.display='none';this.nextElementSibling.style.display='flex';}" />
      <span class="logo-fallback" style="display:none;align-items:center;gap:10px;">
        <span class="logo-icon"><i class="fa fa-gem"></i></span>
        <span class="logo-text">GUJJU <span class="logo-accent">CLOTHING</span></span>
      </span>
    </a>

    <!-- Desktop Navigation Links -->
    <ul class="nav-links" id="nav-links">
      <li><a href="<?= SITE_URL ?>/pages/shop.php?gender=men">MEN</a></li>
      <li><a href="<?= SITE_URL ?>/pages/shop.php?gender=women">WOMEN</a></li>
      <li><a href="<?= SITE_URL ?>/pages/shop.php?tag=new">NEW ARRIVALS</a></li>
      <li><a href="<?= SITE_URL ?>/pages/shop.php?tag=sale">SALE</a></li>
      <li><a href="<?= SITE_URL ?>/pages/about.php">ABOUT US</a></li>
    </ul>

    <!-- Right Actions -->
    <div class="nav-actions">
      <!-- Search Toggle -->
      <button class="nav-btn" id="search-toggle" title="Search" aria-label="Search">
        <i class="fa fa-search"></i>
      </button>

      <!-- Account -->
      <?php if (isLoggedIn()): ?>
        <div class="nav-user-menu">
          <button class="nav-btn" id="user-menu-btn" title="My Account">
            <i class="fa fa-user-circle"></i>
          </button>
          <div class="user-dropdown" id="user-dropdown">
            <a href="<?= SITE_URL ?>/pages/account.php"><i class="fa fa-user"></i> My Account</a>
            <a href="<?= SITE_URL ?>/pages/orders.php"><i class="fa fa-box"></i> My Orders</a>
            <?php if (isAdmin()): ?>
              <a href="<?= SITE_URL ?>/admin/index.php"><i class="fa fa-cog"></i> Admin Panel</a>
            <?php endif; ?>
            <hr />
            <a href="<?= SITE_URL ?>/auth/logout.php" class="logout-link"><i class="fa fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/auth/login.php" class="nav-btn" title="Login"><i class="fa fa-user"></i></a>
      <?php endif; ?>

      <!-- Wishlist -->
      <button class="nav-btn" id="wishlist-nav-btn" title="Wishlist" aria-label="Open Wishlist" onclick="openWishlistDrawer()">
        <i class="far fa-heart"></i>
        <span class="cart-badge" id="wishlist-badge" style="display:none;background:#e74c3c">0</span>
      </button>

      <!-- Cart -->
      <button class="nav-btn cart-btn" id="cart-nav-btn" title="Shopping Cart" aria-label="Open Cart" onclick="openCartDrawer()">
        <i class="fa fa-shopping-bag"></i>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge" id="cart-badge-nav"><?= $cartCount ?></span>
        <?php else: ?>
          <span class="cart-badge" id="cart-badge-nav" style="display:none">0</span>
        <?php endif; ?>
      </button>

      <!-- Hamburger -->
      <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Search Bar -->
  <div class="search-bar" id="search-bar">
    <form action="<?= SITE_URL ?>/pages/shop.php" method="GET" class="search-form">
      <input type="text" name="search" placeholder="Search for clothes, styles, brands…" autocomplete="off"
             value="<?= e($_GET['search'] ?? '') ?>" id="search-input" />
      <button type="submit"><i class="fa fa-search"></i> Search</button>
    </form>
    <button class="search-close" id="search-close"><i class="fa fa-times"></i></button>
  </div>

  <!-- Mobile Overlay -->
  <div class="nav-overlay" id="nav-overlay"></div>
</nav>

<!-- Spacer for fixed navbar -->
<div class="nav-spacer"></div>
