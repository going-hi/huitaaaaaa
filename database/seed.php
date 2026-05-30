<?php

/**
 * Сидер: схема + демо-контент базы знаний.
 * php database/seed.php
 * docker compose exec php php database/seed.php
 */

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('MYSQL_HOST') ?: '127.0.0.1';
$port = (int) (getenv('MYSQL_PORT') ?: 3306);
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : 'root';
$dbName = getenv('MYSQL_DATABASE') ?: 'mindbase';

$root = dirname(__DIR__);
$tablesSqlPath = $root . '/database/tables.sql';

function mb_mysql_ident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function mb_run_sql_file(mysqli $link, string $path): void
{
    $content = file_get_contents($path);
    if ($content === false) {
        fwrite(STDERR, "Не удалось прочитать: {$path}\n");
        exit(1);
    }
    $content = preg_replace('/--.*$/m', '', $content) ?? $content;
    $parts = preg_split('/;\s*\n/', $content) ?: [];
    foreach ($parts as $sql) {
        $sql = trim($sql);
        if ($sql === '') {
            continue;
        }
        if (!$link->query($sql)) {
            fwrite(STDERR, "SQL ошибка: {$link->error}\nФрагмент: " . substr($sql, 0, 120) . "...\n");
            exit(1);
        }
    }
}

function mb_ensure_column(mysqli $link, string $table, string $column, string $ddl): void
{
    $db = $link->real_escape_string(getenv('MYSQL_DATABASE') ?: 'mindbase');
    $t = $link->real_escape_string($table);
    $c = $link->real_escape_string($column);
    $res = $link->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$t}' AND COLUMN_NAME = '{$c}' LIMIT 1");
    if ($res instanceof mysqli_result && $res->num_rows > 0) {
        $res->free();

        return;
    }
    if ($res instanceof mysqli_result) {
        $res->free();
    }
    $link->query($ddl);
}

