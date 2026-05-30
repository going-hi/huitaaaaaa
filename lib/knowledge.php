<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/roles.php';

function mb_storage_documents_dir(): string
{
    $dir = MB_ROOT . '/storage/documents';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    if (!is_writable($dir)) {
        @chmod($dir, 0775);
    }

    return $dir;
}

function mb_storage_documents_writable(): bool
{
    $dir = mb_storage_documents_dir();

    return is_dir($dir) && is_writable($dir);
}

function mb_slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $out = '';
    $len = mb_strlen($text, 'UTF-8');
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($text, $i, 1, 'UTF-8');
        if (isset($map[$ch])) {
            $out .= $map[$ch];
            continue;
        }
        if (preg_match('/[a-z0-9]/', $ch)) {
            $out .= $ch;
        } elseif ($ch === ' ' || $ch === '-' || $ch === '_') {
            $out .= '-';
        }
    }
    $out = preg_replace('/-+/', '-', $out) ?? '';
    $out = trim($out, '-');

    return $out !== '' ? $out : 'article';
}

function mb_unique_slug(mysqli $db, string $base, string $table = 'articles'): string
{
    $slug = $base;
    $n = 0;
    while (true) {
        if ($table === 'categories') {
            $stmt = $db->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
        } elseif ($table === 'access_groups') {
            $stmt = $db->prepare('SELECT id FROM access_groups WHERE slug = ? LIMIT 1');
        } else {
            $stmt = $db->prepare('SELECT id FROM articles WHERE slug = ? LIMIT 1');
        }
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

function mb_format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' МБ';
    }
    if ($bytes >= 1024) {
        return number_format((int) round($bytes / 1024), 0, ',', ' ') . ' КБ';
    }

    return $bytes . ' Б';
}

function mb_format_datetime(string $dt): string
{
    $ts = strtotime($dt);
    if ($ts === false) {
        return $dt;
    }

    return date('d.m.Y H:i', $ts);
}

function mb_format_date_short(string $dt): string
{
    $ts = strtotime($dt);
    if ($ts === false) {
        return $dt;
    }

    return date('d.m.Y', $ts);
}

function mb_relative_date(string $dt): string
{
    $ts = strtotime($dt);
    if ($ts === false) {
        return $dt;
    }
    $diff = (int) floor((time() - $ts) / 86400);
    if ($diff === 0) {
        return 'сегодня';
    }
    if ($diff === 1) {
        return 'вчера';
    }
    if ($diff < 7) {
        return $diff . ' дн. назад';
    }

    return mb_format_date_short($dt);
}

function mb_markdown_to_html(string $md): string
{
    $parts = preg_split('/(```[\s\S]*?```)/', $md, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        $parts = [$md];
    }
    $html = '';
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1 && str_starts_with($part, '```')) {
            $code = preg_replace('/^```\w*\n?|```$/', '', $part) ?? $part;
            $html .= '<pre><code>' . mb_h(trim($code)) . '</code></pre>';
            continue;
        }
        $escaped = mb_h($part);
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $escaped) ?? $escaped;
        $escaped = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $escaped) ?? $escaped;
        $escaped = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $escaped) ?? $escaped;
        $lines = explode("\n", $escaped);
        $buf = [];
        $inList = false;
        foreach ($lines as $line) {
            if (preg_match('/^[-*] (.+)$/', $line, $m)) {
                if (!$inList) {
                    $buf[] = '<ul>';
                    $inList = true;
                }
                $buf[] = '<li>' . $m[1] . '</li>';
                continue;
            }
            if (preg_match('/^\d+\. (.+)$/', $line, $m)) {
                if (!$inList) {
                    $buf[] = '<ol>';
                    $inList = true;
                }
                $buf[] = '<li>' . $m[1] . '</li>';
                continue;
            }
            if ($inList) {
                $buf[] = '</ul>';
                $inList = false;
            }
            if (trim($line) === '') {
                $buf[] = '';
                continue;
            }
            $buf[] = '<p>' . $line . '</p>';
        }
        if ($inList) {
            $buf[] = '</ul>';
        }
        $html .= implode("\n", $buf);
    }

    return $html;
}

/** @return array{id:int,title:string}|null */
function mb_workspace_get(): array
{
    $db = mb_db();
    $res = $db->query('SELECT id, title FROM workspace WHERE id = 1 LIMIT 1');
    if ($res instanceof mysqli_result) {
        $row = $res->fetch_assoc();
        $res->free();
        if ($row !== null) {
            return ['id' => 1, 'title' => (string) $row['title']];
        }
    }

    return ['id' => 1, 'title' => 'MindBase — корпоративная база'];
}

function mb_workspace_save(string $title): ?string
{
    $title = trim($title);
    if ($title === '') {
        return 'Укажите название базы.';
    }
    if (mb_strlen($title, 'UTF-8') > 255) {
        return 'Название слишком длинное.';
    }
    $db = mb_db();
    $stmt = $db->prepare('INSERT INTO workspace (id, title) VALUES (1, ?) ON DUPLICATE KEY UPDATE title = VALUES(title)');
    if ($stmt === false) {
        return 'Ошибка сохранения.';
    }
    $stmt->bind_param('s', $title);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? null : 'Ошибка сохранения.';
}

function mb_user_update_profile(int $userId, string $name, ?string $roleTitle): ?string
{
    $name = trim($name);
    if ($name === '') {
        return 'Укажите имя.';
    }
    $nlen = mb_strlen($name, 'UTF-8');
    if ($nlen < 2 || $nlen > 120) {
        return 'Имя — от 2 до 120 символов.';
    }
    $roleTitle = $roleTitle !== null ? trim($roleTitle) : null;
    if ($roleTitle === '') {
        $roleTitle = null;
    }
    if ($roleTitle !== null && mb_strlen($roleTitle, 'UTF-8') > 120) {
        return 'Должность слишком длинная.';
    }
    $db = mb_db();
    $stmt = $db->prepare('UPDATE users SET name = ?, role_title = ? WHERE id = ?');
    if ($stmt === false) {
        return 'Ошибка сохранения.';
    }
    $stmt->bind_param('ssi', $name, $roleTitle, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        return 'Ошибка сохранения.';
    }
    if (!empty($_SESSION['user']) && is_array($_SESSION['user']) && (int) $_SESSION['user']['id'] === $userId) {
        $_SESSION['user']['name'] = $name;
    }

    return null;
}

