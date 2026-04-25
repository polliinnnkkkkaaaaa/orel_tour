<?php
require_once '../includes/functions.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) post('id');
    $status = post('status');
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    $order = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $order->execute([$orderId]);
    $orderData = $order->fetch();
    $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $userStmt->execute([$orderData['user_id']]);
    $user = $userStmt->fetch();
    if ($orderData && $user)
        send_status_email_if_possible($orderData, $user);
    set_flash('success', 'Статус бронирования обновлён.');
    redirect('orders.php');
}
$pageTitle = 'Заказы';
require_once '../includes/header.php';
$search = get('search');
if ($search !== '') {
    $stmt = $pdo->prepare('SELECT o.*, u.name FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ? OR u.name LIKE ? ORDER BY o.created_at DESC');
    $stmt->execute([(int) $search, '%' . $search . '%']);
    $orders = $stmt->fetchAll();
} else {
    $orders = $pdo->query('
    SELECT 
    o.*, 
    u.name,
    GROUP_CONCAT(p.name SEPARATOR ", ") AS products
    FROM orders o
    JOIN users u ON u.id = o.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON p.id = oi.product_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
')->fetchAll();
}
?>
<h1 class="mb-4">Управление бронированиями</h1>
<form method="get" class="card card-body shadow-sm mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-9">
            <label class="form-label">Поиск по номеру бронирования или имени клиента</label>
            <input type="text" name="search" class="form-control" value="<?= e($search) ?>">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Найти</button>
        </div>
    </div>
</form>
<div class="table-responsive">
    <table class="table bg-white shadow-sm">
        <thead>
            <tr>
                <th>№</th>
                <th>Пользователь</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Телефон</th>
                <th>Адрес проживания</th>
                <th>Туры</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody><?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= (int) $order['id'] ?></td>
                    <td><?= e($order['name']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    <td><?= number_format((float) $order['total_amount'], 2, ',', ' ') ?> ₽</td>
                    <td><?= e($order['contact_phone']) ?></td>
                    <td><?= e($order['delivery_address']) ?></td>
                    <td><?= e($order['products'] ?? '—') ?></td>
                    <td>
                        <form method="post" class="d-flex gap-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $order['id'] ?>"><select name="status" class="form-select form-select-sm">
                                <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Завершён</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                            </select>
                            <button class="btn btn-outline-primary btn-sm">Сохранить</button>
                        </form>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>