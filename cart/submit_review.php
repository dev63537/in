<?php
// cart/submit_review.php — Handle review submission
require_once __DIR__ . '/../includes/functions.php';
startSession();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$name      = trim($_POST['reviewer_name'] ?? '');
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = trim($_POST['comment'] ?? '');

if (!$productId || !$name || $rating < 1 || $rating > 5 || !$comment) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields and select a rating.']);
    exit;
}

// Check product exists
$product = dbFetchOne("SELECT id FROM products WHERE id=? AND status='active'", [$productId]);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

// Rate-limit: 1 review per session per product
$sessionKey = 'reviewed_' . $productId;
if (!empty($_SESSION[$sessionKey])) {
    echo json_encode(['success' => false, 'message' => 'You have already submitted a review for this product.']);
    exit;
}

try {
    dbExecute(
        "INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment, status) VALUES (?,?,?,?,?,'pending')",
        [$productId, $_SESSION['user_id'] ?? null, $name, $rating, $comment]
    );
    $_SESSION[$sessionKey] = true;
    echo json_encode(['success' => true, 'message' => 'Thank you! Your review has been submitted and is pending approval.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Could not save review. Please try again.']);
}