/** @return array{id:int,name:string,email:string,role_title:?string,created_at:string}|null */
function mb_user_get(int $id): ?array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, name, email, role_title, created_at FROM users WHERE id = ? LIMIT 1');
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
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
        'role_title' => $row['role_title'] !== null ? (string) $row['role_title'] : null,
        'created_at' => (string) $row['created_at'],
    ];
}

/** @return list<array<string,mixed>> */
function mb_categories_list(?int $parentId = null): array
{
    $db = mb_db();
    $vis = mb_sql_category_visible('c.id');
    if ($parentId === null) {
        $artVis = mb_sql_article_catalog_visible('a.category_id');
        $sql = "SELECT c.id, c.parent_id, c.name, c.slug, c.icon, c.description, c.sort_order,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND {$artVis}) AS article_count
            FROM categories c WHERE c.parent_id IS NULL AND c.slug != 'help' AND {$vis}
            ORDER BY c.sort_order, c.name";
        $res = $db->query($sql);
    } else {
        if (!mb_user_can_view_category($parentId)) {
            return [];
        }
        $artVis = mb_sql_article_catalog_visible('a.category_id');
        $stmt = $db->prepare("SELECT c.id, c.parent_id, c.name, c.slug, c.icon, c.description, c.sort_order,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND {$artVis}) AS article_count
            FROM categories c WHERE c.parent_id = ? AND {$vis} ORDER BY c.sort_order, c.name");
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('i', $parentId);
        $stmt->execute();
        $res = $stmt->get_result();
    }
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'icon' => (string) $row['icon'],
            'description' => (string) ($row['description'] ?? ''),
            'sort_order' => (int) $row['sort_order'],
            'article_count' => (int) $row['article_count'],
        ];
    }
    $res->free();

    return $rows;
}

/** @return array<string,mixed>|null */
function mb_category_by_slug(string $slug): ?array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, parent_id, name, slug, icon, description FROM categories WHERE slug = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }
    if (!mb_user_can_view_category((int) $row['id'])) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
        'name' => (string) $row['name'],
        'slug' => (string) $row['slug'],
        'icon' => (string) $row['icon'],
        'description' => (string) ($row['description'] ?? ''),
        'group_ids' => mb_category_group_ids((int) $row['id']),
    ];
}

/** @return list<array{name:string,slug:string}> */
function mb_category_ancestors(int $categoryId): array
{
    $chain = [];
    $currentId = $categoryId;
    while ($currentId > 0) {
        $cat = mb_category_by_id($currentId);
        if ($cat === null || $cat['slug'] === 'help') {
            break;
        }
        array_unshift($chain, [
            'name' => $cat['name'],
            'slug' => $cat['slug'],
        ]);
        $currentId = $cat['parent_id'] ?? 0;
    }

    return $chain;
}

/** @return list<array<string,mixed>> */
function mb_categories_list_all(?int $parentId = null): array
{
    $db = mb_db();
    if ($parentId === null) {
        $sql = 'SELECT c.id, c.parent_id, c.name, c.slug, c.icon, c.description, c.sort_order,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id) AS article_count
            FROM categories c WHERE c.parent_id IS NULL ORDER BY c.sort_order, c.name';
        $res = $db->query($sql);
    } else {
        $stmt = $db->prepare('SELECT c.id, c.parent_id, c.name, c.slug, c.icon, c.description, c.sort_order,
            (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id) AS article_count
            FROM categories c WHERE c.parent_id = ? ORDER BY c.sort_order, c.name');
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('i', $parentId);
        $stmt->execute();
        $res = $stmt->get_result();
    }
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'icon' => (string) $row['icon'],
            'description' => (string) ($row['description'] ?? ''),
            'sort_order' => (int) $row['sort_order'],
            'article_count' => (int) $row['article_count'],
            'group_ids' => mb_category_group_ids((int) $row['id']),
        ];
    }
    $res->free();

    return $rows;
}

/** @return list<array<string,mixed>> */
function mb_category_tree(): array
{
    $db = mb_db();
    $vis = mb_sql_category_visible('id');
    $res = $db->query("SELECT id, parent_id, name, slug FROM categories WHERE slug != 'help' AND {$vis} ORDER BY sort_order, name");
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $all = [];
    while ($row = $res->fetch_assoc()) {
        $all[(int) $row['id']] = [
            'id' => (int) $row['id'],
            'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'children' => [],
        ];
    }
    $res->free();
    $roots = [];
    foreach ($all as $id => &$node) {
        $pid = $node['parent_id'];
        if ($pid !== null && isset($all[$pid])) {
            $all[$pid]['children'][] = &$node;
        } else {
            $roots[] = &$node;
        }
    }
    unset($node);

    return $roots;
}

