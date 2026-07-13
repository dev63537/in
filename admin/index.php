<?php
// admin/index.php — Dashboard
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/admin_header.php';

$totalProducts = dbFetchOne("SELECT COUNT(*) AS c FROM products")['c'];
$totalOrders   = dbFetchOne("SELECT COUNT(*) AS c FROM orders")['c'];
$totalUsers    = dbFetchOne("SELECT COUNT(*) AS c FROM users WHERE role='customer'")['c'];
$totalRevenue  = dbFetchOne("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE payment_status='paid'")['s'];

$recentOrders  = dbFetchAll("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8");

// Top 5 Selling Products (by order_items count)
$topProducts   = dbFetchAll(
    "SELECT p.name, p.image, p.price, p.sale_price, COUNT(oi.id) AS sold
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     GROUP BY oi.product_id
     ORDER BY sold DESC LIMIT 5"
);

// Monthly revenue — last 6 months
$monthlyRevenue = dbFetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
            DATE_FORMAT(created_at, '%b %Y')  AS label,
            COALESCE(SUM(total_amount), 0)    AS revenue
     FROM orders
     WHERE payment_status = 'paid'
       AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY ym, label
     ORDER BY ym ASC"
);

// Pad up to 6 months if fewer results (so chart always shows 6 bars)
$chartLabels  = [];
$chartData    = [];
// Build a map of what we have
$revenueMap = [];
foreach ($monthlyRevenue as $mr) {
    $revenueMap[$mr['ym']] = ['label' => $mr['label'], 'revenue' => (float)$mr['revenue']];
}
// Generate last 6 months
for ($i = 5; $i >= 0; $i--) {
    $ym  = date('Y-m', strtotime("-$i month"));
    $lbl = date('M Y', strtotime("-$i month"));
    $chartLabels[] = $revenueMap[$ym]['label'] ?? $lbl;
    $chartData[]   = $revenueMap[$ym]['revenue'] ?? 0;
}

// Low Stock Products
$lowStockProducts = dbFetchAll(
    "SELECT p.name, p.stock, p.low_stock_alert, c.name AS cat_name, p.id
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.stock <= COALESCE(p.low_stock_alert, 5)
     ORDER BY p.stock ASC
     LIMIT 10"
);
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card gold">
    <div class="stat-icon gold-bg"><i class="fa fa-indian-rupee-sign"></i></div>
    <div class="stat-info"><h3><?= formatPrice($totalRevenue) ?></h3><p>Total Revenue</p><div class="stat-trend trend-up"><i class="fa fa-arrow-up"></i> Paid orders</div></div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue-bg"><i class="fa fa-shopping-cart"></i></div>
    <div class="stat-info"><h3><?= $totalOrders ?></h3><p>Total Orders</p></div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon green-bg"><i class="fa fa-users"></i></div>
    <div class="stat-info"><h3><?= $totalUsers ?></h3><p>Customers</p></div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon red-bg"><i class="fa fa-box"></i></div>
    <div class="stat-info"><h3><?= $totalProducts ?></h3><p>Products</p></div>
  </div>
</div>

