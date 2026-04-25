</main>
<footer class="site-footer">
    <div class="container">
        <p>© <?= date('Y') ?> <?= e($company['company_name']) ?>. Адрес: <?= e($company['address']) ?>. Телефон: <a href="tel:<?= e($company['phone']) ?>"><?= e($company['phone']) ?></a>. Email: <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></p>
    </div>
</footer>
<script src="<?= $base ?>script.js"></script>
</body>

</html>