$link = @mysqli_connect($host, $user, $pass, $dbName, $port);
if ($link === false) {
    $link = mysqli_connect($host, $user, $pass, '', $port);
    if ($link === false) {
        fwrite(STDERR, 'Ошибка подключения: ' . mysqli_connect_error() . "\n");
        exit(1);
    }
    $link->set_charset('utf8mb4');
    $dbIdent = mb_mysql_ident($dbName);
    $link->query("CREATE DATABASE IF NOT EXISTS {$dbIdent} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $link->select_db($dbName);
} else {
    $link->set_charset('utf8mb4');
}

mb_run_sql_file($link, $tablesSqlPath);
require __DIR__ . '/migrate_workspaces.php';
mb_ensure_column($link, 'users', 'role_title', 'ALTER TABLE users ADD COLUMN role_title VARCHAR(120) NULL DEFAULT NULL AFTER password_hash');
mb_ensure_column($link, 'users', 'role', "ALTER TABLE users ADD COLUMN role ENUM('admin','editor','user') NOT NULL DEFAULT 'user' AFTER password_hash");
mb_ensure_column($link, 'documents', 'stored_name', 'ALTER TABLE documents ADD COLUMN stored_name VARCHAR(255) NULL DEFAULT NULL AFTER file_type');
mb_ensure_column($link, 'documents', 'mime_type', "ALTER TABLE documents ADD COLUMN mime_type VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream' AFTER stored_name");
mb_ensure_column($link, 'documents', 'uploaded_by', 'ALTER TABLE documents ADD COLUMN uploaded_by INT UNSIGNED NULL DEFAULT NULL AFTER folder_path');

$storageDir = $root . '/storage/documents';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$users = [
    ['Демо пользователь', 'demo@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Аналитик', 'user'],
    ['Администратор', 'admin@mindbase.local', password_hash('admin12345', PASSWORD_DEFAULT), 'Администратор базы', 'admin'],
    ['Редактор', 'editor@mindbase.local', password_hash('editor12345', PASSWORD_DEFAULT), 'Редактор контента', 'editor'],
    ['Мария Соколова', 'maria@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Инженер эксплуатации', 'editor'],
    ['Андрей Петров', 'andrey@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Менеджер продаж', 'user'],
];
$userIds = [];
$stmtUser = $link->prepare('INSERT INTO users (name, email, password_hash, role_title, role) VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role_title = VALUES(role_title), role = VALUES(role)');
foreach ($users as [$name, $email, $hash, $roleTitle, $role]) {
    $stmtUser->bind_param('sssss', $name, $email, $hash, $roleTitle, $role);
    $stmtUser->execute();
    $res = $link->query("SELECT id FROM users WHERE email = '" . $link->real_escape_string($email) . "' LIMIT 1");
    if ($res instanceof mysqli_result) {
        $row = $res->fetch_assoc();
        $userIds[$email] = (int) $row['id'];
        $res->free();
    }
}
$stmtUser->close();

$adminId = $userIds['admin@mindbase.local'] ?? 1;
$mariaId = $userIds['maria@mindbase.local'] ?? $adminId;

$demoWsId = 1;
$inviteToken = bin2hex(random_bytes(32));
$wsTitle = 'Команда «Инним» — внутренняя база';
$link->query("INSERT INTO workspaces (id, title, slug, owner_id, invite_token) VALUES ({$demoWsId}, '"
    . $link->real_escape_string($wsTitle) . "', 'innim-demo', {$adminId}, '"
    . $link->real_escape_string($inviteToken) . "')
    ON DUPLICATE KEY UPDATE title = VALUES(title), owner_id = VALUES(owner_id)");

$workspaceMembers = [
    'admin@mindbase.local' => 'owner',
    'editor@mindbase.local' => 'admin',
    'demo@mindbase.local' => 'user',
    'maria@mindbase.local' => 'editor',
    'andrey@mindbase.local' => 'user',
];
foreach ($workspaceMembers as $email => $role) {
    if (!isset($userIds[$email])) {
        continue;
    }
    $uid = $userIds[$email];
    $link->query("INSERT IGNORE INTO workspace_members (workspace_id, user_id, role) VALUES ({$demoWsId}, {$uid}, '{$role}')");
}

$accessGroupsSeed = [
    ['Разработка', 'developers', 'Инженеры, DevOps, QA'],
    ['Поддержка', 'support', 'Линии L1/L2/L3'],
    ['HR и офис', 'hr', 'Кадры и административный блок'],
    ['Руководство и ИБ', 'management', 'Безопасность, комплаенс, топ-менеджмент'],
];
$groupIds = [];
foreach ($accessGroupsSeed as [$gname, $gslug, $gdesc]) {
    $link->query("INSERT INTO access_groups (workspace_id, name, slug, description) VALUES ({$demoWsId}, '"
        . $link->real_escape_string($gname) . "', '"
        . $link->real_escape_string($gslug) . "', '"
        . $link->real_escape_string($gdesc) . "')
        ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)");
    $r = $link->query("SELECT id FROM access_groups WHERE workspace_id = {$demoWsId} AND slug = '" . $link->real_escape_string($gslug) . "' LIMIT 1");
    if ($r instanceof mysqli_result) {
        $groupIds[$gslug] = (int) $r->fetch_assoc()['id'];
        $r->free();
    }
}

$categories = [
    ['onboarding', null, 'Онбординг', '📂', 'Инструкции для новых сотрудников, доступы, первые шаги в команде.', 10],
    ['product-api', null, 'Продукт и API', '⚙️', 'Описание сервисов, OpenAPI, схемы интеграций, лимиты и SLA.', 20],
    ['support', null, 'Поддержка', '🛟', 'Типовые кейсы, эскалации L2/L3, макросы ответов.', 30],
    ['security', null, 'Безопасность и комплаенс', '🔒', 'ИБ-политики, 152-ФЗ, учёт доступов.', 40],
    ['marketing', null, 'Маркетинг и продажи', '📣', 'Питчи, кейсы клиентов, прайс-листы.', 50],
    ['hr', null, 'HR и офис', '👥', 'Отпуска, ДМС, график, удалёнка.', 60],
    ['dev', null, 'Разработка и эксплуатация', '💻', 'Архитектура, CI/CD, дежурства.', 15],
    ['product', null, 'Продукт', '📦', 'Roadmap и релизы.', 25],
    ['cs', null, 'Клиентский успех', '🤝', 'Онбординг клиента и база для саппорта.', 35],
    ['corp', null, 'Корпоративный блок', '🏢', 'HR, закупки, офис.', 55],
    ['help', null, 'Справка MindBase', '📖', 'Руководство по работе с платформой.', 1],
];

$catIds = [];
$stmtCat = $link->prepare('INSERT INTO categories (workspace_id, slug, parent_id, name, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), icon = VALUES(icon), description = VALUES(description), sort_order = VALUES(sort_order)');
foreach ($categories as [$slug, $parent, $name, $icon, $desc, $sort]) {
    $parentVal = null;
    $stmtCat->bind_param('isisssi', $demoWsId, $slug, $parentVal, $name, $icon, $desc, $sort);
    $stmtCat->execute();
    $r = $link->query("SELECT id FROM categories WHERE workspace_id = {$demoWsId} AND slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
    if ($r instanceof mysqli_result) {
        $catIds[$slug] = (int) $r->fetch_assoc()['id'];
        $r->free();
    }
}
$stmtCat->close();

$children = [
    ['onboarding-day1', 'onboarding', 'Первый день и доступы', 1],
    ['onboarding-tools', 'onboarding', 'Инструменты (Git, CI, таск-трекер)', 2],
    ['onboarding-style', 'onboarding', 'Кодстайл и review', 3],
    ['dev-adr', 'dev', 'Архитектура и ADR', 1],
    ['dev-git', 'dev', 'Гайд по Git / trunk-based', 2],
    ['dev-duty', 'dev', 'Дежурства и инциденты', 3],
    ['dev-load', 'dev', 'Нагрузочное тестирование', 4],
    ['product-roadmap', 'product', 'Roadmap и релизы', 1],
    ['product-api-doc', 'product', 'Документация API', 2],
    ['cs-onboard', 'cs', 'Онбординг клиента', 1],
    ['cs-support-kb', 'cs', 'База знаний для саппорта', 2],
    ['corp-hr', 'corp', 'HR: отпуска, бенефиты', 1],
    ['corp-office', 'corp', 'Закупки и офис', 2],
];
$stmtChild = $link->prepare('INSERT INTO categories (workspace_id, slug, parent_id, name, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id), name = VALUES(name), sort_order = VALUES(sort_order)');
foreach ($children as [$slug, $parentSlug, $name, $sort]) {
    $pid = $catIds[$parentSlug] ?? null;
    $icon = '📄';
    $desc = '';
    $stmtChild->bind_param('isisssi', $demoWsId, $slug, $pid, $name, $icon, $desc, $sort);
    $stmtChild->execute();
    $r = $link->query("SELECT id FROM categories WHERE workspace_id = {$demoWsId} AND slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
    if ($r instanceof mysqli_result) {
        $catIds[$slug] = (int) $r->fetch_assoc()['id'];
        $r->free();
    }
}
$stmtChild->close();

$tags = ['онбординг', 'api', 'инцидент', 'поддержка', '152-фз', 'релиз', 'runbook', 'авторизация'];
$tagIds = [];
foreach ($tags as $t) {
    $slug = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($t, 'UTF-8')) ?: $t;
    $link->query("INSERT INTO tags (workspace_id, name, slug) VALUES ({$demoWsId}, '" . $link->real_escape_string($t) . "', '" . $link->real_escape_string($slug) . "')
        ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $r = $link->query("SELECT id FROM tags WHERE workspace_id = {$demoWsId} AND slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
    if ($r instanceof mysqli_result) {
        $tagIds[$t] = (int) $r->fetch_assoc()['id'];
        $r->free();
    }
}

$articles = [
    ['runbook-payment-gateway', 'dev-duty', $mariaId, 'Runbook: падение платёжного шлюза', 'Пошаговые действия при недоступности платёжного шлюза.', "# Runbook: падение платёжного шлюза\n\n## Симптомы\n- Ошибки 502 на `/api/v1/payments`\n- Алерт `payment_gateway_down`\n\n## Действия\n1. Проверить статус у провайдера.\n2. Включить режим отложенных платежей.\n3. Уведомить поддержку и финансы.\n\n```bash\ncurl -s https://status.provider.example/health\n```", 0, ['инцидент', 'runbook']],
    ['checklist-sales-onboarding', 'onboarding', $userIds['andrey@mindbase.local'] ?? $adminId, 'Чек-лист онбординга Sales', 'Первые две недели в отделе продаж.', "## Чек-лист\n\n- Доступ к CRM\n- Изучить прайс и кейсы\n- Пройти тест по продукту", 0, ['онбординг']],
    ['api-v23-breaking', 'product-api-doc', $adminId, 'Обновление API v2.3 — breaking changes', 'Список несовместимых изменений в API v2.3.', "## Breaking changes\n\n- Поле `user_id` переименовано в `account_id`\n- Удалён устаревший endpoint `/legacy/auth`", 0, ['api', 'релиз']],
    ['auth-error-template', 'support', $adminId, 'Шаблон ответа: типовая ошибка авторизации', 'Макрос для линии поддержки.', "Клиент видит «Сессия истекла» — предложите очистить cookies и повторить вход.", 0, ['поддержка', 'авторизация']],
    ['personal-data-memo', 'security', $adminId, 'Памятка по работе с персональными данными', 'Краткие правила обработки ПДн.', "## Основное\n\n- Хранить только необходимый минимум\n- Не пересылать ПДн в личную почту\n- Сообщать ИБ об инцидентах", 0, ['152-фз']],
    ['release-checklist-v23', 'product-roadmap', $adminId, 'Чек-лист релиза v2.3', 'Контрольный список перед выкладкой.', "- Регрессионные тесты\n- Changelog опубликован\n- Runbook обновлён", 0, ['релиз']],
    ['crm-integration-runbook', 'cs-support-kb', $mariaId, 'Runbook: интеграция с CRM', 'Диагностика синхронизации с CRM.', "Проверьте webhook и API-ключ в настройках интеграции.", 0, ['runbook']],
    ['welcome-help', 'help', $adminId, 'Добро пожаловать', 'Введение в личную базу знаний.', "Это ваша корпоративная база знаний MindBase.\n\n## С чего начать\n\n- Откройте **каталог знаний**\n- Создайте статью через «Новая статья»\n- Используйте поиск в шапке", 1, []],
    ['rules-help', 'help', $adminId, 'Правила оформления статей', 'Единый стиль материалов.', "## Структура\n\n- Один заголовок H1\n- Короткие абзацы\n\n```\n// пример кода\nconst x = 1;\n```", 1, []],
    ['sections-help', 'help', $adminId, 'Как добавить раздел', 'Группировка статей по темам.', "Разделы задаются в каталоге. Создайте статью и выберите категорию при сохранении.", 1, []],
    ['search-help', 'help', $adminId, 'Поиск по базе', 'Как искать материалы.', "Введите слова в строку поиска — ищется заголовок, краткое описание и текст.", 1, []],
    ['export-help', 'help', $adminId, 'Экспорт данных', 'Выгрузка контента.', "В **настройках** доступен экспорт в Markdown.", 1, []],
];

$stmtArt = $link->prepare('INSERT INTO articles (workspace_id, slug, category_id, author_id, title, excerpt, body, is_help) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), title = VALUES(title), excerpt = VALUES(excerpt), body = VALUES(body), is_help = VALUES(is_help)');
$stmtTagLink = $link->prepare('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)');

foreach ($articles as [$slug, $catSlug, $authorId, $title, $excerpt, $body, $isHelp, $tagList]) {
    $cid = $catIds[$catSlug] ?? $catIds['onboarding'];
    $stmtArt->bind_param('isiisssi', $demoWsId, $slug, $cid, $authorId, $title, $excerpt, $body, $isHelp);
    $stmtArt->execute();
    $r = $link->query("SELECT id FROM articles WHERE slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
    if (!$r instanceof mysqli_result) {
        continue;
    }
    $aid = (int) $r->fetch_assoc()['id'];
    $r->free();
    foreach ($tagList as $tn) {
        if (!isset($tagIds[$tn])) {
            continue;
        }
        $tid = $tagIds[$tn];
        $stmtTagLink->bind_param('ii', $aid, $tid);
        $stmtTagLink->execute();
    }
}
$stmtArt->close();
$stmtTagLink->close();

$docs = [
    ['Политика информационной безопасности v3.2', 'PDF', 862080, 'Служба ИБ', '/юридические/'],
    ['Шаблон технического задания (внутренний)', 'DOCX', 131072, 'Офис развития', '/продукт/specs/'],
    ['Реестр интеграций и контрагентов', 'XLSX', 364544, 'PMO', '/продукт/specs/'],
    ['Соглашение о неконкуренции (пример)', 'DOCX', 97280, 'Юридический отдел', '/юридические/'],
    ['Брендбук MindBase — печать и презентации', 'PDF', 13002342, 'Маркетинг', '/маркетинг/'],
    ['Инструкция: доступ к продакшен-логам', 'PDF', 634880, 'SRE', '/разработка/'],
    ['Отчёт по аудиту документооборота Q1', 'PDF', 1153434, 'Внутренний контроль', '/юридические/'],
    ['Шаблон акта приёмки работ', 'DOCX', 72704, 'Финансы', '/юридические/'],
    ['Каталог API-ключей (выгрузка)', 'CSV', 49152, 'ИБ', '/клиенты/nda/'],
];
$link->query('DELETE FROM document_access_groups');
$link->query('DELETE FROM documents');
$stmtDoc = $link->prepare('INSERT INTO documents (workspace_id, title, file_type, stored_name, mime_type, size_bytes, owner_label, folder_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$docIds = [];
foreach ($docs as $i => $d) {
    $ext = strtolower($d[1]);
    $stored = 'seed-doc-' . ($i + 1) . '.' . ($ext === 'docx' ? 'txt' : strtolower($ext === 'xlsx' ? 'csv' : $ext));
    $path = $storageDir . '/' . $stored;
    $body = "MindBase — демо-файл\nНазвание: {$d[0]}\nТип: {$d[1]}\nОтветственный: {$d[3]}\n";
    file_put_contents($path, $body);
    $size = (int) filesize($path);
    $mime = match ($ext) {
        'pdf' => 'application/pdf',
        'csv' => 'text/csv',
        default => 'text/plain',
    };
    $stmtDoc->bind_param('issssissi', $demoWsId, $d[0], $d[1], $stored, $mime, $size, $d[3], $d[4], $adminId);
    $stmtDoc->execute();
    $docIds[] = (int) $link->insert_id;
}
$stmtDoc->close();

$categoryGroupMap = [
    'dev' => ['developers'],
    'product-api' => ['developers'],
    'product' => ['developers'],
    'support' => ['support'],
    'security' => ['management'],
    'hr' => ['hr'],
    'marketing' => ['management'],
];
$link->query('DELETE FROM category_access_groups');
foreach ($categoryGroupMap as $catSlug => $gSlugs) {
    if (!isset($catIds[$catSlug])) {
        continue;
    }
    $cid = $catIds[$catSlug];
    foreach ($gSlugs as $gs) {
        if (!isset($groupIds[$gs])) {
            continue;
        }
        $gid = $groupIds[$gs];
        $link->query("INSERT IGNORE INTO category_access_groups (category_id, group_id) VALUES ({$cid}, {$gid})");
    }
}

$docGroupMap = [
    0 => ['management'],
    8 => ['management'],
];
foreach ($docGroupMap as $idx => $gSlugs) {
    if (!isset($docIds[$idx])) {
        continue;
    }
    $did = $docIds[$idx];
    foreach ($gSlugs as $gs) {
        if (!isset($groupIds[$gs])) {
            continue;
        }
        $link->query('INSERT IGNORE INTO document_access_groups (document_id, group_id) VALUES (' . $did . ', ' . $groupIds[$gs] . ')');
    }
}

$link->query('DELETE FROM user_access_groups');
$userGroupMap = [
    'demo@mindbase.local' => ['developers'],
    'editor@mindbase.local' => ['developers', 'support'],
    'maria@mindbase.local' => ['developers'],
    'andrey@mindbase.local' => ['support'],
];
foreach ($userGroupMap as $email => $gSlugs) {
    if (!isset($userIds[$email])) {
        continue;
    }
    $uid = $userIds[$email];
    foreach ($gSlugs as $gs) {
        if (!isset($groupIds[$gs])) {
            continue;
        }
        $link->query('INSERT IGNORE INTO user_access_groups (user_id, group_id) VALUES (' . $uid . ', ' . $groupIds[$gs] . ')');
    }
}

$courses = [
    ['Введение в MindBase', 'Интерфейс, роли, приглашение коллег.', 'video', 42, 'Центр компетенций', 1],
    ['Оформление статей и Markdown', 'Заголовки, списки, таблицы, вставка кода.', 'doc', 120, 'Команда контента', 2],
    ['Документы, версии и согласование', 'Загрузка файлов и маршрут согласования.', 'mix', 200, 'ИБ и архив', 3],
    ['Инцидент-менеджмент и постмортемы', 'Классификация инцидентов и шаблон постмортема.', 'video', 55, 'SRE-гильдия', 4],
    ['Аттестация: защита персональных данных', '20 вопросов, проходной балл 80%.', 'quiz', 30, 'Комплаенс', 5],
];
$link->query('DELETE FROM courses');
$courseIds = [];
$stmtCourse = $link->prepare('INSERT INTO courses (workspace_id, title, description, course_type, duration_minutes, author_label, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
foreach ($courses as $c) {
    $stmtCourse->bind_param('isssisi', $demoWsId, $c[0], $c[1], $c[2], $c[3], $c[4], $c[5]);
    $stmtCourse->execute();
    $courseIds[] = (int) $link->insert_id;
}
$stmtCourse->close();

$link->query('DELETE FROM course_lesson_progress');
$link->query('DELETE FROM course_lessons');

$lessonRows = [
    [0, 'Добро пожаловать в MindBase', 'Обзор интерфейса и возможностей платформы.', 'welcome-help', 12, 1],
    [0, 'Поиск по базе знаний', 'Как быстро находить статьи и разделы.', 'search-help', 10, 2],
    [1, 'Правила оформления статей', 'Единый стиль Markdown в каталоге.', 'rules-help', 25, 1],
    [1, 'Структура разделов', 'Группировка материалов по темам.', 'sections-help', 15, 2],
    [1, 'Экспорт контента', 'Выгрузка базы в Markdown и HTML.', 'export-help', 10, 3],
    [2, 'Чек-лист онбординга Sales', 'Первые шаги в отделе продаж.', 'checklist-sales-onboarding', 20, 1],
    [2, 'Памятка по персональным данным', 'Правила обработки ПДн.', 'personal-data-memo', 15, 2],
    [3, 'Runbook: платёжный шлюз', 'Действия при инциденте.', 'runbook-payment-gateway', 30, 1],
    [3, 'Шаблон ответа: авторизация', 'Макрос для поддержки.', 'auth-error-template', 10, 2],
    [4, 'Тест: персональные данные', 'Пройдите памятку и отметьте урок.', 'personal-data-memo', 15, 1],
    [4, 'Итоговая аттестация', 'Подтвердите прохождение курса.', null, 15, 2],
];

$stmtLesson = $link->prepare(
    'INSERT INTO course_lessons (course_id, title, description, article_slug, duration_minutes, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
);
foreach ($lessonRows as [$idx, $title, $desc, $slug, $mins, $ord]) {
    $cid = $courseIds[$idx] ?? 0;
    if ($cid <= 0) {
        continue;
    }
    $stmtLesson->bind_param('isssii', $cid, $title, $desc, $slug, $mins, $ord);
    $stmtLesson->execute();
}
$stmtLesson->close();

$demoId = $userIds['demo@mindbase.local'] ?? $adminId;
$lessonIdsByCourse = [];
$r = $link->query('SELECT id, course_id FROM course_lessons ORDER BY course_id, sort_order, id');
if ($r instanceof mysqli_result) {
    while ($row = $r->fetch_assoc()) {
        $lessonIdsByCourse[(int) $row['course_id']][] = (int) $row['id'];
    }
    $r->free();
}

$stmtLessonProg = $link->prepare('INSERT IGNORE INTO course_lesson_progress (user_id, lesson_id) VALUES (?, ?)');
foreach ($courseIds as $i => $cid) {
    if ($cid <= 0 || !isset($lessonIdsByCourse[$cid])) {
        continue;
    }
    $lessons = $lessonIdsByCourse[$cid];
    $completeCount = match ($i) {
        0 => count($lessons),
        1 => (int) ceil(count($lessons) * 0.75),
        2 => (int) ceil(count($lessons) * 0.5),
        default => 0,
    };
    for ($j = 0; $j < $completeCount && $j < count($lessons); $j++) {
        $lid = $lessons[$j];
        $stmtLessonProg->bind_param('ii', $demoId, $lid);
        $stmtLessonProg->execute();
    }
}
$stmtLessonProg->close();

foreach ($courseIds as $cid) {
    if ($cid <= 0) {
        continue;
    }
    $total = count($lessonIdsByCourse[$cid] ?? []);
    $done = 0;
    $stmtCount = $link->prepare(
        'SELECT COUNT(*) AS c FROM course_lesson_progress clp
         INNER JOIN course_lessons l ON l.id = clp.lesson_id
         WHERE l.course_id = ? AND clp.user_id = ?'
    );
    if ($stmtCount !== false) {
        $stmtCount->bind_param('ii', $cid, $demoId);
        $stmtCount->execute();
        $done = (int) ($stmtCount->get_result()->fetch_assoc()['c'] ?? 0);
        $stmtCount->close();
    }
    $percent = $total > 0 ? (int) round($done / $total * 100) : 0;
    $stmtProg = $link->prepare(
        'INSERT INTO course_progress (user_id, course_id, progress_percent) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE progress_percent = VALUES(progress_percent)'
    );
    if ($stmtProg !== false) {
        $stmtProg->bind_param('iii', $demoId, $cid, $percent);
        $stmtProg->execute();
        $stmtProg->close();
    }
}

$link->close();

echo "Готово: схема и демо-данные в БД «{$dbName}».\n";
echo "Учётки:\n";
echo "  admin@mindbase.local / admin12345 — администратор (полный доступ)\n";
echo "  editor@mindbase.local / editor12345 — редактор\n";
echo "  demo@mindbase.local / demo12345 — пользователь (группа «Разработка»)\n";
