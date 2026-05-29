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
mb_ensure_column($link, 'users', 'role_title', 'ALTER TABLE users ADD COLUMN role_title VARCHAR(120) NULL DEFAULT NULL AFTER password_hash');

$users = [
    ['Демо пользователь', 'demo@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Аналитик'],
    ['Администратор', 'admin@mindbase.local', password_hash('admin12345', PASSWORD_DEFAULT), 'Администратор базы'],
    ['Мария Соколова', 'maria@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Инженер эксплуатации'],
    ['Андрей Петров', 'andrey@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT), 'Менеджер продаж'],
];
$userIds = [];
$stmtUser = $link->prepare('INSERT INTO users (name, email, password_hash, role_title) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role_title = VALUES(role_title)');
foreach ($users as [$name, $email, $hash, $role]) {
    $stmtUser->bind_param('ssss', $name, $email, $hash, $role);
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

$link->query("INSERT INTO workspace (id, title) VALUES (1, 'Команда «Инним» — внутренняя база')
    ON DUPLICATE KEY UPDATE title = VALUES(title)");

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
$stmtCat = $link->prepare('INSERT INTO categories (slug, parent_id, name, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), icon = VALUES(icon), description = VALUES(description), sort_order = VALUES(sort_order)');
foreach ($categories as [$slug, $parent, $name, $icon, $desc, $sort]) {
    $parentVal = null;
    $stmtCat->bind_param('sisssi', $slug, $parentVal, $name, $icon, $desc, $sort);
    $stmtCat->execute();
    $r = $link->query("SELECT id FROM categories WHERE slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
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
$stmtChild = $link->prepare('INSERT INTO categories (slug, parent_id, name, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id), name = VALUES(name), sort_order = VALUES(sort_order)');
foreach ($children as [$slug, $parentSlug, $name, $sort]) {
    $pid = $catIds[$parentSlug] ?? null;
    $icon = '📄';
    $desc = '';
    $stmtChild->bind_param('sisssi', $slug, $pid, $name, $icon, $desc, $sort);
    $stmtChild->execute();
    $r = $link->query("SELECT id FROM categories WHERE slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
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
    $link->query("INSERT INTO tags (name, slug) VALUES ('" . $link->real_escape_string($t) . "', '" . $link->real_escape_string($slug) . "')
        ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $r = $link->query("SELECT id FROM tags WHERE slug = '" . $link->real_escape_string($slug) . "' LIMIT 1");
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

$stmtArt = $link->prepare('INSERT INTO articles (slug, category_id, author_id, title, excerpt, body, is_help) VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), title = VALUES(title), excerpt = VALUES(excerpt), body = VALUES(body), is_help = VALUES(is_help)');
$stmtTagLink = $link->prepare('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)');

foreach ($articles as [$slug, $catSlug, $authorId, $title, $excerpt, $body, $isHelp, $tagList]) {
    $cid = $catIds[$catSlug] ?? $catIds['onboarding'];
    $stmtArt->bind_param('siisssi', $slug, $cid, $authorId, $title, $excerpt, $body, $isHelp);
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
$link->query('DELETE FROM documents');
$stmtDoc = $link->prepare('INSERT INTO documents (title, file_type, size_bytes, owner_label, folder_path) VALUES (?, ?, ?, ?, ?)');
foreach ($docs as $d) {
    $stmtDoc->bind_param('ssiss', $d[0], $d[1], $d[2], $d[3], $d[4]);
    $stmtDoc->execute();
}
$stmtDoc->close();

$courses = [
    ['Введение в MindBase', 'Интерфейс, роли, приглашение коллег.', 'video', 42, 'Центр компетенций', 1],
    ['Оформление статей и Markdown', 'Заголовки, списки, таблицы, вставка кода.', 'doc', 120, 'Команда контента', 2],
    ['Документы, версии и согласование', 'Загрузка файлов и маршрут согласования.', 'mix', 200, 'ИБ и архив', 3],
    ['Инцидент-менеджмент и постмортемы', 'Классификация инцидентов и шаблон постмортема.', 'video', 55, 'SRE-гильдия', 4],
    ['Аттестация: защита персональных данных', '20 вопросов, проходной балл 80%.', 'quiz', 30, 'Комплаенс', 5],
];
$link->query('DELETE FROM courses');
$courseIds = [];
$stmtCourse = $link->prepare('INSERT INTO courses (title, description, course_type, duration_minutes, author_label, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
foreach ($courses as $c) {
    $stmtCourse->bind_param('sssisi', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5]);
    $stmtCourse->execute();
    $courseIds[] = (int) $link->insert_id;
}
$stmtCourse->close();

$demoId = $userIds['demo@mindbase.local'] ?? $adminId;
$progress = [100, 75, 40, 0, 0];
$stmtProg = $link->prepare('INSERT INTO course_progress (user_id, course_id, progress_percent) VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE progress_percent = VALUES(progress_percent)');
foreach ($courseIds as $i => $cid) {
    if ($cid <= 0) {
        continue;
    }
    $p = $progress[$i] ?? 0;
    $stmtProg->bind_param('iii', $demoId, $cid, $p);
    $stmtProg->execute();
}
$stmtProg->close();

$link->close();

echo "Готово: схема и демо-данные в БД «{$dbName}».\n";
echo "Учётки: demo@mindbase.local / demo12345, admin@mindbase.local / admin12345\n";
