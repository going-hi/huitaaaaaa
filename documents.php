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
  <title>Документы — MindBase</title>
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
        <input type="search" class="form-input cabinet-search-input" placeholder="Поиск по документам..." aria-label="Поиск по документам">
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
      <?php mb_cabinet_nav_render('documents'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Документы</h1>
      <p class="cabinet-page-lead">Регламенты, шаблоны и выгрузки. Таблица ниже — статичное демо для интерфейса.</p>

      <div class="cabinet-panel cabinet-panel--table">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table">
            <thead>
              <tr>
                <th>Название</th>
                <th>Тип</th>
                <th>Обновлено</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Политика информационной безопасности</td>
                <td>PDF</td>
                <td>2026-04-12</td>
              </tr>
              <tr>
                <td>Шаблон технического задания</td>
                <td>DOCX</td>
                <td>2026-03-01</td>
              </tr>
              <tr>
                <td>Реестр интеграций</td>
                <td>XLSX</td>
                <td>2026-05-02</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Действия</h2>
      <div class="cabinet-inline-btns">
        <button type="button" class="btn btn-outline" disabled>Загрузить файл</button>
        <button type="button" class="btn btn-ghost" disabled>Создать папку</button>
      </div>
      <p class="cabinet-page-lead" style="margin-top: 16px; font-size: 0.9rem;">Загрузка и хранение файлов в демо не подключены — только вёрстка страницы.</p>
    </main>
  </div>
</body>
</html>