function mb_render_category_tree(array $nodes, int $depth = 0, ?string $activeSlug = null): string
{
    if ($nodes === []) {
        return '';
    }
    $html = $depth === 0 ? '<ul class="section-tree">' : '<ul class="section-tree section-tree--nested">';
    foreach ($nodes as $node) {
        if ($node['slug'] === 'help') {
            continue;
        }
        $isActive = $activeSlug !== null && $node['slug'] === $activeSlug;
        $linkClass = 'section-tree__link' . ($isActive ? ' is-active' : '');
        $html .= '<li class="section-tree__item">';
        $count = mb_category_article_count_recursive((int) $node['id']);
        $html .= '<a href="category.php?slug=' . rawurlencode($node['slug']) . '" class="' . $linkClass . '">';
        $html .= '<span class="section-tree__label">' . mb_h($node['name']) . '</span>';
        $html .= '<span class="section-tree__count">' . (int) $count . '</span>';
        $html .= '</a>';
        if ($node['children'] !== []) {
            $html .= mb_render_category_tree($node['children'], $depth + 1, $activeSlug);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

/** SQL: статьи каталога, доступные текущему пользователю. */
function mb_sql_article_catalog_visible(string $categoryIdExpr = 'a.category_id'): string
{
    return '(a.is_help = 0 AND ' . mb_sql_category_visible($categoryIdExpr) . ')';
}

/** @param array<string,mixed> $row */
function mb_document_row_map(array $row): array
{
    $stored = $row['stored_name'] !== null && $row['stored_name'] !== '' ? (string) $row['stored_name'] : '';
    $sizeBytes = (int) $row['size_bytes'];
    $hasFile = false;
    if ($stored !== '') {
        $path = mb_storage_documents_dir() . '/' . basename($stored);
        if (is_file($path)) {
            $hasFile = true;
            $sizeBytes = (int) filesize($path);
        }
    }

    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'file_type' => (string) $row['file_type'],
        'stored_name' => $stored !== '' ? $stored : null,
        'size_bytes' => $sizeBytes,
        'owner_label' => (string) $row['owner_label'],
        'folder_path' => (string) $row['folder_path'],
        'updated_at' => (string) $row['updated_at'],
        'has_file' => $hasFile,
    ];
}

/** @return array{articles:int,categories:int,tags:int,updated_today:int} */
function mb_catalog_stats(): array
{
    $db = mb_db();
    $vis = mb_sql_article_catalog_visible('a.category_id');
    $catVis = mb_sql_category_visible('c.id');
    $articles = 0;
    $categories = 0;
    $tags = 0;
    $today = 0;
    $r = $db->query("SELECT COUNT(*) AS c FROM articles a WHERE {$vis}");
    if ($r instanceof mysqli_result) {
        $articles = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query("SELECT COUNT(*) AS c FROM categories c WHERE c.slug != 'help' AND {$catVis}");
    if ($r instanceof mysqli_result) {
        $categories = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM tags');
    if ($r instanceof mysqli_result) {
        $tags = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query("SELECT COUNT(*) AS c FROM articles a WHERE {$vis} AND DATE(a.updated_at) = CURDATE()");
    if ($r instanceof mysqli_result) {
        $today = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }

    return ['articles' => $articles, 'categories' => $categories, 'tags' => $tags, 'updated_today' => $today];
}

/** @return list<array<string,mixed>> */
function mb_articles_recent(int $limit = 5, bool $helpOnly = false): array
{
    $db = mb_db();
    $help = $helpOnly ? 1 : 0;
    $vis = mb_sql_category_visible('a.category_id');
    $stmt = $db->prepare(
        "SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.is_help = ? AND (a.is_help = 1 OR {$vis})
        ORDER BY a.updated_at DESC LIMIT ?"
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('ii', $help, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = mb_article_row_map($row);
    }
    $stmt->close();

    return $rows;
}

/** @param array<string,mixed> $row */
function mb_article_row_map(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'slug' => (string) $row['slug'],
        'excerpt' => (string) ($row['excerpt'] ?? ''),
        'updated_at' => (string) $row['updated_at'],
        'category_name' => (string) ($row['category_name'] ?? ''),
        'category_slug' => (string) ($row['category_slug'] ?? ''),
        'author_name' => (string) ($row['author_name'] ?? ''),
        'is_help' => !empty($row['is_help']),
    ];
}

/** @return list<array<string,mixed>> */
function mb_articles_by_category(int $categoryId, int $limit = 50): array
{
    $db = mb_db();
    if (!mb_user_can_view_category($categoryId)) {
        return [];
    }
    $stmt = $db->prepare(
        'SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.category_id = ?
        ORDER BY a.updated_at DESC LIMIT ?'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('ii', $categoryId, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = mb_article_row_map($row);
    }
    $stmt->close();

    return $rows;
}

/** @return array<string,mixed>|null */
function mb_article_by_slug(string $slug): ?array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT a.id, a.category_id, a.author_id, a.title, a.slug, a.excerpt, a.body, a.is_help,
        a.views_count, a.created_at, a.updated_at,
        c.name AS category_name, c.slug AS category_slug,
        u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.slug = ? LIMIT 1'
    );
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }
    $isHelp = (bool) $row['is_help'];
    if (!$isHelp && !mb_user_can_view_category((int) $row['category_id'])) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'category_id' => (int) $row['category_id'],
        'author_id' => (int) $row['author_id'],
        'title' => (string) $row['title'],
        'slug' => (string) $row['slug'],
        'excerpt' => (string) ($row['excerpt'] ?? ''),
        'body' => (string) $row['body'],
        'is_help' => (bool) $row['is_help'],
        'views_count' => (int) $row['views_count'],
        'created_at' => (string) $row['created_at'],
        'updated_at' => (string) $row['updated_at'],
        'category_name' => (string) $row['category_name'],
        'category_slug' => (string) $row['category_slug'],
        'author_name' => (string) $row['author_name'],
        'tags' => mb_article_tags((int) $row['id']),
    ];
}

/** @return list<string> */
function mb_article_tags(int $articleId): array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT t.name FROM tags t
        JOIN article_tags at ON at.tag_id = t.id
        WHERE at.article_id = ? ORDER BY t.name'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $articleId);
    $stmt->execute();
    $res = $stmt->get_result();
    $tags = [];
    while ($row = $res->fetch_assoc()) {
        $tags[] = (string) $row['name'];
    }
    $stmt->close();

    return $tags;
}

function mb_article_record_view(int $articleId, ?int $userId): void
{
    $db = mb_db();
    $stmt = $db->prepare('INSERT INTO article_views (article_id, user_id) VALUES (?, ?)');
    if ($stmt !== false) {
        $stmt->bind_param('ii', $articleId, $userId);
        $stmt->execute();
        $stmt->close();
    }
    $db->query('UPDATE articles SET views_count = views_count + 1 WHERE id = ' . (int) $articleId);
}

