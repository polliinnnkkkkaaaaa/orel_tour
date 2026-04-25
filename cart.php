<?php
$pageTitle = 'Корзина';
require_once 'includes/header.php';

$items = cart_detailed_items();
?>

<h1 class="mb-4">Корзина бронирования</h1>

<?php if (!$items): ?>
    <div class="empty-cart">
        <h2>Корзина пока пуста</h2>
        <p>Выберите понравившийся тур в каталоге и добавьте его в корзину.</p>
        <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
    </div>
<?php else: ?>

    <form action="update_cart.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="cart-layout">
            <div class="cart-list">
                <?php foreach ($items as $item): ?>
                    <?php $product = $item['product']; ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                        </div>
                        <div class="cart-item-info">
                            <h3><?= e($product['name']) ?></h3>
                            <p class="cart-muted">Свободные места: <?= (int) $product['stock'] ?></p>
                            <p><strong>Стоимость:</strong><?= number_format((float) $product['price'], 2, ',', ' ') ?> ₽</p>
                            <div class="cart-quantity">
                                <label for="quantity_<?= (int) $product['id'] ?>">Количество:</label>
                                <input type="number" id="quantity_<?= (int) $product['id'] ?>" name="quantities[<?= (int) $product['id'] ?>]" min="0" max="<?= (int) $product['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                            </div>
                        </div>
                        <div class="cart-item-total">
                            <p>Сумма</p>
                            <strong><?= number_format((float) $item['sum'], 2, ',', ' ') ?> ₽</strong>
                            <a href="remove_from_cart.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-danger btn-sm">Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Итого</h2>
                <div class="summary-row">
                    <span>Количество позиций:</span>
                    <strong><?= array_sum(array_column($items, 'quantity')) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Общая сумма:</span>
                    <strong><?= number_format(cart_total(), 2, ',', ' ') ?> ₽</strong>
                </div>
                <div class="cart-summary-buttons">
                    <button type="submit" class="btn btn-outline-primary">Обновить корзину</button>
                    <a href="checkout.php" class="btn btn-primary">Оформить бронирование</a>
                    <a href="catalog.php" class="btn btn-outline-secondary">Продолжить выбор</a>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>