<?php
// pages/product.php — Flipkart-style Product Detail Page
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

// ── Gallery ──────────────────────────────────────────────────────────────
$galleryRows   = dbFetchAll("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order", [$id]);
$galleryLegacy = !empty($product['gallery']) ? explode(',', $product['gallery']) : [];

// Build final gallery array: [{src, alt}]
$allImages = [];
if ($product['image']) {
    $allImages[] = ['src' => $product['image'], 'alt' => $product['name']];
}
foreach ($galleryRows as $img) {
    $allImages[] = ['src' => UPLOAD_URL . $img['image_path'], 'alt' => $img['alt_text'] ?: $product['name']];
}
foreach ($galleryLegacy as $gsrc) {
    if ($gsrc && $gsrc !== $product['image']) {
        $allImages[] = ['src' => $gsrc, 'alt' => $product['name']];
    }
}
if (empty($allImages)) {
    $allImages[] = ['src' => 'https://via.placeholder.com/600x720?text=No+Image', 'alt' => 'No Image'];
}

// ── Reviews ───────────────────────────────────────────────────────────────
$reviews   = dbFetchAll("SELECT * FROM reviews WHERE product_id=? AND status='approved' ORDER BY id DESC", [$id]);
$avgRating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
$ratingCounts = [5=>0,4=>0,3=>0,2=>0,1=>0];
foreach ($reviews as $rv) $ratingCounts[(int)$rv['rating']]++;

// ── Related ───────────────────────────────────────────────────────────────
$related = dbFetchAll(
    "SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON p.category_id=c.id
     WHERE p.category_id=? AND p.id!=? AND p.status='active'
     ORDER BY RAND() LIMIT 6",
    [$product['category_id'] ?? 0, $id]
);

// ── Recently viewed ───────────────────────────────────────────────────────
if (!isset($_SESSION['recently_viewed'])) $_SESSION['recently_viewed'] = [];
$_SESSION['recently_viewed'] = array_diff($_SESSION['recently_viewed'], [$id]);
array_unshift($_SESSION['recently_viewed'], $id);
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);

// ── Sizes & Colours ───────────────────────────────────────────────────────
$sizes  = array_filter(array_map('trim', explode(',', $product['sizes']  ?? '')));
$colors = array_filter(array_map('trim', explode(',', $product['colors'] ?? '')));
if (empty($sizes))  $sizes  = ['XS','S','M','L','XL'];
if (empty($colors)) $colors = ['Black','White'];

