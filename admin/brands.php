<?php
// admin/brands.php — Manage Brands
$pageTitle = 'Brands';
include __DIR__ . '/includes/admin_header.php';

$brands = dbFetchAll("SELECT b.*, COUNT(p.id) AS product_count
    FROM brands b
    LEFT JOIN products p ON p.brand_id = b.id
    GROUP BY b.id
    ORDER BY b.sort_order, b.name");
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3>All Brands (<?= count($brands) ?>)</h3>
    <button onclick="document.getElementById('add-brand-modal').style.display='flex'"
            class="btn btn-primary" style="padding:8px 18px;font-size:.88rem">
      <i class="fa fa-plus"></i> Add Brand
    </button>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($brands as $b): ?>
        <tr>
          <td><strong><?= e($b['name']) ?></strong></td>
          <td style="font-size:.82rem;color:var(--gray)"><?= e($b['slug']) ?></td>
          <td><?= (int)$b['product_count'] ?></td>
          <td><span class="status-badge status-<?= $b['status']==='active'?'active':'cancelled' ?>"><?= ucfirst($b['status']) ?></span></td>
          <td>
            <div class="action-btns">
              <button class="action-btn edit" onclick="openBrandEdit(<?= htmlspecialchars(json_encode($b),ENT_QUOTES) ?>)">Edit</button>
              <a href="<?= SITE_URL ?>/admin/actions/delete_brand.php?id=<?= $b['id'] ?>&csrf_token=<?= csrfToken() ?>"
                 class="action-btn delete"
                 onclick="return confirm('Delete brand <?= e($b['name']) ?>?')">Delete</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($brands)): ?>
        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray)">No brands yet. Add one above.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD MODAL -->
<div id="add-brand-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:480px;position:relative">
    <button onclick="document.getElementById('add-brand-modal').style.display='none'" style="position:absolute;top:14px;right:14px;background:none;border:none;font-size:1.2rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px">Add Brand</h2>
    <form method="POST" action="<?= SITE_URL ?>/admin/actions/add_brand.php" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group"><label>Brand Name *</label><input type="text" name="name" required oninput="autoSlugBrand(this.value)"/></div>
      <div class="form-group"><label>Slug</label><input type="text" name="slug" id="brand-slug" placeholder="auto-generated"/></div>
      <div class="form-group"><label>Status</label>
        <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add Brand</button>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div id="edit-brand-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:480px;position:relative">
    <button onclick="document.getElementById('edit-brand-modal').style.display='none'" style="position:absolute;top:14px;right:14px;background:none;border:none;font-size:1.2rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px">Edit Brand</h2>
    <form method="POST" action="<?= SITE_URL ?>/admin/actions/edit_brand.php" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="id" id="edit-brand-id"/>
      <div class="form-group"><label>Brand Name *</label><input type="text" name="name" id="edit-brand-name" required/></div>
      <div class="form-group"><label>Slug</label><input type="text" name="slug" id="edit-brand-slug"/></div>
      <div class="form-group"><label>Status</label>
        <select name="status" id="edit-brand-status">
          <option value="active">Active</option><option value="inactive">Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
    </form>
  </div>
</div>

<script>
function autoSlugBrand(v){document.getElementById('brand-slug').value=v.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');}
function openBrandEdit(b){
  document.getElementById('edit-brand-id').value=b.id;
  document.getElementById('edit-brand-name').value=b.name;
  document.getElementById('edit-brand-slug').value=b.slug;
  document.getElementById('edit-brand-status').value=b.status;
  document.getElementById('edit-brand-modal').style.display='flex';
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
