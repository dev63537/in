<?php
// pages/shop.php — Enhanced Shop Listing with Grid/List View + Advanced Filters
require_once __DIR__ . '/../includes/functions.php';
startSession();
$pageTitle = "Shop — Devendra's Shop";
$metaDesc  = "Browse our full collection of premium fashion for men, women and kids.";

// ── Filters ────────────────────────────────────────────────────────────────
$page      = max(1, (int)($_GET['page']     ?? 1));
$limit     = 12;
$offset    = ($page - 1) * $limit;
$search    = trim($_GET['search']   ?? '');
$catId     = (int)($_GET['category'] ?? 0);
$brandId   = (int)($_GET['brand']    ?? 0);
$gender    = $_GET['gender']   ?? '';
$tag       = $_GET['tag']      ?? '';
$minPrice  = (float)($_GET['min_price'] ?? 0);
$maxPrice  = (float)($_GET['max_price'] ?? 0);
$sortBy    = $_GET['sort'] ?? 'newest';
$view      = in_array($_GET['view'] ?? 'grid', ['grid','list']) ? ($_GET['view'] ?? 'grid') : 'grid';

// Track recently viewed from session (will be populated by quick_view.php and product.php)
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// ── Build WHERE ──────────────────────────────────────────────────────────
$where  = ["p.status = 'active'"];
$params = [];

if ($search)   { $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catId)    { $where[] = "p.category_id = ?";  $params[] = $catId; }
if ($brandId)  { $where[] = "p.brand_id = ?";     $params[] = $brandId; }
if ($gender)   { $where[] = "p.gender = ?";       $params[] = $gender; }
if ($tag)      { $where[] = "p.tags LIKE ?";      $params[] = "%$tag%"; }
if ($minPrice) { $where[] = "p.price >= ?";       $params[] = $minPrice; }
if ($maxPrice) { $where[] = "p.price <= ?";       $params[] = $maxPrice; }

$whereSQL = implode(' AND ', $where);

// Sort
switch ($sortBy) {
    case 'price_asc':  $order = 'p.price ASC';       break;
    case 'price_desc': $order = 'p.price DESC';      break;
    case 'name_asc':   $order = 'p.name ASC';        break;
    case 'popular':    $order = 'p.is_best_seller DESC, p.created_at DESC'; break;
    default:           $order = 'p.created_at DESC'; break;
}

$total    = (int)(dbFetchOne("SELECT COUNT(*) AS cnt FROM products p WHERE $whereSQL", $params)['cnt'] ?? 0);
$products = dbFetchAll("SELECT p.*, c.name AS category_name, b.name AS brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b     ON p.brand_id    = b.id
    WHERE $whereSQL
    ORDER BY $order
    LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset]));

$categories = getCategories();
$brands     = dbFetchAll("SELECT id, name FROM brands WHERE status='active' ORDER BY name");

// Active category name for breadcrumb
$activeCat   = $catId   ? dbFetchOne("SELECT name FROM categories WHERE id=?",[$catId])   : null;
$activeBrand = $brandId ? dbFetchOne("SELECT name FROM brands WHERE id=?",[$brandId])      : null;

