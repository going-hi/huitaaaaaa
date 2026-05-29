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
$category = $slug !== '' ? mb_category_by_slug($slug) : null;
if ($category === null) {
    header('Location: knowledge-catalog.php', true, 302);
    exit;
}

$articles = mb_articles_by_category((int) $category['id']);
$count = mb_category_article_count_recursive((int) $category['id']);
$children = mb_categories_list((int) $category['id']);

mb_cabinet_head($category['name']);
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('catalog');
?>
      <nav class="cabinet-breadcrumb">
        <a href="knowledge-catalog.php">Каталог</a>
        <span>/</span>
        <span><?= mb_h($category['name']) ?></span>
      </nav>

      <h1 class="cabinet-page-title"><?= mb_h($category['icon']) ?> <?= mb_h($category['name']) ?></h1>
      <?php if ($category['description'] !== ''): ?>
      <p class="cabinet-page-lead"><?= mb_h($category['description']) ?></p>
      <?php endif; ?>

      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><?= (int) $count ?> материалов</span>
        <?php if (mb_can_write()): ?>
        <a href="article-edit.php?category=<?= (int) $category['id'] ?>" class="btn btn-primary btn-sm">+ Статья</a>
        <a href="category-edit.php?id=<?= (int) $category['id'] ?>" class="btn btn-outline btn-sm">Изменить раздел</a>
        <?php endif; ?>
      </div>

      <?php if ($children !== []): ?>
      <h2 class="cabinet-section-heading">Подразделы</h2>
      <div class="cabinet-chips">
        <?php foreach ($children as $ch): ?>
        <a href="category.php?slug=<?= rawurlencode($ch['slug']) ?>" class="cabinet-chip-link"><?= mb_h($ch['name']) ?> <span class="cabinet-chip-count"><?= (int) $ch['article_count'] ?></span></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Статьи</h2>
      <?php if ($articles === []): ?>
      <p class="cabinet-muted-text">В разделе пока нет статей.</p>
      <?php else: ?>
      <ul class="cabinet-feed cabinet-feed--links">
        <?php foreach ($articles as $a): ?>
        <li class="cabinet-feed-item">
          <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-feed-link">
            <span class="cabinet-feed-title"><?= mb_h($a['title']) ?></span>
            <span class="cabinet-feed-meta"><?= mb_h($a['author_name']) ?> · <?= mb_h(mb_format_datetime($a['updated_at'])) ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('catalog');
