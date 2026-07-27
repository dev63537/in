<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = "Gujju Clothing — Premium Fashion Online";
$metaDesc  = "Shop the latest fashion trends at Gujju Clothing. Premium clothing for women, men and kids.";

$featured    = getProducts(8, 0, ['featured_only' => false, 'tag' => 'featured']);
$newArrivals = getProducts(4, 0, ['tag' => 'new']);
$categories  = getCategories();
$saleProd    = getProducts(4, 0, ['tag' => 'sale']);
$reviews     = dbFetchAll("SELECT r.*,p.name AS product_name FROM reviews r LEFT JOIN products p ON r.product_id=p.id WHERE r.status='approved' ORDER BY r.id DESC LIMIT 6");

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SLIDER ===== -->
<section class="hero">
  <div class="hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600')">
    <div class="container hero-content">
      <span class="hero-tag">New Collection 2025</span>
      <h1 class="hero-title">Define Your<br><span>Style Story</span></h1>
      <p class="hero-subtitle">Premium fashion that speaks before you do. Curated styles for every occasion.</p>
      <div class="hero-btns">
        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary"><i class="fa fa-shopping-bag"></i> Shop Now</a>
        <a href="<?= SITE_URL ?>/pages/collections.php" class="btn btn-white">Explore Collections</a>
      </div>
    </div>
  </div>
  <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1600')">
    <div class="container hero-content">
      <span class="hero-tag">Ethnic Wear</span>
      <h1 class="hero-title">Tradition Meets<br><span>Modern Grace</span></h1>
      <p class="hero-subtitle">Celebrate every festive season with our exquisite ethnic collection.</p>
      <div class="hero-btns">
        <a href="<?= SITE_URL ?>/pages/shop.php?category=4" class="btn btn-primary">Shop Ethnic</a>
        <a href="<?= SITE_URL ?>/pages/shop.php?tag=sale" class="btn btn-white"><i class="fa fa-fire"></i> Sale Up to 40% Off</a>
      </div>
    </div>
  </div>
  <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1600')">
    <div class="container hero-content">
      <span class="hero-tag">Men's Edit</span>
      <h1 class="hero-title">Dress Sharp.<br><span>Live Bold.</span></h1>
      <p class="hero-subtitle">Premium menswear crafted for the modern gentleman.</p>
      <div class="hero-btns">
        <a href="<?= SITE_URL ?>/pages/shop.php?category=2" class="btn btn-primary">Shop Men's</a>
        <a href="<?= SITE_URL ?>/pages/shop.php?tag=new" class="btn btn-white">New Arrivals</a>
      </div>
    </div>
  </div>
  <button class="hero-arrow" id="hero-prev"><i class="fa fa-chevron-left"></i></button>
  <button class="hero-arrow" id="hero-next"><i class="fa fa-chevron-right"></i></button>
  <div class="hero-controls">
    <button class="hero-dot active"></button>
    <button class="hero-dot"></button>
    <button class="hero-dot"></button>
  </div>
</section>

<!-- ===== FEATURES STRIP ===== -->
<section class="features-strip">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item animate-on-scroll">
        <div class="feature-icon"><i class="fa fa-truck"></i></div>
        <div class="feature-text"><h4>Free Shipping</h4><p>On orders above ₹999</p></div>
      </div>
      <div class="feature-item animate-on-scroll">
        <div class="feature-icon"><i class="fa fa-undo"></i></div>
        <div class="feature-text"><h4>Easy Returns</h4><p>15-day hassle-free returns</p></div>
      </div>
      <div class="feature-item animate-on-scroll">
        <div class="feature-icon"><i class="fa fa-shield-alt"></i></div>
        <div class="feature-text"><h4>Secure Payment</h4><p>100% secure transactions</p></div>
      </div>
      <div class="feature-item animate-on-scroll">
        <div class="feature-icon"><i class="fa fa-headset"></i></div>
        <div class="feature-text"><h4>24/7 Support</h4><p>We're here to help</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="section section-light">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <h2>Shop by Category</h2>
      <p>Find your perfect style across our curated collections</p>
    </div>
    <div class="categories-grid">
      <?php
      $catImages = [
        1 => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=400',
        2 => 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=400',
        3 => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=400',
        4 => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=400',
        5 => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400',
        6 => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=400',
      ];
      foreach ($categories as $cat):
        $img = $catImages[$cat['id']] ?? 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400';
      ?>
      <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['id'] ?>" class="category-card animate-on-scroll">
        <img src="<?= e($img) ?>" alt="<?= e($cat['name']) ?>" loading="lazy"/>
        <div class="category-info">
          <h3><?= e($cat['name']) ?></h3>
          <span>Shop Now <i class="fa fa-arrow-right"></i></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <h2>Featured Products</h2>
      <p>Handpicked styles our team is loving right now</p>
    </div>
    <div class="product-grid">
      <?php foreach ($featured as $p):
        $hasDiscount = $p['sale_price'] > 0;
        $displayPrice = $hasDiscount ? $p['sale_price'] : $p['price'];
      ?>
      <div class="product-card animate-on-scroll">
        <div class="product-img-wrap">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
          </a>
          <?php if (strpos($p['tags'],'new') !== false): ?><span class="product-badge badge-new">New</span><?php endif; ?>
          <?php if ($hasDiscount): ?><span class="product-badge badge-sale" style="top:40px">Sale</span><?php endif; ?>
          <div class="product-actions">
            <button class="product-action-btn" title="Wishlist"><i class="fa fa-heart"></i></button>
            <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn" title="Quick View"><i class="fa fa-eye"></i></a>
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
            <input type="hidden" name="size" value="M"/>
            <input type="hidden" name="color" value=""/>
            <input type="hidden" name="quantity" value="1"/>
            <button type="submit" class="btn-add-cart"><i class="fa fa-shopping-bag"></i> Add to Cart</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline"><i class="fa fa-th"></i> View All Products</a>
    </div>
  </div>
