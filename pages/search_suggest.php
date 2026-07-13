<?php
// pages/search_suggest.php — AJAX search autocomplete endpoint
require_once __DIR__ . '/../includes/functions.php';
startSession();

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = dbFetchAll(
    "SELECT p.id, p.name, p.price, p.sale_price, p.image,
            c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.status = 'active'
       AND (p.name LIKE ? OR p.tags LIKE ?)
     ORDER BY p.name ASC
     LIMIT 6",
    ["%$q%", "%$q%"]
);

$out = [];
foreach ($results as $r) {
    $out[] = [
        'id'            => $r['id'],
        'name'          => $r['name'],
        'price'         => $r['sale_price'] > 0 ? $r['sale_price'] : $r['price'],
        'image'         => $r['image'],
        'category_name' => $r['category_name'] ?? '',
    ];
}

echo json_encode($out);
