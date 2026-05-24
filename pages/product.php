<?php
require_once __DIR__ . '/../includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect(SITE_URL . '/pages/shop.php'); }

$product = dbFetchOne("SELECT p.*,c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id=? AND p.status='active'", [$id]);
if (!$product) { redirect(SITE_URL . '/pages/shop.php'); }

$reviews  = dbFetchAll("SELECT id, reviewer_name, rating, comment, created_at FROM reviews WHERE product_id=? AND status='approved' ORDER BY id DESC LIMIT 20", [$id]);
$avgRating = count($reviews) ? round(array_sum(array_column($reviews,'rating'))/count($reviews),1) : 0;
$related  = getProducts(4,0,['category_id'=>$product['category_id']]);

$sizes  = explode(',', $product['sizes'] ?? 'XS,S,M,L,XL');
$colors = explode(',', $product['colors'] ?? 'Black,White');
$displayPrice = $product['sale_price'] > 0 ? $product['sale_price'] : $product['price'];
$hasDiscount  = $product['sale_price'] > 0;
$discount     = $hasDiscount ? round((($product['price'] - $product['sale_price'])/$product['price'])*100) : 0;

$gallery = !empty($product['gallery']) ? explode(',', $product['gallery']) : [];
array_unshift($gallery, $product['image']);

$pageTitle = e($product['name']) . ' — Devendra's Shop';
$metaDesc  = substr(strip_tags($product['description']), 0, 155);
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero" style="padding:40px 0">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= SITE_URL ?>/index.php">Home</a> /
      <a href="<?= SITE_URL ?>/pages/shop.php">Shop</a> /
      <?= e($product['name']) ?>
    </nav>
  </div>
</section>

