<?php

/**
 * Миграция: отдельные базы знаний (workspaces) для каждой команды.
 * php database/migrate_workspaces.php
 */

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);

require_once dirname(__DIR__) . '/db.php';
if (!$link instanceof mysqli) {
    fwrite(STDERR, "Нет подключения к MySQL.\n");
    exit(1);
}

function mb_migrate_column_exists(mysqli $db, string $table, string $column): bool
{
    $dbName = getenv('MYSQL_DATABASE') ?: 'mindbase';
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('sss', $dbName, $table, $column);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();

    return $ok;
}

function mb_migrate_table_exists(mysqli $db, string $table): bool
{
    $dbName = getenv('MYSQL_DATABASE') ?: 'mindbase';
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1'
    );
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('ss', $dbName, $table);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();

    return $ok;
}

function mb_migrate_run(mysqli $db, string $sql): void
{
    if (!$db->query($sql)) {
        fwrite(STDERR, "SQL: {$db->error}\n{$sql}\n");
        exit(1);
    }
}

if (!mb_migrate_table_exists($link, 'workspaces')) {
    mb_migrate_run($link, "CREATE TABLE workspaces (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(80) NOT NULL,
        owner_id INT UNSIGNED NOT NULL,
        invite_token VARCHAR(64) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_workspaces_slug (slug),
        UNIQUE KEY uq_workspaces_invite (invite_token),
        KEY idx_workspaces_owner (owner_id),
        CONSTRAINT fk_workspaces_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

if (!mb_migrate_table_exists($link, 'workspace_members')) {
    mb_migrate_run($link, "CREATE TABLE workspace_members (
        workspace_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        role ENUM('owner','admin','editor','user') NOT NULL DEFAULT 'user',
        joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (workspace_id, user_id),
        KEY idx_wm_user (user_id),
        CONSTRAINT fk_wm_workspace FOREIGN KEY (workspace_id) REFERENCES workspaces (id) ON DELETE CASCADE,
        CONSTRAINT fk_wm_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

$tables = ['categories', 'tags', 'access_groups', 'documents', 'courses', 'articles'];
foreach ($tables as $table) {
    if (!mb_migrate_column_exists($link, $table, 'workspace_id')) {
        mb_migrate_run($link, "ALTER TABLE {$table} ADD COLUMN workspace_id INT UNSIGNED NULL DEFAULT NULL AFTER id");
    }
}

if (mb_migrate_table_exists($link, 'workspace') && mb_migrate_table_exists($link, 'workspaces')) {
    $res = $link->query('SELECT COUNT(*) AS c FROM workspaces');
    $count = $res instanceof mysqli_result ? (int) ($res->fetch_assoc()['c'] ?? 0) : 0;
    if ($res instanceof mysqli_result) {
        $res->free();
    }
    if ($count === 0) {
        $ownerId = 1;
        $r = $link->query('SELECT id FROM users ORDER BY id LIMIT 1');
        if ($r instanceof mysqli_result && ($row = $r->fetch_assoc())) {
            $ownerId = (int) $row['id'];
            $r->free();
        }
        $title = 'MindBase — корпоративная база';
        $rt = $link->query('SELECT title FROM workspace WHERE id = 1 LIMIT 1');
        if ($rt instanceof mysqli_result && ($wrow = $rt->fetch_assoc()) && trim((string) $wrow['title']) !== '') {
            $title = (string) $wrow['title'];
            $rt->free();
        }
        $slug = 'default';
        $token = bin2hex(random_bytes(32));
        $stmt = $link->prepare('INSERT INTO workspaces (id, title, slug, owner_id, invite_token) VALUES (1, ?, ?, ?, ?)');
        if ($stmt !== false) {
            $stmt->bind_param('ssis', $title, $slug, $ownerId, $token);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$res = $link->query('SELECT id FROM workspaces ORDER BY id LIMIT 1');
$defaultWs = 1;
if ($res instanceof mysqli_result && ($row = $res->fetch_assoc())) {
    $defaultWs = (int) $row['id'];
    $res->free();
}

foreach ($tables as $table) {
    mb_migrate_run($link, "UPDATE {$table} SET workspace_id = {$defaultWs} WHERE workspace_id IS NULL");
    if (mb_migrate_column_exists($link, $table, 'workspace_id')) {
        $nullRes = $link->query("SELECT COUNT(*) AS c FROM {$table} WHERE workspace_id IS NULL");
        $nulls = $nullRes instanceof mysqli_result ? (int) ($nullRes->fetch_assoc()['c'] ?? 0) : 0;
        if ($nullRes instanceof mysqli_result) {
            $nullRes->free();
        }
        if ($nulls === 0) {
            mb_migrate_run($link, "ALTER TABLE {$table} MODIFY workspace_id INT UNSIGNED NOT NULL");
        }
    }
}

if (mb_migrate_table_exists($link, 'workspace_members')) {
    $res = $link->query('SELECT COUNT(*) AS c FROM workspace_members');
    $mc = $res instanceof mysqli_result ? (int) ($res->fetch_assoc()['c'] ?? 0) : 0;
    if ($res instanceof mysqli_result) {
        $res->free();
    }
    if ($mc === 0) {
        $res = $link->query('SELECT id, role FROM users');
        if ($res instanceof mysqli_result) {
            while ($row = $res->fetch_assoc()) {
                $uid = (int) $row['id'];
                $role = (string) ($row['role'] ?? 'user');
                $wsRole = match ($role) {
                    'admin' => 'admin',
                    'editor' => 'editor',
                    default => 'user',
                };
                if ($uid === (int) ($ownerId ?? 1)) {
                    $ownerRes = $link->query("SELECT owner_id FROM workspaces WHERE id = {$defaultWs} LIMIT 1");
                    if ($ownerRes instanceof mysqli_result && ($or = $ownerRes->fetch_assoc())) {
                        if ((int) $or['owner_id'] === $uid) {
                            $wsRole = 'owner';
                        }
                        $ownerRes->free();
                    }
                }
                $stmt = $link->prepare('INSERT IGNORE INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, ?)');
                if ($stmt !== false) {
                    $stmt->bind_param('iis', $defaultWs, $uid, $wsRole);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $res->free();
        }
    }
}

// Уникальные slug в пределах workspace
$indexes = [
    "ALTER TABLE categories DROP INDEX uq_categories_slug",
    "ALTER TABLE categories ADD UNIQUE KEY uq_categories_ws_slug (workspace_id, slug)",
    "ALTER TABLE articles DROP INDEX uq_articles_slug",
    "ALTER TABLE articles ADD UNIQUE KEY uq_articles_ws_slug (workspace_id, slug)",
    "ALTER TABLE tags DROP INDEX uq_tags_slug",
    "ALTER TABLE tags ADD UNIQUE KEY uq_tags_ws_slug (workspace_id, slug)",
    "ALTER TABLE access_groups DROP INDEX uq_access_groups_slug",
    "ALTER TABLE access_groups ADD UNIQUE KEY uq_access_groups_ws_slug (workspace_id, slug)",
];
foreach ($indexes as $sql) {
    @$link->query($sql);
}

if (mb_migrate_table_exists($link, 'workspace')) {
    @$link->query('DROP TABLE workspace');
}

echo "migrate_workspaces: OK (default workspace id={$defaultWs})\n";
