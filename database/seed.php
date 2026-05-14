<?php

/**
 * Сидер: создаёт БД (имя из MYSQL_DATABASE), таблицы из DDL в database/schema.sql, затем тестовые users.
 *
 * Из корня: php database/seed.php
 * В Docker: docker compose exec php php database/seed.php
 * (перед этим сервис mysql-bootstrap в docker-compose прогоняет database/schema.sql при `compose up`)
 *
 * Если база уже есть (Docker MYSQL_DATABASE или админ создал вручную) — право CREATE не нужно.
 * CREATE DATABASE выполняется только когда базы нет (хостинг/облако часто его запрещает для приложения).
 */

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('MYSQL_HOST') ?: '127.0.0.1';
$port = (int) (getenv('MYSQL_PORT') ?: 3306);
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : 'root';
$dbName = getenv('MYSQL_DATABASE') ?: 'mindbase';
if ($dbName === '') {
    $dbName = 'mindbase';
}

$root = dirname(__DIR__);
$schemaPath = $root . '/database/schema.sql';

/**
 * Экранирование идентификатора MySQL (`столбец`, `таблица`).
 */
function mb_mysql_ident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

/** Вытащить DDL CREATE TABLE … users из schema.sql (без CREATE DATABASE / USE). */
function mb_extract_users_table_ddl(string $schemaPath): string
{
    $content = file_get_contents($schemaPath);
    if ($content === false) {
        fwrite(STDERR, "Не удалось прочитать файл схемы: {$schemaPath}\n");
        exit(1);
    }
    if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+users\b[\s\S]+?;/m', $content, $matches)) {
        fwrite(STDERR, "В {$schemaPath} не найден блок CREATE TABLE users.\n");
        exit(1);
    }
    return trim($matches[0]);
}

$link = @mysqli_connect($host, $user, $pass, $dbName, $port);
if ($link !== false) {
    $link->set_charset('utf8mb4');
} else {
    $link = mysqli_connect($host, $user, $pass, '', $port);
    if ($link === false) {
        fwrite(STDERR, 'Ошибка подключения MySQL: ' . mysqli_connect_error() . "\n");
        exit(1);
    }
    $link->set_charset('utf8mb4');

    $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $dbName);
    $like = $link->real_escape_string($like);
    $res = $link->query("SHOW DATABASES LIKE '{$like}'");
    $dbExists = $res instanceof mysqli_result && $res->num_rows > 0;
    if ($res instanceof mysqli_result) {
        $res->free();
    }

    if ($dbExists) {
        if (!$link->select_db($dbName)) {
            fwrite(
                STDERR,
                'База «' . $dbName . '» есть на сервере, но недоступна для пользователя '
                . $user . ': ' . $link->error . "\n"
                . "Выдайте права GRANT или проверьте MYSQL_USER / MYSQL_PASSWORD.\n"
            );
            exit(1);
        }
    } else {
        $dbIdent = mb_mysql_ident($dbName);
        $createDbSql = "CREATE DATABASE IF NOT EXISTS {$dbIdent} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$link->query($createDbSql)) {
            fwrite(
                STDERR,
                'Базы «' . $dbName . '» нет, а создать её не удалось: ' . $link->error . "\n"
                    . "Создайте пустую БД вручную (или дайте пользователю привилегию CREATE), затем снова: php database/seed.php\n"
            );
            exit(1);
        }
        if (!$link->select_db($dbName)) {
            fwrite(STDERR, 'База создана, но select_db упал: ' . $link->error . "\n");
            exit(1);
        }
    }
}

$tableSql = mb_extract_users_table_ddl($schemaPath);
if (!$link->query($tableSql)) {
    fwrite(STDERR, 'Не удалось создать таблицы: ' . $link->error . "\n");
    exit(1);
}

$rows = [
    ['Демо пользователь', 'demo@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT)],
    ['Администратор', 'admin@mindbase.local', password_hash('admin12345', PASSWORD_DEFAULT)],
];

$insertSql = 'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)';

$stmt = $link->prepare($insertSql);
if ($stmt === false) {
    fwrite(STDERR, 'Ошибка prepare: ' . $link->error . "\n");
    exit(1);
}

foreach ($rows as [$name, $email, $hash]) {
    $stmt->bind_param('sss', $name, $email, $hash);
    if (!$stmt->execute()) {
        fwrite(STDERR, "Ошибка INSERT {$email}: " . $stmt->error . "\n");
        exit(1);
    }
}

$stmt->close();
$link->close();

echo "Готово: БД «{$dbName}», таблицы и данные загружены.\n";
echo "Учётки: demo@mindbase.local / demo12345, admin@mindbase.local / admin12345.\n";