// ── Pricing ───────────────────────────────────────────────────────────────
$displayPrice = ($product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
$hasDiscount  = $product['sale_price'] > 0;
$discountPct  = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
$savings      = $hasDiscount ? ($product['price'] - $product['sale_price']) : 0;

// ── Wishlist ───────────────────────────────────────────────────────────────
$inWishlist = isInWishlist($id);

// ── Stock status ──────────────────────────────────────────────────────────
$stockStatus = 'in_stock';
if ($product['stock'] == 0) $stockStatus = 'out_stock';
elseif ($product['stock'] <= ($product['low_stock_alert'] ?? 5)) $stockStatus = 'low_stock';

// ── Active coupons to show as offers ──────────────────────────────────────
$activeCoupons = dbFetchAll("SELECT code, type, value, min_order FROM coupon_codes WHERE status='active' AND (expires_at IS NULL OR expires_at >= CURDATE()) LIMIT 3");

// ── SEO ───────────────────────────────────────────────────────────────────
$pageTitle = e($product['meta_title'] ?: $product['name'] . " — Devendra's Shop");
$metaDesc  = e($product['meta_description'] ?: $product['short_description'] ?: substr(strip_tags($product['description'] ?? ''), 0, 160));
$ogImage   = $allImages[0]['src'];

// ── Delivery estimate ─────────────────────────────────────────────────────
$deliveryDate = date('D, d M', strtotime('+3 days'));
$fastDate     = date('D, d M', strtotime('+1 days'));

include __DIR__ . '/../includes/header.php';
?>

<!-- ── Breadcrumb ── -->
<section class="page-hero" style="padding:18px 0 14px">
  <div class="container">
    <nav class="breadcrumb" style="justify-content:flex-start">
      <a href="<?= SITE_URL ?>/index.php">Home</a> /
      <a href="<?= SITE_URL ?>/pages/shop.php">Shop</a> /
      <?php if ($product['category_name']): ?>
        <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>">
          <?= e($product['category_name']) ?>
        </a> /
      <?php endif; ?>
      <span style="color:rgba(255,255,255,.9)"><?= e($product['name']) ?></span>
    </nav>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════
     MAIN PRODUCT SECTION — Flipkart/Amazon Style
     ═══════════════════════════════════════════════════ -->
<div class="container">
<div class="fpd-layout">

  <!-- ════ LEFT: VERTICAL THUMBNAIL STRIP + MAIN IMAGE ════ -->
  <div class="fpd-gallery" id="fpd-gallery">

    <!-- Vertical thumbnail strip -->
    <div class="fpd-thumb-col" id="fpd-thumbs">
      <?php foreach ($allImages as $i => $img): ?>
        <div class="fpd-thumb-item <?= $i === 0 ? 'active' : '' ?>"
             onclick="fpdSwap(this, '<?= e($img['src']) ?>', '<?= e($img['alt']) ?>')"
             data-src="<?= e($img['src']) ?>">
          <img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy" />
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Main image + zoom -->
    <div class="fpd-main-wrap">
      <!-- Badges -->
      <?php if ($hasDiscount): ?>
        <div class="fpd-discount-strip"><?= $discountPct ?>% OFF</div>
      <?php endif; ?>
      <?php if (!empty($product['is_new_arrival']) || str_contains($product['tags'] ?? '', 'new')): ?>
        <div class="fpd-new-strip">NEW</div>
      <?php endif; ?>

      <!-- Main image with zoom -->
      <div class="fpd-zoom-wrap" id="fpd-zoom-wrap">
        <img src="<?= e($allImages[0]['src']) ?>" alt="<?= e($product['name']) ?>"
             id="fpd-main-img" class="fpd-main-img" />
        <div class="fpd-zoom-lens" id="fpd-lens"></div>
      </div>

      <!-- Zoom result window (appears on right) -->
      <div class="fpd-zoom-result" id="fpd-zoom-result"></div>

      <!-- Wishlist floating button -->
      <button type="button" id="wishlist-btn"
              class="fpd-wish-btn <?= $inWishlist ? 'wishlisted' : '' ?>"
              data-id="<?= $id ?>"
              onclick="toggleWishlist(this)"
              title="<?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
        <i class="fa<?= $inWishlist ? 's' : 'r' ?> fa-heart"></i>
      </button>
    </div>

    <!-- Mobile thumbnail row (horizontal, shown only on mobile) -->
    <div class="fpd-thumb-row-mobile">
      <?php foreach ($allImages as $i => $img): ?>
        <img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>"
             class="fpd-thumb-mobile <?= $i === 0 ? 'active' : '' ?>"
             onclick="fpdSwapMobile(this, '<?= e($img['src']) ?>')"
             loading="lazy" />
      <?php endforeach; ?>
    </div>

  </div><!-- /fpd-gallery -->

  <!-- ════ RIGHT: PRODUCT INFO ════ -->
  <div class="fpd-info">

    <!-- Category + Brand -->
    <div class="fpd-meta-top">
      <?php if ($product['category_name']): ?>
        <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>" class="fpd-category-link">
          <?= e($product['category_name']) ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($product['brand_name'])): ?>
        <span class="fpd-brand-tag"><?= e($product['brand_name']) ?></span>
      <?php endif; ?>
    </div>

    <!-- Product Name -->
    <h1 class="fpd-product-name"><?= e($product['name']) ?></h1>

    <!-- Rating Summary Bar -->
    <div class="fpd-rating-bar">
      <div class="fpd-rating-pill">
        <span class="fpd-rating-num"><?= number_format($avgRating, 1) ?></span>
        <i class="fas fa-star" style="font-size:.75rem"></i>
      </div>
      <a href="#pd-reviews" onclick="openTab('pd-reviews');return false;" class="fpd-review-link">
        <?= count($reviews) ?> Rating<?= count($reviews) !== 1 ? 's' : '' ?> &amp; <?= count($reviews) ?> Review<?= count($reviews) !== 1 ? 's' : '' ?>
      </a>
    </div>

    <hr class="fpd-divider" />

    <!-- Price Block -->
    <div class="fpd-price-block">
      <div class="fpd-price-row">
        <span class="fpd-price-final"><?= formatPrice($displayPrice) ?></span>
        <?php if ($hasDiscount): ?>
          <span class="fpd-price-mrp">MRP <s><?= formatPrice($product['price']) ?></s></span>
          <span class="fpd-discount-pct"><?= $discountPct ?>% off</span>
        <?php endif; ?>
      </div>
      <?php if ($hasDiscount): ?>
        <div class="fpd-savings">
          <i class="fa fa-tag"></i> You save <?= formatPrice($savings) ?> on this order
        </div>
      <?php endif; ?>
      <div class="fpd-tax-note">Inclusive of all taxes</div>
    </div>

    <!-- Offers Strip -->
    <?php if (!empty($activeCoupons)): ?>
    <div class="fpd-offers-box">
      <div class="fpd-offers-title"><i class="fa fa-ticket-alt"></i> Available Offers</div>
      <?php foreach ($activeCoupons as $cp): ?>
        <div class="fpd-offer-item">
          <i class="fa fa-check-circle" style="color:#27ae60"></i>
          <span>
            <strong>Use code <?= e($cp['code']) ?></strong> —
            <?= $cp['type'] === 'percent' ? $cp['value'].'% off' : '₹'.$cp['value'].' off' ?>
            <?= $cp['min_order'] > 0 ? ' on orders above ₹'.number_format($cp['min_order']) : '' ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Stock Status -->
    <?php
    $stockLabels = [
      'in_stock'  => ['class'=>'fpd-in-stock',  'icon'=>'fa-check-circle',       'txt'=>'In Stock'],
      'low_stock' => ['class'=>'fpd-low-stock',  'icon'=>'fa-exclamation-circle', 'txt'=>'Only '.(int)$product['stock'].' left — Order soon!'],
      'out_stock' => ['class'=>'fpd-out-stock',  'icon'=>'fa-times-circle',       'txt'=>'Out of Stock'],
    ];
    $sl = $stockLabels[$stockStatus];
    ?>
    <div class="fpd-stock <?= $sl['class'] ?>">
      <i class="fa <?= $sl['icon'] ?>"></i> <?= $sl['txt'] ?>
    </div>

    <!-- Short Description -->
    <?php if (!empty($product['short_description'])): ?>
      <p class="fpd-short-desc"><?= e($product['short_description']) ?></p>
    <?php endif; ?>

    <!-- Size Selection -->
    <?php if (!empty($sizes)): ?>
    <div class="fpd-option-block">
      <div class="fpd-option-label">
        Size <span class="fpd-selected-val" id="selected-size-label"></span>
        <a href="<?= SITE_URL ?>/pages/size-guide.php" class="fpd-size-guide-link" target="_blank">Size Guide</a>
      </div>
      <div class="fpd-size-row" id="size-opts">
        <?php foreach ($sizes as $sz): ?>
          <button type="button" class="fpd-size-btn" data-size="<?= e($sz) ?>"
                  onclick="selectSize(this)"><?= e($sz) ?></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Color Selection -->
    <?php if (!empty($colors)): ?>
    <div class="fpd-option-block">
      <div class="fpd-option-label">
        Color <span class="fpd-selected-val" id="selected-color-label"></span>
      </div>
      <div class="fpd-color-row" id="color-opts">
        <?php
        $colorMap = [
          'black'=>'#1a1a1a','white'=>'#f5f5f5','red'=>'#e74c3c','blue'=>'#2980b9','navy'=>'#1a3a5c',
          'green'=>'#27ae60','yellow'=>'#f1c40f','orange'=>'#e67e22','pink'=>'#fd79a8','purple'=>'#8e44ad',
          'grey'=>'#95a5a6','gray'=>'#95a5a6','beige'=>'#d4b896','cream'=>'#f5f0e8','khaki'=>'#bba58f',
          'olive'=>'#8d8741','brown'=>'#795548','gold'=>'#c9a96e','ivory'=>'#f5f0e0','maroon'=>'#800000',
          'teal'=>'#008080','cyan'=>'#00bcd4','magenta'=>'#e91e63','champagne'=>'#f7e7ce','rose'=>'#ffb3ba',
          'sky blue'=>'#87ceeb','multicolor'=>'linear-gradient(135deg,#e74c3c,#3498db,#2ecc71)',
          'sky'=>'#87ceeb','golden'=>'#c9a96e','burgund'=>'#800020','burgundy'=>'#800020',
          'light blue'=>'#add8e6','dark blue'=>'#00008b',
        ];
        foreach ($colors as $cl):
          $bg = $colorMap[strtolower(trim($cl))] ?? ('#'.substr(md5($cl),0,6));
          $isLight = in_array(strtolower(trim($cl)), ['white','cream','ivory','champagne','yellow','beige']);
        ?>
          <button type="button" class="fpd-color-swatch" data-color="<?= e($cl) ?>"
                  style="background:<?= $bg ?>;<?= $isLight ? 'border:2px solid #ddd;' : '' ?>"
                  title="<?= e($cl) ?>"
                  onclick="selectColor(this)">
            <i class="fa fa-check fpd-color-check"></i>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Quantity -->
    <div class="fpd-option-block">
      <div class="fpd-option-label">Quantity</div>
      <div class="fpd-qty-row">
        <button type="button" class="fpd-qty-btn" onclick="changeQty(-1)"><i class="fa fa-minus"></i></button>
        <input type="number" class="fpd-qty-input" id="pd-qty" value="1" min="1" max="<?= max(1,(int)$product['stock']) ?>" />
        <button type="button" class="fpd-qty-btn" onclick="changeQty(1)"><i class="fa fa-plus"></i></button>
      </div>
    </div>

    <!-- Action Buttons -->
    <?php if ($stockStatus !== 'out_stock'): ?>
    <div class="fpd-action-row">
      <button type="button" class="fpd-btn-cart" id="atc-btn"
              data-product-id="<?= $id ?>" onclick="addToCartFromPage()">
        <i class="fa fa-shopping-cart"></i> Add to Cart
      </button>
      <button type="button" class="fpd-btn-buy" onclick="buyNow()">
        <i class="fa fa-bolt"></i> Buy Now
      </button>
    </div>
    <?php else: ?>
    <div class="fpd-action-row">
      <button type="button" class="fpd-btn-oos" disabled>
        <i class="fa fa-times-circle"></i> Out of Stock
      </button>
    </div>
    <?php endif; ?>

    <!-- Delivery Info Strip -->
    <div class="fpd-delivery-strip">
      <div class="fpd-delivery-item">
        <i class="fa fa-truck"></i>
        <div>
          <strong>Standard Delivery</strong>
          <div>By <?= $deliveryDate ?> | <span style="color:#27ae60">Free</span> on orders above ₹999</div>
        </div>
      </div>
      <div class="fpd-delivery-item">
        <i class="fa fa-bolt" style="color:#f39c12"></i>
        <div>
          <strong>Express Delivery</strong>
          <div>By <?= $fastDate ?> | ₹99</div>
        </div>
      </div>
      <div class="fpd-delivery-item">
        <i class="fa fa-undo" style="color:#3498db"></i>
        <div>
          <strong>15 Days Easy Returns</strong>
          <div>No questions asked returns policy</div>
        </div>
      </div>
    </div>

    <!-- Product Meta -->
    <div class="fpd-meta-table">
      <?php if ($product['category_name']): ?>
        <div><span>Category</span><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>"><?= e($product['category_name']) ?></a></div>
      <?php endif; ?>
      <?php if (!empty($product['brand_name'])): ?><div><span>Brand</span><strong><?= e($product['brand_name']) ?></strong></div><?php endif; ?>
      <?php if (!empty($product['material'])): ?><div><span>Material</span><?= e($product['material']) ?></div><?php endif; ?>
      <?php if (!empty($product['sku'])): ?><div><span>SKU</span><?= e($product['sku']) ?></div><?php endif; ?>
      <?php if (!empty($product['gender'])): ?><div><span>Gender</span><?= ucfirst(e($product['gender'])) ?></div><?php endif; ?>
      <?php if (!empty($product['tags'])): ?><div><span>Tags</span><?= e($product['tags']) ?></div><?php endif; ?>
    </div>

  </div><!-- /fpd-info -->
