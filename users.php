<?php
require_once '../includes/functions.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) post('id');
    if (post('action') === 'toggle_block') {
        $stmt = $pdo->prepare('UPDATE users SET is_blocked = IF(is_blocked = 1, 0, 1) WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Статус блокировки изменён.');
    }
    if (post('action') === 'change_role') {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([post('role') === 'admin' ? 'admin' : 'user', $id]);
        set_flash('success', 'Роль пользователя изменена.');
    }
    redirect('users.php');
}
$pageTitle = 'Пользователи';
require_once '../includes/header.php';
$users = $pdo->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
?>
<h1 class="mb-4">Управление пользователями</h1>
<div class="table-responsive">
    <table class="table bg-white shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody><?php foreach ($users as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['login']) ?></td>
                    <td><?= e($item['email']) ?></td>
                    <td><?= e($item['role']) ?></td>
                    <td><?= (int) $item['is_blocked'] ? 'Заблокирован' : 'Активен' ?></td>
                    <td>
                        <form method="post" class="admin-actions"><input type="hidden" name="csrf_token"
                                value="<?= e(csrf_token()) ?>"><input type="hidden" name="id"
                                value="<?= (int) $item['id'] ?>"><input type="hidden" name="action"
                                value="toggle_block">
                                <button class="btn btn-sm btn-outline-danger"><?= (int) $item['is_blocked'] ? 'Разблокировать' : 'Блокировать' ?></button>
                        </form>
                        <form method="post" class="admin-actions"><input type="hidden" name="csrf_token"
                                value="<?= e(csrf_token()) ?>"><input type="hidden" name="id"
                                value="<?= (int) $item['id'] ?>"><input type="hidden" name="action"
                                value="change_role"><input type="hidden" name="role"
                                value="<?= $item['role'] === 'admin' ? 'user' : 'admin' ?>">
                                <button class="btn btn-sm btn-outline-primary"><?= $item['role'] === 'admin' ? 'Сделать пользователем' : 'Сделать администратором' ?></button>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>