<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$article = $slug !== '' ? mb_article_by_slug($slug) : null;
if ($article === null) {
    http_response_code(404);
    mb_cabinet_head('Статья не найдена');
    mb_cabinet_header_render($user, 'Поиск...');
    mb_cabinet_sidebar_open('catalog');
    echo '<h1 class="cabinet-page-title">Статья не найдена</h1><p class="cabinet-muted-text"><a href="knowledge-catalog.php">Вернуться в каталог</a></p>';
    mb_cabinet_sidebar_close();
    mb_cabinet_foot('catalog');
    exit;
}

mb_article_record_view((int) $article['id'], $user['id']);
$canEdit = mb_can_write();

mb_cabinet_head($article['title']);
mb_cabinet_header_render($user, 'Поиск по базе...');
mb_cabinet_sidebar_open('catalog', '', 'cabinet-main--article');
?>
      <?php mb_cabinet_catalog_breadcrumbs(mb_category_ancestors((int) $article['category_id']), $article['title']); ?>
      <article class="cabinet-article is-visible">
      <h1><?= mb_h($article['title']) ?></h1>
      <p class="cabinet-article-meta">
        <?= mb_h($article['category_name']) ?> · <?= mb_h($article['author_name']) ?> · <?= mb_h(mb_format_datetime($article['updated_at'])) ?>
        <?php if ($article['tags'] !== []): ?>
        · <?= mb_h(implode(', ', $article['tags'])) ?>
        <?php endif; ?>
      </p>
      <?php if ($article['excerpt'] !== ''): ?>
      <p class="cabinet-page-lead"><?= mb_h($article['excerpt']) ?></p>
      <?php endif; ?>
      <div class="cabinet-article-body">
        <?= mb_markdown_to_html($article['body']) ?>
      </div>
      <div class="cabinet-form-actions">
        <?php if ($canEdit): ?>
        <a href="article-edit.php?id=<?= (int) $article['id'] ?>" class="btn btn-outline">Редактировать</a>
        <?php endif; ?>
        <a href="knowledge-catalog.php" class="btn btn-ghost">К каталогу</a>
      </div>
      </article>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('catalog');
?>
