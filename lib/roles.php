<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const MB_ROLE_ADMIN = 'admin';
const MB_ROLE_EDITOR = 'editor';
const MB_ROLE_USER = 'user';

/** @return list<string> */
function mb_valid_roles(): array
{
    return [MB_ROLE_ADMIN, MB_ROLE_EDITOR, MB_ROLE_USER];
}

function mb_role_label(string $role): string
{
    return match ($role) {
        MB_ROLE_ADMIN => 'Администратор',
        MB_ROLE_EDITOR => 'Редактор',
        MB_ROLE_USER => 'Пользователь',
        default => $role,
    };
}

function mb_role_badge_class(string $role): string
{
    return match ($role) {
        MB_ROLE_ADMIN => 'role-badge role-badge--admin',
        MB_ROLE_EDITOR => 'role-badge role-badge--editor',
        default => 'role-badge role-badge--user',
    };
}

/** @return array{id:int,name:string,email:string,role:string,role_title:?string}|null */
function mb_current_user_full(): ?array
{
    $u = mb_current_user();
    if ($u === null) {
        return null;
    }
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, name, email, role, role_title FROM users WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('i', $u['id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }
    $role = (string) ($row['role'] ?? MB_ROLE_USER);
    if (!in_array($role, mb_valid_roles(), true)) {
        $role = MB_ROLE_USER;
    }

    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
        'role' => $role,
        'role_title' => $row['role_title'] !== null ? (string) $row['role_title'] : null,
    ];
}

function mb_refresh_session_user(): void
{
    $full = mb_current_user_full();
    if ($full === null) {
        return;
    }
    $_SESSION['user'] = [
        'id' => $full['id'],
        'name' => $full['name'],
        'email' => $full['email'],
        'role' => $full['role'],
    ];
}

function mb_user_role(?array $user = null): string
{
    if (!empty($_SESSION['workspace_role']) && in_array((string) $_SESSION['workspace_role'], mb_valid_roles(), true)) {
        return (string) $_SESSION['workspace_role'];
    }
    if ($user === null) {
        $user = mb_current_user();
    }
    if ($user !== null && function_exists('mb_workspace_current_id')) {
        $wsId = mb_workspace_current_id();
        if ($wsId !== null && function_exists('mb_workspace_member_role')) {
            $wsRole = mb_workspace_member_role($wsId, (int) $user['id']);
            if ($wsRole !== null) {
                return mb_workspace_role_to_app($wsRole);
            }
        }
    }

    return MB_ROLE_USER;
}

function mb_is_admin(?array $user = null): bool
{
    return mb_user_role($user) === MB_ROLE_ADMIN;
}

function mb_can_write(?array $user = null): bool
{
    $role = mb_user_role($user);

    return $role === MB_ROLE_ADMIN || $role === MB_ROLE_EDITOR;
}

function mb_can_manage_users(?array $user = null): bool
{
    return mb_is_admin($user);
}

function mb_require_write(): void
{
    mb_require_login();
    if (!mb_can_write()) {
        http_response_code(403);
        mb_forbidden_page('Недостаточно прав. Редактирование доступно редакторам и администраторам.');
    }
}

function mb_require_admin(): void
{
    mb_require_login();
    if (!mb_is_admin()) {
        http_response_code(403);
        mb_forbidden_page('Раздел только для администраторов.');
    }
}

function mb_forbidden_page(string $message): void
{
    require_once dirname(__DIR__) . '/lib/bootstrap.php';
    $user = mb_current_user();
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Доступ запрещён — MindBase</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="cabinet-page">
  <main class="cabinet-main" style="max-width:560px;margin:4rem auto;padding:2rem">
    <h1>Доступ запрещён</h1>
    <p class="cabinet-page-lead"><?= mb_h($message) ?></p>
    <p><a href="cabinet.php" class="btn btn-primary">В личный кабинет</a></p>
  </main>
</body>
</html>
    <?php
    exit;
}

function mb_user_role_by_id(int $userId): string
{
    if (!function_exists('mb_workspace_current_id')) {
        require_once __DIR__ . '/workspace.php';
    }
    $wsId = mb_workspace_current_id();
    if ($wsId !== null) {
        $wsRole = mb_workspace_member_role($wsId, $userId);
        if ($wsRole !== null) {
            return mb_workspace_role_to_app($wsRole);
        }
    }

    return MB_ROLE_USER;
}

/** @return list<int> */
function mb_user_group_ids(int $userId): array
{
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return [];
    }
    if (!function_exists('mb_workspace_current_id')) {
        require_once __DIR__ . '/workspace.php';
    }
    $wsId = mb_workspace_current_id();
    if ($wsId === null) {
        return [];
    }
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT uag.group_id FROM user_access_groups uag
        INNER JOIN access_groups ag ON ag.id = uag.group_id
        WHERE uag.user_id = ? AND ag.workspace_id = ?'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('ii', $userId, $wsId);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int) $row['group_id'];
    }
    $stmt->close();

    return $ids;
}

function mb_sql_in_int_list(array $ids): string
{
    if ($ids === []) {
        return '0';
    }

    return implode(',', array_map('intval', $ids));
}

