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
      <p class="cabinet-page-lead">Регламенты, шаблоны договоров и служебные файлы. Хранилище версионируется; ниже актуальные экземпляры на май 2026.</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по документам">
        <span class="cabinet-pill"><strong>186</strong> файлов</span>
        <span class="cabinet-pill"><strong>24</strong> папки</span>
        <span class="cabinet-pill">Занято <strong>2,4 ГБ</strong></span>
        <span class="cabinet-pill cabinet-pill--accent">Последняя синхронизация · 14.05.2026 08:00</span>
      </div>

      <div class="cabinet-panel cabinet-panel--table">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table">
            <thead>
              <tr>
                <th>Название</th>
                <th>Тип</th>
                <th>Размер</th>
                <th>Ответственный</th>
                <th>Обновлено</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Политика информационной безопасности v3.2</td>
                <td>PDF</td>
                <td>842 КБ</td>
                <td>Служба ИБ</td>
                <td>12.04.2026</td>
              </tr>
              <tr>
                <td>Шаблон технического задания (внутренний)</td>
                <td>DOCX</td>
                <td>128 КБ</td>
                <td>Офис развития</td>
                <td>01.03.2026</td>
              </tr>
              <tr>
                <td>Реестр интеграций и контрагентов</td>
                <td>XLSX</td>
                <td>356 КБ</td>
                <td>PMO</td>
                <td>02.05.2026</td>
              </tr>
              <tr>
                <td>Соглашение о неконкуренции (пример заполнения)</td>
                <td>DOCX</td>
                <td>95 КБ</td>
                <td>Юридический отдел</td>
                <td>22.01.2026</td>
              </tr>
              <tr>
                <td>Брендбук MindBase — печать и презентации</td>
                <td>PDF</td>
                <td>12,4 МБ</td>
                <td>Маркетинг</td>
                <td>18.04.2026</td>
              </tr>
              <tr>
                <td>Инструкция: доступ к продакшен-логам</td>
                <td>PDF</td>
                <td>620 КБ</td>
                <td>SRE</td>
                <td>10.05.2026</td>
              </tr>
              <tr>
                <td>Отчёт по аудиту документооборота Q1</td>
                <td>PDF</td>
                <td>1,1 МБ</td>
                <td>Внутренний контроль</td>
                <td>05.04.2026</td>
              </tr>
              <tr>
                <td>Шаблон акта приёмки работ</td>
                <td>DOCX</td>
                <td>71 КБ</td>
                <td>Финансы</td>
                <td>28.02.2026</td>
              </tr>
              <tr>
                <td>Каталог API-ключей (выгрузка; конфиденциально)</td>
                <td>CSV</td>
                <td>48 КБ</td>
                <td>ИБ</td>
                <td>14.05.2026</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Избранные папки</h2>
      <div class="cabinet-folder-chips" aria-label="Папки">
        <span class="cabinet-folder-chip">/юридические/</span>
        <span class="cabinet-folder-chip">/продукт/specs/</span>
        <span class="cabinet-folder-chip">/клиенты/nda/</span>
        <span class="cabinet-folder-chip">/кадры/линейный состав/</span>
      </div>

      <h2 class="cabinet-section-heading">Действия</h2>
      <div class="cabinet-inline-btns">
        <button type="button" class="btn btn-primary">Загрузить файл</button>
        <button type="button" class="btn btn-outline">Создать папку</button>
        <button type="button" class="btn btn-ghost">Экспорт оглавления</button>
      </div>
      <p class="cabinet-page-lead cabinet-page-lead--small">Кнопки в учебном стенде не выполняют загрузку — интерфейс показывает типичный рабочий сценарий.</p>
    </main>
  </div>
</body>
</html>
