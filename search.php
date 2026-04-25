<?php
$pageTitle = 'Поиск';
require_once 'includes/header.php';
$query = get('q');
$results = [];
if ($query !== '') {
    $stmt = db()->prepare('SELECT * FROM products WHERE name LIKE :name_q OR description LIKE :desc_q ORDER BY created_at DESC');
    $stmt->execute(['name_q' => '%' . $query . '%', 'desc_q' => '%' . $query . '%']);
    $results = $stmt->fetchAll();
}
?>
<h1 class="mb-4">Поиск туров</h1>
<form method="get" class="card card-body shadow-sm mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-9">
            <label class="form-label">Введите название или описание</label>
            <input type="text" name="q" class="form-control" value="<?= e($query) ?>">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Найти</button>
        </div>
    </div>
</form>
<div class="row g-4">
    <?php foreach ($results as $product): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">
                <img src="<?= e($product['image_path']) ?>" class="card-img-top product-image" alt="">
                <div class="card-body">
                    <h5><?= e($product['name']) ?></h5>
                    <p><?= e(mb_strimwidth($product['description'], 0, 100, '...')) ?></p>
                    <a href="product.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($query !== '' && !$results): ?>
        <div class="col-12">
            <div class="alert alert-warning">Ничего не найдено.</div>
        </div><?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>