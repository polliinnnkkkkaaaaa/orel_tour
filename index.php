<?php
require_once '../includes/functions.php';
require_admin();
$pageTitle = 'Админ-панель';
require_once '../includes/header.php';
$newOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
$productsCount = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$usersCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$newsCount = (int) db()->query('SELECT COUNT(*) FROM news')->fetchColumn();
?>
<h1 class="mb-4">Админ-панель</h1>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-secondary">Новые бронирования</div>
                <div class="display-6"><?= $newOrders ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-secondary">Туры</div>
                <div class="display-6"><?= $productsCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-secondary">Пользователи</div>
                <div class="display-6"><?= $usersCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-secondary">Новости</div>
                <div class="display-6"><?= $newsCount ?></div>
            </div>
        </div>
    </div>
</div>
<div class="admin-sidebar">
    <a href="products.php">Управление турами</a>
    <a href="categories.php">Управление направлениями</a>
    <a href="users.php">Управление пользователями</a>
    <a href="orders.php">Управление бронированиями</a>
    <a href="news.php">Управление новостями</a>
</div>
<?php require_once '../includes/footer.php'; ?>