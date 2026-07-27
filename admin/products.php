<?php
// admin/products.php — Full Product Management with Search, Filter, Bulk Actions, Pagination
$pageTitle = 'Products';
include __DIR__ . '/includes/admin_header.php';

// ── Filters & Pagination ────────────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$catId    = (int)($_GET['category'] ?? 0);
$brandId  = (int)($_GET['brand']    ?? 0);
$status   = $_GET['status'] ?? '';
$gender   = $_GET['gender'] ?? '';
$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

// ── Build WHERE ──────────────────────────────────────────────────────────
$where  = ['1=1'];
$params = [];
if ($search)   { $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.tags LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catId)    { $where[] = 'p.category_id = ?'; $params[] = $catId; }
if ($brandId)  { $where[] = 'p.brand_id = ?';    $params[] = $brandId; }
if ($status)   { $where[] = 'p.status = ?';      $params[] = $status; }
if ($gender)   { $where[] = 'p.gender = ?';      $params[] = $gender; }
$whereSQL = implode(' AND ', $where);

$total    = (int)(dbFetchOne("SELECT COUNT(*) AS cnt FROM products p WHERE $whereSQL", $params)['cnt'] ?? 0);
$pages    = max(1, ceil($total / $perPage));

<<<<<<< HEAD
$qParams  = array_merge($params, [$perPage, $offset]);
=======
$limit    = (int)$perPage;
$offsetInt = (int)$offset;
>>>>>>> origin/master
$products = dbFetchAll("SELECT p.*, c.name AS cat_name, b.name AS brand_name
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN brands b     ON p.brand_id    = b.id
<<<<<<< HEAD
  WHERE $whereSQL ORDER BY p.id DESC LIMIT ? OFFSET ?", $qParams);
=======
  WHERE $whereSQL ORDER BY p.id DESC LIMIT $limit OFFSET $offsetInt", $params);
>>>>>>> origin/master

$categories = getCategories();
$brands     = dbFetchAll("SELECT id, name FROM brands WHERE status='active' ORDER BY name");

// ── Stats ────────────────────────────────────────────────────────────────
$stats = dbFetchOne("SELECT
  COUNT(*) AS total,
  SUM(status='active') AS active,
  SUM(status='inactive') AS inactive,
  SUM(stock = 0) AS out_of_stock,
  SUM(stock > 0 AND stock <= COALESCE(low_stock_alert,5)) AS low_stock
  FROM products");
?>

<!-- Stats Row -->
<div class="pm-stats-row">
  <div class="pm-stat-card">
    <div class="pm-stat-icon" style="background:#e8f4fd"><i class="fa fa-box" style="color:#3498db"></i></div>
    <div><div class="pm-stat-num"><?= (int)$stats['total'] ?></div><div class="pm-stat-lbl">Total Products</div></div>
  </div>
  <div class="pm-stat-card">
    <div class="pm-stat-icon" style="background:#e8fdf2"><i class="fa fa-check-circle" style="color:#27ae60"></i></div>
    <div><div class="pm-stat-num"><?= (int)$stats['active'] ?></div><div class="pm-stat-lbl">Active</div></div>
  </div>
  <div class="pm-stat-card">
    <div class="pm-stat-icon" style="background:#fff8e1"><i class="fa fa-exclamation-triangle" style="color:#f39c12"></i></div>
    <div><div class="pm-stat-num"><?= (int)$stats['low_stock'] ?></div><div class="pm-stat-lbl">Low Stock</div></div>
  </div>
  <div class="pm-stat-card">
    <div class="pm-stat-icon" style="background:#fdecea"><i class="fa fa-times-circle" style="color:#e74c3c"></i></div>
    <div><div class="pm-stat-num"><?= (int)$stats['out_of_stock'] ?></div><div class="pm-stat-lbl">Out of Stock</div></div>
  </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="margin-bottom:16px">
  <form method="GET" action="" class="pm-filter-bar">
    <div class="pm-filter-search">
      <i class="fa fa-search"></i>
      <input type="text" name="search" placeholder="Search name, SKU, tags…" value="<?= e($search) ?>" class="pm-search-input"/>
    </div>
    <select name="category" class="pm-filter-select">
      <option value="">All Categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $catId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="brand" class="pm-filter-select">
      <option value="">All Brands</option>
      <?php foreach ($brands as $b): ?>
        <option value="<?= $b['id'] ?>" <?= $brandId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="pm-filter-select">
      <option value="">All Status</option>
      <option value="active"   <?= $status==='active'  ? 'selected' : '' ?>>Active</option>
      <option value="inactive" <?= $status==='inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>
    <select name="gender" class="pm-filter-select">
      <option value="">All Genders</option>
      <option value="men"    <?= $gender==='men'    ? 'selected':'' ?>>Men</option>
      <option value="women"  <?= $gender==='women'  ? 'selected':'' ?>>Women</option>
      <option value="kids"   <?= $gender==='kids'   ? 'selected':'' ?>>Kids</option>
      <option value="unisex" <?= $gender==='unisex' ? 'selected':'' ?>>Unisex</option>
    </select>
    <button type="submit" class="btn btn-primary" style="padding:9px 20px;font-size:.88rem">
      <i class="fa fa-filter"></i> Filter
    </button>
    <?php if ($search || $catId || $brandId || $status || $gender): ?>
      <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline" style="padding:9px 20px;font-size:.88rem">
        <i class="fa fa-times"></i> Clear
      </a>
    <?php endif; ?>
  </form>
</div>

<!-- Product Table + Bulk Actions -->
<div class="admin-card">
  <div class="admin-card-header">
    <div style="display:flex;align-items:center;gap:12px">
      <h3>Products <span style="color:var(--gray);font-weight:400;font-size:.9rem">(<?= $total ?>)</span></h3>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
      <div class="pm-bulk-bar" id="bulk-bar" style="display:none">
        <span id="bulk-count" style="font-size:.85rem;color:var(--gray)">0 selected</span>
        <form method="POST" action="<?= SITE_URL ?>/admin/actions/bulk_product.php" id="bulk-form" style="display:inline-flex;gap:6px">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
          <input type="hidden" name="action" id="bulk-action-input" value=""/>
          <div id="bulk-ids-container"></div>
          <button type="button" onclick="bulkAction('activate')"   class="btn btn-outline" style="padding:6px 12px;font-size:.8rem">Activate</button>
          <button type="button" onclick="bulkAction('deactivate')" class="btn btn-outline" style="padding:6px 12px;font-size:.8rem">Deactivate</button>
          <button type="button" onclick="bulkAction('delete')"     class="btn btn-outline" style="padding:6px 12px;font-size:.8rem;color:#e74c3c;border-color:#e74c3c">Delete</button>
        </form>
      </div>
      <a href="<?= SITE_URL ?>/admin/product_form.php" class="btn btn-primary" style="padding:9px 20px;font-size:.88rem">
        <i class="fa fa-plus"></i> Add Product
      </a>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table class="admin-table" id="products-table">
      <thead>
        <tr>
          <th style="width:36px"><input type="checkbox" id="select-all" title="Select All"/></th>
          <th>Image</th>
          <th>Name / SKU</th>
          <th>Category</th>
          <th>Brand</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Flags</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <?php
          $stockClass = '';
          if ($p['stock'] == 0) $stockClass = 'stock-out';
          elseif ($p['stock'] <= ($p['low_stock_alert'] ?? 5)) $stockClass = 'stock-low';
        ?>
        <tr>
          <td><input type="checkbox" class="row-check" value="<?= $p['id'] ?>"/></td>
          <td>
            <img src="<?= e($p['image'] ?? '') ?>" alt="<?= e($p['name']) ?>"
                 style="width:52px;height:62px;object-fit:cover;border-radius:8px;background:#f0ece6"
                 onerror="this.style.background='#f0ece6';this.src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='"/>
          </td>
          <td>
            <div style="font-weight:600;font-size:.92rem"><?= e($p['name']) ?></div>
            <?php if (!empty($p['sku'])): ?>
            <div style="font-size:.78rem;color:var(--gray);margin-top:2px">SKU: <?= e($p['sku']) ?></div>
            <?php endif; ?>
          </td>
          <td style="font-size:.88rem"><?= e($p['cat_name'] ?? '—') ?></td>
          <td style="font-size:.88rem"><?= e($p['brand_name'] ?? '—') ?></td>
          <td>
            <div style="font-weight:600"><?= formatPrice($p['price']) ?></div>
            <?php if ($p['sale_price'] > 0): ?>
              <div style="color:#e74c3c;font-size:.78rem"><?= formatPrice($p['sale_price']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <span class="pm-stock-badge <?= $stockClass ?>"><?= (int)$p['stock'] ?></span>
          </td>
          <td>
            <span class="status-badge status-<?= $p['status']==='active'?'active':'cancelled' ?>">
              <?= ucfirst($p['status']) ?>
            </span>
          </td>
          <td>
            <div class="pm-flags">
              <?php if (!empty($p['is_featured']) || !empty($p['featured'])): ?><span class="pm-flag" title="Featured">★</span><?php endif; ?>
              <?php if (!empty($p['is_trending'])): ?><span class="pm-flag pm-flag-blue" title="Trending">🔥</span><?php endif; ?>
              <?php if (!empty($p['is_new_arrival'])): ?><span class="pm-flag pm-flag-green" title="New Arrival">New</span><?php endif; ?>
              <?php if (!empty($p['is_best_seller'])): ?><span class="pm-flag pm-flag-purple" title="Best Seller">BS</span><?php endif; ?>
            </div>
          </td>
          <td>
            <div class="action-btns">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="action-btn view" target="_blank" title="View on Site"><i class="fa fa-eye"></i></a>
              <a href="<?= SITE_URL ?>/admin/product_form.php?id=<?= $p['id'] ?>" class="action-btn edit" title="Edit"><i class="fa fa-pen"></i></a>
              <a href="<?= SITE_URL ?>/admin/actions/duplicate_product.php?id=<?= $p['id'] ?>&csrf_token=<?= csrfToken() ?>" class="action-btn" title="Duplicate" style="color:#8e44ad"><i class="fa fa-copy"></i></a>
              <a href="<?= SITE_URL ?>/admin/actions/delete_product.php?id=<?= $p['id'] ?>&csrf_token=<?= csrfToken() ?>" class="action-btn delete delete-confirm" title="Delete"><i class="fa fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="10" style="text-align:center;padding:48px;color:var(--gray)">
          <i class="fa fa-box-open" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4"></i>
          No products found.
          <a href="<?= SITE_URL ?>/admin/product_form.php" style="color:var(--primary)">Add your first product</a>.
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div style="padding:20px;border-top:1px solid #f0ece6">
    <?php
      $qStr = http_build_query(array_filter(
        compact('search','status','gender') +
        ($catId   ? ['category'=>$catId]   : []) +
        ($brandId ? ['brand'=>$brandId]    : [])
      ));
      echo pagination($total, $perPage, $page, '?' . ($qStr ? $qStr.'&' : ''));
    ?>
  </div>
  <?php endif; ?>
</div>

<script>
// Select All checkbox
document.getElementById('select-all').addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
  updateBulkBar();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBar));

function updateBulkBar() {
  var checked = document.querySelectorAll('.row-check:checked');
  var bar = document.getElementById('bulk-bar');
  document.getElementById('bulk-count').textContent = checked.length + ' selected';
  bar.style.display = checked.length > 0 ? 'flex' : 'none';
}

function bulkAction(action) {
  var checked = document.querySelectorAll('.row-check:checked');
  if (!checked.length) { alert('Select at least one product.'); return; }
  if (action === 'delete' && !confirm('Delete ' + checked.length + ' product(s)? This cannot be undone.')) return;
  document.getElementById('bulk-action-input').value = action;
  var cont = document.getElementById('bulk-ids-container');
  cont.innerHTML = '';
  checked.forEach(function(cb) {
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
    cont.appendChild(inp);
  });
  document.getElementById('bulk-form').submit();
}

// Delete confirm
document.querySelectorAll('.delete-confirm').forEach(function(el) {
  el.addEventListener('click', function(e) {
    if (!confirm('Delete this product permanently? This cannot be undone.')) e.preventDefault();
  });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
