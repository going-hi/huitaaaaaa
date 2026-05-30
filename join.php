<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/workspace.php';

$token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
$user = mb_current_user();

if ($user === null) {
    $next = 'join.php' . ($token !== '' ? ('?token=' . rawurlencode($token)) : '');
    header('Location: login.php?' . http_build_query(['next' => $next]), true, 302);
    exit;
}

if ($token === '') {
    mb_flash_set('cabinet_notice', 'Укажите код приглашения.');
    header('Location: workspaces.php', true, 302);
    exit;
}

$err = mb_workspace_join_by_token((int) $user['id'], $token);
if ($err !== null) {
    mb_flash_set('register_error', $err);
    header('Location: workspaces.php', true, 302);
    exit;
}

mb_flash_set('cabinet_notice', 'Вы присоединились к базе знаний.');
header('Location: cabinet.php', true, 302);
exit;
