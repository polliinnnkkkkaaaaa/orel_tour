<?php
$pageTitle = 'Новость';
require_once 'includes/header.php';
$id = (int)get('id', 0);
$stmt = db()->prepare('SELECT * FROM news WHERE id = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    echo '<div class="alert alert-danger">Новость не найдена.</div>';
    require_once 'includes/footer.php';
    exit;
}
?>
<article class="card shadow-sm">
    <div class="card-body">
        <div class="small text-secondary mb-2"><?= date('d.m.Y H:i', strtotime($item['published_at'])) ?></div>
        <h1><?= e($item['title']) ?></h1>
        <p><?= nl2br(e($item['content'])) ?></p>
        <a href="news.php" class="btn btn-outline-primary">Назад к новостям</a>
    </div>
</article>
<?php require_once 'includes/footer.php'; ?>
