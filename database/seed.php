<?php

/**
 * Сидер: создаёт БД и таблицы из database/schema.sql, затем добавляет тестовых пользователей.
 *
 * Из корня: php database/seed.php
 * В Docker: docker compose exec php php database/seed.php
 */

declare(strict_types=1);

$host = getenv('MYSQL_HOST') ?: '127.0.0.1';
$port = (int) (getenv('MYSQL_PORT') ?: 3306);
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : 'root';
$dbName = getenv('MYSQL_DATABASE') ?: 'mindbase';

$root = dirname(__DIR__);
$schemaPath = $root . '/database/schema.sql';

$link = mysqli_connect($host, $user, $pass, '', $port);
if ($link === false) {
    fwrite(STDERR, 'Ошибка подключения MySQL: ' . mysqli_connect_error() . "\n");
    exit(1);
}
$link->set_charset('utf8mb4');

$sql = file_get_contents($schemaPath);
if ($sql === false) {
    fwrite(STDERR, "Не удалось прочитать файл схемы: {$schemaPath}\n");
    exit(1);
}

if (!$link->multi_query($sql)) {
    fwrite(STDERR, 'Ошибка применения схемы: ' . $link->error . "\n");
    exit(1);
}
do {
    if ($result = $link->store_result()) {
        $result->free();
    }
} while ($link->more_results() && $link->next_result());

if ($link->errno !== 0) {
    fwrite(STDERR, 'Ошибка применения схемы: ' . $link->error . "\n");
    exit(1);
}

if (!$link->select_db($dbName)) {
    fwrite(STDERR, 'Не удалось выбрать БД: ' . $link->error . "\n");
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
