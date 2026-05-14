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
  <title>Моя база знаний — MindBase</title>
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
      <div class="cabinet-header-search">
        <input type="search" class="form-input cabinet-search-input" placeholder="Поиск по базе знаний...">
      </div>
      <div class="cabinet-header-actions">
        <span class="cabinet-user-chip"><?= mb_h($user['name']) ?></span>
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
      <?php
      mb_cabinet_nav_render(
          'base',
          <<<'HTML'
        <p class="cabinet-nav-label">Статьи</p>
        <a href="#welcome" class="cabinet-nav-item cabinet-nav-item--sub active" data-hash-nav>Добро пожаловать</a>
        <a href="#rules" class="cabinet-nav-item cabinet-nav-item--sub" data-hash-nav>Правила оформления</a>
        <a href="#sections" class="cabinet-nav-item cabinet-nav-item--sub" data-hash-nav>Как добавить раздел</a>
        <a href="#search" class="cabinet-nav-item cabinet-nav-item--sub" data-hash-nav>Поиск по базе</a>
        <a href="#export" class="cabinet-nav-item cabinet-nav-item--sub" data-hash-nav>Экспорт данных</a>
HTML
      );
      ?>
    </aside>

    <main class="cabinet-main">
      <article id="welcome" class="cabinet-article">
        <h1>Добро пожаловать</h1>
        <p class="cabinet-article-meta">Главная · Обновлено сегодня</p>
        <p>Это ваша личная база знаний. Здесь вы можете хранить документацию, инструкции и заметки — всё в одном месте.</p>
        <h2>С чего начать</h2>
        <ul>
          <li>Создайте раздел в боковой панели для новой темы.</li>
          <li>Добавляйте статьи в формате Markdown: заголовки, списки, код.</li>
          <li>Используйте поиск в шапке, чтобы быстро находить нужное.</li>
        </ul>
        <p>Платформа MindBase сделана на базе ООО Инним и полностью бесплатна.</p>
      </article>

      <article id="rules" class="cabinet-article">
        <h1>Правила оформления статей</h1>
        <p class="cabinet-article-meta">Документация · 2 дня назад</p>
        <p>Единый стиль помогает быстрее находить информацию. Рекомендуем придерживаться простых правил.</p>
        <h2>Структура</h2>
        <ul>
          <li>Один заголовок первого уровня (H1) в начале статьи.</li>
          <li>Подзаголовки H2 и H3 для разбивки на блоки.</li>
          <li>Короткие абзацы и списки вместо длинных простыней текста.</li>
        </ul>
        <h2>Форматирование</h2>
        <p>Используйте <strong>жирный</strong> и <em>курсив</em> для выделения. Код оформляйте в блоках:</p>
        <pre><code>// Пример кода
const example = "MindBase";</code></pre>
      </article>

      <article id="sections" class="cabinet-article">
        <h1>Как добавить раздел</h1>
        <p class="cabinet-article-meta">Справка · Неделю назад</p>
        <p>Разделы помогают группировать статьи по темам. Например: «Онбординг», «API», «Поддержка».</p>
        <h2>Шаги</h2>
        <ol>
          <li>В боковой панели нажмите «Новый раздел».</li>
          <li>Введите название, например «Документация проекта».</li>
          <li>Сохраните — раздел появится в списке слева.</li>
        </ol>
        <p>Внутри раздела можно создавать подразделы и отдельные статьи.</p>
      </article>

      <article id="search" class="cabinet-article">
        <h1>Поиск по базе</h1>
        <p class="cabinet-article-meta">Справка</p>
        <p>Поиск в шапке страницы ищет по заголовкам и тексту всех статей.</p>
        <h2>Подсказки</h2>
        <ul>
          <li>Вводите несколько слов — результаты будут точнее.</li>
          <li>Поиск учитывает морфологию: «добавить» найдёт и «добавление».</li>
          <li>Можно фильтровать по разделу в результатах.</li>
        </ul>
      </article>

      <article id="export" class="cabinet-article">
        <h1>Экспорт данных</h1>
        <p class="cabinet-article-meta">Настройки</p>
        <p>Вы можете в любой момент выгрузить содержимое базы знаний.</p>
        <h2>Форматы</h2>
        <ul>
          <li><strong>Markdown</strong> — все статьи в виде .md файлов, удобно для бэкапов и переноса.</li>
          <li><strong>HTML</strong> — готовые страницы для просмотра офлайн.</li>
        </ul>
        <p>Экспорт доступен в настройках базы. Данные остаются у вас.</p>
      </article>
    </main>
  </div>

  <script>
    (function() {
      var hashNav = document.querySelectorAll('[data-hash-nav]');
      var articles = document.querySelectorAll('.cabinet-article');
      function updateActive() {
        var id = location.hash.slice(1) || 'welcome';
        hashNav.forEach(function(el) {
          var href = el.getAttribute('href') || '';
          el.classList.toggle('active', href === '#' + id);
        });
        articles.forEach(function(el) {
          el.classList.toggle('is-visible', el.id === id);
        });
      }
      window.addEventListener('hashchange', updateActive);
      window.addEventListener('load', function() {
        if (!location.hash) {
          location.replace('#welcome');
        }
        updateActive();
      });
    })();
  </script>
</body>
</html>
