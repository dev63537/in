<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Manage Reviews — Admin';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        $rid    = (int)($_POST['id'] ?? 0);
        if ($action === 'approve' && $rid) {
            dbExecute("UPDATE reviews SET status='approved' WHERE id=?", [$rid]);
            setFlash('success', 'Review approved.');
        }
        if ($action === 'reject' && $rid) {
            dbExecute("UPDATE reviews SET status='pending' WHERE id=?", [$rid]);
            setFlash('success', 'Review set to pending.');
        }
        if ($action === 'delete' && $rid) {
            dbExecute("DELETE FROM reviews WHERE id=?", [$rid]);
            setFlash('success', 'Review deleted.');
        }
    }
    redirect(SITE_URL . '/admin/reviews.php');
}

$filter  = $_GET['status'] ?? 'all';
$where   = $filter !== 'all' ? "WHERE r.status='$filter'" : '';
$reviews = dbFetchAll("SELECT r.*, p.name AS product_name FROM reviews r LEFT JOIN products p ON r.product_id=p.id $where ORDER BY r.created_at DESC");

include __DIR__ . '/includes/admin_header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:14px">
  <div>
    <h1 style="font-size:1.4rem;font-weight:800">Reviews</h1>
    <p style="color:#888;font-size:.88rem;margin-top:2px"><?= count($reviews) ?> review<?= count($reviews)!=1?'s':'' ?></p>
  </div>
  <div style="display:flex;gap:8px">
    <?php foreach (['all'=>'All','approved'=>'Approved','pending'=>'Pending'] as $f => $l): ?>
    <a href="?status=<?= $f ?>" class="btn <?= $filter===$f?'btn-primary':'btn-outline' ?>" style="padding:8px 16px;font-size:.85rem"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-card">
  <div style="overflow-x:auto">
    <table class="admin-table" style="width:100%">
      <thead><tr>
        <th>#</th><th>Product</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($reviews as $rv): ?>
        <tr>
          <td><?= $rv['id'] ?></td>
          <td style="max-width:120px;font-size:.85rem;font-weight:600"><?= e($rv['product_name'] ?? 'N/A') ?></td>
          <td>
            <div style="font-weight:600;font-size:.9rem"><?= e($rv['reviewer_name']) ?></div>
          </td>
          <td>
            <div style="color:#c9a96e;font-size:.85rem">
              <?php for ($i=1;$i<=5;$i++) echo '<i class="fa fa-star'.($i>$rv['rating']?'-o':'').'"></i>'; ?>
              <span style="color:#888;margin-left:4px">(<?= $rv['rating'] ?>)</span>
            </div>
          </td>
          <td style="max-width:200px;font-size:.85rem;color:#555"><?= e(substr($rv['comment'], 0, 80)) ?>…</td>
          <td><span class="status-badge <?= $rv['status']==='approved'?'status-active':'status-pending' ?>"><?= $rv['status'] ?></span></td>
          <td style="font-size:.82rem;color:#888"><?= date('d M Y', strtotime($rv['created_at'])) ?></td>
          <td>
            <form method="POST" action="" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
              <input type="hidden" name="id"         value="<?= $rv['id'] ?>"/>
              <div class="action-btns">
                <?php if ($rv['status'] !== 'approved'): ?>
                <button type="submit" name="action" value="approve" class="action-btn view"><i class="fa fa-check"></i> Approve</button>
                <?php else: ?>
                <button type="submit" name="action" value="reject" class="action-btn edit"><i class="fa fa-eye-slash"></i> Hide</button>
                <?php endif; ?>
                <button type="submit" name="action" value="delete" class="action-btn delete" onclick="return confirm('Delete this review?')"><i class="fa fa-trash"></i></button>
              </div>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#aaa">No reviews found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
