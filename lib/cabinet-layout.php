<?php

declare(strict_types=1);

require_once __DIR__ . '/roles.php';

/**
 * @param array{id:int,name:string,email:string,role?:string} $user
 */
function mb_cabinet_header_render(array $user, string $searchPlaceholder = 'Поиск...', bool $narrowSearch = true): void
{
    $searchClass = $narrowSearch ? 'cabinet-header-search cabinet-header-search--narrow' : 'cabinet-header-search';
    $role = mb_user_role($user);
    ?>
  <header class="cabinet-header">
    <div class="cabinet-header-inner">
      <a href="index.php" class="logo">
        <img src="logo.png" alt="MindBase" class="logo-img">
        <span>MindBase</span>
      </a>
      <form class="<?= $searchClass ?>" action="search.php" method="get" role="search">
        <input type="search" name="q" class="form-input cabinet-search-input" placeholder="<?= mb_h($searchPlaceholder) ?>" aria-label="<?= mb_h($searchPlaceholder) ?>" value="<?= mb_h(isset($_GET['q']) ? trim((string) $_GET['q']) : '') ?>">
      </form>
      <div class="cabinet-header-actions">
        <span class="<?= mb_h(mb_role_badge_class($role)) ?>" title="<?= mb_h(mb_role_label($role)) ?>"><?= mb_h(mb_role_label($role)) ?></span>
        <span class="cabinet-user-chip" title="<?= mb_h($user['email']) ?>"><?= mb_h($user['name']) ?></span>
        <a href="cabinet.php" class="btn btn-ghost btn-sm">Личный кабинет</a>
        <a href="logout.php" class="btn btn-outline btn-sm">Выйти</a>
      </div>
    </div>
  </header>
    <?php
}

function mb_cabinet_head(string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
