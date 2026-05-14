<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
mb_require_login();
$user = mb_current_user();
$cabinetNotice = mb_flash_take('cabinet_notice');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Обзор — Личный кабинет MindBase</title>
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
        <h2 class="cabinet-sidebar-title">Личный кабинет</h2>
      </div>
      <nav class="cabinet-nav">
        <p class="cabinet-nav-label">Главное</p>
        <a href="cabinet.php" class="cabinet-nav-item active">Обзор</a>
        <a href="cabinet-base.php" class="cabinet-nav-item">Моя база знаний</a>
        <p class="cabinet-nav-label">Аккаунт</p>
        <a href="cabinet-profile.php" class="cabinet-nav-item">Профиль</a>
        <a href="cabinet-settings.php" class="cabinet-nav-item">Настройки</a>
      </nav>
    </aside>

    <main class="cabinet-main">
      <?php if ($cabinetNotice !== null): ?>
      <p class="auth-alert auth-alert--success cabinet-notice" role="status"><?= mb_h($cabinetNotice) ?></p>
      <?php endif; ?>
      <div class="cabinet-dashboard">
        <h1 class="cabinet-page-title">Добро пожаловать</h1>
        <p class="cabinet-page-lead">Краткая сводка по вашей базе знаний и быстрые действия.</p>

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
          <a href="cabinet-base.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">📚</span>
            <span class="cabinet-action-title">Открыть базу</span>
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
