<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/roles.php';

const MB_WS_ROLE_OWNER = 'owner';
const MB_WS_ROLE_ADMIN = 'admin';
const MB_WS_ROLE_EDITOR = 'editor';
const MB_WS_ROLE_MEMBER = 'user';

/** @return list<string> */
function mb_workspace_valid_roles(): array
{
    return [MB_WS_ROLE_OWNER, MB_WS_ROLE_ADMIN, MB_WS_ROLE_EDITOR, MB_WS_ROLE_MEMBER];
}

function mb_workspace_role_to_app(string $wsRole): string
{
    return match ($wsRole) {
        MB_WS_ROLE_OWNER, MB_WS_ROLE_ADMIN => MB_ROLE_ADMIN,
        MB_WS_ROLE_EDITOR => MB_ROLE_EDITOR,
        default => MB_ROLE_USER,
    };
}

function mb_app_role_to_workspace(string $appRole): string
{
    return match ($appRole) {
        MB_ROLE_ADMIN => MB_WS_ROLE_ADMIN,
        MB_ROLE_EDITOR => MB_WS_ROLE_EDITOR,
        default => MB_WS_ROLE_MEMBER,
    };
}

function mb_workspace_invite_token_generate(): string
{
    return bin2hex(random_bytes(32));
}

function mb_workspace_slugify(string $title): string
{
    $text = trim($title);
    if (function_exists('mb_strtolower')) {
        $text = mb_strtolower($text, 'UTF-8');
    } else {
        $text = strtolower($text);
    }
    $map = ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'workspace';
    }

    return $text;
}

function mb_workspace_unique_slug(mysqli $db, string $base): string
{
    $slug = $base;
    $n = 0;
    while (true) {
        $stmt = $db->prepare('SELECT id FROM workspaces WHERE slug = ? LIMIT 1');
        if ($stmt === false) {
            return $slug;
        }
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row === null) {
            return $slug;
        }
        $n++;
        $slug = $base . '-' . $n;
    }
}

/** @return array{id:int,title:string,slug:string,owner_id:int,invite_token:string}|null */
function mb_workspace_by_id(int $id): ?array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, title, slug, owner_id, invite_token FROM workspaces WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'slug' => (string) $row['slug'],
        'owner_id' => (int) $row['owner_id'],
        'invite_token' => (string) $row['invite_token'],
    ];
}

function mb_workspace_member_role(int $workspaceId, int $userId): ?string
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT role FROM workspace_members WHERE workspace_id = ? AND user_id = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('ii', $workspaceId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }
    $role = (string) $row['role'];

    return in_array($role, mb_workspace_valid_roles(), true) ? $role : MB_WS_ROLE_MEMBER;
}

function mb_workspace_user_is_member(int $workspaceId, int $userId): bool
{
    return mb_workspace_member_role($workspaceId, $userId) !== null;
}

/** @return list<array{id:int,title:string,slug:string,role:string}> */
function mb_workspaces_for_user(int $userId): array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT w.id, w.title, w.slug, wm.role
        FROM workspaces w
        INNER JOIN workspace_members wm ON wm.workspace_id = w.id
        WHERE wm.user_id = ?
        ORDER BY w.title'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'slug' => (string) $row['slug'],
            'role' => (string) $row['role'],
        ];
    }
    $stmt->close();

    return $rows;
}

function mb_workspace_current_id(): ?int
{
    if (empty($_SESSION['workspace_id'])) {
        return null;
    }

    return (int) $_SESSION['workspace_id'];
}

/** @return array{id:int,title:string,slug:string,owner_id:int,invite_token:string,role:string}|null */
function mb_workspace_current(): ?array
{
    $u = mb_current_user();
    if ($u === null) {
        return null;
    }
    $id = mb_workspace_current_id();
    if ($id === null) {
        return null;
    }
    $ws = mb_workspace_by_id($id);
    if ($ws === null) {
        return null;
    }
    $role = mb_workspace_member_role($id, $u['id']);
    if ($role === null) {
        return null;
    }
    $ws['role'] = $role;

    return $ws;
}

function mb_workspace_set_current(int $workspaceId, ?int $userId = null): bool
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if ($userId <= 0 || !mb_workspace_user_is_member($workspaceId, $userId)) {
        return false;
    }
    $_SESSION['workspace_id'] = $workspaceId;
    $wsRole = mb_workspace_member_role($workspaceId, $userId);
    if ($wsRole !== null) {
        $_SESSION['workspace_role'] = mb_workspace_role_to_app($wsRole);
    }

    return true;
}

