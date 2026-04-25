<?php
require_once '../includes/functions.php';
require_admin();

$pdo = db();
$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Новость не найдена.');
    redirect('news.php');
}

$stmt = $pdo->prepare('SELECT * FROM news WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    set_flash('error', 'Новость не найдена.');
    redirect('news.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = post('title');
    $content = post('content');
    $publishedAt = post('published_at');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '') {
        $errors[] = 'Введите заголовок новости.';
    }

    if ($content === '') {
        $errors[] = 'Введите текст новости.';
    }

    if ($publishedAt === '') {
        $publishedAt = date('Y-m-d H:i:s');
    } else {
        $publishedAt = str_replace('T', ' ', $publishedAt) . ':00';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('
            UPDATE news
            SET title = :title,
                content = :content,
                published_at = :published_at,
                is_active = :is_active
            WHERE id = :id
        ');

        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'published_at' => $publishedAt,
            'is_active' => $isActive,
            'id' => $id
        ]);

        set_flash('success', 'Новость успешно изменена.');
        redirect('news.php');
    }
}

$pageTitle = 'Редактирование новости';
require_once '../includes/header.php';
?>

<h1>Редактирование новости</h1>

<?php foreach ($errors as $error): ?>
    <div class="message error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="form-box">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Заголовок</label>
        <input type="text" name="title" value="<?= e($news['title']) ?>" required>
        <label>Текст новости</label>
        <textarea name="content" rows="8" required><?= e($news['content']) ?></textarea>
        <label>Дата публикации</label>
        <input type="datetime-local" name="published_at" value="<?= e(date('Y-m-d\TH:i', strtotime($news['published_at']))) ?>">
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" <?= (int) $news['is_active'] === 1 ? 'checked' : '' ?>> Опубликовать новость
        </label>
        <div class="admin-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="news.php" class="btn btn-outline-secondary">Назад</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>