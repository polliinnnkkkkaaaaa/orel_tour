<?php
$pageTitle = 'Регистрация';
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post('name');
    $login = post('login');
    $email = post('email');
    $password = post('password');
    $passwordConfirm = post('password_confirm');
    $errors = [];
    if ($name === '' || $login === '' || $email === '' || $password === '' || $passwordConfirm === '')
        $errors[] = 'Заполните все поля формы.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Введите корректный email.';
    if (login_exists($login))
        $errors[] = 'Такой логин уже занят.';
    if (email_exists($email))
        $errors[] = 'Такой email уже зарегистрирован.';
    if (strlen($password) < 6)
        $errors[] = 'Пароль должен содержать минимум 6 символов.';
    if ($password !== $passwordConfirm)
        $errors[] = 'Пароли не совпадают.';
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (login, password_hash, email, name, role) VALUES (?, ?, ?, ?, "user")');
        $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT), $email, $name]);
        set_flash('success', 'Регистрация прошла успешно. Теперь войдите на сайт.');
        redirect('login.php');
    }
    set_flash('danger', implode(' ', $errors));
    redirect('register.php');
}
require_once 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="mb-3">Регистрация</h1>
                <form method="post" data-validate="register">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" name="login" class="form-control" required>
                        <div id="login-check-result" class="form-help mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подтверждение пароля</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>