function mb_workspace_session_sync(?int $userId = null): void
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if ($userId <= 0) {
        unset($_SESSION['workspace_id'], $_SESSION['workspace_role']);

        return;
    }
    $current = mb_workspace_current_id();
    if ($current !== null && mb_workspace_user_is_member($current, $userId)) {
        $wsRole = mb_workspace_member_role($current, $userId);
        if ($wsRole !== null) {
            $_SESSION['workspace_role'] = mb_workspace_role_to_app($wsRole);
        }

        return;
    }
    $list = mb_workspaces_for_user($userId);
    if (count($list) === 1) {
        mb_workspace_set_current((int) $list[0]['id'], $userId);
    } else {
        unset($_SESSION['workspace_id'], $_SESSION['workspace_role']);
    }
}

function mb_require_workspace(): void
{
    mb_require_login();
    $u = mb_current_user();
    if ($u === null) {
        exit;
    }
    mb_workspace_session_sync($u['id']);
    if (mb_workspace_current_id() === null) {
        header('Location: workspaces.php', true, 302);
        exit;
    }
}

function mb_workspace_can_manage(?array $workspace = null): bool
{
    if ($workspace === null) {
        $workspace = mb_workspace_current();
    }
    if ($workspace === null) {
        return false;
    }
    $role = (string) ($workspace['role'] ?? '');

    return in_array($role, [MB_WS_ROLE_OWNER, MB_WS_ROLE_ADMIN], true);
}

function mb_workspace_require_manage(): void
{
    mb_require_workspace();
    if (!mb_workspace_can_manage()) {
        http_response_code(403);
        mb_forbidden_page('Управление базой доступно владельцу и администраторам.');
    }
}

/** @return array{id:int,title:string,slug:string}|array{error:string} */
function mb_workspace_create(int $ownerId, string $title): array
{
    $title = trim($title);
    if ($title === '') {
        return ['error' => 'Укажите название базы знаний.'];
    }
    if (mb_strlen($title, 'UTF-8') > 255) {
        return ['error' => 'Название не длиннее 255 символов.'];
    }
    $db = mb_db();
    $slug = mb_workspace_unique_slug($db, mb_workspace_slugify($title));
    $token = mb_workspace_invite_token_generate();
    $stmt = $db->prepare('INSERT INTO workspaces (title, slug, owner_id, invite_token) VALUES (?, ?, ?, ?)');
    if ($stmt === false) {
        return ['error' => 'Ошибка создания базы.'];
    }
    $stmt->bind_param('ssis', $title, $slug, $ownerId, $token);
    if (!$stmt->execute()) {
        $stmt->close();

        return ['error' => 'Ошибка создания базы.'];
    }
    $id = (int) $stmt->insert_id;
    $stmt->close();
    mb_workspace_add_member($id, $ownerId, MB_WS_ROLE_OWNER);

    return ['id' => $id, 'title' => $title, 'slug' => $slug];
}

function mb_workspace_add_member(int $workspaceId, int $userId, string $role = MB_WS_ROLE_MEMBER): ?string
{
    if (!in_array($role, mb_workspace_valid_roles(), true)) {
        return 'Некорректная роль.';
    }
    if (mb_workspace_user_is_member($workspaceId, $userId)) {
        return 'Пользователь уже в этой базе.';
    }
    $db = mb_db();
    $stmt = $db->prepare('INSERT INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, ?)');
    if ($stmt === false) {
        return 'Ошибка добавления.';
    }
    $stmt->bind_param('iis', $workspaceId, $userId, $role);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? null : 'Ошибка добавления.';
}

function mb_workspace_join_by_token(int $userId, string $token): ?string
{
    $token = trim($token);
    if (preg_match('/token=([a-f0-9]+)/i', $token, $m)) {
        $token = $m[1];
    }
    if ($token === '') {
        return 'Укажите код приглашения.';
    }
    $db = mb_db();
    $stmt = $db->prepare('SELECT id FROM workspaces WHERE invite_token = ? LIMIT 1');
    if ($stmt === false) {
        return 'Ошибка сервера.';
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return 'Приглашение не найдено или ссылка устарела.';
    }
    $wsId = (int) $row['id'];
    if (mb_workspace_user_is_member($wsId, $userId)) {
        mb_workspace_set_current($wsId, $userId);

        return null;
    }
    $err = mb_workspace_add_member($wsId, $userId, MB_WS_ROLE_MEMBER);
    if ($err !== null) {
        return $err;
    }
    mb_workspace_set_current($wsId, $userId);

    return null;
}