/** Группы доступа раздела с учётом ближайшего ограниченного предка. */
function mb_category_effective_group_ids(int $categoryId): array
{
    static $cache = [];
    if (array_key_exists($categoryId, $cache)) {
        return $cache[$categoryId];
    }
    $currentId = $categoryId;
    $db = mb_db();
    while ($currentId > 0) {
        $groups = mb_category_group_ids($currentId);
        if ($groups !== []) {
            return $cache[$categoryId] = $groups;
        }
        $stmt = $db->prepare('SELECT parent_id FROM categories WHERE id = ? LIMIT 1');
        if ($stmt === false) {
            break;
        }
        $stmt->bind_param('i', $currentId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row === null || $row['parent_id'] === null) {
            break;
        }
        $currentId = (int) $row['parent_id'];
    }

    return $cache[$categoryId] = [];
}

function mb_category_is_visible_to_user(int $categoryId, ?int $userId = null): bool
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return true;
    }
    $effective = mb_category_effective_group_ids($categoryId);
    if ($effective === []) {
        return true;
    }
    $userGroups = mb_user_group_ids($userId);
    foreach ($effective as $groupId) {
        if (in_array($groupId, $userGroups, true)) {
            return true;
        }
    }

    return false;
}

/** @return list<int> */
function mb_visible_category_ids(?int $userId = null): array
{
    static $cache = [];
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (!function_exists('mb_workspace_current_id')) {
        require_once __DIR__ . '/workspace.php';
    }
    $wsId = mb_workspace_current_id() ?? 0;
    $cacheKey = $userId . ':' . $wsId;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    if ($wsId <= 0) {
        return $cache[$cacheKey] = [];
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        $db = mb_db();
        $stmt = $db->prepare("SELECT id FROM categories WHERE workspace_id = ? AND slug != 'help'");
        if ($stmt === false) {
            return $cache[$cacheKey] = [];
        }
        $stmt->bind_param('i', $wsId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_assoc()) {
            $ids[] = (int) $row['id'];
        }
        $stmt->close();

        return $cache[$cacheKey] = $ids;
    }
    $db = mb_db();
    $stmt = $db->prepare("SELECT id FROM categories WHERE workspace_id = ? AND slug != 'help'");
    if ($stmt === false) {
        return $cache[$cacheKey] = [];
    }
    $stmt->bind_param('i', $wsId);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $id = (int) $row['id'];
        if (mb_category_is_visible_to_user($id, $userId)) {
            $ids[] = $id;
        }
    }
    $stmt->close();

    return $cache[$cacheKey] = $ids;
}

/** SQL-фрагмент: категория доступна текущему пользователю (alias — id категории). */
function mb_sql_category_visible(string $categoryIdExpr, ?int $userId = null): string
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return '1=1';
    }
    $ids = mb_visible_category_ids($userId);
    if ($ids === []) {
        return '0';
    }

    return $categoryIdExpr . ' IN (' . implode(',', array_map('intval', $ids)) . ')';
}

function mb_user_can_view_category(int $categoryId, ?int $userId = null): bool
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return true;
    }
    $db = mb_db();
    $stmt = $db->prepare('SELECT 1 FROM categories WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();
    if (!$exists) {
        return false;
    }

    return mb_category_is_visible_to_user($categoryId, $userId);
}

function mb_sql_document_visible(string $documentIdExpr, ?int $userId = null): string
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return '1=1';
    }
    $groups = mb_user_group_ids($userId);
    $gList = mb_sql_in_int_list($groups);
    $did = $documentIdExpr;

    return "(
        (SELECT COUNT(*) FROM document_access_groups dag WHERE dag.document_id = {$did}) = 0
        OR EXISTS (
            SELECT 1 FROM document_access_groups dag
            WHERE dag.document_id = {$did} AND dag.group_id IN ({$gList})
        )
    )";
}

function mb_user_can_view_document(int $documentId, ?int $userId = null): bool
{
    if ($userId === null) {
        $u = mb_current_user();
        $userId = $u !== null ? $u['id'] : 0;
    }
    if (mb_user_role_by_id($userId) === MB_ROLE_ADMIN) {
        return true;
    }
    $db = mb_db();
    $sql = 'SELECT 1 FROM documents d WHERE d.id = ? AND ' . mb_sql_document_visible('d.id', $userId) . ' LIMIT 1';
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('i', $documentId);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();

    return $ok;
}

/** @return list<array{id:int,name:string,slug:string,description:string}> */
function mb_access_groups_list(): array
{
    if (!function_exists('mb_ws_id')) {
        require_once __DIR__ . '/workspace.php';
    }
    $wsId = mb_workspace_current_id();
    if ($wsId === null) {
        return [];
    }
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, name, slug, description FROM access_groups WHERE workspace_id = ? ORDER BY name');
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $wsId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'description' => (string) ($row['description'] ?? ''),
        ];
    }
    $stmt->close();

    return $rows;
}

/** @return list<int> */
function mb_category_group_ids(int $categoryId): array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT group_id FROM category_access_groups WHERE category_id = ?');
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int) $row['group_id'];
    }
    $stmt->close();

    return $ids;
}

