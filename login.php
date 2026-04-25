<?php
$pageTitle = 'Вход';
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $login = post('login');
    $password = post('password');
    if ($login === '' || $password === '') {
        set_flash('danger', 'Введите логин или email и пароль.');
    } else {
        $user = find_user_by_login_or_email($login);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            set_flash('danger', 'Неверный логин/email или пароль.');
        } elseif ((int) $user['is_blocked'] === 1) {
            set_flash('danger', 'Ваш аккаунт заблокирован администратором.');
        } else {
            $_SESSION['user'] = $user;
            set_flash('success', 'Вы успешно вошли в систему.');
            redirect('index.php');
        }
    }
    redirect('login.php');
}
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="mb-3">Вход</h1>
                <form method="post" data-validate="login">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Логин или email</label>
                        <input type="text" name="login" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Войти</button>
                </form>
                <div class="mt-3">
                    <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>