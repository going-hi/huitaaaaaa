<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
mb_require_login();

$format = isset($_GET['format']) ? (string) $_GET['format'] : 'md';
$articles = mb_export_articles();

if ($format === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="mindbase-export.html"');
    echo "<!DOCTYPE html><html lang=\"ru\"><head><meta charset=\"UTF-8\"><title>MindBase export</title></head><body>\n";
    foreach ($articles as $a) {
        echo '<article><h1>' . htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p><em>' . htmlspecialchars($a['updated_at'], ENT_QUOTES, 'UTF-8') . '</em></p>';
        echo '<div>' . mb_markdown_to_html($a['body']) . '</div><hr></article>';
    }
    echo '</body></html>';
    exit;
}

header('Content-Type: text/markdown; charset=utf-8');
header('Content-Disposition: attachment; filename="mindbase-export.md"');
foreach ($articles as $a) {
    echo '# ' . $a['title'] . "\n\n";
    echo '_Обновлено: ' . $a['updated_at'] . "_\n\n";
    echo $a['body'] . "\n\n---\n\n";
}
