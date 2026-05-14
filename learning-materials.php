<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
mb_require_login();
$user = mb_current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Обучающие материалы — MindBase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="cabinet-page">
  <div class="noise"></div>

  <header class="cabinet-header">
    <div class="cabinet-header-inner">
      <a href="index.php" class="logo">
        <img src="logo.png" alt="MindBase" class="logo-img">
        <span>MindBase</span>
      </a>
      <div class="cabinet-header-search cabinet-header-search--narrow">
        <input type="search" class="form-input cabinet-search-input" placeholder="Поиск по материалам..." aria-label="Поиск по материалам">
      </div>
      <div class="cabinet-header-actions">
        <span class="cabinet-user-chip" title="<?= mb_h($user['email']) ?>"><?= mb_h($user['name']) ?></span>
        <a href="cabinet.php" class="btn btn-ghost btn-sm">Обзор</a>
        <a href="logout.php" class="btn btn-outline btn-sm">Выйти</a>
      </div>
    </div>
  </header>

  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head">
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('learning'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Обучающие материалы</h1>
      <p class="cabinet-page-lead">Курсы, видео и чек-листы для развития команды. Список ниже иллюстративный.</p>

      <div class="cabinet-actions-grid">
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">▶️</span>
          <span class="cabinet-action-title">Введение в MindBase</span>
          <span class="cabinet-action-desc">15 мин · как устроены разделы, поиск и роли</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">📋</span>
          <span class="cabinet-action-title">Оформление статей</span>
          <span class="cabinet-action-desc">Текст и Markdown · хорошие практики</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">🎯</span>
          <span class="cabinet-action-title">Работа с документами</span>
          <span class="cabinet-action-desc">Загрузка, версии, согласование</span>
        </article>
      </div>

      <h2 class="cabinet-section-heading">Модули (пример)</h2>
      <div class="cabinet-panel">
        <ol class="cabinet-learning-list">
          <li><strong>Модуль 1.</strong> Навигация и права доступа.</li>
          <li><strong>Модуль 2.</strong> Создание и редактирование материалов.</li>
          <li><strong>Модуль 3.</strong> Поиск, теги и аналитика просмотров.</li>
        </ol>
      </div>
    </main>
  </div>
</body>
</html>
