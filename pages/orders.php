<?php
require_once __DIR__ . '/../includes/functions.php';
startSession(); requireLogin();
$orders = dbFetchAll("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC", [$_SESSION['user_id']]);
$pageTitle = "My Orders — Gujju Clothing";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><h1>My Orders</h1></div></section>
<div class="container" style="padding:40px 20px">
  <?php if (empty($orders)): ?>
    <div style="text-align:center;padding:60px"><i class="fa fa-box-open" style="font-size:3rem;color:#ddd;display:block;margin-bottom:16px"></i><h3>No orders yet</h3><a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">Start Shopping</a></div>
  <?php else: ?>
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden">
      <table style="width:100%;border-collapse:collapse">
        <thead><tr style="background:#f8f6f2"><th style="padding:14px 20px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:1px;color:#888">Order #</th><th style="padding:14px 20px;text-align:left">Date</th><th style="padding:14px 20px;text-align:left">Total</th><th style="padding:14px 20px;text-align:left">Status</th><th style="padding:14px 20px;text-align:left">Payment</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr style="border-bottom:1px solid #f0ece6">
            <td style="padding:14px 20px;font-weight:700"><?= e($o['order_number']) ?></td>
            <td style="padding:14px 20px;color:#888;font-size:.88rem"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td style="padding:14px 20px;font-weight:700"><?= formatPrice($o['total_amount']) ?></td>
            <td style="padding:14px 20px"><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="padding:14px 20px"><span class="status-badge status-<?= e($o['payment_status']) ?>"><?= ucfirst($o['payment_status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
