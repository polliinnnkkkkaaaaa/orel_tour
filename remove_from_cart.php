<?php
require_once 'includes/functions.php';
$id = (int) get('id', 0);
unset($_SESSION['cart'][$id]);
set_flash('success', 'Тур удалён из корзины.');
redirect('cart.php');
