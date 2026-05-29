<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$category = $slug !== '' ? mb_category_by_slug($slug) : null;
if ($category === null) {
    http_response_code(404);
    header('Location: knowledge-catalog.php', true, 302);
    exit;
}

$articles = mb_articles_by_category((int) $category['id']);
$count = mb_category_article_count_recursive((int) $category['id']);
$children = mb_categories_list((int) $category['id']);

mb_cabinet_head($category['name']);
mb_cabinet_header_render($user, 'Поиск по каталогу...');
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Навигация</h2></div>
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title"><?= mb_h($category['icon']) ?> <?= mb_h($category['name']) ?></h1>
      <?php if ($category['description'] !== ''): ?>
      <p class="cabinet-page-lead"><?= mb_h($category['description']) ?></p>
      <?php endif; ?>
      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><strong><?= (int) $count ?></strong> материалов в разделе</span>
        <a href="article-edit.php" class="btn btn-primary btn-sm">Новая статья</a>
      </div>
      <?php if ($children !== []): ?>
      <h2 class="cabinet-section-heading">Подразделы</h2>
      <div class="cabinet-actions-grid cabinet-actions-grid--catalog">
        <?php foreach ($children as $ch): ?>
        <a href="category.php?slug=<?= rawurlencode($ch['slug']) ?>" class="cabinet-action-card">
          <span class="cabinet-action-icon"><?= mb_h($ch['icon']) ?></span>
          <span class="cabinet-action-title"><?= mb_h($ch['name']) ?></span>
          <span class="cabinet-card-foot"><?= (int) $ch['article_count'] ?> статей</span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <h2 class="cabinet-section-heading">Статьи</h2>
      <?php if ($articles === []): ?>
      <p class="cabinet-muted-text">В этом разделе пока нет статей.</p>
      <?php else: ?>
      <ul class="cabinet-feed">
        <?php foreach ($articles as $a): ?>
        <li class="cabinet-feed-item">
          <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-feed-title" style="text-decoration:none;color:inherit"><?= mb_h($a['title']) ?></a>
          <span class="cabinet-feed-meta"><?= mb_h($a['author_name']) ?> · <?= mb_h(mb_format_datetime($a['updated_at'])) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