/** @return list<array<string,mixed>> */
function mb_search_articles(string $q, int $limit = 30, ?int $categoryId = null): array
{
    $q = trim($q);
    if ($q === '') {
        return [];
    }
    $db = mb_db();
    $vis = mb_sql_category_visible('a.category_id');
    $like = '%' . $db->real_escape_string($q) . '%';
    $catFilter = '';
    if ($categoryId !== null && $categoryId > 0) {
        $catFilter = ' AND a.category_id = ' . (int) $categoryId;
    }
    $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE (a.is_help = 1 OR {$vis})
        AND (a.title LIKE '{$like}' OR a.excerpt LIKE '{$like}' OR a.body LIKE '{$like}')
        {$catFilter}
        ORDER BY a.updated_at DESC LIMIT " . (int) $limit;
    $res = $db->query($sql);
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $mapped = mb_article_row_map($row);
        $mapped['snippet'] = mb_search_snippet((string) ($row['excerpt'] ?? ''), $q);
        $rows[] = $mapped;
    }
    $res->free();

    return $rows;
}

function mb_search_snippet(string $excerpt, string $q): string
{
    if ($excerpt !== '') {
        return $excerpt;
    }

    return 'Совпадение в тексте статьи';
}

function mb_search_highlight(string $text, string $q): string
{
    $text = mb_h($text);
    $words = preg_split('/\s+/u', trim($q)) ?: [];
    foreach ($words as $w) {
        if (mb_strlen($w, 'UTF-8') < 2) {
            continue;
        }
        $pattern = '/' . preg_quote($w, '/') . '/iu';
        $text = preg_replace($pattern, '<mark>$0</mark>', $text) ?? $text;
    }

    return $text;
}

