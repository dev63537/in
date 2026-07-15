<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin('/auth/login.php?redirect=/pages/account.php');

$userId = $_SESSION['user_id'];
$user   = dbFetchOne("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?", [$userId]);

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    } else {
        $tab = $_POST['tab'] ?? 'profile';

        if ($tab === 'profile') {
            $name    = trim($_POST['name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            if (!$name) $errors[] = 'Name is required.';
            if (!$errors) {
                dbExecute("UPDATE users SET name=?, phone=?, address=? WHERE id=?", [$name, $phone, $address, $userId]);
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
                $user = dbFetchOne("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?", [$userId]);
            }
        }

        if ($tab === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (!$current || !$new || !$confirm) { $errors[] = 'All password fields are required.'; }
            elseif (strlen($new) < 8)             { $errors[] = 'New password must be at least 8 characters.'; }
            elseif ($new !== $confirm)             { $errors[] = 'New passwords do not match.'; }
            else {
                $dbUser = dbFetchOne("SELECT password FROM users WHERE id=?", [$userId]);
                if (!password_verify($current, $dbUser['password'])) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    dbExecute("UPDATE users SET password=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $userId]);
                    $success = 'Password changed successfully!';
                }
            }
        }
    }
}

$orders = dbFetchAll("SELECT id, order_number, total_amount, status, created_at FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$userId]);

$pageTitle = "My Account — Gujju Clothing";
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>My Account</h1>
    <p>Manage your profile, password and orders</p>
    <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / My Account</nav>
  </div>
</section>

<div class="container" style="padding:50px 20px">
  <div style="display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start">

    <!-- Sidebar -->
    <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,.08);position:sticky;top:90px">
      <div style="text-align:center;margin-bottom:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#c9a96e,#a8844d);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:800;color:#0f0f0f">
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div style="font-weight:700;font-size:1rem"><?= e($user['name']) ?></div>
        <div style="font-size:.8rem;color:#888"><?= e($user['email']) ?></div>
        <div style="font-size:.75rem;color:#c9a96e;margin-top:4px">Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
      </div>
      <nav>
        <?php $navItems = [
          ['#tab-profile','fa-user','My Profile','profile'],
          ['#tab-orders','fa-box','My Orders','orders'],
          ['#tab-password','fa-lock','Change Password','password'],
          [SITE_URL.'/pages/wishlist.php','fa-heart','My Wishlist',''],
          [SITE_URL.'/auth/logout.php','fa-sign-out-alt','Logout',''],
        ]; foreach ($navItems as $n): ?>
        <a href="<?= $n[0] ?>" onclick="<?= $n[3] ? "showTab('{$n[3]}');return false;" : '' ?>"
           style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:8px;color:#555;font-size:.9rem;margin-bottom:4px;transition:.3s"
           onmouseover="this.style.background='#f9f7f4';this.style.color='#c9a96e'" onmouseout="this.style.background='';this.style.color='#555'">
          <i class="fa <?= $n[1] ?>" style="width:18px;color:#c9a96e"></i><?= $n[2] ?>
        </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <!-- Main Content -->
    <div>
      <?php if ($success): ?>
      <div class="flash-message flash-success" style="position:static;margin-bottom:20px">
        <i class="fa fa-check-circle"></i> <?= e($success) ?>
        <button class="flash-close" onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button>
      </div>
      <?php endif; ?>
      <?php if ($errors): ?>
      <div class="flash-message flash-error" style="position:static;margin-bottom:20px">
        <i class="fa fa-exclamation-circle"></i>
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        <button class="flash-close" onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button>
      </div>
      <?php endif; ?>

      <!-- Profile Tab -->
      <div id="tab-profile" class="account-tab">
        <div style="background:#fff;border-radius:14px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08)">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:24px">My Profile</h2>
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="tab" value="profile"/>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <div class="form-group" style="grid-column:1/-1">
                <label>Full Name *</label>
                <input type="text" name="name" required value="<?= e($user['name']) ?>"/>
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?= e($user['email']) ?>" disabled style="background:#f5f5f5;cursor:not-allowed"/>
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+91 XXXXX XXXXX"/>
              </div>
              <div class="form-group" style="grid-column:1/-1">
                <label>Default Delivery Address</label>
                <textarea name="address" rows="3" style="width:100%;padding:12px 16px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;font-size:.95rem;resize:vertical;outline:none"><?= e($user['address'] ?? '') ?></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:8px"><i class="fa fa-save"></i> Save Changes</button>
          </form>
        </div>
      </div>

      <!-- Orders Tab -->
      <div id="tab-orders" class="account-tab" style="display:none">
        <div style="background:#fff;border-radius:14px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08)">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem">Recent Orders</h2>
            <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-outline" style="padding:8px 18px;font-size:.85rem">View All</a>
          </div>
          <?php if (empty($orders)): ?>
          <div style="text-align:center;padding:40px;color:#888">
            <i class="fa fa-box" style="font-size:2.5rem;opacity:.3;margin-bottom:12px;display:block"></i>
            <p>No orders yet. <a href="<?= SITE_URL ?>/pages/shop.php" style="color:#c9a96e">Start shopping!</a></p>
          </div>
          <?php else: ?>
          <div style="overflow-x:auto">
            <table class="admin-table" style="width:100%">
              <thead><tr>
                <th>Order #</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th>
              </tr></thead>
              <tbody>
                <?php foreach ($orders as $o):
                  $sc = ['pending'=>'status-pending','processing'=>'status-processing','shipped'=>'status-processing','delivered'=>'status-active','cancelled'=>'status-cancelled'][$o['status']] ?? 'status-pending';
                ?>
                <tr>
                  <td><strong>#<?= e($o['order_number']) ?></strong></td>
                  <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                  <td><?= formatPrice($o['total_amount']) ?></td>
                  <td><span class="status-badge <?= $sc ?>"><?= ucfirst($o['status']) ?></span></td>
                  <td><a href="<?= SITE_URL ?>/pages/track-order.php" class="action-btn view">Track</a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Password Tab -->
      <div id="tab-password" class="account-tab" style="display:none">
        <div style="background:#fff;border-radius:14px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,.08);max-width:480px">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:24px">Change Password</h2>
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
            <input type="hidden" name="tab" value="password"/>
            <div class="form-group">
              <label>Current Password *</label>
              <input type="password" name="current_password" required placeholder="Enter current password"/>
            </div>
            <div class="form-group">
              <label>New Password *</label>
              <input type="password" name="new_password" required minlength="8" placeholder="At least 8 characters"/>
            </div>
            <div class="form-group">
              <label>Confirm New Password *</label>
              <input type="password" name="confirm_password" required minlength="8" placeholder="Repeat new password"/>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-lock"></i> Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function showTab(name) {
  document.querySelectorAll('.account-tab').forEach(t => t.style.display = 'none');
  const el = document.getElementById('tab-' + name);
  if (el) el.style.display = 'block';
}
// On page load, check hash
const hash = location.hash.replace('#tab-','');
if (hash) showTab(hash);
</script>
<style>@media(max-width:860px){.container > div[style*="grid-template-columns:260px"]{grid-template-columns:1fr!important}}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
