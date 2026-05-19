<?php
// cart/cart_actions.php — AJAX Cart Handler
require_once __DIR__ . '/../includes/functions.php';
startSession();
header('Content-Type: application/json');

$action = $_POST['action'] ?? 'add';

if ($action === 'add') {
    $pid   = (int)($_POST['product_id'] ?? 0);
    $size  = trim($_POST['size'] ?? 'M');
    $color = trim($_POST['color'] ?? '');
    $qty   = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$pid) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }
    $ok = addToCart($pid, $size, $color, $qty);
    echo json_encode(['success'=>$ok, 'cart_count'=>getCartCount(), 'message'=> $ok ? 'Added!' : 'Could not add item.']);

} elseif ($action === 'remove') {
    $key = $_POST['key'] ?? '';
    $ok  = removeFromCart($key);
    echo json_encode(['success'=>$ok, 'cart_count'=>getCartCount()]);

} elseif ($action === 'update') {
    $key = $_POST['key'] ?? '';
    $qty = (int)($_POST['qty'] ?? 0);
    $ok  = updateCartQuantity($key, $qty);
    echo json_encode(['success'=>$ok, 'cart_count'=>getCartCount()]);

} elseif ($action === 'count') {
    echo json_encode(['success'=>true, 'cart_count'=>getCartCount()]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