<div class="container">
  <div class="product-detail">

    <!-- Gallery -->
    <div class="product-gallery">
      <div class="main-img-wrap">
        <img src="<?= e($gallery[0]) ?>" alt="<?= e($product['name']) ?>" id="main-product-img"/>
      </div>
      <?php if (count($gallery) > 1): ?>
      <div class="thumb-grid">
        <?php foreach ($gallery as $i => $img): ?>
        <div class="thumb-img <?= $i===0?'active':'' ?>">
          <img src="<?= e($img) ?>" data-src="<?= e($img) ?>" alt="View <?= $i+1 ?>" class="thumb-img"/>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="product-detail-info">
      <div class="product-category" style="margin-bottom:6px"><?= e($product['category_name']) ?></div>
      <h1><?= e($product['name']) ?></h1>

      <div class="product-rating">
        <span class="stars-filled">
          <?php for ($i=1;$i<=5;$i++): ?><i class="fa fa-star<?= $i > round($avgRating) ? '-o' : '' ?>"></i><?php endfor; ?>
        </span>
        <span><?= $avgRating ?>/5</span>
        <span style="color:#888">(<?= count($reviews) ?> reviews)</span>
      </div>

      <div class="product-price-wrap">
        <span class="price-big"><?= formatPrice($displayPrice) ?></span>
        <?php if ($hasDiscount): ?>
          <span class="price-old"><?= formatPrice($product['price']) ?></span>
          <span class="price-discount"><?= $discount ?>% OFF</span>
        <?php endif; ?>
      </div>

      <p style="color:#555;line-height:1.7;margin-bottom:18px"><?= nl2br(e($product['description'])) ?></p>
      <hr class="detail-divider"/>

      <form class="add-to-cart-form" id="main-cart-form">
        <input type="hidden" name="action" value="add"/>
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>"/>
        <input type="hidden" id="selected-size" name="size" value="<?= e($sizes[0]) ?>"/>
        <input type="hidden" id="selected-color" name="color" value="<?= e($colors[0]) ?>"/>

        <!-- Sizes -->
        <div class="option-label">Size <a class="size-guide-link" href="#">Size Guide</a></div>
        <div class="btn-group" style="margin-bottom:18px">
          <?php foreach ($sizes as $i => $sz): ?>
          <button type="button" class="size-btn <?= $i===0?'active':'' ?>"
                  onclick="document.getElementById('selected-size').value='<?= e(trim($sz)) ?>';this.closest('.btn-group').querySelectorAll('button').forEach(b=>b.classList.remove('active'));this.classList.add('active')">
            <?= e(trim($sz)) ?>
          </button>
          <?php endforeach; ?>
        </div>

        <!-- Colors -->
        <div class="option-label">Color</div>
        <div class="btn-group" style="margin-bottom:18px">
          <?php foreach ($colors as $i => $cl): ?>
          <button type="button" class="color-btn <?= $i===0?'active':'' ?>"
                  onclick="document.getElementById('selected-color').value='<?= e(trim($cl)) ?>';this.closest('.btn-group').querySelectorAll('button').forEach(b=>b.classList.remove('active'));this.classList.add('active')">
            <?= e(trim($cl)) ?>
          </button>
          <?php endforeach; ?>
        </div>

        <!-- Quantity -->
        <div class="option-label">Quantity</div>
        <div class="qty-wrap" style="margin-bottom:20px">
          <button type="button" class="qty-btn" id="qty-minus"><i class="fa fa-minus"></i></button>
          <input type="number" class="qty-input" id="qty-input" name="quantity" value="1" min="1" max="99"/>
          <button type="button" class="qty-btn" id="qty-plus"><i class="fa fa-plus"></i></button>
        </div>

        <div class="detail-btns">
          <button type="submit" class="btn btn-dark"><i class="fa fa-shopping-bag"></i> Add to Cart</button>
          <a href="<?= SITE_URL ?>/cart/checkout.php" class="btn btn-primary btn-buy-now"><i class="fa fa-bolt"></i> Buy Now</a>
        </div>
      </form>

      <div class="product-meta">
        <div>Category: <span><?= e($product['category_name']) ?></span></div>
        <div>Availability: <span style="color:#27ae60"><?= $product['stock'] > 0 ? 'In Stock ('.$product['stock'].' left)' : 'Out of Stock' ?></span></div>
        <?php if (!empty($product['tags'])): ?><div>Tags: <span><?= e($product['tags']) ?></span></div><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Tabs: Description & Reviews -->
  <div style="margin:50px 0">
    <div class="tabs-nav">
      <button class="tab-btn active" data-tab="tab-desc">Description</button>
      <button class="tab-btn" data-tab="tab-reviews">Reviews (<?= count($reviews) ?>)</button>
    </div>
    <div id="tab-desc" class="tab-panel active" style="padding:24px 0;color:#555;line-height:1.8">
      <?= nl2br(e($product['description'])) ?>
    </div>
    <div id="tab-reviews" class="tab-panel">
      <?php if (empty($reviews)): ?>
        <p style="color:#888;padding:24px 0">No reviews yet. Be the first to review!</p>
      <?php else: ?>
        <div class="testimonials-grid" style="margin-top:20px">
          <?php foreach ($reviews as $rv): ?>
          <div class="testimonial-card">
            <div class="testimonial-stars"><?php for ($i=1;$i<=5;$i++): ?><i class="fa fa-star<?= $i>$rv['rating']?'-o':'' ?>"></i><?php endfor; ?></div>
            <p class="testimonial-text">"<?= e($rv['comment']) ?>"</p>
            <div class="testimonial-author">
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($rv['reviewer_name']) ?>&background=c9a96e&color=000&size=44" class="author-avatar" alt=""/>
              <div><div class="author-name"><?= e($rv['reviewer_name']) ?></div></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
  <div style="margin-bottom:60px">
    <div class="section-header"><h2>You May Also Like</h2></div>
    <div class="product-grid">
      <?php foreach (array_slice($related,0,4) as $p):
        if ($p['id'] == $product['id']) continue;
        $dp = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
      ?>
      <div class="product-card">
        <div class="product-img-wrap">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
          </a>
        </div>
        <div class="product-info">
          <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a></div>
          <div class="product-price"><span class="price-current"><?= formatPrice($dp) ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
