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

  <!-- Open Graph / Social Sharing -->
  <meta property="og:type"        content="website" />
  <meta property="og:title"       content="<?= e($pageTitle ?? "Devendra's Shop — Premium Fashion") ?>" />
  <meta property="og:description" content="<?= e($metaDesc ?? "Shop premium fashion at Devendra's Shop.") ?>" />
  <meta property="og:image"       content="<?= isset($ogImage) ? e($ogImage) : SITE_URL.'/assets/images/og-default.jpg' ?>" />
  <meta property="og:url"         content="<?= e(SITE_URL . $_SERVER['REQUEST_URI']) ?>" />
  <meta property="og:site_name"   content="Devendra's Shop" />
  <meta name="twitter:card"       content="summary_large_image" />

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
      <div class="search-suggest-wrap">
        <input type="text" name="search" placeholder="Search for clothes, styles, brands…" autocomplete="off"
               value="<?= e($_GET['search'] ?? '') ?>" id="search-input" />
        <div class="search-suggest-dropdown" id="search-suggest-dropdown"></div>
      </div>
      <button type="submit"><i class="fa fa-search"></i> Search</button>
    </form>
    <button class="search-close" id="search-close"><i class="fa fa-times"></i></button>
  </div>

  <script>
  (function(){
    var inp   = document.getElementById('search-input');
    var drop  = document.getElementById('search-suggest-dropdown');
    var timer = null;
    if(!inp||!drop) return;

    function fmtPrice(n){ return '\u20b9' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,','); }

    function showDropdown(results){
      if(!results.length){ drop.innerHTML=''; drop.classList.remove('open'); return; }
      var html = '';
      results.forEach(function(p){
        var href = (window.SITE_URL||'') + '/pages/product.php?id=' + p.id;
        html += '<a href="' + href + '" class="ss-item">' +
          '<img src="' + p.image + '" alt="' + p.name + '" class="ss-thumb"/>' +
          '<div class="ss-info">' +
            '<span class="ss-name">' + p.name + '</span>' +
            '<span class="ss-cat">' + (p.category_name||'') + '</span>' +
          '</div>' +
          '<span class="ss-price">' + fmtPrice(p.price) + '</span>' +
        '</a>';
      });
      html += '<a href="' + (window.SITE_URL||'') + '/pages/shop.php?search=' + encodeURIComponent(inp.value) + '" class="ss-view-all">View all results <i class="fa fa-arrow-right"></i></a>';
      drop.innerHTML = html;
      drop.classList.add('open');
    }

    inp.addEventListener('keyup', function(e){
      if(e.key==='Escape'){ drop.innerHTML=''; drop.classList.remove('open'); return; }
      clearTimeout(timer);
      var q = inp.value.trim();
      if(q.length < 2){ drop.innerHTML=''; drop.classList.remove('open'); return; }
      timer = setTimeout(function(){
        fetch((window.SITE_URL||'') + '/pages/search_suggest.php?q=' + encodeURIComponent(q))
          .then(function(r){ return r.json(); })
          .then(showDropdown)
          .catch(function(){}); 
      }, 300);
    });

    document.addEventListener('click', function(e){
      if(!inp.contains(e.target) && !drop.contains(e.target)){
        drop.innerHTML=''; drop.classList.remove('open');
      }
    });
  })();
  </script>

  <!-- Mobile Overlay -->
  <div class="nav-overlay" id="nav-overlay"></div>
</nav>

<!-- Spacer for fixed navbar -->
<div class="nav-spacer"></div>
