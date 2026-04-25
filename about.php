<?php
$pageTitle = 'О компании';
require_once 'includes/header.php';
$company = company_info();
?>
<div class="about-section">

    <div class="about-card">
        <h2>О компании</h2>
        <p><?= e($company['description']) ?></p>
    </div>

    <div class="about-card">
        <h2>История</h2>
        <p><?= e($company['history']) ?></p>
    </div>

    <div class="about-card">
        <h2>Миссия</h2>
        <p><?= e($company['mission']) ?></p>
    </div>

    <div class="about-card">
        <h2>Контакты</h2>
        <p><strong>Адрес:</strong> <?= e($company['address']) ?></p>
        <p><strong>Телефон:</strong> <?= e($company['phone']) ?></p>
        <p><strong>Email:</strong> <?= e($company['email']) ?></p>
    </div>

</div>
</div>
</div>
<?php require_once 'includes/footer.php'; ?>