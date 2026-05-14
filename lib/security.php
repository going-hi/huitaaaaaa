<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function mb_flash_set(string $key, string $message): void
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    $_SESSION['_flash'][$key] = $message;
}

function mb_flash_take(string $key): ?string
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        return null;
    }
    if (!isset($_SESSION['_flash'][$key]) || !is_string($_SESSION['_flash'][$key])) {
        return null;
    }
    $m = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $m;
}

function mb_csrf_token(): string
{
    if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function mb_csrf_validate(?string $token): bool
{
    return is_string($token) && isset($_SESSION['_csrf']) && is_string($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function mb_csrf_regenerate(): void
{
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

/** Разрешённые страницы после входа (только имя файла). */
function mb_login_redirect_target(?string $next): string
{
    $default = 'cabinet.php';
    $allow = [
        'cabinet.php',
        'cabinet-base.php',
        'cabinet-profile.php',
        'cabinet-settings.php',
        'knowledge-catalog.php',
        'learning-materials.php',
        'documents.php',
    ];
    if ($next === null) {
        return $default;
    }
    $next = trim($next);
    if ($next === '') {
        return $default;
    }
    $path = parse_url($next, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = $next;
    }
    if (str_contains($path, '..')) {
        return $default;
    }
    $base = basename($path);

    return in_array($base, $allow, true) ? $base : $default;
}
