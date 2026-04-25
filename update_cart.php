<?php
require_once 'includes/functions.php';
verify_csrf();
$quantities = $_POST['quantities'] ?? [];
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
foreach ($quantities as $productId => $quantity) {
    $productId = (int) $productId;
    $quantity = (int) $quantity;
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}
set_flash('success', 'Корзина обновлена.');
redirect('cart.php');
