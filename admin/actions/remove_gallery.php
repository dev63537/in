<?php
// admin/actions/remove_gallery.php — AJAX endpoint
require_once __DIR__ . '/../../includes/functions.php';
startSession();
header('Content-Type: application/json');

if (!isAdmin() || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$imgId     = (int)($_POST['id']         ?? 0);
$productId = (int)($_POST['product_id'] ?? 0);

$img = dbFetchOne("SELECT * FROM product_images WHERE id=? AND product_id=?", [$imgId, $productId]);
if (!$img) { echo json_encode(['success' => false, 'message' => 'Image not found']); exit; }

// Delete physical file from disk
$file = UPLOAD_DIR . $img['image_path'];
if (file_exists($file)) @unlink($file);

dbExecute("DELETE FROM product_images WHERE id=?", [$imgId]);
echo json_encode(['success' => true]);
