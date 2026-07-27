<?php
// admin/actions/reorder_gallery.php — AJAX endpoint
require_once __DIR__ . '/../../includes/functions.php';
startSession();
header('Content-Type: application/json');

if (!isAdmin() || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$orderStr = trim($_POST['order'] ?? '');

if (!$productId || !$orderStr) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']); exit;
}

$ids = array_filter(array_map('intval', explode(',', $orderStr)));

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No images specified']); exit;
}

// Check if all IDs belong to the product
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = array_merge([$productId], $ids);
$dbImages = dbFetchAll("SELECT id FROM product_images WHERE product_id=? AND id IN ($placeholders)", $params);
$validIds = array_column($dbImages, 'id');

// Update sort_order for each valid image ID
$sort = 1;
foreach ($ids as $id) {
    if (in_array($id, $validIds)) {
        dbExecute("UPDATE product_images SET sort_order=? WHERE id=? AND product_id=?", [$sort, $id, $productId]);
        $sort++;
    }
}

echo json_encode(['success' => true]);
