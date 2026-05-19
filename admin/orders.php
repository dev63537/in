<?php
// admin/orders.php
$pageTitle = 'Orders';
include __DIR__ . '/includes/admin_header.php';
$orders = dbFetchAll("SELECT * FROM orders ORDER BY created_at DESC");

if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $allowed = ['pending','processing','shipped','delivered','cancelled','refunded'];
    if (in_array($_GET['status'], $allowed)) {
        dbExecute("UPDATE orders SET status=? WHERE id=?", [$_GET['status'], (int)$_GET['id']]);
        setFlash('success', 'Order status updated.');
        redirect(SITE_URL . '/admin/orders.php');
    }
}
?>
<div class="admin-card">
  <div class="admin-card-header">
    <h3>All Orders (<?= count($orders) ?>)</h3>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order #</th><th>Customer</th><th>Phone</th><th>Amount</th>
          <th>Payment</th><th>Status</th><th>Date</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong><?= e($o['order_number']) ?></strong></td>
          <td>
            <div><?= e($o['shipping_name']) ?></div>
            <div style="font-size:.78rem;color:#888"><?= e($o['shipping_email']) ?></div>
          </td>
          <td><?= e($o['shipping_phone']) ?></td>
          <td><strong><?= formatPrice($o['total_amount']) ?></strong></td>
          <td><span class="status-badge status-<?= e($o['payment_status']) ?>"><?= ucfirst($o['payment_status']) ?></span></td>
          <td>
            <select onchange="window.location.href='?update_status=1&id=<?= $o['id'] ?>&status='+this.value"
                    style="padding:5px 10px;border:1px solid #ddd;border-radius:6px;font-size:.82rem;cursor:pointer">
              <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td style="font-size:.83rem;color:#888"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td>
            <button class="action-btn view" onclick="showOrderDetail(<?= e(json_encode($o)) ?>)">Details</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Order Detail Modal -->
<div id="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:520px;position:relative">
    <button onclick="document.getElementById('order-modal').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:20px">Order Details</h3>
    <div id="order-detail-content"></div>
  </div>
</div>

<script>
function showOrderDetail(o) {
  document.getElementById('order-detail-content').innerHTML =
    '<table style="width:100%;font-size:.9rem;border-collapse:collapse">' +
    '<tr><td style="padding:8px 0;color:#888">Order #</td><td><strong>' + o.order_number + '</strong></td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Customer</td><td>' + o.shipping_name + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Email</td><td>' + o.shipping_email + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Phone</td><td>' + o.shipping_phone + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Address</td><td>' + o.shipping_address + ', ' + o.shipping_city + ' ' + o.shipping_pincode + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Payment</td><td>' + o.payment_method + ' (' + o.payment_status + ')</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Total</td><td><strong>₹' + parseFloat(o.total_amount).toFixed(2) + '</strong></td></tr>' +
    '</table>';
  document.getElementById('order-modal').style.display = 'flex';
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
