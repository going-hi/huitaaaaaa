<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

function mb_db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    require_once dirname(__DIR__) . '/db.php';
    if (!$link instanceof mysqli) {
        throw new RuntimeException('Не удалось подключиться к MySQL.');
    }
    $conn = $link;

    return $conn;
}

function mb_user_find_by_email(string $email): ?array
{
    $db = mb_db();
    $email = strtolower(trim($email));
    $stmt = $db->prepare('SELECT id, name, email, password_hash, role, created_at FROM users WHERE email = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }

    $role = (string) ($row['role'] ?? 'user');

    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
        'password_hash' => (string) $row['password_hash'],
        'role' => in_array($role, ['admin', 'editor', 'user'], true) ? $role : 'user',
        'created_at' => (string) $row['created_at'],
    ];
}

function mb_password_validate(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Пароль не короче 8 символов.';
    }
    if (strlen($password) > 128) {
        return 'Пароль слишком длинный.';
    }
    if (!preg_match('/\pL/u', $password)) {
        return 'Пароль должен содержать хотя бы одну букву.';
    }
    if (!preg_match('/\pN/u', $password)) {
        return 'Пароль должен содержать хотя бы одну цифру.';
    }

    return null;
}

function mb_user_register(string $name, string $email, string $password, string $password2): ?string
{
    $name = trim($name);
    $email = trim($email);
    if ($name === '') {
        return 'Укажите имя.';
    }
    $nlen = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
    if ($nlen < 2) {
        return 'Имя — не менее 2 символов.';
    }
    if ($nlen > 120) {
        return 'Имя не длиннее 120 символов.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Некорректный email.';
    }
    $pwdErr = mb_password_validate($password);
    if ($pwdErr !== null) {
        return $pwdErr;
    }
    if ($password !== $password2) {
        return 'Пароли не совпадают.';
    }
    if (mb_user_find_by_email($email) !== null) {
        return 'Пользователь с таким email уже зарегистрирован.';
    }

    $email = strtolower($email);

    $db = mb_db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';
    $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    if ($stmt === false) {
        return 'Ошибка сервера при регистрации.';
    }
    $stmt->bind_param('ssss', $name, $email, $hash, $role);
    if (!$stmt->execute()) {
        $stmt->close();
        if ($db->errno === 1062) {
            return 'Пользователь с таким email уже зарегистрирован.';
        }

        return 'Ошибка сервера при регистрации.';
    }
    $stmt->close();

    return null;
}

function mb_user_login(string $login, string $password): ?string
{
    $login = trim($login);
    if ($login === '' || $password === '') {
        return 'Заполните все поля.';
    }
    $user = mb_user_find_by_email($login);
    if ($user === null || !password_verify($password, $user['password_hash'])) {
        return 'Неверный email или пароль.';
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user',
    ];
    mb_csrf_regenerate();

    return null;
}

function mb_user_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** @return array{id:int,name:string,email:string,role?:string}|null */
function mb_current_user(): ?array
{
    if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }
    $u = $_SESSION['user'];
    if (!isset($u['id'], $u['name'], $u['email'])) {
        return null;
    }
    $role = (string) ($u['role'] ?? 'user');

    return [
        'id' => (int) $u['id'],
        'name' => (string) $u['name'],
        'email' => (string) $u['email'],
        'role' => in_array($role, ['admin', 'editor', 'user'], true) ? $role : 'user',
    ];
}

function mb_require_login(): void
{
    if (mb_current_user() !== null) {
        return;
    }
    $next = mb_current_request_allowed_page();
    $q = $next !== null ? ('?next=' . rawurlencode($next)) : '';
    header('Location: login.php' . $q, true, 302);
    exit;
}

function mb_current_request_allowed_page(): ?string
{
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || str_contains($path, '..')) {
        return null;
    }
    $base = basename($path);
    $allow = [
        'cabinet.php',
        'cabinet-base.php',
        'cabinet-profile.php',
        'cabinet-settings.php',
        'knowledge-catalog.php',
        'learning-materials.php',
        'documents.php',
        'article.php',
        'article-edit.php',
        'search.php',
        'category.php',
        'category-edit.php',
        'export.php',
        'document-download.php',
        'admin-users.php',
        'admin-access.php',
    ];

    return in_array($base, $allow, true) ? $base : null;
}
