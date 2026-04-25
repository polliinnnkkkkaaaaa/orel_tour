<?php
require_once '../includes/functions.php';
require_admin();

$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Тур не найден.');
    redirect('products.php');
}

$stmt = db()->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Тур не найден.');
    redirect('products.php');
}

$stmt = db()->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', 'Тур удалён.');
redirect('products.php');