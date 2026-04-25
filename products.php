<?php
require_once '../includes/functions.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([(int) post('id')]);
        set_flash('success', 'Тур удалён.');
    } else {
        $id = (int) post('id', 0);
        $categoryId = (int) post('category_id');
        $name = post('name');
        $description = post('description');
        $price = (float) post('price');
        $stock = (int) post('stock');
        $imagePath = post('image_path', 'images/logotip.webp');
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image_path=? WHERE id=?');
            $stmt->execute([$categoryId, $name, $description, $price, $stock, $imagePath, $id]);
            set_flash('success', 'Тур обновлён.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$categoryId, $name, $description, $price, $stock, $imagePath]);
            set_flash('success', 'Тур добавлен.');
        }
        redirect('products.php');
    }
    redirect('products.php');
}
$editId = (int) get('edit', 0);
$editItem = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}
$pageTitle = 'Туры';
require_once '../includes/header.php';
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
?>
<h1 class="mb-4">Управление турами</h1>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h3><?= $editItem ? 'Редактировать тур' : 'Добавить тур' ?></h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editItem['id'] ?? 0) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Направление</label>
                <select name="category_id" class="form-select"><?php foreach (categories() as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= ((int) ($editItem['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
                <div class="col-md-8">
                    <label class="form-label">Название</label>
                    <input type="text" name="name" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" required><?= e($editItem['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Стоимость</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e($editItem['price'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Свободные места</label>
                    <input type="number" name="stock" class="form-control" value="<?= e($editItem['stock'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Путь к картинке</label>
                    <input type="text" name="image_path" class="form-control" value="<?= e($editItem['image_path'] ?? 'images/logotip.webp') ?>">
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
                <th>Название</th>
                <th>Направление</th>
                <th>Стоимость</th>
                <th>Свободные места</th>
                <th></th>
            </tr>
        </thead>
        <tbody><?php foreach ($products as $product): ?>
                <tr>
                    <td><?= (int) $product['id'] ?></td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category_name']) ?></td>
                    <td><?= number_format((float) $product['price'], 2, ',', ' ') ?> ₽</td>
                    <td><?= (int) $product['stock'] ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="product_edit.php?id=<?= (int)$product['id'] ?>" class="btn btn-sm btn-outline-primary">Редактировать</a>
                            <a href="product_delete.php?id=<?= (int)$product['id'] ?>" class="btn btn-sm btn-outline-danger">Удалить</a>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>