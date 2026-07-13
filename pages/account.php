<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
requireLogin('/auth/login.php?redirect=/pages/account.php');

$userId = $_SESSION['user_id'];
$user   = dbFetchOne("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?", [$userId]);

$errors  = [];
$success = '';

// ── Re-order handler ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder_order_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please refresh and try again.');
        header('Location: ' . SITE_URL . '/pages/account.php#tab-orders');
        exit;
    }
    $reorderId = (int)$_POST['reorder_order_id'];
    $orderCheck = dbFetchOne("SELECT id FROM orders WHERE id=? AND user_id=?", [$reorderId, $userId]);
    if ($orderCheck) {
        $items = dbFetchAll(
            "SELECT product_id, quantity, size, color FROM order_items WHERE order_id=?",
            [$reorderId]
        );
        foreach ($items as $item) {
            // Check product still active
            $prod = dbFetchOne("SELECT id FROM products WHERE id=? AND status='active'", [$item['product_id']]);
            if (!$prod) continue;
            // Add or update cart
            $existing = dbFetchOne(
                "SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? AND size=? AND color=?",
                [$userId, $item['product_id'], $item['size'], $item['color']]
            );
            if ($existing) {
                dbExecute("UPDATE cart SET quantity=quantity+? WHERE id=?", [$item['quantity'], $existing['id']]);
            } else {
                dbExecute(
                    "INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?,?,?,?,?)",
                    [$userId, $item['product_id'], $item['quantity'], $item['size'], $item['color']]
                );
            }
        }
        setFlash('success', 'Items from your order have been added to your cart!');
        header('Location: ' . SITE_URL . '/cart/cart.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reorder_order_id'])) {
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

$orders = dbFetchAll("SELECT id, order_number, total_amount, status, payment_status, payment_method, created_at FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$userId]);

$pageTitle = "My Account — Devendra's Shop";
include __DIR__ . '/../includes/header.php';

// Helper: build timeline steps for an order
function buildOrderTimeline(array $order): array {
    $status = strtolower($order['status'] ?? 'pending');
    $pstatus = strtolower($order['payment_status'] ?? '');
    $method  = strtolower($order['payment_method'] ?? '');

    $statusOrder = ['pending' => 0, 'processing' => 2, 'shipped' => 3, 'delivered' => 4, 'cancelled' => -1];
    $currentIdx  = $statusOrder[$status] ?? 0;

    $paymentDone = ($pstatus === 'paid' || $method === 'cod');

    $steps = [
        ['icon' => 'fa-file-alt',       'label' => 'Order Placed',       'done' => true],
        ['icon' => 'fa-check-circle',   'label' => 'Payment Confirmed',   'done' => $paymentDone],
        ['icon' => 'fa-cog',            'label' => 'Processing',          'done' => $currentIdx >= 2],
        ['icon' => 'fa-truck',          'label' => 'Shipped',             'done' => $currentIdx >= 3],
        ['icon' => 'fa-box-open',       'label' => 'Delivered',           'done' => $currentIdx >= 4],
    ];

    // Find active (first not-done after all done, or last done)
    $activeIdx = 0;
    foreach ($steps as $i => $step) {
        if ($step['done']) $activeIdx = $i;
    }
    // If cancelled, mark nothing active after placement
    if ($status === 'cancelled') $activeIdx = 0;

    foreach ($steps as $i => &$step) {
        $step['active'] = ($i === $activeIdx && !$step['done']);
        // If done or is the last done step, mark active on last done
        $step['is_current'] = ($i === $activeIdx);
    }
    return $steps;
}
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
          <?php foreach ($orders as $o):
            $sc = ['pending'=>'status-pending','processing'=>'status-processing','shipped'=>'status-processing','delivered'=>'status-active','cancelled'=>'status-cancelled'][$o['status']] ?? 'status-pending';
            $timeline = buildOrderTimeline($o);
          ?>
          <div class="order-card" style="border:1.5px solid #f0ece6;border-radius:12px;margin-bottom:24px;overflow:hidden">
            <!-- Order Header -->
            <div style="background:#faf8f5;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
              <div>
                <span style="font-weight:700;font-size:.95rem">#<?= e($o['order_number']) ?></span>
                <span style="font-size:.8rem;color:#888;margin-left:12px"><?= date('d M Y', strtotime($o['created_at'])) ?></span>
              </div>
              <div style="display:flex;align-items:center;gap:10px">
                <span style="font-weight:700"><?= formatPrice($o['total_amount']) ?></span>
                <span class="status-badge <?= $sc ?>"><?= ucfirst($o['status']) ?></span>
              </div>
            </div>

            <!-- Order Timeline -->
            <div style="padding:24px 20px 8px">
              <div class="order-timeline">
                <?php foreach ($timeline as $idx => $step): ?>
                <div class="ot-step <?= $step['done'] ? 'ot-done' : '' ?> <?= $step['is_current'] ? 'ot-current' : '' ?>">
                  <div class="ot-icon-wrap">
                    <div class="ot-icon"><i class="fa <?= $step['icon'] ?>"></i></div>
                    <?php if ($idx < count($timeline)-1): ?><div class="ot-line"></div><?php endif; ?>
                  </div>
                  <div class="ot-label"><?= $step['label'] ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Order Actions -->
            <div style="padding:12px 20px 20px;display:flex;gap:10px;flex-wrap:wrap">
              <a href="<?= SITE_URL ?>/pages/track-order.php" class="action-btn view" style="text-decoration:none">
                <i class="fa fa-map-marker-alt"></i> Track Order
              </a>
              <?php if ($o['status'] !== 'cancelled'): ?>
              <form method="POST" action="" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
                <input type="hidden" name="reorder_order_id" value="<?= $o['id'] ?>"/>
                <button type="submit" class="action-btn edit" style="cursor:pointer"
                        onclick="return confirm('Add all items from this order to your cart?')">
                  <i class="fa fa-redo"></i> Re-order
                </button>
              </form>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
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
