<?php
// pages/product.php — Enhanced Product Detail
require_once __DIR__ . '/../includes/functions.php';
startSession();

$id = (int)($_GET['id'] ?? 0);
$product = dbFetchOne(
    "SELECT p.*, c.name AS category_name, b.name AS brand_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN brands b     ON p.brand_id    = b.id
     WHERE p.id = ? AND p.status = 'active'",
    [$id]
);

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(SITE_URL . '/pages/shop.php');
}

// ── Gallery images (product_images table + fallback to gallery column) ──
$galleryRows = dbFetchAll(
    "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order",
    [$id]
);
$galleryLegacy = !empty($product['gallery']) ? explode(',', $product['gallery']) : [];

// ── Reviews ──
$reviews   = dbFetchAll(
    "SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC",
    [$id]
);
$avgRating = count($reviews)
    ? array_sum(array_column($reviews, 'rating')) / count($reviews)
    : 0;

// ── Related products (same category, exclude current) ──
$related = dbFetchAll(
    "SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
     ORDER BY RAND() LIMIT 4",
    [$product['category_id'] ?? 0, $id]
);

// ── Recently viewed (session) ──
if (!isset($_SESSION['recently_viewed'])) $_SESSION['recently_viewed'] = [];
$_SESSION['recently_viewed'] = array_diff($_SESSION['recently_viewed'], [$id]);
array_unshift($_SESSION['recently_viewed'], $id);
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);

// ── Sizes & Colours ──
$sizes  = array_filter(array_map('trim', explode(',', $product['sizes']  ?? '')));
$colors = array_filter(array_map('trim', explode(',', $product['colors'] ?? '')));
if (empty($sizes))  $sizes  = ['XS', 'S', 'M', 'L', 'XL'];
if (empty($colors)) $colors = ['Black', 'White'];

