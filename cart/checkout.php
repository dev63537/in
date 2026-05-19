<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin('/auth/login.php?redirect=/cart/checkout.php');
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) { redirect(SITE_URL . '/cart/cart.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid request.'; }
    else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $state   = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $method  = trim($_POST['payment_method'] ?? 'COD');

        if (!$name || !$email || !$phone || !$address || !$city || !$pincode)
            $errors[] = 'All fields are required.';

        if (!$errors) {
            $subtotal = getCartTotal();
            $shipping = $subtotal >= 999 ? 0 : 99;
            $total    = $subtotal + $shipping;
            $orderNum = 'DSH' . strtoupper(substr(uniqid(), -7));

            dbExecute("INSERT INTO orders (user_id,order_number,total_amount,shipping_charge,payment_method,shipping_name,shipping_email,shipping_phone,shipping_address,shipping_city,shipping_state,shipping_pincode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [$_SESSION['user_id'], $orderNum, $total, $shipping, $method, $name, $email, $phone, $address, $city, $state, $pincode]);
            $orderId = dbLastId();

            foreach ($cart as $item) {
                dbExecute("INSERT INTO order_items (order_id,product_id,product_name,product_image,size,color,quantity,unit_price,subtotal) VALUES (?,?,?,?,?,?,?,?,?)",
                    [$orderId, $item['product_id'], $item['name'], $item['image'], $item['size'], $item['color'], $item['quantity'], $item['price'], $item['price']*$item['quantity']]);
            }
            unset($_SESSION['cart']);
            setFlash('success', "Order #{$orderNum} placed successfully! We'll contact you soon.");
            redirect(SITE_URL . '/pages/orders.php');
        }
    }
}
$pageTitle = "Checkout — Devendra's Shop";
$subtotal  = getCartTotal();
$shipping  = $subtotal >= 999 ? 0 : 99;
$total     = $subtotal + $shipping;
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Checkout</h1>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / <a href="<?= SITE_URL ?>/cart/cart.php">Cart</a> / Checkout</nav>
  </div>
</section>

<div class="container" style="padding:40px 20px">
  <?php if ($errors): ?>
    <div class="flash-message flash-error" style="position:static;margin-bottom:20px">
      <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:36px;align-items:start" class="checkout-grid">
    <!-- Shipping Form -->
    <div class="contact-form-wrap">
      <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px;font-size:1.4rem">Shipping Information</h2>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="form-group" style="grid-column:1/-1"><label>Full Name *</label><input type="text" name="name" required value="<?= e($_POST['name'] ?? $_SESSION['user_name'] ?? '') ?>"/></div>
          <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"/></div>
          <div class="form-group"><label>Phone *</label><input type="tel" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"/></div>
          <div class="form-group" style="grid-column:1/-1"><label>Address *</label><input type="text" name="address" required value="<?= e($_POST['address'] ?? '') ?>"/></div>
          <div class="form-group"><label>City *</label><input type="text" name="city" required value="<?= e($_POST['city'] ?? '') ?>"/></div>
          <div class="form-group"><label>State *</label><input type="text" name="state" value="<?= e($_POST['state'] ?? '') ?>"/></div>
          <div class="form-group"><label>Pincode *</label><input type="text" name="pincode" required maxlength="6" value="<?= e($_POST['pincode'] ?? '') ?>"/></div>
        </div>
        <h3 style="margin:20px 0 14px;font-size:1rem;font-weight:700">Payment Method</h3>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;border:1.5px solid #e0e0e0;border-radius:8px;transition:.3s" onclick="this.style.borderColor='#c9a96e'">
            <input type="radio" name="payment_method" value="COD" checked/> <i class="fa fa-money-bill"></i> Cash on Delivery
          </label>
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;border:1.5px solid #e0e0e0;border-radius:8px;transition:.3s">
            <input type="radio" name="payment_method" value="UPI"/> <i class="fa fa-qrcode"></i> UPI Payment
          </label>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:15px">
          <i class="fa fa-check-circle"></i> Place Order — <?= formatPrice($total) ?>
        </button>
      </form>
    </div>
    <!-- Order Summary -->
    <div class="cart-summary" style="position:sticky;top:90px">
      <h3>Order Summary</h3>
      <?php foreach ($cart as $item): ?>
      <div style="display:flex;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f0ece6">
        <img src="<?= e($item['image']) ?>" style="width:60px;height:70px;object-fit:cover;border-radius:8px"/>
        <div style="flex:1">
          <div style="font-size:.9rem;font-weight:600"><?= e($item['name']) ?></div>
          <div style="font-size:.78rem;color:#888"><?= e($item['size']) ?> / <?= e($item['color']) ?> × <?= $item['quantity'] ?></div>
          <div style="font-weight:700;margin-top:4px"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $shipping == 0 ? '<span style="color:#27ae60">FREE</span>' : formatPrice($shipping) ?></span></div>
      <div class="summary-row total"><span>Total</span><span><?= formatPrice($total) ?></span></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<style>@media(max-width:768px){.checkout-grid{grid-template-columns:1fr!important}}</style>
