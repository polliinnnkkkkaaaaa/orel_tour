<?php
require_once '../includes/functions.php';
require_admin();

$pdo = db();
$id = (int) get('id');

if ($id <= 0) {
    set_flash('error', 'Тур не найден.');
    redirect('products.php');
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Тур не найден.');
    redirect('products.php');
}

$categories = categories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $categoryId = (int) post('category_id');
    $name = post('name');
    $description = post('description');
    $price = (float) post('price');
    $stock = (int) post('stock');
    $imagePath = post('image_path');

    if ($name === '') {
        $errors[] = 'Введите название тура.';
    }
    if ($description === '') {
        $errors[] = 'Введите описание тура.';
    }
    if ($price <= 0) {
        $errors[] = 'Введите корректную цену.';
    }
    if ($stock < 0) {
        $errors[] = 'Количество мест не может быть отрицательным.';
    }
    if ($imagePath === '') {
        $imagePath = $product['image_path'];
    }
    if (!$errors) {
        $stmt = $pdo->prepare('
            UPDATE products
            SET category_id = :category_id,
                name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                image_path = :image_path
            WHERE id = :id
        ');

        $stmt->execute([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'id' => $id
        ]);

        set_flash('success', 'Тур успешно изменён.');
        redirect('products.php');
    }
}

$pageTitle = 'Редактирование тура';
require_once '../includes/header.php';
?>

<h1>Редактирование тура</h1>

<?php foreach ($errors as $error): ?>
    <div class="message error">
        <?= e($error) ?>
    </div>
<?php endforeach; ?>

<div class="form-box">
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Направление</label>
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Название тура</label>
        <input type="text" name="name" value="<?= e($product['name']) ?>" required>
        <label>Описание</label>
        <textarea name="description" rows="7" required><?= e($product['description']) ?></textarea>
        <label>Цена</label>
        <input type="number" name="price" min="0" step="0.01" value="<?= e($product['price']) ?>" required>
        <label>Количество мест</label>
        <input type="number" name="stock" min="0" value="<?= (int) $product['stock'] ?>" required>
        <label>Путь к изображению</label>
        <input type="text" name="image_path" value="<?= e($product['image_path']) ?>" placeholder="images/example.jpg">
        <div class="admin-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="products.php" class="btn btn-outline-secondary">Назад</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>