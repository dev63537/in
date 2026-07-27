<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();

$orderNumber = $_SESSION['last_order_number'] ?? null;
$orderId     = $_SESSION['last_order_id'] ?? null;

if (!$orderNumber) {
    // If visited directly without placing order, redirect
    redirect(SITE_URL . '/pages/shop.php');
}

// Clear the session vars so page can't be revisited directly
unset($_SESSION['last_order_number'], $_SESSION['last_order_id']);

$order     = $orderId ? dbFetchOne("SELECT * FROM orders WHERE id=?", [$orderId]) : null;
$orderItems = $orderId ? dbFetchAll("SELECT * FROM order_items WHERE order_id=?", [$orderId]) : [];

$pageTitle = "Order Confirmed! — Gujju Clothing";
include __DIR__ . '/../includes/header.php';
?>
<div style="min-height:80vh;background:linear-gradient(135deg,#f9f7f4 0%,#fff 100%);padding:60px 20px">
  <div class="container" style="max-width:700px">

    <!-- Success Banner -->
    <div style="text-align:center;margin-bottom:48px">
      <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#27ae60,#2ecc71);margin:0 auto 20px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 32px rgba(39,174,96,.3);animation:popIn .6s ease">
        <i class="fa fa-check" style="font-size:2.2rem;color:#fff"></i>
      </div>
      <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;color:#0f0f0f;margin-bottom:8px">Order Confirmed!</h1>
      <p style="color:#555;font-size:1.05rem">Thank you for shopping with Gujju Clothing 🎉</p>
      <div style="display:inline-block;background:#e8f8f0;border:1px solid #a8e6bc;border-radius:50px;padding:8px 24px;margin-top:12px;font-weight:700;color:#27ae60;font-size:1rem">
        Order #<?= e($orderNumber) ?>
      </div>
    </div>

    <!-- Order Details Card -->
    <?php if ($order): ?>
    <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.08);margin-bottom:28px">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:24px;padding-bottom:14px;border-bottom:2px solid #c9a96e">Order Details</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        <div>
          <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Shipping To</div>
          <div style="font-weight:600"><?= e($order['shipping_name']) ?></div>
          <div style="font-size:.88rem;color:#555;margin-top:2px"><?= e($order['shipping_address']) ?>, <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> – <?= e($order['shipping_pincode']) ?></div>
          <div style="font-size:.85rem;color:#888;margin-top:4px"><i class="fa fa-phone" style="color:#c9a96e"></i> <?= e($order['shipping_phone']) ?></div>
        </div>
        <div>
          <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Payment</div>
          <div style="font-weight:600"><?= e($order['payment_method']) ?></div>
          <div style="margin-top:8px;font-size:.85rem">
            <span style="background:#fef9e7;color:#f39c12;padding:3px 10px;border-radius:50px;font-size:.75rem;font-weight:600"><?= ucfirst($order['payment_status']) ?></span>
          </div>
          <div style="margin-top:10px;font-size:.9rem">Total: <strong style="font-size:1.1rem"><?= formatPrice($order['total_amount']) ?></strong></div>
        </div>
      </div>

      <!-- Order Items -->
      <div>
        <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:12px">Items</div>
        <?php foreach ($orderItems as $item): ?>
        <div style="display:flex;gap:14px;padding:12px;background:#f9f7f4;border-radius:10px;margin-bottom:10px">
          <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" style="width:56px;height:64px;object-fit:cover;border-radius:8px"/>
          <div style="flex:1">
            <div style="font-weight:600;font-size:.9rem"><?= e($item['product_name']) ?></div>
            <div style="font-size:.78rem;color:#888">Size: <?= e($item['size']) ?> | Qty: <?= $item['quantity'] ?></div>
          </div>
          <div style="font-weight:700;white-space:nowrap"><?= formatPrice($item['subtotal']) ?></div>
        </div>
        <?php endforeach; ?>

        <div style="display:flex;justify-content:space-between;padding:14px;border-top:1px solid #f0ece6;margin-top:8px">
          <div>Subtotal</div>
          <div><?= formatPrice($order['total_amount'] - $order['shipping_charge']) ?></div>
        </div>
        <div style="display:flex;justify-content:space-between;padding:4px 14px;color:#888;font-size:.9rem">
          <div>Shipping</div>
          <div><?= $order['shipping_charge'] > 0 ? formatPrice($order['shipping_charge']) : '<span style="color:#27ae60">FREE</span>' ?></div>
        </div>
        <div style="display:flex;justify-content:space-between;padding:14px;border-top:2px solid #c9a96e;margin-top:8px;font-weight:800;font-size:1.05rem">
          <div>Total</div>
          <div style="color:#c9a96e"><?= formatPrice($order['total_amount']) ?></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- What's Next -->
    <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.08);margin-bottom:28px">
      <h3 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin-bottom:20px">What happens next?</h3>
      <?php $steps = [
        ['fa-envelope','Confirmation Email','We\'ve sent your order details to your email address.','#3498db'],
        ['fa-box','Order Processing','Our team will prepare and quality-check your items within 1–2 business days.','#e67e22'],
        ['fa-truck','Shipping','Your order will be dispatched and you\'ll receive a tracking number via email.','#8e44ad'],
        ['fa-home','Delivery','Sit back and relax — your order will arrive in 5–7 business days.','#27ae60'],
      ]; foreach ($steps as $s): ?>
      <div style="display:flex;gap:14px;margin-bottom:16px;align-items:flex-start">
        <div style="width:40px;height:40px;border-radius:50%;background:<?= $s[3] ?>18;border:1px solid <?= $s[3] ?>30;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fa <?= $s[0] ?>" style="color:<?= $s[3] ?>;font-size:.85rem"></i>
        </div>
        <div>
          <div style="font-weight:700;font-size:.92rem;margin-bottom:2px"><?= $s[1] ?></div>
          <div style="font-size:.85rem;color:#555"><?= $s[2] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Action Buttons -->
    <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center">
      <a href="<?= SITE_URL ?>/pages/track-order.php" class="btn btn-outline"><i class="fa fa-search"></i> Track Order</a>
      <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-outline"><i class="fa fa-list"></i> My Orders</a>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary"><i class="fa fa-shopping-bag"></i> Continue Shopping</a>
    </div>
  </div>
</div>

<style>
@keyframes popIn {
  0%   { transform: scale(0); opacity: 0; }
  60%  { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}
</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>