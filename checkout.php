<?php
require_once 'includes/functions.php';
require_auth();
$items = cart_detailed_items();
if (!$items) {
    set_flash('warning', 'Сначала добавьте туры в корзину.');
    redirect('cart.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $address = post('delivery_address');
    $phone = post('contact_phone');
    if ($address === '' || $phone === '') {
        set_flash('danger', 'Заполните адрес проживания и телефон.');
        redirect('checkout.php');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($items as $item) {
            if ((int) $item['product']['stock'] < (int) $item['quantity']) {
                throw new Exception('Недостаточно свободных мест по туру: ' . $item['product']['name']);
            }
        }
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, status, total_amount, delivery_address, contact_phone) VALUES (?, "new", ?, ?, ?)');
        $stmt->execute([current_user()['id'], cart_total(), $address, $phone]);
        $orderId = (int) $pdo->lastInsertId();
        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_moment) VALUES (?, ?, ?, ?)');
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['product']['id'], $item['quantity'], $item['product']['price']]);
            $stockStmt->execute([$item['quantity'], $item['product']['id']]);
        }
        $pdo->commit();
        $_SESSION['cart'] = [];
        set_flash('success', 'Бронирование успешно оформлено. Номер заявки: ' . $orderId);
        redirect('profile.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('danger', $e->getMessage());
        redirect('checkout.php');
    }
}
$pageTitle = 'Оформление бронирования';
require_once 'includes/header.php';
?>
<h1 class="mb-4">Оформление бронирования</h1>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" data-validate="checkout">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3"><label class="form-label">Адрес проживания</label><input type="text" name="delivery_address" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Контактный телефон</label><input type="text" name="contact_phone" class="form-control" required></div>
                    <button class="btn btn-primary">Подтвердить бронирование</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4>Состав бронирования</h4>
                <?php foreach ($items as $item): ?>
                    <div class="d-flex justify-content-between border-bottom py-2"><span><?= e($item['product']['name']) ?>×<?= (int) $item['quantity'] ?></span><span><?= number_format((float) $item['sum'], 2, ',', ' ') ?> ₽</span></div>
                <?php endforeach; ?>
                <div class="mt-3 fs-5 fw-bold">Итого: <?= number_format(cart_total(), 2, ',', ' ') ?> ₽</div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>