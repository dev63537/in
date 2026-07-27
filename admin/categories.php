<?php
$pageTitle = 'Manage Categories — Admin';
require_once __DIR__ . '/../includes/functions.php';

// Handle form actions
$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$catId   = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $ord  = (int)($_POST['sort_order'] ?? 0);
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            if ($name) {
                dbExecute("INSERT INTO categories (name, slug, description, sort_order) VALUES (?,?,?,?)", [$name, $slug, $desc, $ord]);
                setFlash('success', 'Category added successfully.');
            } else { setFlash('error', 'Category name is required.'); }
        }
        if ($action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $ord  = (int)($_POST['sort_order'] ?? 0);
            $stat = $_POST['status'] ?? 'active';
            if ($name && $catId) {
                dbExecute("UPDATE categories SET name=?, description=?, sort_order=?, status=? WHERE id=?", [$name, $desc, $ord, $stat, $catId]);
                setFlash('success', 'Category updated.');
            }
        }
        if ($action === 'delete' && $catId) {
            dbExecute("UPDATE products SET category_id=NULL WHERE category_id=?", [$catId]);
            dbExecute("DELETE FROM categories WHERE id=?", [$catId]);
            setFlash('success', 'Category deleted.');
        }
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// Handle GET delete
if ($action === 'delete' && $catId) {
    dbExecute("UPDATE products SET category_id=NULL WHERE category_id=?", [$catId]);
    dbExecute("DELETE FROM categories WHERE id=?", [$catId]);
    setFlash('success', 'Category deleted.');
    redirect(SITE_URL . '/admin/categories.php');
}

// Get category to edit
$editCat = null;
if ($action === 'editform' && $catId) {
    $editCat = dbFetchOne("SELECT * FROM categories WHERE id=?", [$catId]);
}

$categories = dbFetchAll("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.sort_order, c.name");

include __DIR__ . '/includes/admin_header.php';
?>

<!-- Page Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:14px">
  <div>
    <h1 style="font-size:1.4rem;font-weight:800">Categories</h1>
    <p style="color:#888;font-size:.88rem;margin-top:2px"><?= count($categories) ?> categories</p>
  </div>
  <button class="btn btn-primary" style="font-size:.9rem" onclick="toggleForm()">
    <i class="fa fa-plus"></i> Add Category
  </button>
</div>

<!-- Add/Edit Form -->
<div id="cat-form-wrap" class="admin-card" style="margin-bottom:24px;<?= ($editCat||isset($_GET['add'])) ? '' : 'display:none' ?>">
  <div class="admin-card-header">
    <h3><?= $editCat ? 'Edit Category' : 'Add New Category' ?></h3>
    <button onclick="toggleForm()" style="background:none;border:none;cursor:pointer;color:#888;font-size:1.1rem"><i class="fa fa-times"></i></button>
  </div>
  <div style="padding:24px">
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="action"     value="<?= $editCat ? 'edit' : 'add' ?>"/>
      <?php if ($editCat): ?><input type="hidden" name="id" value="<?= $editCat['id'] ?>"/><?php endif; ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="form-group">
          <label>Category Name *</label>
          <input type="text" name="name" required placeholder="e.g. Summer Collection" value="<?= e($editCat['name'] ?? '') ?>"/>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" min="0" value="<?= e($editCat['sort_order'] ?? '0') ?>"/>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label>Description</label>
          <textarea name="description" rows="2" style="width:100%;padding:11px 15px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;resize:vertical;outline:none"><?= e($editCat['description'] ?? '') ?></textarea>
        </div>
        <?php if ($editCat): ?>
        <div class="form-group">
          <label>Status</label>
          <select name="status" style="width:100%;padding:11px 15px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;outline:none">
            <option value="active"   <?= $editCat['status']==='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $editCat['status']==='inactive'?'selected':'' ?>>Inactive</option>
          </select>
        </div>
        <?php endif; ?>
      </div>
      <div style="margin-top:8px;display:flex;gap:12px">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?= $editCat ? 'Update' : 'Add' ?> Category</button>
        <button type="button" onclick="toggleForm()" class="btn btn-outline">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Categories Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3>All Categories</h3>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table" style="width:100%">
      <thead><tr>
        <th>#</th><th>Name</th><th>Description</th><th>Products</th><th>Order</th><th>Status</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td><?= $cat['id'] ?></td>
          <td><strong><?= e($cat['name']) ?></strong><div style="font-size:.75rem;color:#aaa"><?= e($cat['slug']) ?></div></td>
          <td style="max-width:200px;color:#888;font-size:.85rem"><?= e(substr($cat['description'] ?? '', 0, 60)) ?>…</td>
          <td><span style="background:#f0f0f0;padding:3px 10px;border-radius:50px;font-size:.8rem"><?= $cat['product_count'] ?></span></td>
          <td><?= $cat['sort_order'] ?></td>
          <td><span class="status-badge <?= $cat['status']==='active'?'status-active':'status-cancelled' ?>"><?= $cat['status'] ?></span></td>
          <td>
            <div class="action-btns">
              <a href="?action=editform&id=<?= $cat['id'] ?>" class="action-btn edit" onclick="document.getElementById('cat-form-wrap').style.display=''"><i class="fa fa-edit"></i> Edit</a>
              <a href="?action=delete&id=<?= $cat['id'] ?>" class="action-btn delete" onclick="return confirm('Delete this category? Products will be uncategorized.')"><i class="fa fa-trash"></i> Delete</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa">No categories yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleForm() {
  const w = document.getElementById('cat-form-wrap');
  w.style.display = w.style.display === 'none' ? '' : 'none';
}
</script>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
