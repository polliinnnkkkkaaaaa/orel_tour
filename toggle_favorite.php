<?php
require_once 'includes/functions.php';
$id = (int) get('id', 0);
$stmt = db()->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    set_flash('danger', 'Тур не найден.');
    redirect('catalog.php');
}
$added = toggle_favorite($id);
set_flash('success', $added ? 'Тур добавлен в избранное.' : 'Тур удалён из избранного.');
$back = $_SERVER['HTTP_REFERER'] ?? 'catalog.php';
redirect($back);