</div><!-- /fpd-layout -->

<!-- ═══════════════════════════════
     TABS: Description / Specs / Reviews
     ═══════════════════════════════ -->
<div class="fpd-tabs-section">
  <div class="fpd-tabs-nav">
    <button type="button" class="pd-tab-btn active" data-tab="pd-desc">Description</button>
    <button type="button" class="pd-tab-btn" data-tab="pd-specs">Specifications</button>
    <button type="button" class="pd-tab-btn" data-tab="pd-reviews" id="reviews-tab-btn">
      Reviews &amp; Ratings (<?= count($reviews) ?>)
    </button>
  </div>

  <!-- Description -->
  <div class="pd-tab-panel active" id="pd-desc">
    <div class="fpd-desc-content">
      <?= nl2br(e($product['description'] ?: 'No description available.')) ?>
    </div>
  </div>

  <!-- Specifications -->
  <div class="pd-tab-panel" id="pd-specs">
    <table class="fpd-specs-table">
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

  <!-- Reviews & Ratings -->
  <div class="pd-tab-panel" id="pd-reviews">

    <!-- Rating Summary -->
    <?php if (!empty($reviews)): ?>
    <div class="fpd-rating-summary">
      <div class="fpd-rating-big">
        <div class="fpd-big-num"><?= number_format($avgRating, 1) ?></div>
        <div class="fpd-big-stars">
          <?php for ($s=1;$s<=5;$s++): ?>
            <i class="fa<?= $s <= round($avgRating) ? 's':'r' ?> fa-star"></i>
          <?php endfor; ?>
        </div>
        <div class="fpd-big-count"><?= count($reviews) ?> Verified Ratings</div>
      </div>
      <div class="fpd-rating-bars">
        <?php for ($star=5;$star>=1;$star--): ?>
          <?php $pct = count($reviews) ? round($ratingCounts[$star]/count($reviews)*100) : 0; ?>
          <div class="fpd-rating-bar-row">
            <span><?= $star ?> <i class="fas fa-star" style="font-size:.7rem"></i></span>
            <div class="fpd-rating-bar-track">
              <div class="fpd-rating-bar-fill" style="width:<?= $pct ?>%;background:<?= $star>=4?'#27ae60':($star==3?'#f39c12':'#e74c3c') ?>"></div>
            </div>
            <span><?= $ratingCounts[$star] ?></span>
          </div>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Individual Reviews -->
    <div class="fpd-reviews-list">
      <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $rv): ?>
          <div class="fpd-review-card">
            <div class="fpd-review-header">
              <div class="fpd-reviewer-avatar"><?= strtoupper(substr($rv['reviewer_name'],0,1)) ?></div>
              <div>
                <div class="fpd-reviewer-name"><?= e($rv['reviewer_name']) ?></div>
                <div class="fpd-review-date"><?= date('d M Y', strtotime($rv['created_at'])) ?></div>
              </div>
              <div class="fpd-review-stars-pill">
                <?= $rv['rating'] ?> <i class="fas fa-star" style="font-size:.7rem"></i>
              </div>
            </div>
            <p class="fpd-review-text"><?= e($rv['comment']) ?></p>
            <div class="fpd-verified-badge"><i class="fa fa-check-circle"></i> Verified Purchase</div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="fpd-no-reviews">No reviews yet. Be the first to review this product!</p>
      <?php endif; ?>
    </div>

    <!-- Write a Review Form -->
    <div class="fpd-write-review">
      <h3 class="fpd-write-title"><i class="fa fa-pen"></i> Write a Review</h3>
      <div id="review-success" class="fpd-review-success" style="display:none"></div>
      <form id="review-form" class="fpd-review-form">
        <input type="hidden" name="product_id" value="<?= $id ?>" />

        <div class="fpd-review-field">
          <label>Your Name *</label>
          <input type="text" name="reviewer_name" required placeholder="Enter your name"
                 value="<?= isset($_SESSION['user_name']) ? e($_SESSION['user_name']) : '' ?>" />
        </div>

        <div class="fpd-review-field">
          <label>Your Rating *</label>
          <div class="fpd-star-picker" id="star-picker">
            <?php for ($s=1;$s<=5;$s++): ?>
              <button type="button" class="fpd-star-pick" data-val="<?= $s ?>"
                      onclick="pickStar(this, <?= $s ?>)">
                <i class="far fa-star"></i>
              </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="review-rating" value="" />
          <div class="fpd-rating-label" id="rating-label"></div>
        </div>

        <div class="fpd-review-field">
          <label>Your Review *</label>
          <textarea name="comment" rows="4" required
                    placeholder="Share your experience with this product…"></textarea>
        </div>

        <button type="submit" class="fpd-submit-review" id="review-submit-btn">
          <i class="fa fa-paper-plane"></i> Submit Review
        </button>
      </form>
    </div>

  </div><!-- /pd-reviews -->
