<?php
require_once '../includes/functions.php';
require_admin();

$pdo = db();
$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Направление не найдено.');
    redirect('categories.php');
}

$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('error', 'Направление не найдено.');
    redirect('categories.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = post('name');
    $description = post('description');

    if ($name === '') {
        $errors[] = 'Введите название направления.';
    }
    if ($description === '') {
        $errors[] = 'Введите описание направления.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('
            UPDATE categories
            SET name = :name,
                description = :description
            WHERE id = :id
        ');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'id' => $id
        ]);
        set_flash('success', 'Направление успешно изменено.');
        redirect('categories.php');
    }
}

$pageTitle = 'Редактирование направления';
require_once '../includes/header.php';
?>

<h1>Редактирование направления</h1>

<?php foreach ($errors as $error): ?>
    <div class="message error">
        <?= e($error) ?>
    </div>
<?php endforeach; ?>

<div class="form-box">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Название направления</label>
        <input type="text" name="name" value="<?= e($category['name']) ?>" required>
        <label>Описание</label>
        <textarea name="description" rows="6" required><?= e($category['description']) ?></textarea>
        <div class="admin-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="categories.php" class="btn btn-outline-secondary">Назад</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>