/** @return array{articles:int,categories:int,team:int,views_week:int} */
function mb_dashboard_stats(): array
{
    $db = mb_db();
    $vis = mb_sql_article_catalog_visible('a.category_id');
    $articles = 0;
    $categories = 0;
    $team = 0;
    $views = 0;
    $r = $db->query("SELECT COUNT(*) AS c FROM articles a WHERE {$vis}");
    if ($r instanceof mysqli_result) {
        $articles = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query("SELECT COUNT(DISTINCT a.category_id) AS c FROM articles a WHERE {$vis}");
    if ($r instanceof mysqli_result) {
        $categories = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM users');
    if ($r instanceof mysqli_result) {
        $team = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query(
        "SELECT COUNT(*) AS c FROM article_views v
        INNER JOIN articles a ON a.id = v.article_id
        WHERE {$vis} AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    if ($r instanceof mysqli_result) {
        $views = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }

    return ['articles' => $articles, 'categories' => $categories, 'team' => $team, 'views_week' => $views];
}

/** @return list<array{title:string,meta:string,url:string}> */
function mb_activity_feed(int $limit = 6): array
{
    $items = [];
    foreach (mb_articles_recent($limit) as $a) {
        $items[] = [
            'title' => $a['author_name'] . ' обновил(а) «' . $a['title'] . '»',
            'meta' => $a['category_name'] . ' · ' . mb_format_datetime($a['updated_at']),
            'url' => 'article.php?slug=' . rawurlencode($a['slug']),
        ];
    }

    return $items;
}

function mb_article_save(
    ?int $id,
    int $authorId,
    int $categoryId,
    string $title,
    string $excerpt,
    string $body,
    bool $isHelp = false
): array {
    if (!mb_can_write()) {
        return ['error' => 'Нет прав на редактирование.'];
    }
    if (!$isHelp && !mb_user_can_view_category($categoryId)) {
        return ['error' => 'Нет доступа к выбранному разделу.'];
    }
    if ($isHelp && !mb_is_admin()) {
        return ['error' => 'Справку может редактировать только администратор.'];
    }
    $title = trim($title);
    $excerpt = trim($excerpt);
    $body = trim($body);
    if ($title === '') {
        return ['error' => 'Укажите заголовок.'];
    }
    if ($body === '') {
        return ['error' => 'Добавьте текст статьи.'];
    }
    $db = mb_db();
    $baseSlug = mb_unique_slug($db, mb_slugify($title));
    $help = $isHelp ? 1 : 0;

    if ($id === null) {
        $stmt = $db->prepare(
            'INSERT INTO articles (category_id, author_id, title, slug, excerpt, body, is_help)
            VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if ($stmt === false) {
            return ['error' => 'Ошибка сохранения.'];
        }
        $stmt->bind_param('iissssi', $categoryId, $authorId, $title, $baseSlug, $excerpt, $body, $help);
        if (!$stmt->execute()) {
            $stmt->close();

            return ['error' => 'Ошибка сохранения.'];
        }
        $newId = (int) $stmt->insert_id;
        $stmt->close();

        return ['id' => $newId, 'slug' => $baseSlug];
    }

    $existing = mb_article_by_id($id);
    if ($existing === null) {
        return ['error' => 'Статья не найдена.'];
    }
    $slug = $existing['slug'];
    if (mb_slugify($title) !== mb_slugify($existing['title'])) {
        $slug = mb_unique_slug($db, mb_slugify($title));
    }
    $stmt = $db->prepare(
        'UPDATE articles SET category_id = ?, title = ?, slug = ?, excerpt = ?, body = ?, is_help = ? WHERE id = ?'
    );
    if ($stmt === false) {
        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->bind_param('issssii', $categoryId, $title, $slug, $excerpt, $body, $help, $id);
    if (!$stmt->execute()) {
        $stmt->close();

        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->close();

    return ['id' => $id, 'slug' => $slug];
}

/** @return array<string,mixed>|null */
function mb_article_by_id(int $id): ?array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT slug, title FROM articles WHERE id = ? LIMIT 1');
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

    return ['id' => $id, 'slug' => (string) $row['slug'], 'title' => (string) $row['title']];
}

/** @return list<array<string,mixed>> */
function mb_documents_list(): array
{
    $db = mb_db();
    $vis = mb_sql_document_visible('d.id');
    $res = $db->query("SELECT d.id, d.title, d.file_type, d.stored_name, d.size_bytes, d.owner_label, d.folder_path, d.updated_at
        FROM documents d WHERE {$vis} ORDER BY d.updated_at DESC");
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = mb_document_row_map($row);
    }
    $res->free();

    return $rows;
}

/** @return array<string,mixed>|null */
function mb_document_by_id(int $id): ?array
{
    $db = mb_db();
    $vis = mb_sql_document_visible('d.id');
    $stmt = $db->prepare("SELECT d.id, d.title, d.file_type, d.stored_name, d.mime_type, d.size_bytes,
        d.owner_label, d.folder_path, d.updated_at FROM documents d WHERE d.id = ? AND {$vis} LIMIT 1");
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
        'file_type' => (string) $row['file_type'],
        'stored_name' => (string) ($row['stored_name'] ?? ''),
        'mime_type' => (string) ($row['mime_type'] ?? 'application/octet-stream'),
        'size_bytes' => (int) $row['size_bytes'],
        'owner_label' => (string) $row['owner_label'],
        'folder_path' => (string) $row['folder_path'],
        'updated_at' => (string) $row['updated_at'],
    ];
}

function mb_document_file_path(array $doc): ?string
{
    if ($doc['stored_name'] === '') {
        return null;
    }
    $path = mb_storage_documents_dir() . '/' . basename($doc['stored_name']);
    if (!is_file($path)) {
        return null;
    }

    return $path;
}

function mb_document_upload(int $userId, array $file, string $title, string $ownerLabel, string $folderPath, array $groupIds): array
{
    if (!mb_can_write()) {
        return ['error' => 'Нет прав на загрузку.'];
    }
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Ошибка загрузки файла.'];
    }
    $maxSize = 15 * 1024 * 1024;
    if ((int) ($file['size'] ?? 0) > $maxSize) {
        return ['error' => 'Файл больше 15 МБ.'];
    }
    $title = trim($title);
    if ($title === '') {
        return ['error' => 'Укажите название.'];
    }
    $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'txt', 'xlsx', 'csv', 'md'];
    if (!in_array($ext, $allowed, true)) {
        return ['error' => 'Допустимые форматы: PDF, DOC, DOCX, TXT, XLSX, CSV, MD.'];
    }
    $mimeMap = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain',
        'md' => 'text/markdown',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv' => 'text/csv',
    ];
    if (!mb_storage_documents_writable()) {
        return [
            'error' => 'Нет прав на запись в storage/documents. '
                . 'В Docker: docker compose exec php chown -R www-data:www-data /var/www/html/storage',
        ];
    }
    $stored = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = mb_storage_documents_dir() . '/' . $stored;
    if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
        return ['error' => 'Не удалось сохранить файл. Проверьте права на каталог storage/documents.'];
    }
    $db = mb_db();
    $size = (int) filesize($dest);
    $ftype = strtoupper($ext);
    $folderPath = trim($folderPath) !== '' ? trim($folderPath) : '/';
    if ($folderPath[0] !== '/') {
        $folderPath = '/' . $folderPath;
    }
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';
    $stmt = $db->prepare('INSERT INTO documents (title, file_type, stored_name, mime_type, size_bytes, owner_label, folder_path, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt === false) {
        @unlink($dest);

        return ['error' => 'Ошибка БД.'];
    }
    $stmt->bind_param('ssssissi', $title, $ftype, $stored, $mime, $size, $ownerLabel, $folderPath, $userId);
    if (!$stmt->execute()) {
        @unlink($dest);
        $stmt->close();

        return ['error' => 'Ошибка БД.'];
    }
    $docId = (int) $stmt->insert_id;
    $stmt->close();
    mb_document_set_groups($docId, $groupIds);

    return ['id' => $docId];
}

function mb_document_delete(int $id): ?string
{
    if (!mb_can_write()) {
        return 'Нет прав.';
    }
    $doc = mb_document_by_id($id);
    if ($doc === null && mb_is_admin()) {
        $db = mb_db();
        $stmt = $db->prepare('SELECT stored_name FROM documents WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row === null) {
            return 'Документ не найден.';
        }
        $doc = ['stored_name' => (string) ($row['stored_name'] ?? '')];
    }
    if ($doc === null) {
        return 'Документ не найден.';
    }
    $path = $doc['stored_name'] !== '' ? mb_storage_documents_dir() . '/' . basename($doc['stored_name']) : null;
    $db = mb_db();
    $stmt = $db->prepare('DELETE FROM documents WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    if ($path !== null && is_file($path)) {
        @unlink($path);
    }

    return null;
}

/** @param list<int> $groupIds */
function mb_category_save(
    ?int $id,
    ?int $parentId,
    string $name,
    string $icon,
    string $description,
    int $sortOrder,
    array $groupIds
): array {
    if (!mb_can_write()) {
        return ['error' => 'Нет прав на редактирование разделов.'];
    }
    $name = trim($name);
    if ($name === '') {
        return ['error' => 'Укажите название раздела.'];
    }
    $db = mb_db();
    $slug = mb_unique_slug($db, mb_slugify($name), 'categories');
    if ($id !== null) {
        $existing = mb_category_by_id($id);
        if ($existing === null && !mb_is_admin()) {
            return ['error' => 'Раздел не найден.'];
        }
        if ($existing !== null) {
            $slug = $existing['slug'];
            if (mb_slugify($name) !== mb_slugify($existing['name'])) {
                $slug = mb_unique_slug($db, mb_slugify($name), 'categories');
            }
        }
        $stmt = $db->prepare('UPDATE categories SET parent_id = ?, name = ?, slug = ?, icon = ?, description = ?, sort_order = ? WHERE id = ?');
        if ($stmt === false) {
            return ['error' => 'Ошибка сохранения.'];
        }
        $stmt->bind_param('issssii', $parentId, $name, $slug, $icon, $description, $sortOrder, $id);
        $stmt->execute();
        $stmt->close();
        if (mb_is_admin()) {
            mb_category_set_groups($id, $groupIds);
        }

        return ['id' => $id, 'slug' => $slug];
    }
    $stmt = $db->prepare('INSERT INTO categories (parent_id, name, slug, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt === false) {
        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->bind_param('issssi', $parentId, $name, $slug, $icon, $description, $sortOrder);
    $stmt->execute();
    $newId = (int) $stmt->insert_id;
    $stmt->close();
    if (mb_is_admin()) {
        mb_category_set_groups($newId, $groupIds);
    }

    return ['id' => $newId, 'slug' => $slug];
}

/** @return array<string,mixed>|null */
function mb_category_by_id(int $id): ?array
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT id, parent_id, name, slug, icon, description, sort_order FROM categories WHERE id = ? LIMIT 1');
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
        'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
        'name' => (string) $row['name'],
        'slug' => (string) $row['slug'],
        'icon' => (string) $row['icon'],
        'description' => (string) ($row['description'] ?? ''),
        'sort_order' => (int) $row['sort_order'],
        'group_ids' => mb_category_group_ids((int) $row['id']),
    ];
}

function mb_category_delete(int $id): ?string
{
    if (!mb_can_write()) {
        return 'Нет прав.';
    }
    $cat = mb_category_by_id($id);
    if ($cat === null) {
        return 'Раздел не найден.';
    }
    $db = mb_db();
    $children = mb_categories_list_all($id);
    if ($children !== []) {
        return 'Сначала удалите подразделы.';
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS c FROM articles WHERE category_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $count = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
    if ($count > 0 && !mb_is_admin()) {
        return 'В разделе есть статьи. Удаление доступно только администратору.';
    }
    if ($count > 0 && mb_is_admin()) {
        $db->query('DELETE FROM articles WHERE category_id = ' . (int) $id);
    }
    $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    return null;
}

/** @return array{files:int,folders:int,bytes:int} */
function mb_documents_stats(): array
{
    $docs = mb_documents_list();
    $bytes = 0;
    $folders = [];
    foreach ($docs as $d) {
        $bytes += (int) $d['size_bytes'];
        $folders[$d['folder_path']] = true;
    }

    return [
        'files' => count($docs),
        'folders' => count($folders),
        'bytes' => $bytes,
    ];
}

/** @return list<string> */
function mb_document_folders(): array
{
    $db = mb_db();
    $vis = mb_sql_document_visible('d.id');
    $res = $db->query("SELECT DISTINCT d.folder_path FROM documents d WHERE {$vis} ORDER BY d.folder_path");
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $paths = [];
    while ($row = $res->fetch_assoc()) {
        $paths[] = (string) $row['folder_path'];
    }
    $res->free();

    return $paths;
}

/** @return list<array<string,mixed>> */
function mb_courses_list(int $userId): array
{
    $db = mb_db();
    $res = $db->query(
        'SELECT c.id, c.title, c.description, c.course_type, c.duration_minutes, c.author_label,
        COALESCE(p.progress_percent, 0) AS progress_percent
        FROM courses c
        LEFT JOIN course_progress p ON p.course_id = c.id AND p.user_id = ' . (int) $userId . '
        ORDER BY c.sort_order, c.id'
    );
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'description' => (string) $row['description'],
            'course_type' => (string) $row['course_type'],
            'duration_minutes' => (int) $row['duration_minutes'],
            'author_label' => (string) $row['author_label'],
            'progress_percent' => (int) $row['progress_percent'],
        ];
    }
    $res->free();

    return $rows;
}

