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
  <meta name="description" content="<?= e($metaDesc ?? "Devendra's Shop — Premium Fashion for Every Occasion. Shop Trendy Clothing Online.") ?>" />
  <meta name="keywords" content="clothing store, fashion, trendy clothes, online shopping" />
  <meta name="theme-color" content="#0f0f0f" />
  <title><?= e($pageTitle ?? "Devendra's Shop — Premium Fashion") ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet" />

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
  <div class="nav-container">

    <!-- Logo -->
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
      <span class="logo-icon"><i class="fa fa-gem"></i></span>
      <span class="logo-text">DEVENDRA'S <span class="logo-accent">SHOP</span></span>
    </a>

    <!-- Desktop Navigation Links -->
    <ul class="nav-links" id="nav-links">
      <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>
      <li class="has-dropdown">
        <a href="<?= SITE_URL ?>/pages/shop.php">Shop <i class="fa fa-chevron-down fa-xs"></i></a>
        <div class="nav-dropdown">
          <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= e($cat['id']) ?>">
              <i class="fa fa-tag"></i> <?= e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
          <a href="<?= SITE_URL ?>/pages/shop.php?tag=new"><i class="fa fa-star"></i> New Arrivals</a>
          <a href="<?= SITE_URL ?>/pages/shop.php?tag=sale"><i class="fa fa-fire"></i> Sale</a>
        </div>
      </li>
      <li><a href="<?= SITE_URL ?>/pages/collections.php">Collections</a></li>
      <li><a href="<?= SITE_URL ?>/pages/about.php">About</a></li>
      <li><a href="<?= SITE_URL ?>/pages/contact.php">Contact</a></li>
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

      <!-- Cart -->
      <a href="<?= SITE_URL ?>/cart/cart.php" class="nav-btn cart-btn" title="Shopping Cart" aria-label="Cart">
        <i class="fa fa-shopping-bag"></i>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

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