function mb_workspace_add_member_by_email(int $workspaceId, string $email, string $role = MB_WS_ROLE_MEMBER): ?string
{
    if (!mb_workspace_can_manage()) {
        return 'Недостаточно прав.';
    }
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Некорректный email.';
    }
    $user = mb_user_find_by_email($email);
    if ($user === null) {
        return 'Пользователь не найден. Попросите коллегу зарегистрироваться, затем добавьте снова.';
    }
    if ((int) $user['id'] === (int) (mb_current_user()['id'] ?? 0)) {
        return 'Вы уже в этой базе.';
    }

    return mb_workspace_add_member($workspaceId, (int) $user['id'], mb_app_role_to_workspace($role));
}

function mb_workspace_set_member_role(int $workspaceId, int $userId, string $appRole): ?string
{
    if (!mb_workspace_can_manage()) {
        return 'Недостаточно прав.';
    }
    $ws = mb_workspace_current();
    if ($ws === null) {
        return 'База не выбрана.';
    }
    $targetRole = mb_workspace_member_role($workspaceId, $userId);
    if ($targetRole === MB_WS_ROLE_OWNER) {
        return 'Роль владельца нельзя изменить.';
    }
    if ($userId === (int) ($ws['owner_id'] ?? 0)) {
        return 'Нельзя изменить роль владельца.';
    }
    $newRole = mb_app_role_to_workspace($appRole);
    if ($newRole === MB_WS_ROLE_OWNER) {
        return 'Нельзя назначить второго владельца.';
    }
    $db = mb_db();
    $stmt = $db->prepare('UPDATE workspace_members SET role = ? WHERE workspace_id = ? AND user_id = ?');
    if ($stmt === false) {
        return 'Ошибка сохранения.';
    }
    $stmt->bind_param('sii', $newRole, $workspaceId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    $cu = mb_current_user();
    if ($ok && $cu !== null && $cu['id'] === $userId) {
        mb_workspace_set_current($workspaceId, $userId);
    }

    return $ok ? null : 'Ошибка сохранения.';
}

function mb_workspace_regenerate_invite(int $workspaceId): ?string
{
    if (!mb_workspace_can_manage()) {
        return null;
    }
    $token = mb_workspace_invite_token_generate();
    $db = mb_db();
    $stmt = $db->prepare('UPDATE workspaces SET invite_token = ? WHERE id = ?');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('si', $token, $workspaceId);
    $stmt->execute();
    $stmt->close();

    return $token;
}

function mb_workspace_invite_url(string $token): string
{
    if (!function_exists('mb_site_base_url')) {
        require_once __DIR__ . '/seo.php';
    }
    $base = mb_site_base_url();

    return $base . '/join.php?token=' . rawurlencode($token);
}

function mb_workspace_save_title(int $workspaceId, string $title): ?string
{
    if (!mb_workspace_can_manage()) {
        return 'Недостаточно прав.';
    }
    $title = trim($title);
    if ($title === '') {
        return 'Укажите название.';
    }
    if (mb_strlen($title, 'UTF-8') > 255) {
        return 'Название не длиннее 255 символов.';
    }
    $db = mb_db();
    $stmt = $db->prepare('UPDATE workspaces SET title = ? WHERE id = ?');
    if ($stmt === false) {
        return 'Ошибка сохранения.';
    }
    $stmt->bind_param('si', $title, $workspaceId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? null : 'Ошибка сохранения.';
}

/** @return list<array{id:int,name:string,email:string,role:string,role_title:?string,group_ids:list<int>}> */
function mb_workspace_members_list(int $workspaceId): array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT u.id, u.name, u.email, u.role_title, wm.role
        FROM workspace_members wm
        INNER JOIN users u ON u.id = wm.user_id
        WHERE wm.workspace_id = ?
        ORDER BY u.name'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $workspaceId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $uid = (int) $row['id'];
        $wsRole = (string) $row['role'];
        $rows[] = [
            'id' => $uid,
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'role' => mb_workspace_role_to_app($wsRole),
            'role_title' => $row['role_title'] !== null ? (string) $row['role_title'] : null,
            'group_ids' => mb_user_group_ids($uid),
        ];
    }
    $stmt->close();

    return $rows;
}

function mb_ws_id(): int
{
    $id = mb_workspace_current_id();
    if ($id === null) {
        mb_require_workspace();
        $id = mb_workspace_current_id();
    }

    return (int) $id;
}
