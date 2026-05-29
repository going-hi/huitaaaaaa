<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
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
mb_cabinet_sidebar_open('catalog');
?>
      <h1 class="cabinet-page-title">Каталог знаний</h1>
      <p class="cabinet-page-lead"><?= mb_h($workspace['title']) ?></p>

      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><strong><?= (int) $stats['articles'] ?></strong> статей</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['categories'] ?></strong> разделов</span>
        <?php if (mb_can_write()): ?>
        <a href="article-edit.php" class="btn btn-primary btn-sm">+ Статья</a>
        <a href="category-edit.php" class="btn btn-outline btn-sm">+ Раздел</a>
        <?php endif; ?>
        <a href="search.php" class="btn btn-ghost btn-sm">Поиск</a>
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

      <h2 class="cabinet-section-heading">Недавние материалы</h2>
      <div class="cabinet-panel cabinet-panel--table cabinet-panel--wide">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data">
            <thead>
              <tr>
                <th class="col-title">Название</th>
                <th class="col-author">Раздел</th>
                <th class="col-date">Обновлено</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $a): ?>
              <tr>
                <td class="col-title" data-label="Название">
                  <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-table-link"><?= mb_h($a['title']) ?></a>
                </td>
                <td class="col-author" data-label="Раздел">
                  <?php if (!empty($a['category_slug'])): ?>
                  <a href="category.php?slug=<?= rawurlencode($a['category_slug']) ?>" class="cabinet-table-link cabinet-table-link--muted"><?= mb_h($a['category_name']) ?></a>
                  <?php else: ?>
                  <?= mb_h($a['category_name']) ?>
                  <?php endif; ?>
                </td>
                <td class="col-date" data-label="Обновлено"><?= mb_h(mb_format_datetime($a['updated_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Дерево разделов</h2>
      <div class="cabinet-panel cabinet-panel--tree-nav cabinet-panel--wide">
        <?= mb_render_category_tree($tree) ?>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('catalog');
