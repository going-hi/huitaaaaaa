<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
mb_require_login();
$user = mb_current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Настройки — MindBase</title>
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
      <div class="cabinet-header-spacer"></div>
      <div class="cabinet-header-actions">
        <span class="cabinet-user-chip"><?= mb_h($user['name']) ?></span>
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
        <a href="cabinet.php" class="cabinet-nav-item">Обзор</a>
        <a href="cabinet-base.php" class="cabinet-nav-item">Моя база знаний</a>
        <p class="cabinet-nav-label">Аккаунт</p>
        <a href="cabinet-profile.php" class="cabinet-nav-item">Профиль</a>
        <a href="cabinet-settings.php" class="cabinet-nav-item active">Настройки</a>
      </nav>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Настройки</h1>
      <p class="cabinet-page-lead">Экспорт и параметры рабочего пространства (без бэкенда).</p>

      <h2 class="cabinet-section-heading">Экспорт базы знаний</h2>
      <div class="cabinet-panel">
        <p class="cabinet-muted-text" style="margin-bottom: 16px;">Выгрузите копию контента в удобном формате.</p>
        <div class="cabinet-inline-btns">
          <button type="button" class="btn btn-outline">Скачать Markdown</button>
          <button type="button" class="btn btn-outline">Скачать HTML</button>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Рабочее пространство</h2>
      <div class="cabinet-panel">
        <label class="form-label">
          <span>Название базы</span>
          <input type="text" class="form-input" value="Команда «Инним» — внутренняя база">
        </label>
        <div class="cabinet-form-actions">
          <button type="button" class="btn btn-primary">Сохранить</button>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Опасная зона</h2>
      <div class="cabinet-panel cabinet-panel--danger">
        <p class="cabinet-muted-text">Удаление базы необратимо. Перед удалением рекомендуем экспортировать данные.</p>
        <button type="button" class="btn btn-outline" style="border-color: rgba(248, 113, 113, 0.5); color: #fca5a5;">Удалить рабочее пространство</button>
      </div>
    </main>
  </div>
</body>
</html>