function mb_category_set_groups(int $categoryId, array $groupIds): void
{
    $db = mb_db();
    $db->query('DELETE FROM category_access_groups WHERE category_id = ' . (int) $categoryId);
    $stmt = $db->prepare('INSERT INTO category_access_groups (category_id, group_id) VALUES (?, ?)');
    if ($stmt === false) {
        return;
    }
    foreach ($groupIds as $gid) {
        $gid = (int) $gid;
        if ($gid <= 0) {
            continue;
        }
        $stmt->bind_param('ii', $categoryId, $gid);
        $stmt->execute();
    }
    $stmt->close();
}

/** @return list<int> */
function mb_document_group_ids(int $documentId): array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT group_id FROM document_access_groups WHERE document_id = ?');
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $documentId);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int) $row['group_id'];
    }
    $stmt->close();

    return $ids;
}

function mb_document_set_groups(int $documentId, array $groupIds): void
{
    $db = mb_db();
    $db->query('DELETE FROM document_access_groups WHERE document_id = ' . (int) $documentId);
    $stmt = $db->prepare('INSERT INTO document_access_groups (document_id, group_id) VALUES (?, ?)');
    if ($stmt === false) {
        return;
    }
    foreach ($groupIds as $gid) {
        $gid = (int) $gid;
        if ($gid <= 0) {
            continue;
        }
        $stmt->bind_param('ii', $documentId, $gid);
        $stmt->execute();
    }
    $stmt->close();
}

function mb_user_set_groups(int $userId, array $groupIds): void
{
    $db = mb_db();
    $db->query('DELETE FROM user_access_groups WHERE user_id = ' . (int) $userId);
    $stmt = $db->prepare('INSERT INTO user_access_groups (user_id, group_id) VALUES (?, ?)');
    if ($stmt === false) {
        return;
    }
    foreach ($groupIds as $gid) {
        $gid = (int) $gid;
        if ($gid <= 0) {
            continue;
        }
        $stmt->bind_param('ii', $userId, $gid);
        $stmt->execute();
    }
    $stmt->close();
}

function mb_user_set_role(int $userId, string $role): ?string
{
    if (!in_array($role, mb_valid_roles(), true)) {
        return 'Некорректная роль.';
    }
    if (!function_exists('mb_workspace_set_member_role')) {
        require_once __DIR__ . '/workspace.php';
    }

    return mb_workspace_set_member_role(mb_ws_id(), $userId, $role);
}

function mb_user_remove_from_workspace(int $userId): ?string
{
    if (!function_exists('mb_workspace_remove_member')) {
        require_once __DIR__ . '/workspace.php';
    }

    return mb_workspace_remove_member(mb_ws_id(), $userId);
}

/** @return list<array<string,mixed>> */
function mb_users_list(): array
{
    if (!function_exists('mb_workspace_members_list')) {
        require_once __DIR__ . '/workspace.php';
    }

    return mb_workspace_members_list(mb_ws_id());
}

function mb_group_slugify(string $name): string
{
    $s = mb_strtolower(trim($name), 'UTF-8');
    $s = preg_replace('/[^a-z0-9а-яё]+/u', '-', $s) ?? $s;
    $s = trim($s, '-');

    return $s !== '' ? $s : 'group';
}

function mb_group_unique_slug(mysqli $db, string $base): string
{
    if (!function_exists('mb_ws_id')) {
        require_once __DIR__ . '/workspace.php';
    }
    $wsId = mb_ws_id();
    $slug = $base;
    $n = 0;
    while (true) {
        $stmt = $db->prepare('SELECT id FROM access_groups WHERE slug = ? AND workspace_id = ? LIMIT 1');
        if ($stmt === false) {
            return $slug;
        }
        $stmt->bind_param('si', $slug, $wsId);
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

function mb_access_group_save(?int $id, string $name, string $description): array
{
    $name = trim($name);
    $description = trim($description);
    if ($name === '') {
        return ['error' => 'Укажите название группы.'];
    }
    $db = mb_db();
    $wsId = mb_ws_id();
    $slug = mb_group_unique_slug($db, mb_group_slugify($name));
    if ($id === null) {
        $stmt = $db->prepare('INSERT INTO access_groups (workspace_id, name, slug, description) VALUES (?, ?, ?, ?)');
        if ($stmt === false) {
            return ['error' => 'Ошибка сохранения.'];
        }
        $stmt->bind_param('isss', $wsId, $name, $slug, $description);
        $stmt->execute();
        $newId = (int) $stmt->insert_id;
        $stmt->close();

        return ['id' => $newId];
    }
    $stmt = $db->prepare('UPDATE access_groups SET name = ?, description = ? WHERE id = ? AND workspace_id = ?');
    if ($stmt === false) {
        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->bind_param('ssii', $name, $description, $id, $wsId);
    $stmt->execute();
    $stmt->close();

    return ['id' => $id];
}

function mb_access_group_delete(int $id): ?string
{
    $db = mb_db();
    $stmt = $db->prepare('DELETE FROM access_groups WHERE id = ?');
    if ($stmt === false) {
        return 'Ошибка удаления.';
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    return null;
}
