<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function get($key, $default = '')
{
    return isset($_GET[$key]) ? trim((string) $_GET[$key]) : $default;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_auth(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return is_auth() && current_user()['role'] === 'admin';
}

function require_auth(): void
{
    if (!is_auth()) {
        set_flash('danger', 'Сначала войдите в аккаунт.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        set_flash('danger', 'Эта страница доступна только администратору.');
        redirect('../login.php');
    }
}

function company_info(): array
{
    $stmt = db()->query('SELECT * FROM company_info ORDER BY id ASC LIMIT 1');
    $row = $stmt->fetch();
    return $row ?: [
        'company_name' => SITE_NAME,
        'description' => 'Описание компании пока не заполнено.',
        'address' => 'Адрес не указан',
        'phone' => '+7 (000) 000-00-00',
        'email' => 'shop@example.com',
        'logo_path' => 'images/logotip.webp',
        'history' => 'История компании пока не заполнена.',
        'mission' => 'Миссия компании пока не заполнена.',
        'values_text' => 'Ценности компании пока не заполнены.'
    ];
}

function categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function find_user_by_login_or_email(string $login): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE login = :login OR email = :email LIMIT 1');
    $stmt->execute(['login' => $login, 'email' => $login]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function login_exists(string $login, ?int $excludeId = null): bool
{
    $sql = 'SELECT id FROM users WHERE login = :login';
    $params = ['login' => $login];
    if ($excludeId !== null) {
        $sql .= ' AND id != :id';
        $params['id'] = $excludeId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetchColumn();
}

function email_exists(string $email, ?int $excludeId = null): bool
{
    $sql = 'SELECT id FROM users WHERE email = :email';
    $params = ['email' => $email];
    if ($excludeId !== null) {
        $sql .= ' AND id != :id';
        $params['id'] = $excludeId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetchColumn();
}

function cart_items_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function add_to_cart_session(int $productId, int $quantity): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += $quantity;
}

function favorites_list(): array
{
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    return $_SESSION['favorites'];
}

function toggle_favorite(int $productId): bool
{
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    if (in_array($productId, $_SESSION['favorites'], true)) {
        $_SESSION['favorites'] = array_values(array_filter($_SESSION['favorites'], fn($id) => (int) $id !== $productId));
        return false;
    }
    $_SESSION['favorites'][] = $productId;
    return true;
}

function products_by_ids(array $ids): array
{
    if (!$ids) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id IN ($placeholders)");
    $stmt->execute(array_values($ids));
    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[$row['id']] = $row;
    }
    return $rows;
}

function cart_detailed_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    $products = products_by_ids(array_keys($cart));
    $items = [];
    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) {
            continue;
        }
        $product = $products[$productId];
        $qty = max(1, (int) $qty);
        $items[] = [
            'product' => $product,
            'quantity' => $qty,
            'sum' => $qty * (float) $product['price']
        ];
    }
    return $items;
}

function cart_total(): float
{
    $total = 0;
    foreach (cart_detailed_items() as $item) {
        $total += $item['sum'];
    }
    return $total;
}

function order_status_label(string $status): string
{
    $map = [
        'new' => 'Новый',
        'processing' => 'В обработке',
        'completed' => 'Завершён',
        'cancelled' => 'Отменён'
    ];
    return $map[$status] ?? $status;
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;
    return [$page, $pages, $offset];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('Ошибка CSRF-токена. Обновите страницу и попробуйте ещё раз.');
        }
    }
}

function send_status_email_if_possible(array $order, array $user): void
{
    $subject = 'Статус бронирования №' . $order['id'];
    $message = "Здравствуйте, {$user['name']}! Статус вашего бронирования изменён на: " . order_status_label($order['status']);
    @mail($user['email'], $subject, $message, 'Content-Type: text/plain; charset=UTF-8');
}
