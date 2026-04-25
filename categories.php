<?php
require_once '../includes/functions.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int) post('id')]);
        set_flash('success', 'Направление удалена.');
    } else {
        $id = (int) post('id', 0);
        $name = post('name');
        $description = post('description');
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
            $stmt->execute([$name, $description, $id]);
            set_flash('success', 'Направление обновлена.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            set_flash('success', 'Направление добавлена.');
        }
    }
    redirect('categories.php');
}
$editId = (int) get('edit', 0);
$editItem = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}
$pageTitle = 'Направления';
require_once '../includes/header.php';
$items = $pdo->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();
?>
<h1 class="mb-4">Управление направлениями</h1>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden"
                name="id" value="<?= (int) ($editItem['id'] ?? 0) ?>">
            <div class="mb-3">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control"><?= e($editItem['description'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary"><?= $editItem ? 'Сохранить' : 'Добавить' ?></button>
        </form>
    </div>
</div>
<div class="table-responsive">
    <table class="table bg-white shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Описание</th>
                <th></th>
            </tr>
        </thead>
        <tbody><?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['description']) ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="category_edit.php?id=<?= (int) $category['id'] ?>" class="btn btn-sm btn-outline-primary">Редактировать</a>
                            <a href="category_delete.php?id=<?= (int) $category['id'] ?>" class="btn btn-sm btn-outline-danger">Удалить</a>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>