<?php
require_once '../includes/functions.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = ?');
        $stmt->execute([(int) post('id')]);
        set_flash('success', 'Новость удалена.');
    } else {
        $id = (int) post('id', 0);
        $title = post('title');
        $content = post('content');
        $publishedAt = post('published_at');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE news SET title=?, content=?, published_at=?, is_active=? WHERE id=?');
            $stmt->execute([$title, $content, $publishedAt, $isActive, $id]);
            set_flash('success', 'Новость обновлена.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO news (title, content, published_at, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $content, $publishedAt, $isActive]);
            set_flash('success', 'Новость добавлена.');
        }
    }
    redirect('news.php');
}
$editId = (int) get('edit', 0);
$editItem = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}
$pageTitle = 'Новости';
require_once '../includes/header.php';
$items = $pdo->query('SELECT * FROM news ORDER BY published_at DESC')->fetchAll();
?>
<h1 class="mb-4">Управление новостями</h1>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editItem['id'] ?? 0) ?>">
            <div class="mb-3">
                <label class="form-label">Заголовок</label>
                <input type="text" name="title" class="form-control" value="<?= e($editItem['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Текст</label>
                <textarea name="content" class="form-control" required><?= e($editItem['content'] ?? '') ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Дата публикации</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="<?= isset($editItem['published_at']) ? date('Y-m-d\TH:i', strtotime($editItem['published_at'])) : date('Y-m-d\TH:i') ?>">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !isset($editItem['is_active']) || (int) $editItem['is_active'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Показывать на сайте</label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary mt-3"><?= $editItem ? 'Сохранить' : 'Добавить' ?></button>
        </form>
    </div>
</div>
<div class="table-responsive">
    <table class="table bg-white shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Дата</th>
                <th>Активна</th>
                <th></th>
            </tr>
        </thead>
        <tbody><?php foreach ($items as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= e($item['title']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($item['published_at'])) ?></td>
                    <td><?= (int) $item['is_active'] ? 'Да' : 'Нет' ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="news_edit.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-primary">Редактировать</a>
                            <a href="news_delete.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-outline-danger">Удалить</a>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>