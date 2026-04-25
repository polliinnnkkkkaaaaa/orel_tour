<?php
$pageTitle = 'Каталог туров';
require_once 'includes/header.php';
$categoryId = (int) get('category_id', 0);
$sort = get('sort', 'name_asc');
$minPrice = (float) get('min_price', 0);
$maxPrice = (float) get('max_price', 0);
$page = max(1, (int) get('page', 1));
$perPage = 6;

$where = ['1=1'];
$params = [];
if ($categoryId > 0) {
    $where[] = 'p.category_id = :category_id';
    $params['category_id'] = $categoryId;
}
if ($minPrice > 0) {
    $where[] = 'p.price >= :min_price';
    $params['min_price'] = $minPrice;
}
if ($maxPrice > 0) {
    $where[] = 'p.price <= :max_price';
    $params['max_price'] = $maxPrice;
}
$orderSql = 'p.name ASC';
if ($sort === 'price_asc')
    $orderSql = 'p.price ASC';
if ($sort === 'price_desc')
    $orderSql = 'p.price DESC';
if ($sort === 'name_desc')
    $orderSql = 'p.name DESC';
$whereSql = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$page, $pages, $offset] = paginate($total, $perPage, $page);

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $whereSql ORDER BY $orderSql LIMIT :limit OFFSET :offset";
$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
?>
<div class="row g-4">
    <div class="col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4>Фильтры</h4>
                <form method="get">
                    <div class="mb-3">
                        <label class="form-label">Направление</label>
                        <select name="category_id" class="form-select">
                            <option value="0">Все направления</option>
                            <?php foreach (categories() as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Сортировка</label>
                        <select name="sort" class="form-select">
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Название А-Я</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Название Я-А</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Стоимость по возрастанию</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Стоимость по убыванию</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Стоимость от</label>
                        <input type="number" name="min_price" class="form-control" value="<?= e($minPrice ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Стоимость до</label>
                        <input type="number" name="max_price" class="form-control" value="<?= e($maxPrice ?: '') ?>">
                    </div>
                    <button class="btn btn-primary w-100">Применить</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <h1 class="mb-3">Каталог туров</h1>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= e($product['image_path']) ?>" class="card-img-top product-image" alt="<?= e($product['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <div class="text-secondary small mb-2"><?= e($product['category_name']) ?></div>
                            <h5><?= e($product['name']) ?></h5>
                            <p class="flex-grow-1"><?= e(mb_strimwidth($product['description'], 0, 100, '...')) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><?= number_format((float) $product['price'], 2, ',', ' ') ?> ₽</span>
                                <a href="product.php?id=<?= (int) $product['id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$products): ?>
                <div class="col-12">
                    <div class="alert alert-warning">По выбранным параметрам туры не найдены.</div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>