</div><!-- /fpd-tabs-section -->

<!-- ═══════════════════════════════
     RELATED PRODUCTS — Horizontal Scroll
     ═══════════════════════════════ -->
<?php if (!empty($related)): ?>
<div class="fpd-related-section">
  <div class="fpd-related-header">
    <h2>Similar Products</h2>
    <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_id'] ?>" class="fpd-see-all">See All <i class="fa fa-chevron-right"></i></a>
  </div>
  <div class="fpd-related-scroll">
    <?php foreach ($related as $rp):
      if ($rp['id'] == $id) continue;
      $rPrice = $rp['sale_price'] > 0 ? $rp['sale_price'] : $rp['price'];
      $rDisc  = $rp['sale_price'] > 0 ? round((1 - $rp['sale_price']/$rp['price'])*100) : 0;
    ?>
      <div class="fpd-related-card">
        <?php if ($rDisc > 0): ?>
          <div class="fpd-related-disc"><?= $rDisc ?>% OFF</div>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rp['id'] ?>">
          <img src="<?= e($rp['image'] ?: 'https://via.placeholder.com/300x380') ?>"
               alt="<?= e($rp['name']) ?>" loading="lazy" />
        </a>
        <div class="fpd-related-info">
          <div class="fpd-related-cat"><?= e($rp['category_name'] ?? '') ?></div>
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $rp['id'] ?>" class="fpd-related-name">
            <?= e($rp['name']) ?>
          </a>
          <div class="fpd-related-price">
            <strong><?= formatPrice($rPrice) ?></strong>
            <?php if ($rp['sale_price'] > 0): ?>
              <s style="color:#aaa;font-size:.8rem"><?= formatPrice($rp['price']) ?></s>
            <?php endif; ?>
          </div>
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
  '@context'=>'https://schema.org','@type'=>'Product',
  'name'=>$product['name'],
  'description'=>$product['short_description'] ?: substr(strip_tags($product['description'] ?? ''),0,200),
  'sku'=>$product['sku'] ?? '',
  'image'=>[$allImages[0]['src']],
  'brand'=>['@type'=>'Brand','name'=>$product['brand_name'] ?: "Devendra's Shop"],
  'offers'=>['@type'=>'Offer','price'=>(string)$displayPrice,'priceCurrency'=>'INR',
    'availability'=>$stockStatus==='out_stock'?'https://schema.org/OutOfStock':'https://schema.org/InStock',
    'url'=>SITE_URL.'/pages/product.php?id='.$id],
];
if (count($reviews) > 0) $schema['aggregateRating'] = ['@type'=>'AggregateRating','ratingValue'=>number_format($avgRating,1),'reviewCount'=>count($reviews)];
echo json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
?>
</script>

