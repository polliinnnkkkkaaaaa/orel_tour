<?php
$pageTitle = 'Избранное';
require_once 'includes/header.php';
$favorites = favorites_list();
$products = products_by_ids($favorites);
?>

<h1 class="mb-4">Избранные туры</h1>

<?php if (!$favorites): ?>
    <div class="alert alert-info">Список избранных туров пуст.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($favorites as $id): ?>
            <?php
            if (!isset($products[$id])) {
                continue;
            }
            $product = $products[$id];
            ?>

            <div class="col-md-6 col-xl-4">
                <div class="card favorite-card">
                    <img src="<?= e($product['image_path']) ?>" class="product-image" alt="<?= e($product['name']) ?>">
                    <div class="card-body">
                        <h3><?= e($product['name']) ?></h3>
                        <p><strong>Цена:</strong><?= number_format((float) $product['price'], 2, ',', ' ') ?> ₽</p>
                        <p><strong>Свободных мест:</strong><?= (int) $product['stock'] ?></p>

                        <div class="favorite-actions">
                            <a href="product.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>

                            <?php if ((int) $product['stock'] > 0): ?>
                                <form action="add_to_cart.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect_to" value="favorites.php">

                                    <button type="submit" class="btn btn-primary btn-sm">В корзину</button>
                                </form>
                            <?php else: ?>
                                <span class="btn btn-outline-secondary btn-sm">Нет мест</span>
                            <?php endif; ?>

                            <a href="toggle_favorite.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-danger btn-sm">Удалить</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>