function mb_course_update_progress(int $userId, int $courseId, int $percent): ?string
{
    $percent = max(0, min(100, $percent));
    $db = mb_db();
    $stmt = $db->prepare(
        'INSERT INTO course_progress (user_id, course_id, progress_percent) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE progress_percent = VALUES(progress_percent)'
    );
    if ($stmt === false) {
        return 'Ошибка сохранения прогресса.';
    }
    $stmt->bind_param('iii', $userId, $courseId, $percent);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? null : 'Ошибка сохранения прогресса.';
}

/** @return array<string,mixed>|null */
function mb_course_by_id(int $courseId, int $userId): ?array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT c.id, c.title, c.description, c.course_type, c.duration_minutes, c.author_label,
        COALESCE(p.progress_percent, 0) AS progress_percent
        FROM courses c
        LEFT JOIN course_progress p ON p.course_id = c.id AND p.user_id = ?
        WHERE c.id = ? LIMIT 1'
    );
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('ii', $userId, $courseId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'course_type' => (string) $row['course_type'],
        'duration_minutes' => (int) $row['duration_minutes'],
        'author_label' => (string) $row['author_label'],
        'progress_percent' => (int) $row['progress_percent'],
    ];
}

/** @return list<array<string,mixed>> */
function mb_course_lessons_list(int $courseId, int $userId): array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT l.id, l.title, l.description, l.article_slug, l.duration_minutes, l.sort_order,
        (clp.lesson_id IS NOT NULL) AS is_completed
        FROM course_lessons l
        LEFT JOIN course_lesson_progress clp ON clp.lesson_id = l.id AND clp.user_id = ?
        WHERE l.course_id = ?
        ORDER BY l.sort_order, l.id'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('ii', $userId, $courseId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'description' => (string) $row['description'],
            'article_slug' => $row['article_slug'] !== null ? (string) $row['article_slug'] : null,
            'duration_minutes' => (int) $row['duration_minutes'],
            'is_completed' => (bool) $row['is_completed'],
        ];
    }
    $stmt->close();

    return $rows;
}

