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
      <p class="cabinet-page-lead">Программа адаптации и повышения квалификации: видеолекции, статьи и тесты с фиксацией прогресса.</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по обучению">
        <span class="cabinet-pill"><strong>11</strong> курсов</span>
        <span class="cabinet-pill"><strong>48</strong> уроков</span>
        <span class="cabinet-pill">Средняя оценка <strong>4,7</strong> / 5</span>
        <span class="cabinet-pill cabinet-pill--accent">Индивидуальный план: <strong>62%</strong> (май 2026)</span>
      </div>

      <h2 class="cabinet-section-heading">Витрина курсов</h2>
      <div class="cabinet-course-list">
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag cabinet-tag--video">Видео</span>
            <h3 class="cabinet-course-name">Введение в MindBase</h3>
            <p class="cabinet-course-desc">Интерфейс, роли, приглашение коллег, личный vs командный контент.</p>
            <p class="cabinet-course-author">Курс · Центр компетенций · обновлено 08.05.2026</p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time">42 мин</span>
            <div class="cabinet-progress" role="img" aria-label="Прогресс 100%"><span style="width:100%"></span></div>
            <span class="cabinet-course-progress-label">Завершено</span>
          </div>
        </article>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag cabinet-tag--doc">Текст</span>
            <h3 class="cabinet-course-name">Оформление статей и Markdown</h3>
            <p class="cabinet-course-desc">Заголовки, списки, таблицы, вставка кода, внутренние ссылки между статьями.</p>
            <p class="cabinet-course-author">Модуль · Команда контента · 120 мин на прохождение</p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time">2 ч</span>
            <div class="cabinet-progress" aria-label="Прогресс 75%"><span style="width:75%"></span></div>
            <span class="cabinet-course-progress-label">75%</span>
          </div>
        </article>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag cabinet-tag--mix">Смешанный</span>
            <h3 class="cabinet-course-name">Документы, версии и согласование</h3>
            <p class="cabinet-course-desc">Загрузка файлов, история изменений, маршрут согласования с юридическим отделом.</p>
            <p class="cabinet-course-author">Модуль · ИБ и архив · дедлайн модуля 20.05.2026</p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time">3 ч 20 мин</span>
            <div class="cabinet-progress" aria-label="Прогресс 40%"><span style="width:40%"></span></div>
            <span class="cabinet-course-progress-label">40%</span>
          </div>
        </article>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag cabinet-tag--video">Видео</span>
            <h3 class="cabinet-course-name">Инцидент-менеджмент и постмортемы</h3>
            <p class="cabinet-course-desc">Классификация инцидентов, коммуникации, шаблон постмортема в базе знаний.</p>
            <p class="cabinet-course-author">SRE-гильдия · рейтинг 4,9</p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time">55 мин</span>
            <div class="cabinet-progress" aria-label="Прогресс 0%"><span style="width:0%"></span></div>
            <span class="cabinet-course-progress-label">Не начато</span>
          </div>
        </article>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag cabinet-tag--quiz">Тест</span>
            <h3 class="cabinet-course-name">Аттестация: защита персональных данных</h3>
            <p class="cabinet-course-desc">20 вопросов, проходной балл 80%. Обязательно для доступа к разделу «Клиенты».</p>
            <p class="cabinet-course-author">Комплаенс · действует до 31.12.2026</p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time">30 мин</span>
            <div class="cabinet-progress" aria-label="Прогресс ожидание"><span style="width:0%"></span></div>
            <span class="cabinet-course-progress-label">Очередь</span>
          </div>
        </article>
      </div>

      <h2 class="cabinet-section-heading">Треки по ролям</h2>
      <div class="cabinet-panel">
        <ol class="cabinet-learning-list">
          <li><strong>Руководитель проекта.</strong> Постановка целей в базе, теги релизов, связывание задач Jira со статьями.</li>
          <li><strong>Разработчик.</strong> Шаблоны ADR, дежурства, ссылки на логи и дашборды мониторинга.</li>
          <li><strong>Поддержка L1/L2.</strong> Быстрый поиск макросов, эскалация в L3, обратная связь в статью.</li>
          <li><strong>Новичок.</strong> Пошаговый чек-лист первых двух недель с отметками выполнения.</li>
        </ol>
      </div>
    </main>
  </div>
</body>
</html>
