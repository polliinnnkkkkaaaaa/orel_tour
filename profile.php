<?php
require_once 'includes/functions.php';
require_auth();
$pdo = db();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('form_type') === 'profile') {
    verify_csrf();
    $name = post('name');
    $email = post('email');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Введите имя и корректный email.');
        redirect('profile.php');
    }
    if (email_exists($email, $user['id'])) {
        set_flash('danger', 'Этот email уже используется другим пользователем.');
        redirect('profile.php');
    }
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute([$name, $email, $user['id']]);
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    set_flash('success', 'Профиль обновлён.');
    redirect('profile.php');
}
if (get('cancel_order')) {
    $orderId = (int) get('cancel_order');
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();
    if ($order && $order['status'] === 'new') {
        $pdo->beginTransaction();
        $itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();
        foreach ($items as $item) {
            $restore = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            $restore->execute([$item['quantity'], $item['product_id']]);
        }
        $update = $pdo->prepare('UPDATE orders SET status = "cancelled" WHERE id = ?');
        $update->execute([$orderId]);
        $pdo->commit();
        set_flash('success', 'Бронирование отменено.');
    }
    redirect('profile.php');
}
$pageTitle = 'Личный кабинет';
require_once 'includes/header.php';
$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orderStmt->execute([$user['id']]);
$orders = $orderStmt->fetchAll();
?>
<h1 class="mb-4">Личный кабинет</h1>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3>Мои данные</h3>
                <form method="post" data-validate="profile">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="form_type" value="profile">
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-control" value="<?= e($user['login']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                    </div>
                    <button class="btn btn-primary">Сохранить изменения</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3>История бронирований</h3>
                <?php if (!$orders): ?>
                    <div class="alert alert-info">У вас пока нет бронирований.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Дата</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= (int) $order['id'] ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format((float) $order['total_amount'], 2, ',', ' ') ?> ₽</td>
                                        <td><span
                                                class="badge text-bg-secondary badge-status"><?= e(order_status_label($order['status'])) ?></span>
                                        </td>
                                        <td><?php if ($order['status'] === 'new'): ?><a class="btn btn-outline-danger btn-sm"
                                                    href="profile.php?cancel_order=<?= (int) $order['id'] ?>"
                                                    onclick="return confirm('Отменить бронирование?')">Отменить</a><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>