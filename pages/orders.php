<?php
require_once __DIR__ . '/../includes/functions.php';
startSession(); requireLogin();

$userId = $_SESSION['user_id'];
$orders = dbFetchAll("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC", [$userId]);

// Fetch items for all user orders
$orderItemsMap = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $inClause = implode(',', array_fill(0, count($orderIds), '?'));
    $items = dbFetchAll("SELECT oi.*, p.name as curr_name, p.image as curr_image 
                        FROM order_items oi 
                        LEFT JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id IN ($inClause)", $orderIds);
    foreach ($items as $item) {
        $orderItemsMap[$item['order_id']][] = $item;
    }
}

$pageTitle = "My Orders — Gujju Clothing";
include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>My Orders & Purchased Items</h1>
    <p>Track your orders and leave reviews for items you've purchased</p>
  </div>
</section>

<div class="container" style="padding:40px 20px">
  <?php if (empty($orders)): ?>
    <div style="text-align:center;padding:60px">
      <i class="fa fa-box-open" style="font-size:3rem;color:#ddd;display:block;margin-bottom:16px"></i>
      <h3>No orders yet</h3>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">Start Shopping</a>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $o): 
      $items = $orderItemsMap[$o['id']] ?? [];
    ?>
      <div class="order-card" style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:30px;overflow:hidden;border:1px solid #f0ece6">
        <div style="background:var(--dark);color:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <div>
            <strong>Order #<?= e($o['order_number']) ?></strong>
            <span style="opacity:.7;font-size:.85rem;margin-left:12px"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></span>
          </div>
          <div style="display:flex;gap:12px;align-items:center">
            <span>Total: <strong><?= formatPrice($o['total_amount']) ?></strong></span>
            <span class="status-badge status-<?= e($o['status']) ?>" style="text-transform:capitalize;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:700"><?= ucfirst($o['status']) ?></span>
          </div>
        </div>

        <div style="padding:20px 24px">
          <h4 style="font-size:.95rem;margin-bottom:14px;color:var(--gray);text-transform:uppercase;letter-spacing:1px">Items in this order</h4>
          <?php foreach ($items as $item): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 0;border-bottom:1px solid #f5f5f5">
              <div style="display:flex;align-items:center;gap:14px">
                <?php if ($item['product_image'] || $item['curr_image']): ?>
                  <img src="<?= SITE_URL ?>/uploads/products/<?= e($item['product_image'] ?: $item['curr_image']) ?>" 
                       style="width:50px;height:60px;object-fit:cover;border-radius:8px" alt="" />
                <?php else: ?>
                  <div style="width:50px;height:60px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa"><i class="fa fa-image"></i></div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;font-size:.95rem">
                    <?php if ($item['product_id']): ?>
                      <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $item['product_id'] ?>" style="color:var(--dark)"><?= e($item['product_name'] ?: $item['curr_name']) ?></a>
                    <?php else: ?>
                      <?= e($item['product_name']) ?>
                    <?php endif; ?>
                  </div>
                  <div style="font-size:.82rem;color:var(--gray)">
                    Size: <?= e($item['size'] ?: 'M') ?> | Qty: <?= (int)$item['quantity'] ?> | <?= formatPrice($item['unit_price']) ?>
                  </div>
                </div>
              </div>

              <!-- Write Review Button -->
              <?php if ($item['product_id']): ?>
                <button type="button" class="btn btn-outline" style="padding:6px 16px;font-size:.82rem"
                        onclick="toggleReviewForm('rf-<?= $o['id'] ?>-<?= $item['product_id'] ?>')">
                  <i class="fa fa-star" style="color:var(--copper)"></i> Write Review
                </button>
              <?php endif; ?>
            </div>

            <!-- Inline Review Form -->
            <?php if ($item['product_id']): ?>
              <div id="rf-<?= $o['id'] ?>-<?= $item['product_id'] ?>" style="display:none;background:#FAF6F0;padding:18px;border-radius:12px;margin:12px 0 18px">
                <form class="order-review-form" onsubmit="submitOrderReview(event, this)">
                  <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>" />
                  <input type="hidden" name="reviewer_name" value="<?= e($_SESSION['user_name'] ?? 'Customer') ?>" />
                  <h5 style="margin-bottom:10px;font-size:.95rem;color:var(--dark)">Review for <?= e($item['product_name'] ?: $item['curr_name']) ?></h5>
                  <div style="margin-bottom:12px">
                    <label style="font-size:.85rem;display:block;margin-bottom:4px;font-weight:600">Rating:</label>
                    <select name="rating" required style="padding:8px 12px;border:1px solid #ddd;border-radius:6px">
                      <option value="5">⭐⭐⭐⭐⭐ (5/5 Excellent)</option>
                      <option value="4">⭐⭐⭐⭐ (4/5 Very Good)</option>
                      <option value="3">⭐⭐⭐ (3/5 Good)</option>
                      <option value="2">⭐⭐ (2/5 Average)</option>
                      <option value="1">⭐ (1/5 Poor)</option>
                    </select>
                  </div>
                  <div style="margin-bottom:12px">
                    <label style="font-size:.85rem;display:block;margin-bottom:4px;font-weight:600">Your Feedback:</label>
                    <textarea name="comment" rows="3" required placeholder="Tell us about the quality, fit, and design…" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:.9rem"></textarea>
                  </div>
                  <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary" style="padding:8px 20px;font-size:.85rem">Submit Review</button>
                    <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.85rem" onclick="toggleReviewForm('rf-<?= $o['id'] ?>-<?= $item['product_id'] ?>')">Cancel</button>
                  </div>
                </form>
              </div>
            <?php endif; ?>

          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
function toggleReviewForm(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
}

function submitOrderReview(e, form) {
  e.preventDefault();
  const btn = form.querySelector('[type=submit]');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting…';
  btn.disabled = true;

  fetch('<?= SITE_URL ?>/cart/submit_review.php', {
    method: 'POST',
    body: new FormData(form)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      form.parentElement.style.display = 'none';
    } else {
      alert(data.message || 'Error submitting review.');
    }
  })
  .catch(() => alert('Network error. Please try again.'))
  .finally(() => {
    btn.innerHTML = orig;
    btn.disabled = false;
  });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
