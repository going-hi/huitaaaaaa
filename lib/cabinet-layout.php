<?php

declare(strict_types=1);

require_once __DIR__ . '/roles.php';

/**
 * @param array{id:int,name:string,email:string,role?:string} $user
 */
function mb_cabinet_header_render(array $user, string $searchPlaceholder = 'Поиск...', bool $narrowSearch = true): void
{
    $searchClass = $narrowSearch ? 'cabinet-header-search cabinet-header-search--narrow' : 'cabinet-header-search';
    ?>
  <header class="cabinet-header">
    <div class="cabinet-header-inner">
      <button type="button" class="cabinet-menu-toggle" data-cabinet-menu-toggle aria-expanded="false" aria-label="Меню">
        <span></span><span></span><span></span>
      </button>
      <a href="index.php" class="logo">
        <img src="logo.png" alt="" class="logo-img">
        <span>MindBase</span>
      </a>
      <form class="<?= $searchClass ?>" action="search.php" method="get" role="search">
        <input type="search" name="q" class="form-input cabinet-search-input" placeholder="<?= mb_h($searchPlaceholder) ?>" value="<?= mb_h(isset($_GET['q']) ? trim((string) $_GET['q']) : '') ?>">
      </form>
      <div class="cabinet-header-actions">
        <span class="cabinet-user-chip hide-mobile"><?= mb_h($user['name']) ?></span>
        <a href="logout.php" class="btn btn-outline btn-sm">Выйти</a>
      </div>
    </div>
  </header>
  <div class="cabinet-sidebar-backdrop" aria-hidden="true"></div>
    <?php
}

/**
 * @param 'catalog'|'learning'|'documents'|'overview'|'profile'|'settings'|'admin-users'|'admin-access' $active
 */
function mb_cabinet_footer_render(string $active): void
{
    $items = [
        ['catalog', 'knowledge-catalog.php', 'Каталог'],
        ['learning', 'learning-materials.php', 'Обучение'],
        ['documents', 'documents.php', 'Файлы'],
        ['overview', 'cabinet.php', 'Кабинет'],
    ];
    ?>
  <footer class="cabinet-footer" role="contentinfo">
    <div class="cabinet-footer-bar">
      <a href="index.php" class="cabinet-footer-brand">MindBase</a>
      <nav class="cabinet-footer-nav" aria-label="Быстрая навигация">
        <?php foreach ($items as [$key, $href, $label]): ?>
        <a href="<?= mb_h($href) ?>" class="cabinet-footer-link<?= $active === $key ? ' is-active' : '' ?>"><?= mb_h($label) ?></a>
        <?php endforeach; ?>
        <a href="cabinet-settings.php" class="cabinet-footer-link">Настройки</a>
        <a href="logout.php" class="cabinet-footer-link">Выйти</a>
      </nav>
    </div>
    <nav class="cabinet-bottom-nav" aria-label="Навигация (мобильная)">
      <?php foreach ($items as [$key, $href, $label]): ?>
      <a href="<?= mb_h($href) ?>" class="cabinet-bottom-nav__item<?= $active === $key ? ' is-active' : '' ?>"><?= mb_h($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </footer>
    <?php
}

function mb_cabinet_sidebar_open(string $active, string $suffix = '', string $mainClass = ''): void
{
    require_once __DIR__ . '/cabinet-nav.php';
    if ($mainClass === 'cabinet-main--article') {
        $classes = 'cabinet-main cabinet-main--article';
    } elseif ($mainClass !== '') {
        $classes = 'cabinet-main cabinet-main--fluid ' . $mainClass;
    } else {
        $classes = 'cabinet-main cabinet-main--fluid';
    }
    ?>
  <div class="cabinet-layout cabinet-layout--fluid">
    <aside class="cabinet-sidebar">
      <?php mb_cabinet_nav_render($active, $suffix); ?>
    </aside>
    <main class="<?= mb_h($classes) ?>">
    <?php
}

function mb_cabinet_sidebar_close(): void
{
    ?>
    </main>
  </div>
    <?php
}

/**
 * @param list<array{label:string,href?:string}> $items
 */
function mb_cabinet_breadcrumbs_render(array $items): void
{
    if ($items === []) {
        return;
    }
    ?>
      <nav class="cabinet-breadcrumb" aria-label="Навигация">
        <?php foreach ($items as $i => $item):
            if ($i > 0): ?>
        <span class="cabinet-breadcrumb-sep" aria-hidden="true">/</span>
            <?php endif;
            $href = $item['href'] ?? null;
            if ($href !== null && $href !== ''): ?>
        <a class="cabinet-breadcrumb-link" href="<?= mb_h($href) ?>"><?= mb_h($item['label']) ?></a>
            <?php else: ?>
        <span class="cabinet-breadcrumb-current" aria-current="page"><?= mb_h($item['label']) ?></span>
            <?php endif;
        endforeach; ?>
      </nav>
    <?php
}

/**
 * @param list<array{name:string,slug:string}> $categories
 */
function mb_cabinet_catalog_breadcrumbs(array $categories, ?string $currentLabel = null): void
{
    $items = [['label' => 'Каталог', 'href' => 'knowledge-catalog.php']];
    $last = count($categories) - 1;
    foreach ($categories as $i => $cat) {
        $item = ['label' => $cat['name']];
        if ($i !== $last || $currentLabel !== null) {
            $item['href'] = 'category.php?slug=' . rawurlencode($cat['slug']);
        }
        $items[] = $item;
    }
    if ($currentLabel !== null) {
        $items[] = ['label' => $currentLabel];
    }
    mb_cabinet_breadcrumbs_render($items);
}

function mb_cabinet_head(string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= mb_h($title) ?> — MindBase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css?v=<?= (int) @filemtime(__DIR__ . '/../styles.css') ?>">
</head>
<body class="cabinet-page">
  <div class="noise"></div>
    <?php
}

/** @param 'catalog'|'learning'|'documents'|'overview'|'profile'|'settings'|'admin-users'|'admin-access' $activeNav */
function mb_cabinet_foot(string $activeNav = 'overview'): void
{
    mb_cabinet_footer_render($activeNav);
    ?>
  <script src="cabinet.js?v=<?= (int) @filemtime(__DIR__ . '/../cabinet.js') ?>" defer></script>
</body>
</html>
    <?php
}
