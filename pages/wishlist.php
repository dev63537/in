<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();

// Handle wishlist add/remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['product_id'] ?? 0);
    if ($action === 'add' && $pid)    addToWishlist($pid);
    if ($action === 'remove' && $pid) removeFromWishlist($pid);
    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['count' => count(getWishlist()), 'in_wishlist' => isInWishlist($pid)]);
        exit;
    }
    redirect(SITE_URL . '/pages/wishlist.php');
}

$wishlistIds = getWishlist();
$products    = [];
if (!empty($wishlistIds)) {
    $in  = implode(',', array_map('intval', $wishlistIds));
    $products = dbFetchAll("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id IN ($in) AND p.status='active'");
}

$pageTitle = "My Wishlist — Gujju Clothing";
$metaDesc  = "Your saved items on Gujju Clothing. Add them to cart anytime.";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>My Wishlist</h1>
    <p><?= count($products) ?> item<?= count($products) != 1 ? 's' : '' ?> saved</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Wishlist</nav>
  </div>
</section>

<div class="container" style="padding:50px 20px">
  <?php if (empty($products)): ?>
  <div style="text-align:center;padding:80px 20px">
    <div style="width:80px;height:80px;border-radius:50%;background:#fff0f5;border:2px solid #ffcdd2;margin:0 auto 20px;display:flex;align-items:center;justify-content:center">
      <i class="fa fa-heart" style="font-size:2rem;color:#e74c3c;opacity:.5"></i>
    </div>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:12px">Your wishlist is empty</h2>
    <p style="color:#888;margin-bottom:28px">Save items you love and find them here for later.</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary"><i class="fa fa-shopping-bag"></i> Browse Products</a>
  </div>
  <?php else: ?>

  <div style="display:flex;justify-content:flex-end;margin-bottom:20px">
    <form method="POST" action="" onsubmit="return confirm('Clear entire wishlist?')">
      <input type="hidden" name="action" value="clear_all"/>
      <button type="button" onclick="clearWishlist()" class="btn btn-outline" style="font-size:.85rem;padding:8px 18px">
        <i class="fa fa-trash"></i> Clear All
      </button>
    </form>
  </div>

  <div class="product-grid">
    <?php foreach ($products as $p):
      $displayPrice = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
      $hasDiscount  = $p['sale_price'] > 0;
    ?>
    <div class="product-card animate-on-scroll" id="wish-card-<?= $p['id'] ?>">
      <div class="product-img-wrap">
        <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
          <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
        </a>
        <?php if ($hasDiscount): ?><span class="product-badge badge-sale">Sale</span><?php endif; ?>
        <!-- Remove from Wishlist -->
        <div class="product-actions" style="opacity:1;transform:translateX(0)">
          <form method="POST" action="" style="display:contents">
            <input type="hidden" name="action" value="remove"/>
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
            <button type="submit" class="product-action-btn" title="Remove from Wishlist" style="background:#fff0f5">
              <i class="fa fa-heart" style="color:#e74c3c"></i>
            </button>
          </form>
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn" title="View Product">
            <i class="fa fa-eye"></i>
          </a>
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
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Continue Shopping</a>
  </div>
  <?php endif; ?>
</div>

<script>
function clearWishlist() {
  if (!confirm('Remove all items from your wishlist?')) return;
  fetch('<?= SITE_URL ?>/pages/wishlist.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'action=clear_all'
  }).then(() => location.reload());
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
