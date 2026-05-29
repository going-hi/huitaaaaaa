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
 * @param 'catalog'|'learning'|'documents'|'overview'|'base'|'profile'|'settings' $active
 */
function mb_cabinet_bottom_nav_render(string $active): void
{
    $items = [
        ['catalog', 'knowledge-catalog.php', 'Каталог'],
        ['learning', 'learning-materials.php', 'Обучение'],
        ['documents', 'documents.php', 'Файлы'],
        ['overview', 'cabinet.php', 'Кабинет'],
    ];
    ?>
  <nav class="cabinet-bottom-nav" aria-label="Навигация">
    <?php foreach ($items as [$key, $href, $label]): ?>
    <a href="<?= mb_h($href) ?>" class="cabinet-bottom-nav__item<?= $active === $key ? ' is-active' : '' ?>"><?= mb_h($label) ?></a>
    <?php endforeach; ?>
  </nav>
    <?php
}

function mb_cabinet_sidebar_open(string $active, string $suffix = '', string $mainClass = ''): void
{
    require_once __DIR__ . '/cabinet-nav.php';
    $mainAttr = $mainClass !== '' ? ' class="cabinet-main ' . mb_h($mainClass) . '"' : ' class="cabinet-main"';
    ?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <?php mb_cabinet_nav_render($active, $suffix); ?>
    </aside>
    <main<?= $mainAttr ?>>
    <?php
}

function mb_cabinet_sidebar_close(): void
{
    ?>
    </main>
  </div>
    <?php
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
  <link rel="stylesheet" href="styles.css">
</head>
<body class="cabinet-page">
  <div class="noise"></div>
    <?php
}

/** @param 'catalog'|'learning'|'documents'|'overview'|'base'|'profile'|'settings' $activeNav */
function mb_cabinet_foot(string $activeNav = 'overview'): void
{
    mb_cabinet_bottom_nav_render($activeNav);
    ?>
  <script src="cabinet.js" defer></script>
</body>
</html>
    <?php
}
