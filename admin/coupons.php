<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Manage Coupons — Admin';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        $cid    = (int)($_POST['id'] ?? 0);

        if ($action === 'add') {
            $code    = strtoupper(trim($_POST['code'] ?? ''));
            $type    = $_POST['type'] ?? 'percent';
            $value   = (float)($_POST['value'] ?? 0);
            $min     = (float)($_POST['min_order'] ?? 0);
            $limit   = (int)($_POST['usage_limit'] ?? 0);
            $expires = $_POST['expires_at'] ?: null;
            if ($code && $value > 0) {
                try {
                    dbExecute("INSERT INTO coupon_codes (code,type,value,min_order,usage_limit,expires_at) VALUES (?,?,?,?,?,?)", [$code,$type,$value,$min,$limit,$expires]);
                    setFlash('success', "Coupon '$code' created.");
                } catch (Exception $e) { setFlash('error', 'Coupon code already exists.'); }
            } else { setFlash('error', 'Code and value are required.'); }
        }

        if ($action === 'toggle' && $cid) {
            $cur = dbFetchOne("SELECT status FROM coupon_codes WHERE id=?", [$cid]);
            $new = ($cur['status'] === 'active') ? 'inactive' : 'active';
            dbExecute("UPDATE coupon_codes SET status=? WHERE id=?", [$new, $cid]);
            setFlash('success', "Coupon " . ($new === 'active' ? 'activated' : 'deactivated') . ".");
        }

        if ($action === 'delete' && $cid) {
            dbExecute("DELETE FROM coupon_codes WHERE id=?", [$cid]);
            setFlash('success', 'Coupon deleted.');
        }
    }
    redirect(SITE_URL . '/admin/coupons.php');
}

$coupons = dbFetchAll("SELECT * FROM coupon_codes ORDER BY id DESC");

include __DIR__ . '/../includes/admin_header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:14px">
  <div>
    <h1 style="font-size:1.4rem;font-weight:800">Coupons</h1>
    <p style="color:#888;font-size:.88rem;margin-top:2px"><?= count($coupons) ?> coupon<?= count($coupons)!=1?'s':'' ?></p>
  </div>
  <button class="btn btn-primary" onclick="toggleForm()"><i class="fa fa-plus"></i> Add Coupon</button>
</div>

<!-- Add Coupon Form -->
<div id="coupon-form" class="admin-card" style="margin-bottom:24px;display:none">
  <div class="admin-card-header">
    <h3>Create New Coupon</h3>
    <button onclick="toggleForm()" style="background:none;border:none;cursor:pointer;color:#888"><i class="fa fa-times"></i></button>
  </div>
  <div style="padding:24px">
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="action" value="add"/>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px">
        <div class="form-group">
          <label>Coupon Code *</label>
          <input type="text" name="code" required placeholder="e.g. SAVE20" style="text-transform:uppercase"/>
        </div>
        <div class="form-group">
          <label>Discount Type</label>
          <select name="type" style="width:100%;padding:11px 15px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;outline:none">
            <option value="percent">Percentage (%)</option>
            <option value="fixed">Fixed Amount (₹)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Discount Value *</label>
          <input type="number" name="value" required min="1" step="0.01" placeholder="e.g. 10 or 200"/>
        </div>
        <div class="form-group">
          <label>Minimum Order (₹)</label>
          <input type="number" name="min_order" min="0" value="0"/>
        </div>
        <div class="form-group">
          <label>Usage Limit (0 = unlimited)</label>
          <input type="number" name="usage_limit" min="0" value="0"/>
        </div>
        <div class="form-group">
          <label>Expires On</label>
          <input type="date" name="expires_at"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:4px"><i class="fa fa-save"></i> Create Coupon</button>
    </form>
  </div>
</div>

<!-- Coupons Table -->
<div class="admin-card">
  <div style="overflow-x:auto">
    <table class="admin-table" style="width:100%">
      <thead><tr>
        <th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used/Limit</th><th>Expires</th><th>Status</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($coupons as $c):
          $expired = $c['expires_at'] && strtotime($c['expires_at']) < time();
        ?>
        <tr>
          <td><strong style="font-family:monospace;font-size:1rem;color:#c9a96e"><?= e($c['code']) ?></strong></td>
          <td><?= $c['type'] === 'percent' ? '<i class="fa fa-percent"></i> Percent' : '<i class="fa fa-rupee-sign"></i> Fixed' ?></td>
          <td><strong><?= $c['type'] === 'percent' ? $c['value'].'%' : '₹'.$c['value'] ?></strong></td>
          <td><?= $c['min_order'] > 0 ? '₹'.number_format($c['min_order']) : '<span style="color:#aaa">None</span>' ?></td>
          <td><?= $c['used_count'] ?> / <?= $c['usage_limit'] > 0 ? $c['usage_limit'] : '∞' ?></td>
          <td style="font-size:.85rem">
            <?php if ($c['expires_at']): ?>
            <span style="color:<?= $expired?'#e74c3c':'#555' ?>"><?= date('d M Y', strtotime($c['expires_at'])) ?></span>
            <?php if ($expired): ?><span style="color:#e74c3c;font-size:.75rem;display:block">Expired</span><?php endif; ?>
            <?php else: ?><span style="color:#aaa">No expiry</span><?php endif; ?>
          </td>
          <td><span class="status-badge <?= $c['status']==='active'&&!$expired?'status-active':'status-cancelled' ?>"><?= $c['status'] ?></span></td>
          <td>
            <form method="POST" action="" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
              <input type="hidden" name="id"         value="<?= $c['id'] ?>"/>
              <div class="action-btns">
                <button type="submit" name="action" value="toggle" class="action-btn <?= $c['status']==='active'?'edit':'view' ?>">
                  <i class="fa fa-<?= $c['status']==='active'?'pause':'play' ?>"></i> <?= $c['status']==='active'?'Disable':'Enable' ?>
                </button>
                <button type="submit" name="action" value="delete" class="action-btn delete" onclick="return confirm('Delete coupon <?= e($c['code']) ?>?')">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($coupons)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#aaa">No coupons yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleForm() {
  const f = document.getElementById('coupon-form');
  f.style.display = f.style.display === 'none' ? '' : 'none';
}
</script>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
