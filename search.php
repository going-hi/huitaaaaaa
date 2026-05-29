<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$catFilter = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;
$results = $q !== '' ? mb_search_articles($q, 50, $catFilter) : [];
$categories = mb_categories_list(null);

mb_cabinet_head('Поиск по статьям');
mb_cabinet_header_render($user, 'Поиск по статьям...', false);
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Навигация</h2></div>
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Поиск по статьям</h1>
      <div class="cabinet-panel cabinet-search-panel">
        <form class="cabinet-search-form" method="get" action="search.php" role="search">
          <div class="cabinet-search-form-row">
            <input type="search" name="q" class="form-input cabinet-search-form-input" placeholder="Заголовок, описание или текст..." value="<?= mb_h($q) ?>" autofocus>
            <select name="category" class="form-input cabinet-search-form-select" aria-label="Раздел">
              <option value="">Все разделы</option>
              <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= $catFilter === (int) $c['id'] ? 'selected' : '' ?>><?= mb_h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Найти</button>
          </div>
        </form>
      </div>
      <?php if ($q === ''): ?>
      <p class="cabinet-page-lead">Введите ключевые слова. Поиск учитывает только статьи, доступные вашей роли и группам.</p>
      <?php else: ?>
      <p class="cabinet-page-lead">По запросу «<strong><?= mb_h($q) ?></strong>» найдено: <strong><?= count($results) ?></strong></p>
      <?php if ($results === []): ?>
      <div class="cabinet-empty-state">
        <p>Ничего не найдено</p>
        <p class="cabinet-muted-text">Попробуйте другие слова или снимите фильтр по разделу.</p>
      </div>
      <?php else: ?>
      <ul class="cabinet-search-results">
        <?php foreach ($results as $a): ?>
        <li class="cabinet-search-result">
          <a href="article.php?slug=<?= rawurlencode($a['slug']) ?>" class="cabinet-search-result-title"><?= mb_search_highlight($a['title'], $q) ?></a>
          <span class="cabinet-search-result-meta"><?= mb_h($a['category_name']) ?> · <?= mb_h(mb_format_datetime($a['updated_at'])) ?></span>
          <?php if (!empty($a['snippet'])): ?>
          <p class="cabinet-search-result-snippet"><?= mb_search_highlight($a['snippet'], $q) ?></p>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
<?php mb_cabinet_foot('catalog'); ?>
