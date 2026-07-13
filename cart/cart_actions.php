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

} elseif ($action === 'wishlist_add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid) {
        addToWishlist($pid);
        echo json_encode(['success'=>true, 'in_wishlist'=>true, 'wishlist_count'=>count(getWishlist())]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Invalid product.']);
    }

} elseif ($action === 'wishlist_remove') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid) {
        removeFromWishlist($pid);
        echo json_encode(['success'=>true, 'in_wishlist'=>false, 'wishlist_count'=>count(getWishlist())]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Invalid product.']);
    }

} elseif ($action === 'get_cart') {
    // Returns cart items for the drawer UI
    startSession();
    $cart  = $_SESSION['cart'] ?? [];
    $items = [];
    $total = 0;
    foreach ($cart as $key => $item) {
        $items[] = [
            'key'   => $key,
            'pid'   => $item['product_id'],
            'name'  => $item['name'],
            'image' => $item['image'],
            'price' => $item['price'],
            'size'  => $item['size'],
            'color' => $item['color'],
            'qty'   => $item['quantity'],
        ];
        $total += $item['price'] * $item['quantity'];
    }
    echo json_encode([
        'success' => true,
        'items'   => $items,
        'count'   => getCartCount(),
        'total'   => number_format($total, 2),
        'total_raw' => $total,
    ]);

} elseif ($action === 'get_wishlist') {
    // Returns wishlist product cards for the drawer UI
    $ids = getWishlist();
    $products = [];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = dbFetchAll(
            "SELECT id, name, price, sale_price, image FROM products WHERE id IN ($placeholders) AND status='active'",
            $ids
        );
        foreach ($rows as $r) {
            $products[] = [
                'id'         => $r['id'],
                'name'       => $r['name'],
                'image'      => $r['image'],
                'price'      => $r['sale_price'] > 0 ? $r['sale_price'] : $r['price'],
                'orig_price' => $r['price'],
                'on_sale'    => $r['sale_price'] > 0,
            ];
        }
    }
    echo json_encode([
        'success'  => true,
        'products' => $products,
        'count'    => count($products),
    ]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
