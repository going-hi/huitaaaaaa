<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
mb_require_login();
$user = mb_current_user();
$cabinetNotice = mb_flash_take('cabinet_notice');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Личный кабинет — MindBase</title>
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
        <input type="search" class="form-input cabinet-search-input" placeholder="Быстрый поиск..." aria-label="Поиск">
      </div>
      <div class="cabinet-header-actions">
        <span class="cabinet-user-chip" title="<?= mb_h($user['email']) ?>"><?= mb_h($user['name']) ?></span>
        <a href="cabinet-base.php" class="btn btn-ghost btn-sm">Моя база</a>
        <a href="logout.php" class="btn btn-outline btn-sm">Выйти</a>
      </div>
    </div>
  </header>

  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head">
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('overview'); ?>
    </aside>

    <main class="cabinet-main">
      <?php if ($cabinetNotice !== null): ?>
      <p class="auth-alert auth-alert--success cabinet-notice" role="status"><?= mb_h($cabinetNotice) ?></p>
      <?php endif; ?>
      <div class="cabinet-dashboard">
        <h1 class="cabinet-page-title">Личный кабинет</h1>
        <p class="cabinet-page-lead">Обзор: краткая сводка по базе знаний и быстрые действия.</p>

        <div class="cabinet-stats-grid">
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value">24</span>
            <span class="cabinet-stat-label">Статей</span>
          </article>
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value">6</span>
            <span class="cabinet-stat-label">Разделов</span>
          </article>
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value">3</span>
            <span class="cabinet-stat-label">Участников</span>
          </article>
        </div>

        <h2 class="cabinet-section-heading">Быстрые действия</h2>
        <div class="cabinet-actions-grid">
          <a href="knowledge-catalog.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">📚</span>
            <span class="cabinet-action-title">Каталог знаний</span>
            <span class="cabinet-action-desc">Разделы и темы материалов организации</span>
          </a>
          <a href="learning-materials.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">🎓</span>
            <span class="cabinet-action-title">Обучающие материалы</span>
            <span class="cabinet-action-desc">Курсы, видео и чек-листы для команды</span>
          </a>
          <a href="documents.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">📄</span>
            <span class="cabinet-action-title">Документы</span>
            <span class="cabinet-action-desc">Регламенты, шаблоны и файлы</span>
          </a>
          <a href="cabinet-base.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">🗂️</span>
            <span class="cabinet-action-title">Моя база знаний</span>
            <span class="cabinet-action-desc">Документация, правила и справка по разделам</span>
          </a>
          <a href="cabinet-profile.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">👤</span>
            <span class="cabinet-action-title">Профиль</span>
            <span class="cabinet-action-desc">Имя и контакты для отображения в команде</span>
          </a>
          <a href="cabinet-settings.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">⚙️</span>
            <span class="cabinet-action-title">Настройки</span>
            <span class="cabinet-action-desc">Экспорт данных и параметры базы</span>
          </a>
        </div>

        <div class="cabinet-tip">
          <strong>Совет.</strong> Используйте поиск в разделе «Моя база знаний», чтобы быстрее находить нужные статьи.
        </div>
      </div>
    </main>
  </div>
</body>
</html>