// ── Pricing ──
$displayPrice = ($product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
$hasDiscount  = $product['sale_price'] > 0;
$discountPct  = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

// ── Wishlist ──
$inWishlist = function_exists('isInWishlist') ? isInWishlist($id) : false;

// ── Stock status ──
$stockStatus = 'in_stock';
if ($product['stock'] == 0) {
    $stockStatus = 'out_stock';
} elseif ($product['stock'] <= ($product['low_stock_alert'] ?? 5)) {
    $stockStatus = 'low_stock';
}

// ── SEO ──
$pageTitle = e($product['meta_title'] ?: $product['name'] . " — Devendra's Shop");
$metaDesc  = e($product['meta_description']
    ?: $product['short_description']
    ?: substr(strip_tags($product['description'] ?? ''), 0, 160));

include __DIR__ . '/../includes/header.php';
?>

<!-- ── Breadcrumb ── -->
<section class="page-hero" style="padding:28px 0">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= SITE_URL ?>/index.php">Home</a> /
      <a href="<?= SITE_URL ?>/pages/shop.php">Shop</a> /
      <?php if ($product['category_name']): ?>
        <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>">
          <?= e($product['category_name']) ?>
        </a> /
      <?php endif; ?>
      <?= e($product['name']) ?>
    </nav>
  </div>
</section>

<div class="container">
  <div class="pd-layout">

    <!-- ════════════════════════════════════
         GALLERY
         ════════════════════════════════════ -->
    <div class="pd-gallery">

      <!-- Main image -->
      <div class="pd-main-img-wrap">
        <img src="<?= e($product['image'] ?: 'https://via.placeholder.com/600x720?text=No+Image') ?>"
             alt="<?= e($product['name']) ?>"
             id="pd-main-img"/>
      </div>

      <!-- Thumbnails -->
      <div class="pd-thumbs">
        <?php if ($product['image']): ?>
          <img src="<?= e($product['image']) ?>"
               class="pd-thumb active"
               onclick="swapImage(this,'<?= e($product['image']) ?>')"
               alt="Main"/>
        <?php endif; ?>
        <?php foreach ($galleryRows as $img):
            $gSrc = (strpos($img['image_path'], 'http') === 0) ? $img['image_path'] : UPLOAD_URL . $img['image_path'];
        ?>
          <img src="<?= e($gSrc) ?>"
               class="pd-thumb"
               onclick="swapImage(this,'<?= e($gSrc) ?>')"
               alt="<?= e($img['alt_text'] ?? $product['name']) ?>"/>
        <?php endforeach; ?>
        <?php foreach ($galleryLegacy as $gsrc): ?>
          <?php if ($gsrc && $gsrc !== $product['image']): ?>
          <img src="<?= e($gsrc) ?>"
               class="pd-thumb"
               onclick="swapImage(this,'<?= e($gsrc) ?>')"
               alt="<?= e($product['name']) ?>"/>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

    </div><!-- /pd-gallery -->

    <!-- ════════════════════════════════════
         PRODUCT INFO
         ════════════════════════════════════ -->
    <div class="pd-info">

      <!-- Badges -->
      <div class="pd-badge-row">
        <?php if (!empty($product['is_new_arrival']) || str_contains($product['tags'] ?? '', 'new')): ?>
          <span class="pd-badge pd-badge-new"><i class="fa fa-bolt"></i> New Arrival</span>
        <?php endif; ?>
        <?php if ($hasDiscount): ?>
          <span class="pd-badge pd-badge-sale"><i class="fa fa-tag"></i> Sale</span>
        <?php endif; ?>
        <?php if (!empty($product['is_featured']) || !empty($product['featured'])): ?>
          <span class="pd-badge pd-badge-featured"><i class="fa fa-star"></i> Featured</span>
        <?php endif; ?>
        <?php if (!empty($product['is_trending'])): ?>
          <span class="pd-badge pd-badge-trending"><i class="fa fa-fire"></i> Trending</span>
        <?php endif; ?>
      </div>

      <!-- Name -->
      <h1 class="pd-name"><?= e($product['name']) ?></h1>

      <!-- Rating summary -->
      <div class="pd-rating">
        <div class="pd-stars">
          <?php for ($s = 1; $s <= 5; $s++): ?>
            <i class="fa<?= $s <= round($avgRating) ? 's' : 'r' ?> fa-star"></i>
          <?php endfor; ?>
        </div>
        <span class="pd-rating-count">
          <?= number_format($avgRating, 1) ?>
          (<?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>)
        </span>
        <a href="#pd-reviews" onclick="openTab('pd-reviews');return false;"
           style="font-size:.82rem;color:var(--primary)">Write a review</a>
      </div>

      <!-- Price -->
      <div class="pd-price-row">
        <span class="pd-price-current <?= $hasDiscount ? 'on-sale' : '' ?>">
          <?= formatPrice($displayPrice) ?>
        </span>
        <?php if ($hasDiscount): ?>
          <span class="pd-price-original"><?= formatPrice($product['price']) ?></span>
          <span class="pd-discount-badge"><?= $discountPct ?>% OFF</span>
        <?php endif; ?>
      </div>

      <!-- Stock status -->
      <?php
        $stockLabels = [
          'in_stock'  => ['class' => 'pd-in-stock',  'icon' => 'fa-check-circle',       'txt' => 'In Stock'],
          'low_stock' => ['class' => 'pd-low-stock',  'icon' => 'fa-exclamation-circle', 'txt' => 'Only ' . (int)$product['stock'] . ' left'],
          'out_stock' => ['class' => 'pd-out-stock',  'icon' => 'fa-times-circle',       'txt' => 'Out of Stock'],
        ];
        $sl = $stockLabels[$stockStatus];
      ?>
      <div class="pd-stock-status <?= $sl['class'] ?>">
        <i class="fa <?= $sl['icon'] ?>"></i> <?= $sl['txt'] ?>
      </div>

      <!-- Short description -->
      <?php if (!empty($product['short_description'])): ?>
        <p style="color:#555;line-height:1.7;margin-bottom:20px">
          <?= e($product['short_description']) ?>
        </p>
      <?php endif; ?>

      <!-- Size Selection -->
      <?php if (!empty($sizes)): ?>
      <div>
        <div class="pd-section-label">
          Size <span id="selected-size-label" style="text-transform:none;color:var(--dark)"></span>
        </div>
        <div class="pd-size-opts" id="size-opts">
          <?php foreach ($sizes as $sz): ?>
            <button type="button" class="pd-size-btn"
                    data-size="<?= e($sz) ?>"
                    onclick="selectSize(this)"><?= e($sz) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Color Selection -->
      <?php if (!empty($colors)): ?>
      <div>
        <div class="pd-section-label">
          Color <span id="selected-color-label" style="text-transform:none;color:var(--dark)"></span>
        </div>
        <div class="pd-color-opts" id="color-opts">
          <?php
          $colorMap = [
            'black'      => '#1a1a1a', 'white'     => '#f5f5f5', 'red'     => '#e74c3c',
            'blue'       => '#2980b9', 'navy'      => '#1a3a5c', 'green'   => '#27ae60',
            'yellow'     => '#f1c40f', 'orange'    => '#e67e22', 'pink'    => '#fd79a8',
            'purple'     => '#8e44ad', 'grey'      => '#95a5a6', 'gray'    => '#95a5a6',
            'beige'      => '#d4b896', 'cream'     => '#f5f0e8', 'khaki'   => '#bba58f',
            'olive'      => '#8d8741', 'brown'     => '#795548', 'gold'    => '#c9a96e',
            'ivory'      => '#f5f0e0', 'maroon'    => '#800000', 'teal'    => '#008080',
            'cyan'       => '#00bcd4', 'magenta'   => '#e91e63', 'champagne'=>'#f7e7ce',
            'rose'       => '#ffb3ba', 'sky blue'  => '#87ceeb',
            'multicolor' => 'linear-gradient(135deg,#e74c3c,#3498db,#2ecc71)',
          ];
          foreach ($colors as $cl):
            $bgKey = strtolower(trim($cl));
            $bg    = $colorMap[$bgKey] ?? ('#' . substr(md5($cl), 0, 6));
          ?>
            <button type="button" class="pd-color-btn"
                    data-color="<?= e($cl) ?>"
                    style="background:<?= $bg ?>;"
                    title="<?= e($cl) ?>"
                    onclick="selectColor(this)"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Quantity -->
      <div class="pd-section-label" style="margin-bottom:10px">Quantity</div>
      <div class="pd-qty-wrap">
        <button type="button" class="pd-qty-btn" onclick="changeQty(-1)"><i class="fa fa-minus"></i></button>
        <input type="number" class="pd-qty-input" id="pd-qty"
               value="1" min="1" max="<?= max(1, (int)$product['stock']) ?>">
        <button type="button" class="pd-qty-btn" onclick="changeQty(1)"><i class="fa fa-plus"></i></button>
      </div>

      <!-- Action Buttons -->
      <div class="pd-action-row">
        <?php if ($stockStatus !== 'out_stock'): ?>
          <button type="button" class="btn btn-primary" id="atc-btn"
                  onclick="addToCartFromPage()"
                  data-product-id="<?= $id ?>">
            <i class="fa fa-shopping-bag"></i> Add to Cart
          </button>
          <button type="button" class="btn btn-dark" onclick="buyNow()">
            <i class="fa fa-bolt"></i> Buy Now
          </button>
        <?php else: ?>
          <button type="button" class="btn btn-outline" disabled
                  style="opacity:.5;flex:1;cursor:not-allowed">Out of Stock</button>
        <?php endif; ?>
      </div>

      <!-- Wishlist -->
      <button type="button" id="wishlist-btn"
              class="pd-wishlist-btn <?= $inWishlist ? 'wishlisted' : '' ?>"
              data-wishlist-toggle="<?= $id ?>"
              data-product-name="<?= e($product['name']) ?>">
        <i class="fa<?= $inWishlist ? 's' : 'r' ?> fa-heart"></i>
        <?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>
      </button>

      <!-- Meta -->
      <dl class="pd-meta-list" style="margin-top:24px">
        <?php if ($product['category_name']): ?>
          <div><dt>Category:</dt>
          <dd><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>"
                 style="color:var(--primary)"><?= e($product['category_name']) ?></a></dd></div>
        <?php endif; ?>
        <?php if (!empty($product['brand_name'])): ?>
          <div><dt>Brand:</dt><dd><?= e($product['brand_name']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($product['material'])): ?>
          <div><dt>Material:</dt><dd><?= e($product['material']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($product['sku'])): ?>
          <div><dt>SKU:</dt><dd><?= e($product['sku']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($product['tags'])): ?>
          <div><dt>Tags:</dt><dd><?= e($product['tags']) ?></dd></div>
        <?php endif; ?>
      </dl>

    </div><!-- /pd-info -->
  </div><!-- /pd-layout -->

  <!-- ════════════════════════════════════
       TABS
       ════════════════════════════════════ -->
  <div class="pd-tabs">
    <button type="button" class="pd-tab-btn active" data-tab="pd-desc">Description</button>
    <button type="button" class="pd-tab-btn" data-tab="pd-specs">Specifications</button>
    <button type="button" class="pd-tab-btn" data-tab="pd-reviews">
      Reviews (<?= count($reviews) ?>)
    </button>
  </div>

  <div class="pd-tab-panel active" id="pd-desc">
    <div style="max-width:800px;line-height:1.85;color:#444;font-size:.97rem">
      <?= nl2br(e($product['description'] ?: 'No description available.')) ?>
    </div>
  </div>

  <div class="pd-tab-panel" id="pd-specs">
    <table class="pd-specs-table" style="max-width:600px">
      <tbody>
        <?php if ($product['category_name']): ?><tr><td>Category</td><td><?= e($product['category_name']) ?></td></tr><?php endif; ?>
        <?php if (!empty($product['brand_name'])): ?><tr><td>Brand</td><td><?= e($product['brand_name']) ?></td></tr><?php endif; ?>
        <?php if (!empty($product['gender'])): ?><tr><td>Gender</td><td><?= ucfirst(e($product['gender'])) ?></td></tr><?php endif; ?>
        <?php if (!empty($product['material'])): ?><tr><td>Material</td><td><?= e($product['material']) ?></td></tr><?php endif; ?>
        <tr><td>Available Sizes</td><td><?= e(implode(', ', $sizes)) ?></td></tr>
        <tr><td>Available Colors</td><td><?= e(implode(', ', $colors)) ?></td></tr>
        <?php if (!empty($product['sku'])): ?><tr><td>SKU</td><td><?= e($product['sku']) ?></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pd-tab-panel" id="pd-reviews">
    <?php if (!empty($reviews)): ?>
      <?php foreach ($reviews as $rv): ?>
        <div class="pd-review-card">
          <div class="pd-review-stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <i class="fa<?= $s <= $rv['rating'] ? 's' : 'r' ?> fa-star"></i>
            <?php endfor; ?>
          </div>
          <p class="pd-review-text">"<?= e($rv['comment']) ?>"</p>
          <div class="pd-review-author">
            <strong><?= e($rv['reviewer_name']) ?></strong>
            &middot; <?= date('d M Y', strtotime($rv['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color:var(--gray)">No reviews yet. Be the first to review this product!</p>
    <?php endif; ?>
  </div>

  <!-- ════════════════════════════════════
       RELATED PRODUCTS
       ════════════════════════════════════ -->
  <?php if (!empty($related)): ?>
  <div style="margin:60px 0 40px">
    <div class="section-header" style="text-align:left;margin-bottom:30px">
      <h2 style="font-size:1.8rem">Related Products</h2>
    </div>
    <div class="product-grid">
      <?php foreach ($related as $rp):
        if ($rp['id'] == $id) continue;
        $rPrice = $rp['sale_price'] > 0 ? $rp['sale_price'] : $rp['price'];
      ?>
        <div class="product-card animate-on-scroll">
          <div class="product-img-wrap">
            <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rp['id'] ?>">
              <img src="<?= e($rp['image']) ?>" alt="<?= e($rp['name']) ?>" loading="lazy"/>
            </a>
          </div>
          <div class="product-info">
            <div class="product-category"><?= e($rp['category_name'] ?? '') ?></div>
            <div class="product-name">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rp['id'] ?>"><?= e($rp['name']) ?></a>
            </div>
            <div class="product-price">
              <span class="price-current"><?= formatPrice($rPrice) ?></span>
            </div>
            <form class="add-to-cart-form" data-product-name="<?= e($rp['name']) ?>">
              <input type="hidden" name="action"     value="add">
              <input type="hidden" name="product_id" value="<?= $rp['id'] ?>">
              <input type="hidden" name="size"       value="M">
              <input type="hidden" name="color"      value="">
              <input type="hidden" name="quantity"   value="1">
              <button type="submit" class="btn-add-cart">
                <i class="fa fa-shopping-bag"></i> Add to Cart
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /container -->

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
<?php
$schema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $product['name'],
    'description' => $product['short_description'] ?: substr(strip_tags($product['description'] ?? ''), 0, 200),
    'sku'         => $product['sku'] ?? '',
    'image'       => [$product['image']],
    'brand'       => ['@type' => 'Brand', 'name' => $product['brand_name'] ?: "Devendra's Shop"],
    'offers'      => [
        '@type'         => 'Offer',
        'price'         => (string)$displayPrice,
        'priceCurrency' => defined('CURRENCY') ? CURRENCY : 'INR',
        'availability'  => $stockStatus === 'out_stock'
            ? 'https://schema.org/OutOfStock'
            : 'https://schema.org/InStock',
        'url'           => SITE_URL . '/pages/product.php?id=' . $id,
    ],
];
if (count($reviews) > 0) {
    $schema['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => number_format($avgRating, 1),
        'reviewCount' => count($reviews),
    ];
}
echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<script>
/* ══════════════════════════════════════
   Product Page — Interactive JS
   ══════════════════════════════════════ */

// Gallery swap
function swapImage(thumb, src) {
  document.getElementById('pd-main-img').src = src;
  document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

// Size selection
var selectedSize = '';
function selectSize(btn) {
  document.querySelectorAll('.pd-size-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  selectedSize = btn.dataset.size;
  var lbl = document.getElementById('selected-size-label');
  if (lbl) lbl.textContent = '— ' + selectedSize;
}

// Colour selection
var selectedColor = '';
function selectColor(btn) {
  document.querySelectorAll('.pd-color-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  selectedColor = btn.dataset.color;
  var lbl = document.getElementById('selected-color-label');
  if (lbl) lbl.textContent = '— ' + selectedColor;
}

// Quantity stepper
function changeQty(delta) {
  var inp = document.getElementById('pd-qty');
  var max = parseInt(inp.max) || 99;
  inp.value = Math.max(1, Math.min(max, parseInt(inp.value || 1) + delta));
}

// Add to cart (AJAX)
function addToCartFromPage() {
  var btn   = document.getElementById('atc-btn');
  var pid   = btn.dataset.productId;
  var size  = selectedSize  || '<?= e(array_values($sizes)[0] ?? 'M') ?>';
  var color = selectedColor || '<?= e(array_values($colors)[0] ?? '') ?>';
  var qty   = parseInt(document.getElementById('pd-qty').value) || 1;

  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method  : 'POST',
    headers : {'Content-Type': 'application/x-www-form-urlencoded'},
    body    : 'action=add&product_id=' + pid
            + '&size='     + encodeURIComponent(size)
            + '&color='    + encodeURIComponent(color)
            + '&quantity=' + qty
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      var badge = document.querySelector('.cart-badge');
      if (badge) badge.textContent = data.cart_count;
      var orig = btn.innerHTML;
      btn.innerHTML = '<i class="fa fa-check"></i> Added!';
      btn.style.background = '#27ae60';
      setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; }, 2000);
    } else {
      alert(data.message || 'Could not add to cart.');
    }
  })
  .catch(() => alert('Network error. Please try again.'));
}

// Buy Now — add then redirect
function buyNow() {
  addToCartFromPage();
  setTimeout(() => { window.location = '<?= SITE_URL ?>/cart/cart.php'; }, 500);
}

// Wishlist toggle (AJAX)
function toggleWishlist(btn) {
  var id     = btn.dataset.id;
  var inList = btn.classList.contains('wishlisted');
  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method  : 'POST',
    headers : {'Content-Type': 'application/x-www-form-urlencoded'},
    body    : 'action=' + (inList ? 'wishlist_remove' : 'wishlist_add') + '&product_id=' + id
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.classList.toggle('wishlisted');
      var icon = btn.querySelector('i');
      if (btn.classList.contains('wishlisted')) {
        icon.className = 'fas fa-heart';
        btn.lastChild.textContent = ' Remove from Wishlist';
      } else {
        icon.className = 'far fa-heart';
        btn.lastChild.textContent = ' Add to Wishlist';
      }
    }
  })
  .catch(() => {/* silent */});
}

// Tabs
function openTab(tabId) {
  document.querySelectorAll('.pd-tab-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.tab === tabId);
  });
  document.querySelectorAll('.pd-tab-panel').forEach(p => {
    p.classList.toggle('active', p.id === tabId);
  });
}
document.querySelectorAll('.pd-tab-btn').forEach(btn => {
  btn.addEventListener('click', function () { openTab(this.dataset.tab); });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
