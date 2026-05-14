<?php

/**
 * Сидер: заполняет таблицу users тестовыми записями.
 * Запуск из корня проекта: php database/seed.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/db.php';

if (!$link instanceof mysqli) {
    fwrite(STDERR, "Нет подключения к MySQL.\n");
    exit(1);
}

$rows = [
    ['Демо пользователь', 'demo@mindbase.local', password_hash('demo12345', PASSWORD_DEFAULT)],
    ['Администратор', 'admin@mindbase.local', password_hash('admin12345', PASSWORD_DEFAULT)],
];

$sql = 'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)';

$stmt = $link->prepare($sql);
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

echo "Сид выполнен: demo@mindbase.local / demo12345, admin@mindbase.local / admin12345.\n";