<script>
/* ════════════════════════════════════════
   Product Page JS — Flipkart Style
   ════════════════════════════════════════ */

// ── Gallery swap ──────────────────────────────────────────────
function fpdSwap(thumb, src, alt) {
  document.getElementById('fpd-main-img').src = src;
  document.querySelectorAll('.fpd-thumb-item').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
  // Update zoom result if open
  setupZoom();
}
function fpdSwapMobile(thumb, src) {
  document.getElementById('fpd-main-img').src = src;
  document.querySelectorAll('.fpd-thumb-mobile').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

// ── Hover Zoom (Amazon style) ─────────────────────────────────
function setupZoom() {
  const wrap   = document.getElementById('fpd-zoom-wrap');
  const lens   = document.getElementById('fpd-lens');
  const result = document.getElementById('fpd-zoom-result');
  const img    = document.getElementById('fpd-main-img');
  if (!wrap || !lens || !result) return;

  const cx = result.offsetWidth  / lens.offsetWidth;
  const cy = result.offsetHeight / lens.offsetHeight;
  result.style.backgroundImage  = `url('${img.src}')`;
  result.style.backgroundSize   = `${img.width * cx}px ${img.height * cy}px`;

  wrap.onmousemove = function(e) {
    const pos = getCursorPos(e, wrap, img);
    let x = pos.x - lens.offsetWidth  / 2;
    let y = pos.y - lens.offsetHeight / 2;
    x = Math.max(0, Math.min(x, img.width  - lens.offsetWidth));
    y = Math.max(0, Math.min(y, img.height - lens.offsetHeight));
    lens.style.left = x + 'px';
    lens.style.top  = y + 'px';
    result.style.backgroundPosition = `-${x * cx}px -${y * cy}px`;
  };
  wrap.onmouseenter = function() {
    lens.style.display   = 'block';
    result.style.display = 'block';
    result.style.backgroundImage = `url('${img.src}')`;
    result.style.backgroundSize  = `${img.width * cx}px ${img.height * cy}px`;
  };
  wrap.onmouseleave = function() {
    lens.style.display   = 'none';
    result.style.display = 'none';
  };
}
function getCursorPos(e, wrap, img) {
  const r  = img.getBoundingClientRect();
  const x  = e.pageX - r.left - window.pageXOffset;
  const y  = e.pageY - r.top  - window.pageYOffset;
  return {x, y};
}
window.addEventListener('load', setupZoom);

// ── Size / Color selection ────────────────────────────────────
var selectedSize = '', selectedColor = '';
function selectSize(btn) {
  document.querySelectorAll('.fpd-size-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  selectedSize = btn.dataset.size;
  const lbl = document.getElementById('selected-size-label');
  if (lbl) lbl.textContent = '— ' + selectedSize;
}
function selectColor(btn) {
  document.querySelectorAll('.fpd-color-swatch').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  selectedColor = btn.dataset.color;
  const lbl = document.getElementById('selected-color-label');
  if (lbl) lbl.textContent = '— ' + selectedColor;
}

// ── Quantity ──────────────────────────────────────────────────
function changeQty(delta) {
  const inp = document.getElementById('pd-qty');
  const max = parseInt(inp.max) || 99;
  inp.value = Math.max(1, Math.min(max, parseInt(inp.value||1) + delta));
}

// ── Add to Cart ───────────────────────────────────────────────
function addToCartFromPage() {
  const btn   = document.getElementById('atc-btn');
  const pid   = btn.dataset.productId;
  const size  = selectedSize  || '<?= e(array_values($sizes)[0] ?? 'M') ?>';
  const color = selectedColor || '<?= e(array_values($colors)[0] ?? '') ?>';
  const qty   = parseInt(document.getElementById('pd-qty').value) || 1;

  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adding…';
  btn.disabled  = true;

  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=add&product_id='+pid+'&size='+encodeURIComponent(size)+'&color='+encodeURIComponent(color)+'&quantity='+qty
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const badge = document.querySelector('.cart-badge');
      if (badge) badge.textContent = data.cart_count;
      btn.innerHTML = '<i class="fa fa-check"></i> Added to Cart!';
      btn.style.background = '#27ae60';
      setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; btn.disabled = false; }, 2000);
    } else {
      alert(data.message || 'Could not add to cart.');
      btn.innerHTML = orig; btn.disabled = false;
    }
  })
  .catch(() => { alert('Network error.'); btn.innerHTML = orig; btn.disabled = false; });
}

