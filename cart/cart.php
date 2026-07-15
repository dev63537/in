<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
$pageTitle = "Shopping Cart — Gujju Clothing";
$cart = $_SESSION['cart'] ?? [];
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Shopping Cart</h1>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Cart</nav>
  </div>
</section>

<div class="container">
<?php if (empty($cart)): ?>
  <div style="text-align:center;padding:80px 20px">
    <i class="fa fa-shopping-bag" style="font-size:4rem;color:#ddd;margin-bottom:20px;display:block"></i>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:12px">Your cart is empty</h2>
    <p style="color:#888;margin-bottom:28px">Looks like you haven't added anything yet.</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary"><i class="fa fa-store"></i> Start Shopping</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <!-- Cart Table -->
    <div>
      <div class="cart-table">
        <table>
          <thead>
            <tr>
              <th colspan="2">Product</th>
              <th>Size / Color</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $key => $item): ?>
            <tr>
              <td style="width:90px">
                <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="cart-item-img"/>
              </td>
              <td>
                <div class="cart-item-name"><?= e($item['name']) ?></div>
              </td>
              <td>
                <div class="cart-item-meta"><?= e($item['size']) ?> / <?= e($item['color']) ?></div>
              </td>
              <td><?= formatPrice($item['price']) ?></td>
              <td>
                <input type="number" class="cart-qty-input" value="<?= (int)$item['quantity'] ?>"
                      min="1" max="99" data-key="<?= e($key) ?>"/>
              </td>
              <td><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
              <td>
                <button class="cart-remove-btn" data-key="<?= e($key) ?>" title="Remove">
                  <i class="fa fa-trash-alt"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Continue Shopping</a>
      </div>
    </div>

    <!-- Cart Summary -->
    <div class="cart-summary">
      <h3>Order Summary</h3>
      <?php 
      $subtotal = getCartTotal(); 
      $shipping = $subtotal >= 999 ? 0 : 99; 
      $discount = 0;
      if (isset($_SESSION['coupon'])) {
          $c = $_SESSION['coupon'];
          if ($subtotal >= $c['min_order']) {
              $discount = $c['type'] === 'percent' ? ($subtotal * $c['value'] / 100) : $c['value'];
              if ($discount > $subtotal) $discount = $subtotal;
          } else {
              unset($_SESSION['coupon']);
          }
      }
      $total = $subtotal - $discount + $shipping; 
      ?>
      <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $shipping == 0 ? '<span style="color:#27ae60">FREE</span>' : formatPrice($shipping) ?></span></div>
      <?php if ($shipping > 0): ?>
      <div class="summary-row" style="font-size:.8rem;color:#888"><span colspan="2">Add <?= formatPrice(999 - $subtotal) ?> more for free shipping</span></div>
      <?php endif; ?>
      <?php if ($discount > 0): ?>
      <div class="summary-row" style="color:#27ae60"><span>Discount (<?= e($_SESSION['coupon']['code']) ?>)</span><span>-<?= formatPrice($discount) ?></span></div>
      <?php endif; ?>
      <div class="coupon-wrap">
        <input type="text" id="coupon-input" placeholder="Coupon code" value="<?= isset($_SESSION['coupon']) ? e($_SESSION['coupon']['code']) : '' ?>" <?= isset($_SESSION['coupon']) ? 'disabled' : '' ?>/>
        <button type="button" id="apply-coupon-btn"><?= isset($_SESSION['coupon']) ? 'Remove' : 'Apply' ?></button>
      </div>
      <div class="summary-row total"><span>Total</span><span id="order-total"><?= formatPrice($total) ?></span></div>
      <a href="<?= SITE_URL ?>/cart/checkout.php" class="btn btn-primary btn-full" style="margin-top:16px">
        <i class="fa fa-lock"></i> Proceed to Checkout
      </a>
      <div style="margin-top:14px;text-align:center;font-size:.8rem;color:#888">
        <i class="fa fa-shield-alt"></i> Secure & Encrypted Checkout
      </div>
    </div>
  </div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
document.getElementById('apply-coupon-btn')?.addEventListener('click', function() {
    let isRemove = this.innerText.trim() === 'Remove';
    let code = document.getElementById('coupon-input').value.trim();
    if (!code && !isRemove) return;
    
    fetch('<?= SITE_URL ?>/cart/cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: isRemove ? 'remove_coupon' : 'apply_coupon', code: code })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Invalid coupon');
        }
    });
});
</script>
