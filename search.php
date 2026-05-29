<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$results = $q !== '' ? mb_search_articles($q, 50) : [];

mb_cabinet_head('Поиск');
mb_cabinet_header_render($user, 'Поиск по базе...', false);
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Навигация</h2></div>
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Поиск</h1>
      <?php if ($q === ''): ?>
      <p class="cabinet-page-lead">Введите запрос в строке поиска в шапке.</p>
      <?php else: ?>
      <p class="cabinet-page-lead">Запрос «<?= mb_h($q) ?>» — найдено <?= count($results) ?>.</p>
      <?php if ($results === []): ?>
      <p class="cabinet-muted-text">Ничего не найдено. Попробуйте другие слова.</p>
      <?php else: ?>
      <ul class="cabinet-feed">
        <?php foreach ($results as $a): ?>
        <li class="cabinet-feed-item">
          <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-feed-title" style="color:inherit;text-decoration:none"><?= mb_h($a['title']) ?></a>
          <span class="cabinet-feed-meta"><?= mb_h($a['category_name']) ?> · <?= mb_h(mb_format_datetime($a['updated_at'])) ?></span>
          <?php if ($a['excerpt'] !== ''): ?>
          <span class="cabinet-feed-meta"><?= mb_h($a['excerpt']) ?></span>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
