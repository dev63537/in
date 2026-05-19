<?php
// admin/products.php — Manage Products
$pageTitle = 'Products';
include __DIR__ . '/includes/admin_header.php';
$categories = getCategories();
$products   = dbFetchAll("SELECT p.*,c.name AS cat FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
?>
<div class="admin-card">
  <div class="admin-card-header">
    <h3>All Products (<?= count($products) ?>)</h3>
    <button onclick="document.getElementById('add-modal').style.display='flex'" class="btn btn-primary" style="padding:8px 18px;font-size:.88rem">
      <i class="fa fa-plus"></i> Add Product
    </button>
  </div>
  <div style="overflow-x:auto">
    <table class="admin-table">
      <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><img src="<?= e($p['image']) ?>" style="width:50px;height:60px;object-fit:cover;border-radius:8px"/></td>
          <td><strong><?= e($p['name']) ?></strong></td>
          <td><?= e($p['cat'] ?? '—') ?></td>
          <td>
            <?= formatPrice($p['price']) ?>
            <?php if ($p['sale_price'] > 0): ?><br><span style="color:#e74c3c;font-size:.8rem"><?= formatPrice($p['sale_price']) ?></span><?php endif; ?>
          </td>
          <td><?= (int)$p['stock'] ?></td>
          <td><span class="status-badge status-<?= $p['status']==='active'?'active':'cancelled' ?>"><?= ucfirst($p['status']) ?></span></td>
          <td>
            <div class="action-btns">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="action-btn view" target="_blank">View</a>
              <button class="action-btn edit" onclick="openEdit(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">Edit</button>
              <a href="<?= SITE_URL ?>/admin/actions/delete_product.php?id=<?= $p['id'] ?>" class="action-btn delete delete-confirm">Delete</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD MODAL -->
<div id="add-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;position:relative">
    <button onclick="document.getElementById('add-modal').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px">Add New Product</h2>
    <form action="<?= SITE_URL ?>/admin/actions/add_product.php" method="POST" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="admin-form-grid">
        <div class="form-group" style="grid-column:1/-1"><label>Product Name *</label><input type="text" name="name" required/></div>
        <div class="form-group"><label>Category</label>
          <select name="category_id"><option value="">— Select —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" step="0.01" required/></div>
        <div class="form-group"><label>Sale Price (₹)</label><input type="number" name="sale_price" step="0.01" value="0"/></div>
        <div class="form-group"><label>Stock *</label><input type="number" name="stock" value="10" required/></div>
        <div class="form-group" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        <div class="form-group"><label>Sizes (comma-separated)</label><input type="text" name="sizes" value="XS,S,M,L,XL,XXL"/></div>
        <div class="form-group"><label>Colors (comma-separated)</label><input type="text" name="colors" value="Black,White,Beige"/></div>
        <div class="form-group"><label>Tags (e.g. new,sale,featured)</label><input type="text" name="tags"/></div>
        <div class="form-group"><label>Image URL or Upload</label><input type="text" name="image_url" placeholder="https://..."/></div>
        <div class="form-group" style="grid-column:1/-1"><label>Upload Image File</label><input type="file" name="image_file" accept="image/*"/></div>
        <div class="form-group"><label><input type="checkbox" name="featured" value="1"/> Featured Product</label></div>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:12px"><i class="fa fa-plus"></i> Add Product</button>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;position:relative">
    <button onclick="document.getElementById('edit-modal').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer"><i class="fa fa-times"></i></button>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:24px">Edit Product</h2>
    <form action="<?= SITE_URL ?>/admin/actions/edit_product.php" method="POST" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="id" id="edit-id"/>
      <div class="admin-form-grid">
        <div class="form-group" style="grid-column:1/-1"><label>Product Name *</label><input type="text" name="name" id="edit-name" required/></div>
        <div class="form-group"><label>Category</label>
          <select name="category_id" id="edit-cat"><option value="">— Select —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" id="edit-price" step="0.01" required/></div>
        <div class="form-group"><label>Sale Price (₹)</label><input type="number" name="sale_price" id="edit-sale-price" step="0.01"/></div>
        <div class="form-group"><label>Stock</label><input type="number" name="stock" id="edit-stock"/></div>
        <div class="form-group"><label>Status</label><select name="status" id="edit-status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="form-group" style="grid-column:1/-1"><label>Description</label><textarea name="description" id="edit-desc" rows="3"></textarea></div>
        <div class="form-group"><label>Sizes</label><input type="text" name="sizes" id="edit-sizes"/></div>
        <div class="form-group"><label>Colors</label><input type="text" name="colors" id="edit-colors"/></div>
        <div class="form-group"><label>Tags</label><input type="text" name="tags" id="edit-tags"/></div>
        <div class="form-group"><label>Image URL</label><input type="text" name="image_url" id="edit-img"/></div>
        <div class="form-group"><label>Upload New Image</label><input type="file" name="image_file" accept="image/*"/></div>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:12px"><i class="fa fa-save"></i> Save Changes</button>
    </form>
  </div>
</div>

<script>
function openEdit(p) {
  document.getElementById('edit-id').value       = p.id;
  document.getElementById('edit-name').value     = p.name;
  document.getElementById('edit-price').value    = p.price;
  document.getElementById('edit-sale-price').value = p.sale_price;
  document.getElementById('edit-stock').value    = p.stock;
  document.getElementById('edit-desc').value     = p.description || '';
  document.getElementById('edit-sizes').value    = p.sizes || '';
  document.getElementById('edit-colors').value   = p.colors || '';
  document.getElementById('edit-tags').value     = p.tags || '';
  document.getElementById('edit-img').value      = p.image || '';
  document.getElementById('edit-status').value   = p.status;
  var catSel = document.getElementById('edit-cat');
  for(var i=0;i<catSel.options.length;i++){if(catSel.options[i].value==p.category_id){catSel.selectedIndex=i;break;}}
  document.getElementById('edit-modal').style.display = 'flex';
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
