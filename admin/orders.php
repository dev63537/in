<?php
// admin/orders.php
$pageTitle = 'Orders';
include __DIR__ . '/includes/admin_header.php';

// ── Status update ────────────────────────────────────────────
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $allowed = ['pending','processing','shipped','delivered','cancelled','refunded'];
    if (in_array($_GET['status'], $allowed)) {
        dbExecute("UPDATE orders SET status=? WHERE id=?", [$_GET['status'], (int)$_GET['id']]);
        setFlash('success', 'Order status updated.');
        // Preserve search params
        $back = SITE_URL . '/admin/orders.php';
        $qs = http_build_query(array_filter([
            'q'      => $_GET['q']      ?? '',
            'status' => $_GET['filter_status'] ?? '',
        ]));
        redirect($back . ($qs ? '?' . $qs : ''));
    }
}

// ── Search / filter ──────────────────────────────────────────
$q            = trim($_GET['q'] ?? '');
$filterStatus = $_GET['filter_status'] ?? '';

$where  = ['1=1'];
$params = [];
if ($q) {
    $where[] = '(order_number LIKE ? OR shipping_name LIKE ? OR shipping_phone LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
}
$allowedStatuses = ['pending','processing','shipped','delivered','cancelled','refunded'];
if ($filterStatus && in_array($filterStatus, $allowedStatuses)) {
    $where[] = 'status = ?';
    $params[] = $filterStatus;
}
$whereSQL = implode(' AND ', $where);

$orders = dbFetchAll(
    "SELECT id, order_number, shipping_name, shipping_email, shipping_phone,
            shipping_address, shipping_city, shipping_pincode, shipping_state,
            total_amount, payment_method, payment_status, status, notes, created_at
     FROM orders
     WHERE $whereSQL
     ORDER BY created_at DESC
     LIMIT 200",
    $params
);

// ── CSV Export ───────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Re-fetch all (no LIMIT) for export
    $allOrders = dbFetchAll(
        "SELECT id, order_number, shipping_name, shipping_email, shipping_phone,
                shipping_address, shipping_city, shipping_pincode,
                total_amount, payment_method, payment_status, status, created_at
         FROM orders WHERE $whereSQL ORDER BY created_at DESC",
        $params
    );
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Order #','Customer','Email','Phone','City','Address','Pincode','Amount','Payment Method','Payment Status','Order Status','Date']);
    foreach ($allOrders as $o) {
        fputcsv($out, [
            $o['order_number'],
            $o['shipping_name'],
            $o['shipping_email'],
            $o['shipping_phone'],
            $o['shipping_city'],
            $o['shipping_address'],
            $o['shipping_pincode'],
            number_format($o['total_amount'], 2),
            $o['payment_method'],
            $o['payment_status'],
            $o['status'],
            date('d M Y', strtotime($o['created_at'])),
        ]);
    }
    fclose($out);
    exit;
}
?>

