<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Shop — Devendra's Shop";

$page       = max(1, (int)($_GET['page'] ?? 1));
$limit      = 12;
$offset     = ($page - 1) * $limit;
$categories = getCategories();

$filters = [];
if (!empty($_GET['category']))  $filters['category_id'] = (int)$_GET['category'];
if (!empty($_GET['search']))    $filters['search']      = $_GET['search'];
if (!empty($_GET['tag']))       $filters['tag']         = $_GET['tag'];
if (!empty($_GET['max_price'])) $filters['max_price']   = (float)$_GET['max_price'];

$products = getProducts($limit, $offset, $filters);

// Total count for pagination
$whereArr = ["p.status='active'"]; $params = [];
if (!empty($filters['category_id'])) { $whereArr[] = "p.category_id=?"; $params[] = $filters['category_id']; }
if (!empty($filters['search']))      { $whereArr[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
if (!empty($filters['tag']))         { $whereArr[] = "p.tags LIKE ?"; $params[] = '%'.$filters['tag'].'%'; }
$total = dbFetchOne("SELECT COUNT(*) AS cnt FROM products p WHERE " . implode(' AND ', $whereArr), $params)['cnt'];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= !empty($_GET['search']) ? 'Search: ' . e($_GET['search']) : 'All Products' ?></h1>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Shop</nav>
  </div>
</section>

<div class="container">
  <div class="shop-layout">

  <!-- Mobile Filter Toggle -->
  <button class="btn btn-outline shop-sidebar-toggle" id="filter-toggle"
          style="display:none;width:100%;margin-bottom:16px;justify-content:center">
    <i class="fa fa-sliders-h"></i> Show / Hide Filters
  </button>

    <!-- SIDEBAR -->
    <aside class="shop-sidebar" id="shop-sidebar">
      <form method="GET" action="" id="filter-form">
        <div class="sidebar-section">
          <div class="sidebar-title">Categories</div>
          <div class="sidebar-item"><label><input type="radio" name="category" value=""> All Categories</label></div>
          <?php foreach ($categories as $cat): ?>
          <div class="sidebar-item">
            <label>
              <input type="radio" name="category" value="<?= $cat['id'] ?>" <?= (($_GET['category'] ?? '') == $cat['id']) ? 'checked' : '' ?>>
              <?= e($cat['name']) ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="sidebar-section">
          <div class="sidebar-title">Filter by Tag</div>
          <?php foreach (['new'=>'New Arrivals','sale'=>'On Sale','featured'=>'Featured','hot'=>'Hot'] as $tag => $label): ?>
          <div class="sidebar-item">
            <label><input type="radio" name="tag" value="<?= $tag ?>" <?= (($_GET['tag'] ?? '') === $tag) ? 'checked' : '' ?>><?= $label ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="sidebar-section">
          <div class="sidebar-title">Max Price: <span id="price-val">₹<?= $_GET['max_price'] ?? 5000 ?></span></div>
          <input type="range" id="price-range" name="max_price" min="200" max="5000" step="100"
                 value="<?= (int)($_GET['max_price'] ?? 5000) ?>" style="width:100%;accent-color:#c9a96e"/>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%"><i class="fa fa-filter"></i> Apply Filters</button>
        <a href="<?= SITE_URL ?>/pages/shop.php" style="display:block;text-align:center;margin-top:10px;font-size:.85rem;color:#888">Clear Filters</a>
      </form>
    </aside>

    <!-- MAIN -->
    <div class="shop-main">
      <div class="shop-toolbar">
        <div class="shop-results"><?= $total ?> product<?= $total != 1 ? 's' : '' ?> found</div>
        <div class="shop-sort">
          <select onchange="window.location.href=this.value">
            <option value="?<?= http_build_query(array_merge($_GET, ['sort'=>'newest'])) ?>">Newest First</option>
            <option value="?<?= http_build_query(array_merge($_GET, ['sort'=>'price_asc'])) ?>">Price: Low to High</option>
            <option value="?<?= http_build_query(array_merge($_GET, ['sort'=>'price_desc'])) ?>">Price: High to Low</option>
          </select>
        </div>
      </div>

      <?php if (empty($products)): ?>
        <div style="text-align:center;padding:60px 20px">
          <i class="fa fa-search" style="font-size:3rem;color:#ddd;margin-bottom:16px;display:block"></i>
          <h3>No products found</h3>
          <p style="color:#888;margin-top:8px">Try adjusting your filters.</p>
        </div>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $p):
            $displayPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
            $hasDiscount  = $p['sale_price'] > 0;
          ?>
          <div class="product-card animate-on-scroll">
            <div class="product-img-wrap">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
              </a>
              <?php if (strpos($p['tags'] ?? '','new') !== false): ?><span class="product-badge badge-new">New</span><?php endif; ?>
              <?php if ($hasDiscount): ?><span class="product-badge badge-sale" style="top:40px">Sale</span><?php endif; ?>
              <div class="product-actions">
                <button class="product-action-btn" title="Wishlist"><i class="fa fa-heart"></i></button>
                <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn" title="View"><i class="fa fa-eye"></i></a>
              </div>
            </div>
            <div class="product-info">
              <div class="product-category"><?= e($p['category_name'] ?? '') ?></div>
              <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a></div>
              <div class="product-price">
                <span class="price-current <?= $hasDiscount ? 'price-sale' : '' ?>"><?= formatPrice($displayPrice) ?></span>
                <?php if ($hasDiscount): ?><span class="price-original"><?= formatPrice($p['price']) ?></span><?php endif; ?>
              </div>
              <form class="add-to-cart-form">
                <input type="hidden" name="action" value="add"/>
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
                <input type="hidden" name="size" value="M"/><input type="hidden" name="color" value=""/><input type="hidden" name="quantity" value="1"/>
                <button type="submit" class="btn-add-cart"><i class="fa fa-shopping-bag"></i> Add to Cart</button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?= pagination($total, $limit, $page, '?' . http_build_query(array_merge(array_filter($_GET), ['page'=>'']))) ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