function mb_course_lesson_complete(int $userId, int $lessonId): ?string
{
    $db = mb_db();
    $stmt = $db->prepare('SELECT course_id FROM course_lessons WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        return 'Урок не найден.';
    }
    $stmt->bind_param('i', $lessonId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return 'Урок не найден.';
    }
    $courseId = (int) $row['course_id'];

    $stmt = $db->prepare(
        'INSERT IGNORE INTO course_lesson_progress (user_id, lesson_id) VALUES (?, ?)'
    );
    if ($stmt === false) {
        return 'Ошибка сохранения.';
    }
    $stmt->bind_param('ii', $userId, $lessonId);
    $stmt->execute();
    $stmt->close();

    mb_course_sync_progress_from_lessons($userId, $courseId);

    return null;
}

function mb_course_sync_progress_from_lessons(int $userId, int $courseId): void
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT COUNT(*) AS total,
        (SELECT COUNT(*) FROM course_lesson_progress clp
         INNER JOIN course_lessons l ON l.id = clp.lesson_id
         WHERE l.course_id = ? AND clp.user_id = ?) AS done
        FROM course_lessons WHERE course_id = ?'
    );
    if ($stmt === false) {
        return;
    }
    $stmt->bind_param('iii', $courseId, $userId, $courseId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $total = (int) ($row['total'] ?? 0);
    $done = (int) ($row['done'] ?? 0);
    $percent = $total > 0 ? (int) round($done / $total * 100) : 0;
    mb_course_update_progress($userId, $courseId, $percent);
}

/** @return array{courses:int,lessons:int,avg_progress:int} */
function mb_learning_stats(int $userId): array
{
    $db = mb_db();
    $courses = 0;
    $r = $db->query('SELECT COUNT(*) AS c FROM courses');
    if ($r instanceof mysqli_result) {
        $courses = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $lessons = 0;
    $r2 = $db->query('SELECT COUNT(*) AS c FROM course_lessons');
    if ($r2 instanceof mysqli_result) {
        $lessons = (int) ($r2->fetch_assoc()['c'] ?? 0);
        $r2->free();
    }
    $avg = 0;
    $stmt = $db->prepare(
        'SELECT COALESCE(AVG(COALESCE(p.progress_percent, 0)), 0) AS a
        FROM courses c
        LEFT JOIN course_progress p ON p.course_id = c.id AND p.user_id = ?'
    );
    if ($stmt !== false) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $avg = (int) round((float) ($row['a'] ?? 0));
        $stmt->close();
    }

    return ['courses' => $courses, 'lessons' => $lessons, 'avg_progress' => $avg];
}

function mb_course_type_label(string $type): string
{
    return match ($type) {
        'video' => 'Видео',
        'doc' => 'Текст',
        'mix' => 'Смешанный',
        'quiz' => 'Тест',
        default => 'Курс',
    };
}

function mb_course_type_class(string $type): string
{
    return match ($type) {
        'video' => 'cabinet-tag--video',
        'doc' => 'cabinet-tag--doc',
        'mix' => 'cabinet-tag--mix',
        'quiz' => 'cabinet-tag--quiz',
        default => '',
    };
}

/** @return list<string> */
function mb_course_types(): array
{
    return ['video', 'doc', 'mix', 'quiz'];
}

/** @return array<string,mixed>|null */
function mb_course_get(int $courseId): ?array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT id, title, description, course_type, duration_minutes, author_label, sort_order
        FROM courses WHERE id = ? LIMIT 1'
    );
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'course_type' => (string) $row['course_type'],
        'duration_minutes' => (int) $row['duration_minutes'],
        'author_label' => (string) $row['author_label'],
        'sort_order' => (int) $row['sort_order'],
    ];
}

/** @return list<array{id:int,title:string,description:string,article_slug:?string,duration_minutes:int,sort_order:int}> */
function mb_course_lessons_admin_list(int $courseId): array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT id, title, description, article_slug, duration_minutes, sort_order
        FROM course_lessons WHERE course_id = ? ORDER BY sort_order, id'
    );
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'description' => (string) $row['description'],
            'article_slug' => $row['article_slug'] !== null ? (string) $row['article_slug'] : null,
            'duration_minutes' => (int) $row['duration_minutes'],
            'sort_order' => (int) $row['sort_order'],
        ];
    }
    $stmt->close();

    return $rows;
}

/** @return array{id:int,title:string,description:string,article_slug:?string,duration_minutes:int,sort_order:int,course_id:int}|null */
function mb_course_lesson_get(int $lessonId): ?array
{
    $db = mb_db();
    $stmt = $db->prepare(
        'SELECT id, course_id, title, description, article_slug, duration_minutes, sort_order
        FROM course_lessons WHERE id = ? LIMIT 1'
    );
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('i', $lessonId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row === null) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'course_id' => (int) $row['course_id'],
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'article_slug' => $row['article_slug'] !== null ? (string) $row['article_slug'] : null,
        'duration_minutes' => (int) $row['duration_minutes'],
        'sort_order' => (int) $row['sort_order'],
    ];
}

/** @return list<array{slug:string,title:string}> */
function mb_article_slug_options(): array
{
    $db = mb_db();
    $vis = mb_sql_article_catalog_visible('a.category_id');
    $res = $db->query("SELECT a.slug, a.title FROM articles a WHERE {$vis} ORDER BY a.title");
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'slug' => (string) $row['slug'],
            'title' => (string) $row['title'],
        ];
    }
    $res->free();

    return $rows;
}

function mb_course_sync_duration(int $courseId): void
{
    $db = mb_db();
    $stmt = $db->prepare(
        'UPDATE courses SET duration_minutes = (
            SELECT COALESCE(SUM(l.duration_minutes), 0) FROM course_lessons l WHERE l.course_id = ?
        ) WHERE id = ?'
    );
    if ($stmt === false) {
        return;
    }
    $stmt->bind_param('ii', $courseId, $courseId);
    $stmt->execute();
    $stmt->close();
}

