<?php
require_once 'includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
$login = get('login');
if ($login === '') {
    echo json_encode(['available' => false, 'message' => 'Введите логин']);
    exit;
}
$available = !login_exists($login);
echo json_encode([
    'available' => $available,
    'message' => $available ? 'Логин свободен.' : 'Этот логин уже используется.'
], JSON_UNESCAPED_UNICODE);
