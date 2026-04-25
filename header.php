<?php
require_once __DIR__ . '/functions.php';
$company = company_info();
$flash = get_flash();
$user = current_user();
$isAdminPage = str_contains($_SERVER['PHP_SELF'], '/admin/');
$base = $isAdminPage ? '../' : '';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . e($company['company_name']) : e($company['company_name']) ?></title>
    <meta name="description" content="Сайт ООО «ОрёлТур». Подбор туров, бронирование поездок, акции и новости.">
    <link rel="stylesheet" href="<?= $base ?>style.css">
</head>

<body>
    <header class="site-header">
        <div class="container nav">
            <a class="logo logo-link" href="<?= $base ?>index.php">
                <img src="images/logotip.webp" class="site-logo" alt="ОрёлТур">
                <span><?= e($company['company_name']) ?></span>
            </a>
            <nav>
                <a href="<?= $base ?>index.php">Главная</a>
                <a href="<?= $base ?>catalog.php">Туры и услуги</a>
                <a href="<?= $base ?>about.php">О компании</a>
                <a href="<?= $base ?>news.php">Новости</a>
                <a href="<?= $base ?>search.php">Поиск</a>
                <?php if ($user): ?>
                    <a href="<?= $base ?>favorites.php">Избранное</a>
                    <a href="<?= $base ?>cart.php">Корзина (<?= cart_items_count() ?>)</a>
                    <a href="<?= $base ?>profile.php">Личный кабинет</a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?= $isAdminPage ? 'index.php' : 'admin/index.php' ?>">Админка</a>
                    <?php endif; ?>
                    <span class="user-name">Вы вошли как: <b><?= e($user['name']) ?></b></span>
                    <a class="btn-link" href="<?= $base ?>logout.php">Выйти</a>
                <?php else: ?>
                    <a href="<?= $base ?>login.php">Войти</a>
                    <a href="<?= $base ?>register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if ($flash): ?>
            <div class="message <?= e($flash['type']) === 'success' ? 'success' : 'error' ?>"><?= e($flash['message']) ?>
            </div>
        <?php endif; ?>