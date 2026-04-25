<?php
require_once '../includes/functions.php';
require_admin();

$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Направление не найдено.');
    redirect('categories.php');
}

$stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
$stmt->execute([$id]);
$countProducts = (int) $stmt->fetchColumn();

if ($countProducts > 0) {
    set_flash('error', 'Нельзя удалить направление, потому что к нему привязаны туры.');
    redirect('categories.php');
}

$stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', 'Направление удалено.');
redirect('categories.php');