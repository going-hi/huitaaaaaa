<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
mb_require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$doc = $id > 0 ? mb_document_by_id($id) : null;
if ($doc === null) {
    http_response_code(404);
    echo 'Документ не найден или доступ запрещён.';
    exit;
}

$path = mb_document_file_path($doc);
if ($path === null) {
    http_response_code(404);
    echo 'Файл отсутствует на сервере.';
    exit;
}

$filename = preg_replace('/[^\pL\pN._\- ]/u', '_', $doc['title']) . '.' . strtolower($doc['file_type']);
header('Content-Type: ' . $doc['mime_type']);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . (string) filesize($path));
readfile($path);
exit;
