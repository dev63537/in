<?php
// admin/import_products.php — Bulk CSV Product Import
$pageTitle = 'Import Products';
include __DIR__ . '/includes/admin_header.php';

/* ────────────────────────────────────────────────────────────
   STEP 1 — Handle "confirmed import" POST
──────────────────────────────────────────────────────────── */
$importResults = [];
$importDone    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token mismatch. Please try again.');
        redirect(SITE_URL . '/admin/import_products.php');
    }

    $rows = json_decode($_POST['rows_json'] ?? '[]', true);
    if (!is_array($rows) || empty($rows)) {
        setFlash('error', 'No data to import.');
        redirect(SITE_URL . '/admin/import_products.php');
    }

    $inserted = 0;
    $errors   = [];

    // Cache categories (lower-cased name => id) so we avoid N+1
    $catRows   = dbFetchAll("SELECT id, name FROM categories");
    $catMap    = [];
    foreach ($catRows as $cr) {
        $catMap[strtolower(trim($cr['name']))] = (int)$cr['id'];
    }

    foreach ($rows as $idx => $row) {
        $lineNum = $idx + 2; // +2: 1-based + header row

        $name = trim($row['name'] ?? '');
        if ($name === '') {
            $errors[] = "Row $lineNum: 'name' is required — skipped.";
            continue;
        }

        $price = (float)str_replace(',', '', $row['price'] ?? '0');
        if ($price <= 0) {
            $errors[] = "Row $lineNum ($name): invalid price — skipped.";
            continue;
        }

        $salePrice = (float)str_replace(',', '', $row['sale_price'] ?? '0');
        $stock     = max(0, (int)($row['stock'] ?? 0));
        $sizes     = trim($row['sizes']  ?? '');
        $colors    = trim($row['colors'] ?? '');
        $tags      = trim($row['tags']   ?? '');
        $status    = in_array(trim($row['status'] ?? ''), ['active','inactive']) ? trim($row['status']) : 'active';
        $catName   = trim($row['category_name'] ?? '');
        $desc      = trim($row['description'] ?? '');

        // Resolve or create category
        $catId = null;
        if ($catName !== '') {
            $key = strtolower($catName);
            if (isset($catMap[$key])) {
                $catId = $catMap[$key];
            } else {
                // Create new category
                $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($catName));
                $slug = trim($slug, '-');
                // Ensure slug uniqueness
                $existing = dbFetchOne("SELECT id FROM categories WHERE slug = ?", [$slug]);
                if ($existing) $slug = $slug . '-' . time();
                dbExecute(
                    "INSERT INTO categories (name, slug, status) VALUES (?, ?, 'active')",
                    [$catName, $slug]
                );
                $newCatId = dbLastId();
                $catMap[$key] = (int)$newCatId;
                $catId = (int)$newCatId;
            }
        }

        // Generate SKU
        $sku = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $name), 0, 6)) . '-' . rand(1000, 9999);

        try {
            dbExecute(
                "INSERT INTO products
                    (name, description, price, sale_price, stock, sizes, colors, tags, status, category_id, sku, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$name, $desc, $price, $salePrice, $stock, $sizes, $colors, $tags, $status, $catId, $sku]
            );
            $inserted++;
        } catch (\Exception $ex) {
            $errors[] = "Row $lineNum ($name): DB error — " . $ex->getMessage();
        }
    }

    $importDone = true;
    $importResults = ['inserted' => $inserted, 'errors' => $errors];
}

/* ────────────────────────────────────────────────────────────
   STEP 0 — Handle CSV Upload (preview)
──────────────────────────────────────────────────────────── */
$previewRows = [];
$parseError  = '';
$csvHeaders  = ['name','description','price','sale_price','stock','sizes','colors','tags','status','category_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && !isset($_POST['confirm_import'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $parseError = 'Security token mismatch.';
    } else {
        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $parseError = 'Upload error (code ' . $file['error'] . ').';
        } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
            $parseError = 'Only .csv files are accepted.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $parseError = 'File too large (max 5 MB).';
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                $parseError = 'Could not open the uploaded file.';
            } else {
                // Read header
                $header = fgetcsv($handle);
                if (!$header) {
                    $parseError = 'CSV is empty or malformed.';
                } else {
                    // Normalize headers
                    $header = array_map('strtolower', array_map('trim', $header));
                    $rowNum = 1;
                    while (($line = fgetcsv($handle)) !== false) {
                        $rowNum++;
                        if (count($line) < 1 || (count($line) === 1 && $line[0] === '')) continue;
                        $row = [];
                        foreach ($csvHeaders as $col) {
                            $pos = array_search($col, $header);
                            $row[$col] = ($pos !== false && isset($line[$pos])) ? trim($line[$pos]) : '';
                        }
                        $previewRows[] = $row;
                        if ($rowNum > 502) { // safety limit: 500 rows + header
                            $parseError = 'Only first 500 rows will be imported.';
                            break;
                        }
                    }
                    fclose($handle);
                    if (empty($previewRows) && !$parseError) {
                        $parseError = 'The CSV has no data rows.';
                    }
                }
            }
        }
    }
}
?>

