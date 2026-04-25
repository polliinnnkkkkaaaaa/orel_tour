<?php
require_once 'includes/functions.php';

verify_csrf();

$productId = (int) post('product_id', 0);
$quantity = max(1, (int) post('quantity', 1));
$redirectTo = post('redirect_to', 'cart.php');

$allowedRedirects = [
    'cart.php',
    'catalog.php',
    'favorites.php',
    'product.php'
];

if (!in_array($redirectTo, $allowedRedirects, true)) {
    $redirectTo = 'cart.php';
}

$stmt = db()->prepare('SELECT id, stock, name FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Тур не найден.');
    redirect('catalog.php');
}

if ($quantity > (int) $product['stock']) {
    set_flash('error', 'Недостаточно свободных мест по выбранному туру.');
    redirect('product.php?id=' . $productId);
}

add_to_cart_session($productId, $quantity);

set_flash('success', 'Тур добавлен в корзину.');

if ($redirectTo === 'product.php') {
    redirect('product.php?id=' . $productId);
}

redirect($redirectTo);