function mb_course_save(
    ?int $id,
    string $title,
    string $description,
    string $courseType,
    int $durationMinutes,
    string $authorLabel,
    int $sortOrder
): array {
    if (!mb_can_write()) {
        return ['error' => 'Нет прав на редактирование курсов.'];
    }
    $title = trim($title);
    if ($title === '') {
        return ['error' => 'Укажите название курса.'];
    }
    if (!in_array($courseType, mb_course_types(), true)) {
        $courseType = 'doc';
    }
    $durationMinutes = max(0, min(9999, $durationMinutes));
    $sortOrder = max(0, $sortOrder);
    $authorLabel = trim($authorLabel);
    $description = trim($description);
    $db = mb_db();
    if ($id !== null && $id > 0) {
        $existing = mb_course_get($id);
        if ($existing === null) {
            return ['error' => 'Курс не найден.'];
        }
        $stmt = $db->prepare(
            'UPDATE courses SET title = ?, description = ?, course_type = ?, duration_minutes = ?, author_label = ?, sort_order = ? WHERE id = ?'
        );
        if ($stmt === false) {
            return ['error' => 'Ошибка сохранения.'];
        }
        $stmt->bind_param('sssisii', $title, $description, $courseType, $durationMinutes, $authorLabel, $sortOrder, $id);
        $stmt->execute();
        $stmt->close();

        return ['id' => $id];
    }
    $stmt = $db->prepare(
        'INSERT INTO courses (title, description, course_type, duration_minutes, author_label, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
    );
    if ($stmt === false) {
        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->bind_param('sssisi', $title, $description, $courseType, $durationMinutes, $authorLabel, $sortOrder);
    $stmt->execute();
    $newId = (int) $stmt->insert_id;
    $stmt->close();

    return ['id' => $newId];
}

function mb_course_delete(int $id): ?string
{
    if (!mb_can_write()) {
        return 'Нет прав на удаление курсов.';
    }
    if (mb_course_get($id) === null) {
        return 'Курс не найден.';
    }
    $db = mb_db();
    $stmt = $db->prepare('DELETE FROM courses WHERE id = ?');
    if ($stmt === false) {
        return 'Ошибка удаления.';
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    return null;
}

function mb_course_lesson_save(
    ?int $id,
    int $courseId,
    string $title,
    string $description,
    ?string $articleSlug,
    int $durationMinutes,
    int $sortOrder
): array {
    if (!mb_can_write()) {
        return ['error' => 'Нет прав на редактирование уроков.'];
    }
    if (mb_course_get($courseId) === null) {
        return ['error' => 'Курс не найден.'];
    }
    $title = trim($title);
    if ($title === '') {
        return ['error' => 'Укажите название урока.'];
    }
    $description = trim($description);
    $articleSlug = $articleSlug !== null ? trim($articleSlug) : '';
    if ($articleSlug === '') {
        $articleSlug = null;
    } elseif (mb_article_by_slug($articleSlug) === null) {
        return ['error' => 'Статья с указанным slug не найдена.'];
    }
    $durationMinutes = max(1, min(999, $durationMinutes));
    $sortOrder = max(0, $sortOrder);
    $db = mb_db();
    if ($id !== null && $id > 0) {
        $existing = mb_course_lesson_get($id);
        if ($existing === null || (int) $existing['course_id'] !== $courseId) {
            return ['error' => 'Урок не найден.'];
        }
        $stmt = $db->prepare(
            'UPDATE course_lessons SET title = ?, description = ?, article_slug = ?, duration_minutes = ?, sort_order = ? WHERE id = ?'
        );
        if ($stmt === false) {
            return ['error' => 'Ошибка сохранения.'];
        }
        $stmt->bind_param('sssiii', $title, $description, $articleSlug, $durationMinutes, $sortOrder, $id);
        $stmt->execute();
        $stmt->close();
        mb_course_sync_duration($courseId);

        return ['id' => $id, 'course_id' => $courseId];
    }
    $stmt = $db->prepare(
        'INSERT INTO course_lessons (course_id, title, description, article_slug, duration_minutes, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
    );
    if ($stmt === false) {
        return ['error' => 'Ошибка сохранения.'];
    }
    $stmt->bind_param('isssii', $courseId, $title, $description, $articleSlug, $durationMinutes, $sortOrder);
    $stmt->execute();
    $newId = (int) $stmt->insert_id;
    $stmt->close();
    mb_course_sync_duration($courseId);

    return ['id' => $newId, 'course_id' => $courseId];
}

function mb_course_lesson_delete(int $id): ?string
{
    if (!mb_can_write()) {
        return 'Нет прав на удаление уроков.';
    }
    $lesson = mb_course_lesson_get($id);
    if ($lesson === null) {
        return 'Урок не найден.';
    }
    $courseId = (int) $lesson['course_id'];
    $db = mb_db();
    $stmt = $db->prepare('DELETE FROM course_lessons WHERE id = ?');
    if ($stmt === false) {
        return 'Ошибка удаления.';
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    mb_course_sync_duration($courseId);

    return null;
}

function mb_format_duration(int $minutes): string
{
    if ($minutes < 60) {
        return $minutes . ' мин';
    }
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;

    return $m > 0 ? ($h . ' ч ' . $m . ' мин') : ($h . ' ч');
}

/** @return list<array{id:int,title:string,slug:string,body:string,updated_at:string}> */
function mb_export_articles(): array
{
    $db = mb_db();
    $vis = mb_sql_category_visible('a.category_id');
    $res = $db->query("SELECT a.id, a.title, a.slug, a.body, a.updated_at FROM articles a WHERE {$vis} ORDER BY a.id");
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'slug' => (string) $row['slug'],
            'body' => (string) $row['body'],
            'updated_at' => (string) $row['updated_at'],
        ];
    }
    $res->free();

    return $rows;
}

function mb_category_article_count_recursive(int $categoryId): int
{
    if (!mb_user_can_view_category($categoryId)) {
        return 0;
    }
    $db = mb_db();
    $total = 0;
    $stmt = $db->prepare('SELECT COUNT(*) AS c FROM articles a WHERE a.category_id = ? AND a.is_help = 0');
    if ($stmt !== false) {
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $total += (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();
    }
    foreach (mb_categories_list($categoryId) as $ch) {
        $total += mb_category_article_count_recursive((int) $ch['id']);
    }

    return $total;
}