function buyNow() {
  addToCartFromPage();
  setTimeout(() => { window.location = '<?= SITE_URL ?>/cart/cart.php'; }, 600);
}

// ── Wishlist toggle ───────────────────────────────────────────
function toggleWishlist(btn) {
  const id = btn.dataset.id;
  const inList = btn.classList.contains('wishlisted');
  fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action='+(inList?'wishlist_remove':'wishlist_add')+'&product_id='+id
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.classList.toggle('wishlisted');
      const i = btn.querySelector('i');
      i.className = btn.classList.contains('wishlisted') ? 'fas fa-heart' : 'far fa-heart';
    }
  });
}

// ── Tabs ──────────────────────────────────────────────────────
function openTab(tabId) {
  document.querySelectorAll('.pd-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab===tabId));
  document.querySelectorAll('.pd-tab-panel').forEach(p => p.classList.toggle('active', p.id===tabId));
}
document.querySelectorAll('.pd-tab-btn').forEach(btn => {
  btn.addEventListener('click', function() { openTab(this.dataset.tab); });
});

// ── Star Picker for Review Form ───────────────────────────────
const ratingLabels = {1:'Poor',2:'Fair',3:'Good',4:'Very Good',5:'Excellent'};
function pickStar(btn, val) {
  document.getElementById('review-rating').value = val;
  document.getElementById('rating-label').textContent = ratingLabels[val] || '';
  document.querySelectorAll('.fpd-star-pick i').forEach((i, idx) => {
    i.className = idx < val ? 'fas fa-star' : 'far fa-star';
  });
  document.querySelectorAll('.fpd-star-pick').forEach((b, idx) => {
    b.classList.toggle('active', idx < val);
  });
}

// ── Review Form Submit ────────────────────────────────────────
document.getElementById('review-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const rating = document.getElementById('review-rating').value;
  if (!rating) { alert('Please select a star rating.'); return; }
  const btn = document.getElementById('review-submit-btn');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting…';
  btn.disabled  = true;

  fetch('<?= SITE_URL ?>/cart/submit_review.php', {
    method:'POST',
    body: new FormData(this)
  })
  .then(r => r.json())
  .then(data => {
    const box = document.getElementById('review-success');
    box.textContent = data.message;
    box.className   = 'fpd-review-success ' + (data.success ? 'fpd-rev-ok' : 'fpd-rev-err');
    box.style.display = 'block';
    if (data.success) { this.reset(); document.querySelectorAll('.fpd-star-pick i').forEach(i => i.className='far fa-star'); document.getElementById('review-rating').value=''; document.getElementById('rating-label').textContent=''; }
    btn.innerHTML = orig; btn.disabled = false;
  })
  .catch(() => { alert('Network error.'); btn.innerHTML = orig; btn.disabled = false; });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