// Recently Viewed products (up to 4, excluding products on current page to avoid obvious overlap)
$recentlyViewed = [];
if (!empty($_SESSION['recently_viewed'])) {
    $rvIds = array_slice($_SESSION['recently_viewed'], 0, 4);
    if (!empty($rvIds)) {
        $placeholders = implode(',', array_fill(0, count($rvIds), '?'));
        $recentlyViewed = dbFetchAll(
            "SELECT p.id, p.name, p.price, p.sale_price, p.image, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id IN ($placeholders) AND p.status = 'active'",
            $rvIds
        );
        // Preserve order from session
        $rvOrder = array_flip($rvIds);
        usort($recentlyViewed, fn($a, $b) => ($rvOrder[$a['id']] ?? 99) - ($rvOrder[$b['id']] ?? 99));
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1><?= $search ? 'Search: ' . e($search) : ($activeCat ? e($activeCat['name']) : 'All Products') ?></h1>
    <nav class="breadcrumb">
      <a href="<?= SITE_URL ?>/index.php">Home</a> /
      <?php if ($activeCat): ?>
        <a href="<?= SITE_URL ?>/pages/shop.php">Shop</a> / <?= e($activeCat['name']) ?>
      <?php else: ?>
        Shop
      <?php endif; ?>
    </nav>
  </div>
</section>

<div class="container">
  <div class="shop-layout">

    <!-- SIDEBAR FILTERS -->
    <aside class="shop-sidebar">
      <form method="GET" action="" id="filter-form">
        <?php if ($view !== 'grid'): ?><input type="hidden" name="view" value="<?= e($view) ?>"/><?php endif; ?>

        <!-- Search -->
        <div class="sidebar-section">
          <div class="sidebar-title">Search</div>
          <div style="position:relative">
            <input type="text" name="search" value="<?= e($search) ?>"
                   placeholder="Search products…"
                   style="width:100%;padding:9px 12px 9px 36px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;font-size:.88rem;outline:none"/>
            <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.85rem"></i>
          </div>
        </div>

        <!-- Categories -->
        <div class="sidebar-section">
          <div class="sidebar-title">Categories</div>
          <div class="sidebar-item"><label><input type="radio" name="category" value="" <?= !$catId?'checked':'' ?>> All Categories</label></div>
          <?php foreach ($categories as $cat): ?>
            <div class="sidebar-item">
              <label>
                <input type="radio" name="category" value="<?= $cat['id'] ?>" <?= $catId==$cat['id']?'checked':'' ?>>
                <?= e($cat['name']) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Brands -->
        <?php if (!empty($brands)): ?>
        <div class="sidebar-section">
          <div class="sidebar-title">Brand</div>
          <div class="sidebar-item"><label><input type="radio" name="brand" value="" <?= !$brandId?'checked':'' ?>> All Brands</label></div>
          <?php foreach ($brands as $b): ?>
            <div class="sidebar-item">
              <label><input type="radio" name="brand" value="<?= $b['id'] ?>" <?= $brandId==$b['id']?'checked':'' ?>> <?= e($b['name']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Gender -->
        <div class="sidebar-section">
          <div class="sidebar-title">Gender</div>
          <?php foreach ([''=> 'All','men'=>'Men','women'=>'Women','kids'=>'Kids','unisex'=>'Unisex'] as $v => $l): ?>
          <div class="sidebar-item">
            <label><input type="radio" name="gender" value="<?= $v ?>" <?= $gender===$v?'checked':'' ?>> <?= $l ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Tags -->
        <div class="sidebar-section">
          <div class="sidebar-title">Tags</div>
          <?php foreach ([''=> 'All','new'=>'New Arrivals','sale'=>'On Sale','featured'=>'Featured','hot'=>'Hot Picks'] as $v => $l): ?>
          <div class="sidebar-item">
            <label><input type="radio" name="tag" value="<?= $v ?>" <?= $tag===$v?'checked':'' ?>> <?= $l ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Price Range -->
        <div class="sidebar-section">
          <div class="sidebar-title">Price: <span id="price-val">
            <?= $maxPrice ? '₹' . (int)$minPrice . ' – ₹' . (int)$maxPrice : 'Any' ?>
          </span></div>
          <div style="display:flex;gap:8px;margin-bottom:10px">
            <input type="number" name="min_price" placeholder="Min ₹" value="<?= $minPrice ?: '' ?>"
                   style="width:50%;padding:7px 10px;border:1.5px solid #e0e0e0;border-radius:6px;font-family:inherit;font-size:.83rem;outline:none"/>
            <input type="number" name="max_price" placeholder="Max ₹" value="<?= $maxPrice ?: '' ?>"
                   style="width:50%;padding:7px 10px;border:1.5px solid #e0e0e0;border-radius:6px;font-family:inherit;font-size:.83rem;outline:none"/>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
          <i class="fa fa-filter"></i> Apply Filters
        </button>
        <?php if ($search||$catId||$brandId||$gender||$tag||$minPrice||$maxPrice): ?>
        <a href="<?= SITE_URL ?>/pages/shop.php" style="display:block;text-align:center;margin-top:10px;font-size:.83rem;color:#888">
          <i class="fa fa-times"></i> Clear All Filters
        </a>
        <?php endif; ?>
      </form>
    </aside><!-- /sidebar -->

    <!-- MAIN -->
    <div class="shop-main">

      <!-- Toolbar -->
      <div class="shop-toolbar">
        <div class="shop-results">
          <?= $total ?> product<?= $total!=1?'s':'' ?> found
          <?php if ($search): ?><span style="color:var(--gray)"> for "<?= e($search) ?>"</span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <!-- View Toggle -->
          <div class="shop-view-toggle">
            <button type="button" class="view-btn <?= $view==='grid'?'active':'' ?>"
                    onclick="setView('grid')" title="Grid View">
              <i class="fa fa-th"></i>
            </button>
            <button type="button" class="view-btn <?= $view==='list'?'active':'' ?>"
                    onclick="setView('list')" title="List View">
              <i class="fa fa-list"></i>
            </button>
          </div>
          <!-- Sort -->
          <select class="pm-filter-select" onchange="applySort(this.value)">
            <option value="newest"     <?= $sortBy==='newest'?    'selected':'' ?>>Newest First</option>
            <option value="price_asc"  <?= $sortBy==='price_asc'? 'selected':'' ?>>Price: Low → High</option>
            <option value="price_desc" <?= $sortBy==='price_desc'?'selected':'' ?>>Price: High → Low</option>
            <option value="name_asc"   <?= $sortBy==='name_asc'?  'selected':'' ?>>Name A–Z</option>
            <option value="popular"    <?= $sortBy==='popular'?   'selected':'' ?>>Most Popular</option>
          </select>
        </div>
      </div>

      <!-- Active Filter Chips -->
      <?php
      $chips = [];
      if ($search)   $chips[] = ['Search: ' . e($search),   'search'];
      if ($catId && $activeCat) $chips[] = [e($activeCat['name']), 'category'];
      if ($brandId && $activeBrand) $chips[] = [e($activeBrand['name']), 'brand'];
      if ($gender)   $chips[] = [ucfirst($gender), 'gender'];
      if ($tag)      $chips[] = [ucfirst($tag), 'tag'];
      if ($minPrice||$maxPrice) $chips[] = ['₹'.($minPrice?:0).' – ₹'.($maxPrice?:5000), 'price'];
      ?>
      <?php if (!empty($chips)): ?>
      <div class="active-filters">
        <?php foreach ($chips as $chip): ?>
          <span class="filter-chip"><?= $chip[0] ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Products -->
      <?php if (empty($products)): ?>
        <div style="text-align:center;padding:80px 20px;color:var(--gray)">
          <i class="fa fa-search" style="font-size:3.5rem;display:block;margin-bottom:16px;opacity:.3"></i>
          <h3 style="margin-bottom:8px">No products found</h3>
          <p>Try adjusting your search or filters.</p>
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">Clear Filters</a>
        </div>

      <?php elseif ($view === 'list'): ?>
        <!-- LIST VIEW -->
        <div class="product-list-grid">
          <?php foreach ($products as $p):
            $displayPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
            $hasDiscount  = $p['sale_price'] > 0;
            $discountPct  = $hasDiscount ? round((1 - $p['sale_price']/$p['price'])*100) : 0;
          ?>
          <div class="product-list-card">
            <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
              <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"
                   style="width:140px;height:170px;object-fit:cover;border-radius:10px"/>
            </a>
            <div>
              <div style="font-size:.78rem;color:var(--gray);margin-bottom:4px"><?= e($p['category_name']??'') ?></div>
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>"
                 style="font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:600;color:var(--dark)">
                <?= e($p['name']) ?>
              </a>
              <?php if (!empty($p['short_description'])): ?>
                <p style="color:#666;font-size:.88rem;margin:8px 0;line-height:1.6"><?= e(substr($p['short_description'],0,120)) ?>…</p>
              <?php endif; ?>
              <div style="display:flex;align-items:baseline;gap:10px;margin-top:8px">
                <span style="font-size:1.2rem;font-weight:700;<?= $hasDiscount?'color:#e74c3c':'' ?>"><?= formatPrice($displayPrice) ?></span>
                <?php if ($hasDiscount): ?>
                  <span style="text-decoration:line-through;color:var(--gray);font-size:.9rem"><?= formatPrice($p['price']) ?></span>
                  <span style="background:#e74c3c;color:#fff;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:50px"><?= $discountPct ?>% OFF</span>
                <?php endif; ?>
              </div>
              <?php if (!empty($p['sizes'])): ?>
                <div style="margin-top:8px;display:flex;gap:5px;flex-wrap:wrap">
                  <?php foreach (array_slice(explode(',', $p['sizes']), 0, 5) as $sz): ?>
                    <span style="padding:2px 8px;border:1px solid #ddd;border-radius:4px;font-size:.73rem"><?= e(trim($sz)) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
              <form class="add-to-cart-form">
                <input type="hidden" name="action" value="add"/>
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
                <input type="hidden" name="size" value="M"/>
                <input type="hidden" name="color" value=""/>
                <input type="hidden" name="quantity" value="1"/>
                <button type="submit" class="btn btn-primary" style="padding:9px 16px;font-size:.83rem;white-space:nowrap">
                  <i class="fa fa-shopping-bag"></i> Add to Cart
                </button>
              </form>
              <button class="btn btn-outline" style="padding:9px 16px;font-size:.83rem"
                      onclick="openQuickView(<?= $p['id'] ?>)">
                <i class="fa fa-eye"></i> Quick View
              </button>
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="btn btn-outline" style="padding:9px 16px;font-size:.83rem">
                View Details
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <!-- GRID VIEW -->
        <div class="product-grid">
          <?php foreach ($products as $p):
            $displayPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
            $hasDiscount  = $p['sale_price'] > 0;
            $discountPct  = $hasDiscount ? round((1 - $p['sale_price']/$p['price'])*100) : 0;
            $isNew        = strpos($p['tags']??'','new') !== false || !empty($p['is_new_arrival']);
          ?>
          <div class="product-card animate-on-scroll">
            <div class="product-img-wrap">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
              </a>
              <?php if ($isNew): ?>
                <span class="product-badge badge-new">New</span>
              <?php endif; ?>
              <?php if ($hasDiscount): ?>
                <!-- Discount % badge top-right -->
                <span class="discount-badge-overlay"><?= $discountPct ?>% OFF</span>
              <?php endif; ?>
              <div class="product-actions">
                <button class="product-action-btn" title="Wishlist"
                        onclick="event.preventDefault();toggleWishlistShop(this,<?= $p['id'] ?>)">
                  <i class="far fa-heart"></i>
                </button>
                <button class="product-action-btn" title="Quick View"
                        onclick="event.preventDefault();openQuickView(<?= $p['id'] ?>)">
                  <i class="fa fa-eye"></i>
                </button>
                <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn" title="View Full Details">
                  <i class="fa fa-external-link-alt"></i>
                </a>
              </div>
              <!-- Quick View button visible on hover -->
              <button class="quick-view-hover-btn" onclick="openQuickView(<?= $p['id'] ?>)">
                <i class="fa fa-eye"></i> Quick View
              </button>
            </div>
            <div class="product-info">
              <div class="product-category"><?= e($p['category_name']??'') ?></div>
              <div class="product-name">
                <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a>
              </div>
              <div class="product-price">
                <span class="price-current <?= $hasDiscount?'price-sale':'' ?>"><?= formatPrice($displayPrice) ?></span>
                <?php if ($hasDiscount): ?>
                  <span class="price-original"><?= formatPrice($p['price']) ?></span>
                <?php endif; ?>
              </div>
              <form class="add-to-cart-form">
                <input type="hidden" name="action" value="add"/>
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
                <input type="hidden" name="size" value="M"/>
                <input type="hidden" name="color" value=""/>
                <input type="hidden" name="quantity" value="1"/>
                <button type="submit" class="btn-add-cart"><i class="fa fa-shopping-bag"></i> Add to Cart</button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Pagination -->
      <?php if ($total > $limit):
        $qs = http_build_query(array_filter(
          ['search'=>$search,'category'=>$catId?:null,'brand'=>$brandId?:null,
           'gender'=>$gender,'tag'=>$tag,'sort'=>$sortBy,'view'=>$view,
           'min_price'=>$minPrice?:null,'max_price'=>$maxPrice?:null]
        ));
        echo pagination($total, $limit, $page, '?' . ($qs ? $qs . '&' : ''));
      endif; ?>

    </div><!-- /shop-main -->
  </div><!-- /shop-layout -->
</div><!-- /container -->

<!-- ===== RECENTLY VIEWED STRIP ===== -->
<?php if (!empty($recentlyViewed)): ?>
<section class="recently-viewed-section">
  <div class="container">
    <div class="section-header" style="margin-bottom:30px">
      <h2><i class="fa fa-history" style="color:var(--primary);font-size:1.4rem;vertical-align:middle;margin-right:8px"></i>Recently Viewed</h2>
    </div>
    <div class="rv-grid">
      <?php foreach ($recentlyViewed as $rv):
        $rvDisplay   = $rv['sale_price'] > 0 ? $rv['sale_price'] : $rv['price'];
        $rvDiscount  = $rv['sale_price'] > 0;
        $rvPct       = $rvDiscount ? round((1 - $rv['sale_price']/$rv['price'])*100) : 0;
      ?>
      <div class="rv-card">
        <div class="rv-img-wrap">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rv['id'] ?>">
            <img src="<?= e($rv['image']) ?>" alt="<?= e($rv['name']) ?>" loading="lazy"/>
          </a>
          <?php if ($rvDiscount): ?>
            <span class="discount-badge-overlay"><?= $rvPct ?>% OFF</span>
          <?php endif; ?>
        </div>
        <div class="rv-info">
          <div class="rv-cat"><?= e($rv['category_name']??'') ?></div>
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rv['id'] ?>" class="rv-name"><?= e($rv['name']) ?></a>
          <div class="rv-price">
            <span class="rv-price-current <?= $rvDiscount?'price-sale':'' ?>"><?= formatPrice($rvDisplay) ?></span>
            <?php if ($rvDiscount): ?><span class="rv-price-original"><?= formatPrice($rv['price']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== QUICK VIEW MODAL ===== -->
<div id="quick-view-overlay" class="qv-overlay" onclick="closeQuickView(event)" aria-modal="true" role="dialog" aria-label="Quick View">
  <div class="qv-modal" id="quick-view-modal">
    <button class="qv-close" onclick="closeQuickViewBtn()" aria-label="Close">&times;</button>
    <div class="qv-body" id="qv-body">
      <!-- content injected by JS -->
      <div class="qv-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    </div>
  </div>
</div>

<script>
function setView(v) {
  var url = new URL(window.location.href);
  url.searchParams.set('view', v);
  window.location.href = url.toString();
}

function applySort(s) {
  var url = new URL(window.location.href);
  url.searchParams.set('sort', s);
  url.searchParams.set('page', '1');
  window.location.href = url.toString();
}

function toggleWishlistShop(btn, pid) {
  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=wishlist_add&product_id='+pid
  }).then(r=>r.json()).then(data=>{
    if(data.success){
      var icon = btn.querySelector('i');
      icon.className = 'fas fa-heart';
      icon.style.color = '#e74c3c';
    }
  }).catch(()=>{});
}

// ── Quick View ──────────────────────────────────────────────
function openQuickView(pid) {
  var overlay = document.getElementById('quick-view-overlay');
  var body    = document.getElementById('qv-body');
  body.innerHTML = '<div class="qv-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>';
  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';

  fetch('<?= SITE_URL ?>/pages/quick_view.php?id=' + pid)
    .then(r => r.json())
    .then(p => {
      if (p.error) { body.innerHTML = '<p style="color:#e74c3c;text-align:center;padding:40px">' + p.error + '</p>'; return; }
      var salePrice = parseFloat(p.sale_price);
      var price     = parseFloat(p.price);
      var hasDisc   = salePrice > 0;
      var dispPrice = hasDisc ? salePrice : price;
      var discPct   = hasDisc ? Math.round((1 - salePrice/price)*100) : 0;

      // Format price helper
      function fmt(n){ return '₹' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,','); }

      // Sizes HTML
      var sizesHtml = '';
      if (p.sizes && p.sizes.length) {
        sizesHtml = '<div class="qv-label">Select Size</div><div class="qv-opts">';
        p.sizes.forEach(function(s){
          sizesHtml += '<button type="button" class="qv-size-btn" onclick="qvSelectSize(this)">' + s + '</button>';
        });
        sizesHtml += '</div>';
      }

      // Colors HTML
      var colorsHtml = '';
      if (p.colors && p.colors.length) {
        colorsHtml = '<div class="qv-label">Select Color</div><div class="qv-opts">';
        p.colors.forEach(function(c){
          colorsHtml += '<button type="button" class="qv-color-btn" style="background:' + c + '" title="' + c + '" onclick="qvSelectColor(this)"></button>';
        });
        colorsHtml += '</div>';
      }

      body.innerHTML =
        '<div class="qv-inner">' +
          '<div class="qv-img-col">' +
            '<img src="' + p.image + '" alt="' + p.name + '" class="qv-product-img"/>' +
          '</div>' +
          '<div class="qv-info-col">' +
            '<div class="qv-cat">' + (p.category_name||'') + '</div>' +
            '<h2 class="qv-name">' + p.name + '</h2>' +
            '<div class="qv-price-row">' +
              '<span class="qv-price-current' + (hasDisc?' qv-price-sale':'') + '">' + fmt(dispPrice) + '</span>' +
              (hasDisc ? '<span class="qv-price-original">' + fmt(price) + '</span><span class="qv-disc-badge">' + discPct + '% OFF</span>' : '') +
            '</div>' +
            (p.short_description ? '<p class="qv-desc">' + p.short_description + '</p>' : '') +
            sizesHtml +
            colorsHtml +
            '<input type="hidden" id="qv-selected-size" value=""/>' +
            '<input type="hidden" id="qv-selected-color" value=""/>' +
            '<div class="qv-action-row">' +
              '<button class="btn btn-primary qv-atc-btn" onclick="qvAddToCart(' + p.id + ')">' +
                '<i class="fa fa-shopping-bag"></i> Add to Cart' +
              '</button>' +
              '<a href="<?= SITE_URL ?>/pages/product.php?id=' + p.id + '" class="btn btn-outline">' +
                '<i class="fa fa-external-link-alt"></i> Full Details' +
              '</a>' +
            '</div>' +
          '</div>' +
        '</div>';
    })
    .catch(function(){ body.innerHTML = '<p style="color:#e74c3c;text-align:center;padding:40px">Failed to load product.</p>'; });
}

function closeQuickView(e) {
  if (e.target === document.getElementById('quick-view-overlay')) closeQuickViewBtn();
}
function closeQuickViewBtn() {
  document.getElementById('quick-view-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeQuickViewBtn(); });

function qvSelectSize(btn) {
  btn.closest('.qv-opts').querySelectorAll('.qv-size-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('qv-selected-size').value = btn.textContent;
}
function qvSelectColor(btn) {
  btn.closest('.qv-opts').querySelectorAll('.qv-color-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('qv-selected-color').value = btn.title;
}

function qvAddToCart(pid) {
  var size  = (document.getElementById('qv-selected-size')  || {}).value || 'M';
  var color = (document.getElementById('qv-selected-color') || {}).value || '';
  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'action=add&product_id='+pid+'&size='+encodeURIComponent(size)+'&color='+encodeURIComponent(color)+'&quantity=1'
  }).then(r=>r.json()).then(function(d){
    if(d.success){
      closeQuickViewBtn();
      // Show brief toast
      var t = document.createElement('div');
      t.className = 'toast';
      t.innerHTML = '<i class="fa fa-check-circle" style="color:#27ae60"></i> Added to cart!';
      document.body.appendChild(t);
      setTimeout(()=>t.classList.add('show'),10);
      setTimeout(()=>{ t.classList.remove('show'); setTimeout(()=>t.remove(),400); },2500);
      // Update cart badge
      if(d.cart_count !== undefined){
        var badge = document.querySelector('.cart-badge');
        if(badge) badge.textContent = d.cart_count;
        else {
          var cb = document.querySelector('.cart-btn');
          if(cb){ var nb=document.createElement('span'); nb.className='cart-badge'; nb.textContent=d.cart_count; cb.appendChild(nb); }
        }
      }
    }
  }).catch(()=>{});
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
