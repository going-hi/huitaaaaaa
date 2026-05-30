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
foreach ($children as $i => $ch) {
    $children[$i]['article_count'] = mb_category_article_count_recursive((int) $ch['id']);
}

mb_cabinet_head($category['name']);
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('catalog', '', 'cabinet-main--wide');
?>
      <?php mb_cabinet_catalog_breadcrumbs(mb_category_ancestors((int) $category['id'])); ?>

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
      <div class="cabinet-panel cabinet-panel--table">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data cabinet-table--compact">
            <thead>
              <tr>
                <th>Раздел</th>
                <th class="col-count">Статей</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($children as $ch): ?>
              <tr>
                <td data-label="Раздел">
                  <a href="category.php?slug=<?= rawurlencode($ch['slug']) ?>" class="cabinet-table-link"><?= mb_h($ch['name']) ?></a>
                </td>
                <td class="col-count" data-label="Статей"><?= (int) $ch['article_count'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Статьи</h2>
      <?php if ($articles === []): ?>
      <p class="cabinet-muted-text">В разделе пока нет статей.</p>
      <?php else: ?>
      <div class="cabinet-panel cabinet-panel--table cabinet-panel--wide">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data">
            <thead>
              <tr>
                <th class="col-title">Название</th>
                <th class="col-author">Автор</th>
                <th class="col-date">Обновлено</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($articles as $a): ?>
              <tr>
                <td class="col-title" data-label="Название">
                  <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-table-link"><?= mb_h($a['title']) ?></a>
                </td>
                <td class="col-author" data-label="Автор"><?= mb_h($a['author_name']) ?></td>
                <td class="col-date" data-label="Обновлено"><?= mb_h(mb_format_datetime($a['updated_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('catalog');
