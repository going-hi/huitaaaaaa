<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$stats = mb_catalog_stats();
$topCategories = mb_categories_list(null);
$recent = mb_articles_recent(5);
$tree = mb_category_tree();
$workspace = mb_workspace_get();

mb_cabinet_head('Каталог знаний');
mb_cabinet_header_render($user, 'Поиск по каталогу...');
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head">
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Каталог знаний</h1>
      <p class="cabinet-page-lead">Единая структура материалов: <?= mb_h($workspace['title']) ?>.</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по каталогу">
        <span class="cabinet-pill"><strong><?= (int) $stats['articles'] ?></strong> материалов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['categories'] ?></strong> разделов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['tags'] ?></strong> тегов</span>
        <?php if ($stats['updated_today'] > 0): ?>
        <span class="cabinet-pill cabinet-pill--accent">Обновлено сегодня · <?= (int) $stats['updated_today'] ?> <?= $stats['updated_today'] === 1 ? 'запись' : 'записей' ?></span>
        <?php endif; ?>
        <a href="article-edit.php" class="btn btn-primary btn-sm">Новая статья</a>
      </div>

      <div class="cabinet-actions-grid cabinet-actions-grid--catalog">
        <?php foreach ($topCategories as $cat):
            if ($cat['slug'] === 'help') {
                continue;
            }
            $total = mb_category_article_count_recursive((int) $cat['id']);
            ?>
        <a href="category.php?slug=<?= rawurlencode($cat['slug']) ?>" class="cabinet-action-card">
          <span class="cabinet-action-icon"><?= mb_h($cat['icon']) ?></span>
          <span class="cabinet-action-title"><?= mb_h($cat['name']) ?></span>
          <span class="cabinet-action-desc"><?= mb_h($cat['description']) ?></span>
          <span class="cabinet-card-foot"><?= (int) $total ?> <?= $total === 1 ? 'статья' : 'статей' ?></span>
        </a>
        <?php endforeach; ?>
      </div>

      <h2 class="cabinet-section-heading">Недавно в каталоге</h2>
      <ul class="cabinet-feed">
        <?php foreach ($recent as $a): ?>
        <li class="cabinet-feed-item">
          <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-feed-title" style="text-decoration:none;color:inherit"><?= mb_h($a['title']) ?></a>
          <span class="cabinet-feed-meta"><?= mb_h($a['category_name']) ?> · <?= mb_h($a['author_name']) ?> · <?= mb_h(mb_format_datetime($a['updated_at'])) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>

      <h2 class="cabinet-section-heading">Дерево разделов</h2>
      <div class="cabinet-panel">
        <p><strong><?= mb_h($workspace['title']) ?></strong></p>
        <?= mb_render_category_tree($tree) ?>
      </div>

      <div class="cabinet-tip">
        <strong>Поиск.</strong> Используйте строку в шапке — учитываются заголовок, описание и текст статей.
      </div>
    </main>
  </div>
</body>
</html>