<!-- Monthly Revenue Chart -->
<div class="admin-card" style="margin-bottom:24px">
  <div class="admin-card-header">
    <h3><i class="fa fa-chart-bar" style="margin-right:8px;color:var(--primary,#8B6F47)"></i>Monthly Revenue — Last 6 Months</h3>
    <span style="font-size:.82rem;color:#aaa">Paid orders only</span>
  </div>
  <div style="padding:24px 28px">
    <canvas id="revenueChart" height="90"></canvas>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px;margin-bottom:24px">
  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3>Recent Orders</h3>
      <a href="<?= SITE_URL ?>/admin/orders.php" class="action-btn view">View All</a>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><strong><?= e($o['order_number']) ?></strong></td>
            <td><?= e($o['shipping_name']) ?></td>
            <td><?= formatPrice($o['total_amount']) ?></td>
            <td><span class="status-badge status-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentOrders)): ?>
          <tr><td colspan="4" style="text-align:center;color:#aaa;padding:28px">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Selling Products -->
  <div class="admin-card">
    <div class="admin-card-header"><h3>Top 5 Selling Products</h3></div>
    <?php if (empty($topProducts)): ?>
    <div style="padding:28px;text-align:center;color:#aaa;font-size:.9rem">No sales data yet.</div>
    <?php endif; ?>
    <?php foreach ($topProducts as $idx => $tp): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f8f8f8">
      <div style="width:26px;height:26px;border-radius:50%;background:#f0ece6;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#8B6F47;flex-shrink:0">
        <?= $idx + 1 ?>
      </div>
      <img src="<?= e($tp['image'] ?? '') ?>" style="width:46px;height:54px;object-fit:cover;border-radius:8px;background:#f0ece6"
           onerror="this.style.visibility='hidden'"/>
      <div style="flex:1;min-width:0">
        <div style="font-size:.88rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($tp['name']) ?></div>
        <div style="font-size:.75rem;color:#aaa;margin-top:2px"><?= formatPrice($tp['sale_price'] > 0 ? $tp['sale_price'] : $tp['price']) ?></div>
      </div>
      <div style="background:#f0ece6;padding:4px 12px;border-radius:50px;font-size:.78rem;font-weight:700;flex-shrink:0"><?= (int)$tp['sold'] ?> sold</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Low Stock Products -->
<?php if (!empty($lowStockProducts)): ?>
<div class="admin-card" style="margin-bottom:24px">
  <div class="admin-card-header">
    <h3>
      <i class="fa fa-triangle-exclamation" style="color:#e67e22;margin-right:8px"></i>
      Low Stock / Out of Stock Products
    </h3>
    <a href="<?= SITE_URL ?>/admin/products.php" class="action-btn view">Manage All</a>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Alert At</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lowStockProducts as $ls): ?>
        <?php
          $rowBg = $ls['stock'] == 0 ? 'background:#fdecea' : 'background:#fff8e1';
        ?>
        <tr style="<?= $rowBg ?>">
          <td style="font-weight:600"><?= e($ls['name']) ?></td>
          <td style="font-size:.85rem;color:#888"><?= e($ls['cat_name'] ?? '—') ?></td>
          <td>
            <?php if ($ls['stock'] == 0): ?>
              <span style="background:#e74c3c;color:#fff;padding:3px 10px;border-radius:50px;font-size:.75rem;font-weight:700">Out of Stock</span>
            <?php else: ?>
              <span style="background:#f39c12;color:#fff;padding:3px 10px;border-radius:50px;font-size:.75rem;font-weight:700"><?= (int)$ls['stock'] ?> left</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.85rem;color:#888"><?= (int)($ls['low_stock_alert'] ?? 5) ?></td>
          <td>
            <?php if ($ls['stock'] == 0): ?>
              <span style="color:#e74c3c;font-weight:600;font-size:.82rem">⚠ Restock Needed</span>
            <?php else: ?>
              <span style="color:#e67e22;font-weight:600;font-size:.82rem">⚠ Low Stock</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= SITE_URL ?>/admin/product_form.php?id=<?= $ls['id'] ?>" class="action-btn edit" style="font-size:.8rem;padding:5px 12px">
              <i class="fa fa-pen"></i> Edit
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
  var labels  = <?= json_encode($chartLabels) ?>;
  var data    = <?= json_encode($chartData) ?>;
  var maxVal  = Math.max(...data, 1);
  var ctx     = document.getElementById('revenueChart').getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue (₹)',
        data:  data,
        backgroundColor: data.map(function(v) {
          return v === 0 ? 'rgba(200,185,154,.25)' : 'rgba(139,111,71,.75)';
        }),
        borderColor: data.map(function(v) {
          return v === 0 ? 'rgba(200,185,154,.4)' : 'rgba(139,111,71,1)';
        }),
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              return ' ₹' + Number(ctx.parsed.y).toLocaleString('en-IN', {minimumFractionDigits: 2});
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 12 }, color: '#888' }
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,.04)' },
          ticks: {
            font: { size: 11 },
            color: '#aaa',
            callback: function(v) {
              return '₹' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v);
            }
          }
        }
      }
    }
  });
})();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
