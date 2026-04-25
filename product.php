<?php
$pageTitle = 'Тур';
require_once 'includes/header.php';
$productId = (int) get('id', 0);
$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    echo '<div class="alert alert-danger">Тур не найден.</div>';
    require_once 'includes/footer.php';
    exit;
}
?>
<div class="row g-4 align-items-start">
    <div class="col-lg-5">
        <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>" class="product-detail-img shadow-sm">
    </div>
    <div class="col-lg-7">
        <div class="text-secondary mb-2">Направление: <?= e($product['category_name']) ?></div>
        <h1><?= e($product['name']) ?></h1>
        <p><?= nl2br(e($product['description'])) ?></p>
        <div class="mb-2">
            <strong>Стоимость:</strong>
            <?= number_format((float) $product['price'], 2, ',', ' ') ?> ₽
        </div>
        <div class="mb-4"><strong>Свободные места:</strong>
            <?= (int) $product['stock'] > 0 ? (int) $product['stock'] : 'мест нет' ?>
        </div>

        <?php if ((int) $product['stock'] > 0): ?>
            <form action="add_to_cart.php" method="post" class="row g-3 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="col-sm-3">
                    <label class="form-label">Количество человек</label>
                    <input type="number" name="quantity" min="1" max="<?= (int) $product['stock'] ?>" value="1" class="form-control">
                </div>
                <div class="col-sm-9 d-flex gap-2">
                    <button class="btn btn-primary">Забронировать</button>
                    <a href="toggle_favorite.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-secondary">В избранное</a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">На этот тур сейчас нет свободных мест.</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>