</section>

<!-- ===== PROMO BANNER ===== -->
<section class="section" style="padding:20px 0 60px">
  <div class="container">
    <div class="promo-banner">
      <div class="promo-content">
        <div class="promo-tag"><i class="fa fa-fire"></i> Limited Time Offer</div>
        <h2 class="promo-title">Mega Sale — Up to 40% Off</h2>
        <p class="promo-subtitle">Don't miss out on our biggest sale of the season. Use code <strong style="color:#c9a96e">SHOP20</strong> for extra 20% off!</p>
        <div class="promo-timer" id="promo-timer">
          <div class="timer-block"><span class="num" id="t-h">00</span><span class="lbl">Hours</span></div>
          <div class="timer-block"><span class="num" id="t-m">00</span><span class="lbl">Mins</span></div>
          <div class="timer-block"><span class="num" id="t-s">00</span><span class="lbl">Secs</span></div>
        </div>
        <a href="<?= SITE_URL ?>/pages/shop.php?tag=sale" class="btn btn-primary"><i class="fa fa-tag"></i> Shop Sale Now</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== NEW ARRIVALS ===== -->
<section class="section section-light">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <h2>New Arrivals</h2>
      <p>Just dropped — be the first to wear it</p>
    </div>
    <div class="product-grid">
      <?php foreach ($newArrivals as $p):
        $displayPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
      ?>
      <div class="product-card animate-on-scroll">
        <div class="product-img-wrap">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
          </a>
          <span class="product-badge badge-new">New</span>
        </div>
        <div class="product-info">
          <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>"><?= e($p['name']) ?></a></div>
          <div class="product-price"><span class="price-current"><?= formatPrice($displayPrice) ?></span></div>
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
  </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <h2>What Our Customers Say</h2>
      <p>Real reviews from real Gujju Clothing shoppers</p>
    </div>
    <div class="testimonials-grid">
      <?php foreach ($reviews as $rv): ?>
      <div class="testimonial-card animate-on-scroll">
        <div class="testimonial-stars">
          <?php for ($i=1;$i<=5;$i++): ?><i class="fa fa-star<?= $i > $rv['rating'] ? '-o' : '' ?>"></i><?php endfor; ?>
        </div>
        <p class="testimonial-text">"<?= e($rv['comment']) ?>"</p>
        <div class="testimonial-author">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($rv['reviewer_name']) ?>&background=c9a96e&color=000&size=44" alt="<?= e($rv['reviewer_name']) ?>" class="author-avatar"/>
          <div>
            <div class="author-name"><?= e($rv['reviewer_name']) ?></div>
            <div class="author-tag">Verified Buyer · <?= e($rv['product_name']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
// Countdown timer
(function(){
  var end = new Date(); end.setHours(end.getHours()+23,59,59);
  function tick(){
    var diff = Math.max(0, end - new Date());
    var h = Math.floor(diff/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
    var f = n => String(n).padStart(2,'0');
    document.getElementById('t-h').textContent = f(h);
    document.getElementById('t-m').textContent = f(m);
    document.getElementById('t-s').textContent = f(s);
  }
  tick(); setInterval(tick,1000);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
