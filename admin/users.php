<?php
// admin/users.php
$pageTitle = 'Customers';
include __DIR__ . '/includes/admin_header.php';
$users = dbFetchAll("SELECT id, name, email, role, status, created_at FROM users WHERE role='customer' ORDER BY id DESC LIMIT 200");
?>
<div class="admin-card">
  <div class="admin-card-header"><h3>All Customers (<?= count($users) ?>)</h3></div>
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['phone'] ?? '—') ?></td>
        <td><span class="status-badge status-<?= e($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
        <td style="font-size:.83rem;color:#888"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