<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-size:1.35rem;font-weight:700;color:#1a1a1a;margin-bottom:4px">
      <i class="fa fa-file-csv" style="color:var(--primary,#8B6F47);margin-right:8px"></i>Import Products from CSV
    </h2>
    <p style="color:#888;font-size:.88rem">Upload a CSV file to bulk-create products. Up to 500 rows per import.</p>
  </div>
  <a href="<?= SITE_URL ?>/admin/download_template.php"
     style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#f0ece6;color:#5a4330;font-weight:600;border-radius:8px;text-decoration:none;font-size:.88rem">
    <i class="fa fa-download"></i> Download Template
  </a>
</div>

<?php if ($importDone): ?>
  <!-- ── Import Result ── -->
  <div class="admin-card" style="margin-bottom:20px">
    <div class="admin-card-header">
      <h3><i class="fa fa-check-circle" style="color:#27ae60;margin-right:8px"></i>Import Complete</h3>
    </div>
    <div style="padding:20px 24px">
      <div style="font-size:1.1rem;font-weight:600;color:#27ae60;margin-bottom:12px">
        ✅ <?= $importResults['inserted'] ?> product(s) imported successfully.
      </div>
      <?php if (!empty($importResults['errors'])): ?>
      <div style="margin-top:12px">
        <strong style="color:#e74c3c">Errors / Skipped rows:</strong>
        <ul style="margin-top:8px;padding-left:20px;color:#c0392b;font-size:.88rem;line-height:1.8">
          <?php foreach ($importResults['errors'] as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <div style="margin-top:20px;display:flex;gap:12px">
        <a href="<?= SITE_URL ?>/admin/import_products.php" class="btn btn-outline" style="padding:10px 22px;font-size:.88rem">
          <i class="fa fa-upload"></i> Import Another File
        </a>
        <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-primary" style="padding:10px 22px;font-size:.88rem">
          <i class="fa fa-box"></i> View Products
        </a>
      </div>
    </div>
  </div>

<?php elseif (!empty($previewRows)): ?>
  <!-- ── Preview Table ── -->
  <div class="admin-card" style="margin-bottom:20px">
    <div class="admin-card-header">
      <h3><i class="fa fa-table" style="margin-right:8px;color:var(--primary,#8B6F47)"></i>Preview — <?= count($previewRows) ?> row(s)</h3>
      <span style="font-size:.82rem;color:#888">Review the data below, then click Confirm Import.</span>
    </div>
    <?php if ($parseError): ?>
    <div style="padding:12px 24px;background:#fff8e1;color:#b7770d;font-size:.88rem;border-bottom:1px solid #ffe082">
      <i class="fa fa-exclamation-triangle"></i> <?= e($parseError) ?>
    </div>
    <?php endif; ?>
    <div style="overflow-x:auto">
      <table class="admin-table" style="font-size:.82rem">
        <thead>
          <tr>
            <th>#</th>
            <?php foreach ($csvHeaders as $h): ?>
            <th><?= ucwords(str_replace('_',' ',$h)) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($previewRows as $ri => $row): ?>
          <?php
            $hasError = trim($row['name']) === '' || (float)str_replace(',','',$row['price']) <= 0;
            $rowStyle = $hasError ? 'background:#fdecea' : ($ri % 2 === 1 ? 'background:#fafafa' : '');
          ?>
          <tr style="<?= $rowStyle ?>">
            <td><?= $ri + 1 ?></td>
            <?php foreach ($csvHeaders as $h): ?>
            <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($row[$h]) ?>">
              <?php if ($hasError && ($h === 'name' || $h === 'price')): ?>
                <span style="color:#e74c3c;font-weight:700"><?= e($row[$h] ?: '(empty)') ?></span>
              <?php else: ?>
                <?= e($row[$h]) ?>
              <?php endif; ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Confirm form -->
    <div style="padding:20px 24px;border-top:1px solid #f0ece6;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      <form method="POST" action="<?= SITE_URL ?>/admin/import_products.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <input type="hidden" name="confirm_import" value="1"/>
        <input type="hidden" name="rows_json" value="<?= e(json_encode($previewRows)) ?>"/>
        <button type="submit" class="btn btn-primary" style="padding:11px 28px;font-size:.92rem"
                onclick="return confirm('Import <?= count($previewRows) ?> product(s)? This cannot be undone.')">
          <i class="fa fa-cloud-upload-alt"></i> Confirm &amp; Import <?= count($previewRows) ?> Products
        </button>
      </form>
      <a href="<?= SITE_URL ?>/admin/import_products.php" class="btn btn-outline" style="padding:11px 22px;font-size:.88rem">
        <i class="fa fa-times"></i> Cancel / Re-upload
      </a>
    </div>
  </div>

<?php else: ?>
  <!-- ── Upload Form ── -->
  <?php if ($parseError): ?>
  <div class="flash-message flash-error" style="position:static;margin-bottom:16px">
    <i class="fa fa-circle-exclamation"></i> <?= e($parseError) ?>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">
    <!-- Upload Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fa fa-upload" style="margin-right:8px;color:var(--primary,#8B6F47)"></i>Upload CSV File</h3>
      </div>
      <div style="padding:32px">
        <form method="POST" action="<?= SITE_URL ?>/admin/import_products.php" enctype="multipart/form-data" id="upload-form">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>

          <!-- Drop Zone -->
          <label for="csv_file" id="drop-zone"
                 style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                        border:2px dashed #c8b99a;border-radius:12px;padding:48px 24px;cursor:pointer;
                        transition:all .2s;background:#fdf9f5;text-align:center"
                 ondragover="this.style.borderColor='#8B6F47';this.style.background='#f5ede4';event.preventDefault()"
                 ondragleave="this.style.borderColor='#c8b99a';this.style.background='#fdf9f5'"
                 ondrop="handleDrop(event)">
            <i class="fa fa-file-csv" style="font-size:3rem;color:#c8b99a;margin-bottom:16px"></i>
            <div style="font-weight:600;font-size:1rem;color:#5a4330;margin-bottom:6px">Drop your CSV file here</div>
            <div style="color:#aaa;font-size:.85rem;margin-bottom:16px">or click to browse</div>
            <span style="background:#8B6F47;color:#fff;padding:8px 22px;border-radius:8px;font-size:.85rem;font-weight:600">
              Choose File
            </span>
            <div id="file-name" style="margin-top:14px;font-size:.82rem;color:#888"></div>
          </label>
          <input type="file" name="csv_file" id="csv_file" accept=".csv"
                 style="position:absolute;opacity:0;width:0;height:0"
                 onchange="document.getElementById('file-name').textContent = this.files[0]?.name ?? ''"/>

          <button type="submit" class="btn btn-primary"
                  style="width:100%;margin-top:20px;padding:13px;font-size:.95rem">
            <i class="fa fa-eye"></i> Parse &amp; Preview
          </button>
        </form>
      </div>
    </div>

    <!-- Instructions Card -->
    <div class="admin-card">
      <div class="admin-card-header"><h3><i class="fa fa-circle-info" style="margin-right:8px;color:var(--primary,#8B6F47)"></i>CSV Format Guide</h3></div>
      <div style="padding:20px 22px;font-size:.85rem;line-height:1.8">
        <p style="margin-bottom:12px">The CSV must have these columns (in any order):</p>
        <table style="width:100%;border-collapse:collapse">
          <?php
          $guide = [
            'name'          => 'Required. Product name.',
            'description'   => 'Optional. Product description.',
            'price'         => 'Required. e.g. 1999',
            'sale_price'    => 'Optional. e.g. 1499 (0 = no sale)',
            'stock'         => 'Optional. Integer, default 0.',
            'sizes'         => 'Optional. Comma-separated: XS,S,M,L',
            'colors'        => 'Optional. Comma-separated: Red,Blue',
            'tags'          => 'Optional. Comma-separated: new,sale',
            'status'        => 'active or inactive',
            'category_name' => 'Optional. Created if not found.',
          ];
          foreach ($guide as $col => $desc):
          ?>
          <tr style="border-bottom:1px solid #f0ece6">
            <td style="padding:6px 8px 6px 0;font-weight:700;color:#5a4330;white-space:nowrap"><?= e($col) ?></td>
            <td style="padding:6px 0;color:#666"><?= e($desc) ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
        <div style="margin-top:16px;padding:12px;background:#fff8e1;border-radius:8px;font-size:.82rem;color:#7a6000">
          <i class="fa fa-lightbulb" style="margin-right:6px"></i>
          Download the template above for a ready-to-fill example.
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
function handleDrop(e) {
  e.preventDefault();
  var lbl = document.querySelector('#drop-zone');
  lbl.style.borderColor = '#c8b99a';
  lbl.style.background  = '#fdf9f5';
  var file = e.dataTransfer.files[0];
  if (!file) return;
  var inp = document.getElementById('csv_file');
  var dt  = new DataTransfer();
  dt.items.add(file);
  inp.files = dt.files;
  document.getElementById('file-name').textContent = file.name;
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
