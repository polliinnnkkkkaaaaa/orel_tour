<?php
require_once '../includes/functions.php';
require_admin();

$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Новость не найдена.');
    redirect('news.php');
}

$stmt = db()->prepare('DELETE FROM news WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', 'Новость удалена.');
redirect('news.php');