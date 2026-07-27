<?php
// admin/product_form.php — Add / Edit Product (Full Page Form)

require_once __DIR__ . '/../includes/functions.php';

$editId  = (int)($_GET['id'] ?? 0);
$product = null;
$errors  = [];
$images  = [];

if ($editId) {
    $product = dbFetchOne("SELECT * FROM products WHERE id = ?", [$editId]);
    if (!$product) { setFlash('error', 'Product not found.'); redirect(SITE_URL . '/admin/products.php'); }
    $images = dbFetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order", [$editId]);
    $pageTitle = 'Edit Product: ' . $product['name'];
} else {
    $pageTitle = 'Add New Product';
}

include __DIR__ . '/includes/admin_header.php';
$categories   = getCategories();
$subcategories= dbFetchAll("SELECT * FROM subcategories WHERE status='active' ORDER BY name");
$brands       = dbFetchAll("SELECT id, name FROM brands WHERE status='active' ORDER BY name");
?>

<style>
.pf-tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid #f0ece6;padding-bottom:0}
.pf-tab{padding:10px 22px;font-size:.9rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--gray);transition:.2s}
.pf-tab.active{color:var(--primary);border-bottom-color:var(--primary)}
.pf-panel{display:none}.pf-panel.active{display:block}
.pf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.pf-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.pf-full{grid-column:1/-1}
.img-preview-wrap{position:relative;display:inline-block}
.img-preview-wrap img{width:120px;height:140px;object-fit:cover;border-radius:10px;border:2px solid #f0ece6}
.img-remove-btn{position:absolute;top:-8px;right:-8px;background:#e74c3c;color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center}
.gallery-grid{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
.checkbox-group{display:flex;flex-wrap:wrap;gap:16px}
.checkbox-label{display:flex;align-items:center;gap:8px;font-size:.9rem;cursor:pointer;padding:8px 14px;border:1.5px solid #e0e0e0;border-radius:8px;transition:.2s}
.checkbox-label:hover{border-color:var(--primary)}
.checkbox-label input[type=checkbox]{width:16px;height:16px;accent-color:var(--primary)}
.upload-zone{border:2px dashed #d0c8be;border-radius:12px;padding:32px;text-align:center;cursor:pointer;transition:.3s;background:#faf8f5}
.upload-zone:hover{border-color:var(--primary);background:#fdf9f3}
.upload-zone i{font-size:2rem;color:var(--gray);margin-bottom:8px;display:block}
@media(max-width:768px){.pf-grid,.pf-grid-3{grid-template-columns:1fr}.pf-tabs{overflow-x:auto}}
</style>

<!-- Breadcrumb -->
<div style="margin-bottom:20px;font-size:.88rem;color:var(--gray)">
  <a href="<?= SITE_URL ?>/admin/products.php" style="color:var(--primary)"><i class="fa fa-box"></i> Products</a>
  <i class="fa fa-chevron-right" style="margin:0 8px;font-size:.7rem"></i>
  <?= $editId ? 'Edit Product' : 'Add New Product' ?>
</div>

<form method="POST" enctype="multipart/form-data"
      action="<?= SITE_URL ?>/admin/actions/<?= $editId ? 'edit_product' : 'add_product' ?>.php"
      id="product-form">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
  <?php if ($editId): ?><input type="hidden" name="id" value="<?= $editId ?>"/><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

    <!-- LEFT COLUMN -->
    <div>
      <!-- Tabs -->
      <div class="admin-card">
        <div class="pf-tabs" id="pf-tabs">
          <button type="button" class="pf-tab active" data-tab="basic">Basic Info</button>
          <button type="button" class="pf-tab" data-tab="pricing">Pricing &amp; Stock</button>
          <button type="button" class="pf-tab" data-tab="images">Images</button>
          <button type="button" class="pf-tab" data-tab="seo">SEO &amp; Tags</button>
        </div>

        <!-- TAB: Basic Info -->
        <div class="pf-panel active" id="tab-basic">
          <div class="pf-grid">
            <div class="form-group pf-full">
              <label>Product Name <span style="color:#e74c3c">*</span></label>
              <input type="text" name="name" id="pf-name" required
                     value="<?= e($product['name'] ?? '') ?>"
                     placeholder="e.g. Floral Wrap Midi Dress" oninput="autoSlug(this.value)"/>
            </div>
            <div class="form-group">
              <label>SKU <span style="color:var(--gray);font-size:.8rem">(auto-generated)</span></label>
              <input type="text" name="sku" id="pf-sku"
                     value="<?= e($product['sku'] ?? '') ?>"
                     placeholder="e.g. DSH-WM-001"/>
            </div>
            <div class="form-group">
              <label>Slug <span style="color:var(--gray);font-size:.8rem">(SEO URL)</span></label>
              <input type="text" name="slug" id="pf-slug"
                     value="<?= e($product['slug'] ?? '') ?>"
                     placeholder="e.g. floral-wrap-midi-dress"/>
            </div>
            <div class="form-group">
              <label>Category</label>
              <select name="category_id" id="pf-cat">
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Brand</label>
              <select name="brand_id">
                <option value="">— Select Brand —</option>
                <?php foreach ($brands as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected':'' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <?php foreach (['unisex'=>'Unisex','men'=>'Men','women'=>'Women','kids'=>'Kids'] as $v => $l): ?>
                  <option value="<?= $v ?>" <?= ($product['gender'] ?? 'unisex') === $v ? 'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Material</label>
              <input type="text" name="material" value="<?= e($product['material'] ?? '') ?>" placeholder="e.g. 100% Cotton"/>
            </div>
            <div class="form-group pf-full">
              <label>Short Description</label>
              <input type="text" name="short_description" value="<?= e($product['short_description'] ?? '') ?>" placeholder="One-line summary shown on listing cards"/>
            </div>
            <div class="form-group pf-full">
              <label>Full Description</label>
              <textarea name="description" rows="5" placeholder="Detailed product description…"><?= e($product['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label>Sizes <span style="color:var(--gray);font-size:.8rem">(comma-separated)</span></label>
              <input type="text" name="sizes" value="<?= e($product['sizes'] ?? 'XS,S,M,L,XL,XXL') ?>" placeholder="XS,S,M,L,XL,XXL"/>
            </div>
            <div class="form-group">
              <label>Colors <span style="color:var(--gray);font-size:.8rem">(comma-separated)</span></label>
              <input type="text" name="colors" value="<?= e($product['colors'] ?? 'Black,White') ?>" placeholder="Black,White,Red"/>
            </div>
          </div>
        </div>

        <!-- TAB: Pricing & Stock -->
        <div class="pf-panel" id="tab-pricing">
          <div class="pf-grid">
            <div class="form-group">
              <label>Price (₹) <span style="color:#e74c3c">*</span></label>
              <input type="number" name="price" step="0.01" min="0" required value="<?= e($product['price'] ?? '') ?>" placeholder="0.00"/>
            </div>
            <div class="form-group">
              <label>Discount / Sale Price (₹)</label>
              <input type="number" name="sale_price" step="0.01" min="0" value="<?= e($product['sale_price'] ?? 0) ?>" placeholder="0.00"/>
            </div>
            <div class="form-group">
              <label>Cost Price (₹) <span style="color:var(--gray);font-size:.8rem">(admin only)</span></label>
              <input type="number" name="cost_price" step="0.01" min="0" value="<?= e($product['cost_price'] ?? 0) ?>" placeholder="0.00"/>
            </div>
            <div class="form-group">
              <label>Stock Quantity <span style="color:#e74c3c">*</span></label>
              <input type="number" name="stock" min="0" required value="<?= e($product['stock'] ?? 0) ?>" placeholder="0"/>
            </div>
            <div class="form-group">
              <label>Low Stock Alert</label>
              <input type="number" name="low_stock_alert" min="1" value="<?= e($product['low_stock_alert'] ?? 5) ?>" placeholder="5"/>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="active"   <?= ($product['status'] ?? 'active') === 'active'   ? 'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected':'' ?>>Inactive</option>
              </select>
            </div>
          </div>

          <div style="margin-top:20px">
            <label style="display:block;font-weight:600;margin-bottom:12px">Product Flags</label>
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) || !empty($product['featured']) ? 'checked':'' ?>/>
                <i class="fa fa-star" style="color:#f39c12"></i> Featured
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="is_trending" value="1" <?= !empty($product['is_trending']) ? 'checked':'' ?>/>
                <i class="fa fa-fire" style="color:#e74c3c"></i> Trending
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="is_new_arrival" value="1" <?= !empty($product['is_new_arrival']) ? 'checked':'' ?>/>
                <i class="fa fa-bolt" style="color:#3498db"></i> New Arrival
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="is_best_seller" value="1" <?= !empty($product['is_best_seller']) ? 'checked':'' ?>/>
                <i class="fa fa-trophy" style="color:#8e44ad"></i> Best Seller
              </label>
            </div>
          </div>
        </div>

        <!-- TAB: Images -->
        <div class="pf-panel" id="tab-images">
          <div class="form-group">
            <label style="font-weight:600;margin-bottom:10px;display:block">Main Product Image</label>
            <?php if (!empty($product['image'])): ?>
              <div style="margin-bottom:12px">
                <img src="<?= e($product['image']) ?>" id="main-img-preview"
                     style="width:160px;height:190px;object-fit:cover;border-radius:12px;border:2px solid #f0ece6"/>
              </div>
            <?php endif; ?>
            <div class="upload-zone" onclick="document.getElementById('main-image-input').click()">
              <i class="fa fa-cloud-upload-alt"></i>
              <div style="font-weight:600;margin-bottom:4px">Click to upload main image</div>
              <div style="color:var(--gray);font-size:.82rem">JPG, PNG, WEBP — max 2MB</div>
            </div>
            <input type="file" name="main_image" id="main-image-input" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewMainImage(this)"/>
            <div class="form-group" style="margin-top:12px">
              <label>Or paste Image URL</label>
              <input type="text" name="image_url" id="image-url-input"
                     value="<?= e($product['image'] ?? '') ?>" placeholder="https://…"/>
            </div>
          </div>

          <div class="form-group" style="margin-top:24px">
            <label style="font-weight:600;margin-bottom:10px;display:block">Gallery Images <span style="color:var(--gray);font-size:.8rem">(up to 6)</span></label>
            <?php if (!empty($images)): ?>
              <div class="gallery-grid" id="gallery-preview">
                <?php foreach ($images as $img):
                  // Support both full URLs and bare filenames
                  $imgSrc = (strpos($img['image_path'], 'http') === 0)
                            ? $img['image_path']
                            : UPLOAD_URL . $img['image_path'];
                ?>
                  <div class="img-preview-wrap" id="gimg-<?= $img['id'] ?>">
                    <img src="<?= e($imgSrc) ?>" alt="Gallery" style="width:100px;height:120px;object-fit:cover;border-radius:8px"/>
                    <button type="button" class="img-remove-btn"
                            onclick="removeGalleryImage(<?= $img['id'] ?>, <?= $editId ?>)" title="Remove">
                      <i class="fa fa-times"></i>
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="gallery-grid" id="gallery-preview"></div>
            <?php endif; ?>
            <div class="upload-zone" style="margin-top:12px" onclick="document.getElementById('gallery-input').click()">
              <i class="fa fa-images"></i>
              <div style="font-weight:600;margin-bottom:4px">Click to upload gallery images</div>
              <div style="color:var(--gray);font-size:.82rem">Select up to 6 images at once</div>
            </div>
            <input type="file" name="gallery_images[]" id="gallery-input" multiple
                   accept="image/jpeg,image/png,image/webp" style="display:none"
                   onchange="previewGallery(this)"/>
          </div>
        </div>

        <!-- TAB: SEO & Tags -->
        <div class="pf-panel" id="tab-seo">
          <div class="pf-grid">
            <div class="form-group">
              <label>Tags <span style="color:var(--gray);font-size:.8rem">(comma-separated)</span></label>
              <input type="text" name="tags" value="<?= e($product['tags'] ?? '') ?>" placeholder="new,sale,featured"/>
            </div>
            <div class="form-group">
              <label>Meta Title</label>
              <input type="text" name="meta_title" value="<?= e($product['meta_title'] ?? '') ?>" placeholder="SEO title (60 chars max)"/>
            </div>
            <div class="form-group pf-full">
              <label>Meta Description</label>
              <textarea name="meta_description" rows="3" placeholder="SEO description (160 chars max)"><?= e($product['meta_description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div><!-- .admin-card -->
    </div><!-- LEFT COLUMN -->

    <!-- RIGHT COLUMN -->
    <div style="position:sticky;top:90px">
      <div class="admin-card">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Publish</h3>
        <div class="form-group" style="margin-bottom:12px">
          <label>Status</label>
          <select name="status" id="status-sidebar">
            <option value="active"  <?= ($product['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
            <i class="fa fa-save"></i> <?= $editId ? 'Save Changes' : 'Add Product' ?>
          </button>
          <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline" style="width:100%;justify-content:center">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
        </div>
      </div>

      <?php if ($editId && !empty($product['image'])): ?>
      <div class="admin-card" style="margin-top:16px">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:12px">Current Main Image</h3>
        <img src="<?= e($product['image']) ?>" style="width:100%;border-radius:10px;object-fit:cover;max-height:220px"/>
      </div>
      <?php endif; ?>

      <?php if ($editId): ?>
      <div class="admin-card" style="margin-top:16px">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:12px">Product Info</h3>
        <div style="font-size:.82rem;color:var(--gray);line-height:2">
          <div>ID: <strong style="color:var(--dark)"><?= $editId ?></strong></div>
          <div>Created: <strong style="color:var(--dark)"><?= date('d M Y', strtotime($product['created_at'])) ?></strong></div>
          <?php if (!empty($product['updated_at'])): ?>
          <div>Updated: <strong style="color:var(--dark)"><?= date('d M Y H:i', strtotime($product['updated_at'])) ?></strong></div>
          <?php endif; ?>
          <div>SKU: <strong style="color:var(--dark)"><?= e($product['sku'] ?? '—') ?></strong></div>
        </div>
        <div style="margin-top:14px;display:flex;gap:8px">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $editId ?>" target="_blank"
             class="btn btn-outline" style="padding:7px 14px;font-size:.82rem;flex:1;justify-content:center">
            <i class="fa fa-eye"></i> Preview
          </a>
          <a href="<?= SITE_URL ?>/admin/actions/duplicate_product.php?id=<?= $editId ?>&csrf_token=<?= csrfToken() ?>"
             class="btn btn-outline" style="padding:7px 14px;font-size:.82rem;flex:1;justify-content:center">
            <i class="fa fa-copy"></i> Duplicate
          </a>
        </div>
      </div>
      <?php endif; ?>
    </div><!-- RIGHT COLUMN -->

  </div><!-- grid -->
</form>

<script>
// Tab switching
document.querySelectorAll('.pf-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.pf-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pf-panel').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('tab-' + this.dataset.tab).classList.add('active');
  });
});

// Auto-generate slug from name
function autoSlug(name) {
  var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  document.getElementById('pf-slug').value = slug;
  // Auto-generate SKU if empty
  var sku = document.getElementById('pf-sku');
  if (!sku.value || sku.dataset.autoSet) {
    var cat = document.getElementById('pf-cat');
    var catText = cat.options[cat.selectedIndex] ? cat.options[cat.selectedIndex].text.substring(0,3).toUpperCase() : 'PRD';
    var rnd = Math.floor(Math.random()*900)+100;
    sku.value = 'DSH-' + catText + '-' + rnd;
    sku.dataset.autoSet = 'true';
  }
}

// Main image preview
function previewMainImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var prev = document.getElementById('main-img-preview');
      if (!prev) {
        prev = document.createElement('img');
        prev.id = 'main-img-preview';
        prev.style = 'width:160px;height:190px;object-fit:cover;border-radius:12px;border:2px solid #f0ece6;display:block;margin-bottom:12px';
        document.getElementById('main-image-input').parentNode.insertBefore(prev, document.getElementById('main-image-input').parentNode.firstChild);
      }
      prev.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
    document.getElementById('image-url-input').value = '';
  }
}

// Gallery preview
function previewGallery(input) {
  var grid = document.getElementById('gallery-preview');
  var max = 6;
  var current = grid.querySelectorAll('.img-preview-wrap').length;
  Array.from(input.files).slice(0, max - current).forEach(function(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var wrap = document.createElement('div');
      wrap.className = 'img-preview-wrap';
      wrap.innerHTML = '<img src="' + e.target.result + '" style="width:100px;height:120px;object-fit:cover;border-radius:8px"/>';
      grid.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
}

// Remove gallery image via AJAX
function removeGalleryImage(imgId, productId) {
  if (!confirm('Remove this image?')) return;
  fetch('<?= SITE_URL ?>/admin/actions/remove_gallery.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'id=' + imgId + '&product_id=' + productId + '&csrf_token=<?= csrfToken() ?>'
  }).then(r => r.json()).then(data => {
    if (data.success) {
      var el = document.getElementById('gimg-' + imgId);
      if (el) el.remove();
    } else { alert(data.message || 'Error removing image'); }
  });
}

// Prevent server crash (ERR_CONNECTION_RESET) by validating file sizes before upload
document.getElementById('product-form').addEventListener('submit', function(e) {
  var maxBytes = 2 * 1024 * 1024; // 2MB per file limit
  var totalMaxBytes = 8 * 1024 * 1024; // 8MB total payload limit for InfinityFree
  var totalBytes = 0;
  
  var main = document.getElementById('main-image-input');
  if (main.files && main.files.length > 0) {
    if (main.files[0].size > maxBytes) {
      alert('Main image exceeds the 2MB limit! Please choose a smaller image.');
      e.preventDefault();
      return;
    }
    totalBytes += main.files[0].size;
  }
  
  var gallery = document.getElementById('gallery-input');
  if (gallery.files && gallery.files.length > 0) {
    for (var i = 0; i < gallery.files.length; i++) {
      if (gallery.files[i].size > maxBytes) {
        alert('Gallery image "' + gallery.files[i].name + '" exceeds the 2MB limit! Please choose a smaller image.');
        e.preventDefault();
        return;
      }
      totalBytes += gallery.files[i].size;
    }
  }
  
  if (totalBytes > totalMaxBytes) {
    alert('Total size of all images combined exceeds 8MB! Your server will block this upload and crash (ERR_CONNECTION_RESET). Please upload fewer images or compress them first.');
    e.preventDefault();
  }
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