<!-- Search & Filter Bar -->
<div class="admin-card" style="margin-bottom:16px">
  <form method="GET" action="" style="display:flex;align-items:center;gap:10px;padding:16px 20px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px;position:relative">
      <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.85rem"></i>
      <input type="text" name="q" value="<?= e($q) ?>"
             placeholder="Search by order #, name, phone…"
             style="width:100%;padding:9px 12px 9px 36px;border:1px solid #ddd;border-radius:8px;font-size:.88rem;outline:none"/>
    </div>
    <select name="filter_status"
            style="padding:9px 14px;border:1px solid #ddd;border-radius:8px;font-size:.88rem;cursor:pointer;background:#fff">
      <option value="">All Statuses</option>
      <?php foreach ($allowedStatuses as $s): ?>
      <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary" style="padding:9px 20px;font-size:.88rem">
      <i class="fa fa-filter"></i> Filter
    </button>
    <?php if ($q || $filterStatus): ?>
    <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline" style="padding:9px 18px;font-size:.88rem">
      <i class="fa fa-times"></i> Clear
    </a>
    <?php endif; ?>
  </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>Orders <span style="font-weight:400;font-size:.9rem;color:#aaa">(<?= count($orders) ?>)</span></h3>
    <div style="display:flex;gap:10px;align-items:center">
      <?php
        $exportQs = http_build_query(array_filter(['q' => $q, 'filter_status' => $filterStatus, 'export' => 'csv']));
      ?>
      <a href="?<?= $exportQs ?>"
         style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#27ae60;color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none">
        <i class="fa fa-file-csv"></i> Export CSV
      </a>
    </div>
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
            <select onchange="window.location.href='?update_status=1&id=<?= $o['id'] ?>&status='+this.value+'<?= $q ? '&q=' . urlencode($q) : '' ?><?= $filterStatus ? '&filter_status=' . urlencode($filterStatus) : '' ?>'"
                    style="padding:5px 10px;border:1px solid #ddd;border-radius:6px;font-size:.82rem;cursor:pointer">
              <?php foreach ($allowedStatuses as $s): ?>
              <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td style="font-size:.83rem;color:#888"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td>
            <div class="action-btns">
              <button class="action-btn view" onclick="showOrderDetail(<?= e(json_encode($o)) ?>)">
                <i class="fa fa-eye"></i> Details
              </button>
              <a href="<?= SITE_URL ?>/admin/invoice.php?id=<?= $o['id'] ?>" target="_blank"
                 class="action-btn" title="Print Invoice"
                 style="color:#8e44ad;border-color:#8e44ad;display:inline-flex;align-items:center;gap:5px">
                <i class="fa fa-print"></i> Invoice
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:48px;color:#aaa">
            <i class="fa fa-search" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
            No orders found<?= ($q || $filterStatus) ? ' for your search.' : '.' ?>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Order Detail Modal -->
<div id="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:540px;position:relative;max-height:90vh;overflow-y:auto">
    <button onclick="document.getElementById('order-modal').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h3 style="font-weight:700;margin-bottom:20px">Order Details</h3>
    <div id="order-detail-content"></div>
    <div id="order-modal-actions" style="margin-top:20px;display:flex;gap:10px"></div>
  </div>
</div>

<script>
function showOrderDetail(o) {
  document.getElementById('order-detail-content').innerHTML =
    '<table style="width:100%;font-size:.9rem;border-collapse:collapse">' +
    '<tr><td style="padding:8px 0;color:#888;width:38%">Order #</td><td><strong>' + o.order_number + '</strong></td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Customer</td><td>' + o.shipping_name + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Email</td><td>' + o.shipping_email + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Phone</td><td>' + o.shipping_phone + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Address</td><td>' + o.shipping_address + ', ' + o.shipping_city + (o.shipping_state ? ', ' + o.shipping_state : '') + ' — ' + o.shipping_pincode + '</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Payment</td><td>' + o.payment_method + ' (<em>' + o.payment_status + '</em>)</td></tr>' +
    '<tr><td style="padding:8px 0;color:#888">Order Status</td><td>' + o.status + '</td></tr>' +
    '<tr style="border-top:2px solid #eee"><td style="padding:12px 0;color:#888">Total</td><td><strong style="font-size:1.05rem">₹' + parseFloat(o.total_amount).toLocaleString('en-IN', {minimumFractionDigits:2}) + '</strong></td></tr>' +
    (o.notes ? '<tr><td style="padding:8px 0;color:#888;vertical-align:top">Notes</td><td style="font-size:.85rem;color:#555">' + o.notes + '</td></tr>' : '') +
    '</table>';
  document.getElementById('order-modal-actions').innerHTML =
    '<a href="<?= SITE_URL ?>/admin/invoice.php?id=' + o.id + '" target="_blank" style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:#8e44ad;color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none"><i class="fa fa-print" style="font-family:FontAwesome"></i> Print Invoice</a>';
  document.getElementById('order-modal').style.display = 'flex';
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
