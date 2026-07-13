<?php
// admin/invoice.php — Printable Order Invoice
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireAdmin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    header('Location: ' . SITE_URL . '/admin/orders.php');
    exit;
}

$order = dbFetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
if (!$order) {
    die('Order not found.');
}

$items = dbFetchAll(
    "SELECT oi.*, p.name AS product_name, p.sku FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$orderId]
);

$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
$discount  = (float)($order['discount_amount'] ?? 0);
$shipping  = (float)($order['shipping_amount'] ?? 0);
$total     = (float)$order['total_amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Invoice #<?= e($order['order_number']) ?> — Devendra's Shop</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 13px;
      color: #111;
      background: #f4f4f4;
    }
    .invoice-wrapper {
      max-width: 760px;
      margin: 30px auto;
      background: #fff;
      padding: 48px 52px;
      box-shadow: 0 4px 24px rgba(0,0,0,.10);
      border-radius: 4px;
    }
    /* ── Header ── */
    .inv-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #111;
      padding-bottom: 24px;
      margin-bottom: 28px;
    }
    .inv-brand { font-size: 22px; font-weight: 800; letter-spacing: -.5px; }
    .inv-brand small { display: block; font-size: 11px; font-weight: 400; color: #555; margin-top: 3px; }
    .inv-meta { text-align: right; }
    .inv-meta h2 { font-size: 28px; font-weight: 800; letter-spacing: 1px; color: #111; }
    .inv-meta p { font-size: 12px; color: #555; margin-top: 4px; }
    .inv-meta strong { color: #111; }
    /* ── Addresses ── */
    .inv-addresses {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 30px;
    }
    .inv-box { padding: 16px 18px; border: 1px solid #ddd; border-radius: 4px; }
    .inv-box h4 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 8px; }
    .inv-box p { font-size: 13px; line-height: 1.6; }
    /* ── Items Table ── */
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .inv-table thead tr { background: #111; color: #fff; }
    .inv-table thead th { padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
    .inv-table tbody tr { border-bottom: 1px solid #eee; }
    .inv-table tbody tr:last-child { border-bottom: none; }
    .inv-table tbody td { padding: 11px 12px; font-size: 13px; vertical-align: top; }
    .inv-table tfoot td { padding: 9px 12px; font-size: 13px; }
    .inv-table tfoot tr.total-row td { font-weight: 800; font-size: 15px; border-top: 2px solid #111; }
    .inv-table tfoot tr.total-row td:last-child { font-size: 17px; }
    .align-right { text-align: right; }
    /* ── Payment/Status ── */
    .inv-footer-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid #ddd;
      font-size: 12px;
      color: #555;
    }
    .inv-footer-row p { margin-bottom: 4px; }
    .inv-footer-row strong { color: #111; }
    .status-pill {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 50px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .5px;
      text-transform: uppercase;
    }
    .pill-paid     { background:#e8fdf2; color:#27ae60; border:1px solid #27ae60; }
    .pill-pending  { background:#fff8e1; color:#e67e22; border:1px solid #e67e22; }
    .pill-cod      { background:#eaf4ff; color:#2980b9; border:1px solid #2980b9; }
    .pill-delivered{ background:#e8fdf2; color:#27ae60; border:1px solid #27ae60; }
    .pill-shipped  { background:#eaf4ff; color:#2980b9; border:1px solid #2980b9; }
    .pill-cancelled{ background:#fdecea; color:#e74c3c; border:1px solid #e74c3c; }
    /* ── Print Button ── */
    .print-btn-bar {
      text-align: center;
      margin: 24px 0 0;
    }
    .print-btn-bar a {
      display: inline-block;
      margin-right: 10px;
      padding: 10px 24px;
      background: #111;
      color: #fff;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      border-radius: 4px;
      cursor: pointer;
    }
    .print-btn-bar a.back { background: #eee; color: #333; }
    .inv-note {
      margin-top: 32px;
      padding: 14px 18px;
      background: #fafafa;
      border-left: 3px solid #111;
      font-size: 12px;
      color: #555;
      line-height: 1.6;
    }
    /* ── Print media ── */
    @media print {
      body { background: #fff; }
      .invoice-wrapper { box-shadow: none; margin: 0; padding: 24px; border-radius: 0; }
      .print-btn-bar { display: none; }
    }
  </style>
</head>
<body>
<div class="invoice-wrapper">

  <!-- Header -->
  <div class="inv-header">
    <div class="inv-brand">
      Devendra's Shop
      <small>Premium Fashion &amp; Lifestyle</small>
    </div>
    <div class="inv-meta">
      <h2>INVOICE</h2>
      <p><strong>#<?= e($order['order_number']) ?></strong></p>
      <p>Date: <strong><?= date('d M Y', strtotime($order['created_at'])) ?></strong></p>
      <p>
        Order Status:
        <?php
          $statusPills = [
            'pending'    => 'pill-pending',
            'processing' => 'pill-cod',
            'shipped'    => 'pill-shipped',
            'delivered'  => 'pill-delivered',
            'cancelled'  => 'pill-cancelled',
            'refunded'   => 'pill-cancelled',
          ];
          $pillClass = $statusPills[$order['status']] ?? 'pill-pending';
        ?>
        <span class="status-pill <?= $pillClass ?>"><?= ucfirst(e($order['status'])) ?></span>
      </p>
    </div>
  </div>

  <!-- Addresses -->
  <div class="inv-addresses">
    <div class="inv-box">
      <h4>From</h4>
      <p>
        <strong>Devendra's Shop</strong><br>
        support@devendrashop.com<br>
        India
      </p>
    </div>
    <div class="inv-box">
      <h4>Bill To / Ship To</h4>
      <p>
        <strong><?= e($order['shipping_name']) ?></strong><br>
        <?= e($order['shipping_address']) ?><br>
        <?= e($order['shipping_city']) ?><?= !empty($order['shipping_state']) ? ', ' . e($order['shipping_state']) : '' ?> — <?= e($order['shipping_pincode'] ?? '') ?><br>
        <?= e($order['shipping_phone']) ?><br>
        <?= e($order['shipping_email']) ?>
      </p>
    </div>
  </div>

  <!-- Items -->
  <table class="inv-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th>SKU</th>
        <th>Size / Color</th>
        <th class="align-right">Qty</th>
        <th class="align-right">Unit Price</th>
        <th class="align-right">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $idx => $item): ?>
      <tr>
        <td><?= $idx + 1 ?></td>
        <td><strong><?= e($item['product_name'] ?? $item['name'] ?? '—') ?></strong></td>
        <td style="color:#888;font-size:11px"><?= e($item['sku'] ?? '—') ?></td>
        <td><?= e($item['size'] ?? '—') ?> / <?= e($item['color'] ?? '—') ?></td>
        <td class="align-right"><?= (int)$item['quantity'] ?></td>
        <td class="align-right"><?= formatPrice($item['price']) ?></td>
        <td class="align-right"><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="align-right" style="color:#555">Subtotal</td>
        <td class="align-right"><?= formatPrice($subtotal) ?></td>
      </tr>
      <?php if ($discount > 0): ?>
      <tr>
        <td colspan="6" class="align-right" style="color:#27ae60">Discount<?= !empty($order['coupon_code']) ? ' (' . e($order['coupon_code']) . ')' : '' ?></td>
        <td class="align-right" style="color:#27ae60">− <?= formatPrice($discount) ?></td>
      </tr>
      <?php endif; ?>
      <?php if ($shipping > 0): ?>
      <tr>
        <td colspan="6" class="align-right" style="color:#555">Shipping</td>
        <td class="align-right"><?= formatPrice($shipping) ?></td>
      </tr>
      <?php else: ?>
      <tr>
        <td colspan="6" class="align-right" style="color:#27ae60">Shipping</td>
        <td class="align-right" style="color:#27ae60">FREE</td>
      </tr>
      <?php endif; ?>
      <tr class="total-row">
        <td colspan="6" class="align-right">Total</td>
        <td class="align-right"><?= formatPrice($total) ?></td>
      </tr>
    </tfoot>
  </table>

  <!-- Payment & Notes -->
  <div class="inv-footer-row">
    <div>
      <p>Payment Method: <strong><?= ucfirst(e($order['payment_method'] ?? '—')) ?></strong></p>
      <p>Payment Status:
        <?php $ps = $order['payment_status'] ?? 'pending'; ?>
        <span class="status-pill <?= $ps === 'paid' ? 'pill-paid' : 'pill-pending' ?>"><?= ucfirst(e($ps)) ?></span>
      </p>
      <?php if (!empty($order['transaction_id'])): ?>
      <p style="margin-top:4px">Transaction ID: <strong><?= e($order['transaction_id']) ?></strong></p>
      <?php endif; ?>
    </div>
    <div style="text-align:right">
      <p style="font-size:11px;color:#aaa">Generated on <?= date('d M Y, h:i A') ?></p>
      <p style="font-size:11px;color:#aaa">Devendra's Shop — devendrashop.com</p>
    </div>
  </div>

  <?php if (!empty($order['notes'])): ?>
  <div class="inv-note">
    <strong>Order Notes:</strong> <?= e($order['notes']) ?>
  </div>
  <?php endif; ?>

  <div class="inv-note" style="margin-top:16px">
    Thank you for shopping with Devendra's Shop! For any queries, please contact us at support@devendrashop.com.
    This is a computer-generated invoice and does not require a signature.
  </div>

</div>

<!-- Print Bar -->
<div class="print-btn-bar">
  <a class="back" href="<?= SITE_URL ?>/admin/orders.php"><i style="margin-right:6px">←</i> Back to Orders</a>
  <a href="#" onclick="window.print();return false;">🖨 Print Invoice</a>
</div>

</body>
</html>
