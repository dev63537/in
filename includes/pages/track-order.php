<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = "Track Your Order — Gujju Clothing";
$metaDesc  = "Track your Gujju Clothing order in real-time. Enter your order number to see shipping status.";

$order = null;
$orderItems = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNum = trim($_POST['order_number'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if (!$orderNum || !$email) {
        $error = 'Please enter both your order number and email address.';
    } else {
        $order = dbFetchOne(
            "SELECT * FROM orders WHERE order_number = ? AND shipping_email = ?",
            [$orderNum, $email]
        );
        if (!$order) {
            $error = 'No order found with those details. Please check your order number and email.';
        } else {
            $orderItems = dbFetchAll(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$order['id']]
            );
        }
    }
}

// If user is logged in, pre-fill from latest order session
$prefillOrder = $_SESSION['last_order_number'] ?? '';

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Track Your Order</h1>
    <p>Get real-time updates on your delivery status</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Track Order</nav>
  </div>
</section>

<div class="container" style="max-width:800px;padding:60px 20px">

  <!-- Track Form -->
  <div style="background:#fff;border-radius:16px;padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.09);margin-bottom:40px">
    <div style="text-align:center;margin-bottom:28px">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(201,169,110,.12);border:1px solid rgba(201,169,110,.3);margin:0 auto 16px;display:flex;align-items:center;justify-content:center">
        <i class="fa fa-shipping-fast" style="font-size:1.5rem;color:#c9a96e"></i>
      </div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:6px">Enter Your Order Details</h2>
      <p style="color:#888;font-size:.9rem">You can find your order number in your confirmation email</p>
    </div>

    <?php if ($error): ?>
    <div class="flash-message flash-error" style="position:static;margin-bottom:20px">
      <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label for="order_number">Order Number *</label>
          <div class="input-icon">
            <i class="fa fa-hashtag"></i>
            <input type="text" id="order_number" name="order_number" placeholder="e.g. DSH3F7A2B1"
                   value="<?= e($_POST['order_number'] ?? $prefillOrder) ?>" required style="text-transform:uppercase"/>
          </div>
        </div>
        <div class="form-group">
          <label for="track_email">Email Address *</label>
          <div class="input-icon">
            <i class="fa fa-envelope"></i>
            <input type="email" id="track_email" name="email" placeholder="you@example.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required/>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
        <i class="fa fa-search"></i> Track My Order
      </button>
    </form>
  </div>

  <!-- Order Result -->
  <?php if ($order): ?>
  <?php
    $statusColors = [
      'pending'    => ['#f39c12','#fef9e7','fa-clock'],
      'processing' => ['#3498db','#eaf4fb','fa-cog'],
      'shipped'    => ['#8e44ad','#f5eef8','fa-truck'],
      'delivered'  => ['#27ae60','#e8f8f0','fa-check-circle'],
      'cancelled'  => ['#e74c3c','#fdedec','fa-times-circle'],
      'refunded'   => ['#e74c3c','#fdedec','fa-undo'],
    ];
    $sc = $statusColors[$order['status']] ?? ['#888','#f5f5f5','fa-question-circle'];

    $steps = ['pending','processing','shipped','delivered'];
    $currentStep = array_search($order['status'], $steps);
  ?>

  <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.09)">
    <!-- Order Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;flex-wrap:wrap;gap:16px">
      <div>
        <div style="font-size:.8rem;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Order Number</div>
        <div style="font-size:1.3rem;font-weight:800;color:#0f0f0f">#<?= e($order['order_number']) ?></div>
        <div style="font-size:.85rem;color:#888;margin-top:4px">Placed on <?= date('d M Y', strtotime($order['created_at'])) ?></div>
      </div>
      <div style="background:<?= $sc[1] ?>;color:<?= $sc[0] ?>;padding:10px 20px;border-radius:50px;font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:8px">
        <i class="fa <?= $sc[2] ?>"></i> <?= ucfirst($order['status']) ?>
      </div>
    </div>

    <!-- Progress Bar (only for non-cancelled) -->
    <?php if (!in_array($order['status'], ['cancelled','refunded'])): ?>
    <div style="margin-bottom:32px">
      <div style="display:flex;justify-content:space-between;position:relative">
        <div style="position:absolute;top:20px;left:0;right:0;height:3px;background:#f0ece6;z-index:0">
          <div style="height:100%;background:#c9a96e;width:<?= $currentStep !== false ? ($currentStep / (count($steps)-1) * 100) : 0 ?>%;transition:.5s"></div>
        </div>
        <?php foreach ($steps as $i => $step):
          $done   = $currentStep !== false && $i <= $currentStep;
          $active = $currentStep !== false && $i === $currentStep;
        ?>
        <div style="text-align:center;z-index:1;flex:1">
          <div style="width:40px;height:40px;border-radius:50%;background:<?= $done?'#c9a96e':'#f0ece6' ?>;color:<?= $done?'#0f0f0f':'#aaa' ?>;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;border:3px solid <?= $active?'#a8844d':($done?'#c9a96e':'#e0e0e0') ?>">
            <?= $done && !$active ? '<i class="fa fa-check"></i>' : ($i+1) ?>
          </div>
          <div style="font-size:.75rem;font-weight:600;color:<?= $done?'#0f0f0f':'#aaa' ?>;text-transform:capitalize"><?= $step ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Shipping Info -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
      <div style="background:#f9f7f4;border-radius:10px;padding:18px">
        <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:8px">Shipping To</div>
        <div style="font-weight:600;margin-bottom:4px"><?= e($order['shipping_name']) ?></div>
        <div style="font-size:.88rem;color:#555"><?= e($order['shipping_address']) ?>, <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> – <?= e($order['shipping_pincode']) ?></div>
        <div style="font-size:.85rem;color:#888;margin-top:6px"><i class="fa fa-phone" style="color:#c9a96e"></i> <?= e($order['shipping_phone']) ?></div>
      </div>
      <div style="background:#f9f7f4;border-radius:10px;padding:18px">
        <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:8px">Payment</div>
        <div style="font-weight:600"><?= e($order['payment_method']) ?></div>
        <div style="margin-top:8px"><span style="background:<?= $order['payment_status']==='paid'?'#e8f8f0':'#fef9e7' ?>;color:<?= $order['payment_status']==='paid'?'#27ae60':'#f39c12' ?>;padding:3px 10px;border-radius:50px;font-size:.78rem;font-weight:600"><?= ucfirst($order['payment_status']) ?></span></div>
        <div style="margin-top:10px;font-size:.85rem;color:#555">Total: <strong><?= formatPrice($order['total_amount']) ?></strong></div>
      </div>
    </div>

    <!-- Order Items -->
    <div>
      <div style="font-weight:700;font-size:.9rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;color:#888">Items in this order</div>
      <?php foreach ($orderItems as $item): ?>
      <div style="display:flex;gap:14px;padding:14px;background:#fafafa;border-radius:10px;margin-bottom:10px">
        <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" style="width:60px;height:70px;object-fit:cover;border-radius:8px"/>
        <div style="flex:1">
          <div style="font-weight:600;font-size:.9rem;margin-bottom:3px"><?= e($item['product_name']) ?></div>
          <div style="font-size:.78rem;color:#888">Size: <?= e($item['size']) ?> | Qty: <?= $item['quantity'] ?></div>
          <div style="font-weight:700;margin-top:4px"><?= formatPrice($item['subtotal']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Help Box -->
  <div style="text-align:center;margin-top:36px;padding:28px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06)">
    <p style="color:#888;margin-bottom:14px">Can't find your order or need more help?</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="<?= SITE_URL ?>/pages/faq.php" class="btn btn-outline"><i class="fa fa-question-circle"></i> FAQ</a>
      <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-primary"><i class="fa fa-headset"></i> Contact Support</a>
    </div>
  </div>
</div>
<style>@media(max-width:600px){.track-grid{grid-template-columns:1fr!important}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
