<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

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

    return [
        'id' => (int) $row['id'],
        'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
        'name' => (string) $row['name'],
        'slug' => (string) $row['slug'],
        'icon' => (string) $row['icon'],
        'description' => (string) ($row['description'] ?? ''),
    ];
}

/** @return list<array<string,mixed>> */
function mb_category_tree(): array
{
    $db = mb_db();
    $res = $db->query('SELECT id, parent_id, name, slug FROM categories ORDER BY sort_order, name');
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

function mb_render_category_tree(array $nodes, int $depth = 0): string
{
    if ($nodes === []) {
        return '';
    }
    $html = $depth === 0 ? '<ul class="cabinet-tree">' : '<ul>';
    foreach ($nodes as $node) {
        if ($node['slug'] === 'help') {
            continue;
        }
        $html .= '<li>';
        $html .= '<a href="category.php?slug=' . rawurlencode($node['slug']) . '">' . mb_h($node['name']) . '</a>';
        $html .= mb_render_category_tree($node['children'], $depth + 1);
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

/** @return array{articles:int,categories:int,tags:int,updated_today:int} */
function mb_catalog_stats(): array
{
    $db = mb_db();
    $articles = 0;
    $categories = 0;
    $tags = 0;
    $today = 0;
    $r = $db->query('SELECT COUNT(*) AS c FROM articles WHERE is_help = 0');
    if ($r instanceof mysqli_result) {
        $articles = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM categories');
    if ($r instanceof mysqli_result) {
        $categories = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM tags');
    if ($r instanceof mysqli_result) {
        $tags = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM articles WHERE is_help = 0 AND DATE(updated_at) = CURDATE()');
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
    $stmt = $db->prepare(
        'SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.is_help = ?
        ORDER BY a.updated_at DESC LIMIT ?'
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
function mb_search_articles(string $q, int $limit = 30): array
{
    $q = trim($q);
    if ($q === '') {
        return [];
    }
    $db = mb_db();
    $like = '%' . $db->real_escape_string($q) . '%';
    $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.title LIKE '{$like}' OR a.excerpt LIKE '{$like}' OR a.body LIKE '{$like}'
        ORDER BY a.updated_at DESC LIMIT " . (int) $limit;
    $res = $db->query($sql);
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = mb_article_row_map($row);
    }
    $res->free();

    return $rows;
}

/** @return array{articles:int,categories:int,team:int,views_week:int} */
function mb_dashboard_stats(): array
{
    $db = mb_db();
    $articles = 0;
    $categories = 0;
    $team = 0;
    $views = 0;
    $r = $db->query('SELECT COUNT(*) AS c FROM articles WHERE is_help = 0');
    if ($r instanceof mysqli_result) {
        $articles = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(DISTINCT category_id) AS c FROM articles WHERE is_help = 0');
    if ($r instanceof mysqli_result) {
        $categories = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM users');
    if ($r instanceof mysqli_result) {
        $team = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }
    $r = $db->query('SELECT COUNT(*) AS c FROM article_views WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
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
function mb_help_articles(): array
{
    $db = mb_db();
    $res = $db->query(
        "SELECT a.id, a.title, a.slug, a.excerpt, a.updated_at, a.is_help,
        '' AS category_name, '' AS category_slug, '' AS author_name
        FROM articles a WHERE a.is_help = 1 ORDER BY a.id"
    );
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = mb_article_row_map($row);
    }
    $res->free();

    return $rows;
}

/** @return list<array<string,mixed>> */
function mb_documents_list(): array
{
    $db = mb_db();
    $res = $db->query('SELECT id, title, file_type, size_bytes, owner_label, folder_path, updated_at FROM documents ORDER BY updated_at DESC');
    if (!$res instanceof mysqli_result) {
        return [];
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'file_type' => (string) $row['file_type'],
            'size_bytes' => (int) $row['size_bytes'],
            'owner_label' => (string) $row['owner_label'],
            'folder_path' => (string) $row['folder_path'],
            'updated_at' => (string) $row['updated_at'],
        ];
    }
    $res->free();

    return $rows;
}

/** @return array{files:int,folders:int,bytes:int} */
function mb_documents_stats(): array
{
    $db = mb_db();
    $files = 0;
    $bytes = 0;
    $r = $db->query('SELECT COUNT(*) AS c, COALESCE(SUM(size_bytes),0) AS b FROM documents');
    if ($r instanceof mysqli_result) {
        $row = $r->fetch_assoc();
        $files = (int) ($row['c'] ?? 0);
        $bytes = (int) ($row['b'] ?? 0);
        $r->free();
    }
    $folders = 0;
    $r = $db->query('SELECT COUNT(DISTINCT folder_path) AS c FROM documents');
    if ($r instanceof mysqli_result) {
        $folders = (int) ($r->fetch_assoc()['c'] ?? 0);
        $r->free();
    }

    return ['files' => $files, 'folders' => $folders, 'bytes' => $bytes];
}

/** @return list<string> */
function mb_document_folders(): array
{
    $db = mb_db();
    $res = $db->query('SELECT DISTINCT folder_path FROM documents ORDER BY folder_path');
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
    $lessons = $courses;
    $avg = 0;
    $stmt = $db->prepare('SELECT COALESCE(AVG(progress_percent), 0) AS a FROM course_progress WHERE user_id = ?');
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
    $res = $db->query('SELECT id, title, slug, body, updated_at FROM articles ORDER BY id');
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
    $db = mb_db();
    $total = 0;
    $stmt = $db->prepare('SELECT COUNT(*) AS c FROM articles WHERE category_id = ?');
    if ($stmt !== false) {
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $total += (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();
    }
    $children = mb_categories_list($categoryId);
    foreach ($children as $ch) {
        $total += mb_category_article_count_recursive((int) $ch['id']);
    }

    return $total;
}
