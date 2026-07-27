<?php
// admin/index.php — Dashboard
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/admin_header.php';

$totalProducts = dbFetchOne("SELECT COUNT(*) AS c FROM products")['c'];
$totalOrders   = dbFetchOne("SELECT COUNT(*) AS c FROM orders")['c'];
$totalUsers    = dbFetchOne("SELECT COUNT(*) AS c FROM users WHERE role='customer'")['c'];
$totalRevenue  = dbFetchOne("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE payment_status='paid'")['s'];

$recentOrders  = dbFetchAll("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8");
$topProducts   = dbFetchAll("SELECT p.name,p.image,COUNT(oi.id) AS sold FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5");
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

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px">
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
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Products -->
  <div class="admin-card">
    <div class="admin-card-header"><h3>Top Selling Products</h3></div>
    <?php foreach ($topProducts as $tp): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f8f8f8">
      <img src="<?= e($tp['image']) ?>" style="width:46px;height:54px;object-fit:cover;border-radius:8px"/>
      <div style="flex:1;font-size:.88rem;font-weight:600"><?= e($tp['name']) ?></div>
      <div style="background:#f0ece6;padding:4px 12px;border-radius:50px;font-size:.78rem;font-weight:700"><?= $tp['sold'] ?> sold</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
