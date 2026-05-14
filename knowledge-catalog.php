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
  <title>Каталог знаний — MindBase</title>
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
        <input type="search" class="form-input cabinet-search-input" placeholder="Поиск по каталогу..." aria-label="Поиск по каталогу">
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
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Каталог знаний</h1>
      <p class="cabinet-page-lead">Единая структура материалов компании: разделы, вложенные темы и поиск по тегам.</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по каталогу">
        <span class="cabinet-pill"><strong>128</strong> материалов</span>
        <span class="cabinet-pill"><strong>14</strong> разделов</span>
        <span class="cabinet-pill"><strong>37</strong> тегов</span>
        <span class="cabinet-pill cabinet-pill--accent">Обновлено сегодня · 6 записей</span>
      </div>

      <div class="cabinet-actions-grid cabinet-actions-grid--catalog">
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">📂</span>
          <span class="cabinet-action-title">Онбординг</span>
          <span class="cabinet-action-desc">Инструкции для новых сотрудников, доступы, первые шаги в команде.</span>
          <span class="cabinet-card-foot">22 статьи · правка 2 дня назад</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">⚙️</span>
          <span class="cabinet-action-title">Продукт и API</span>
          <span class="cabinet-action-desc">Описание сервисов, OpenAPI, схемы интеграций, лимиты и SLA.</span>
          <span class="cabinet-card-foot">34 статьи · правка вчера</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">🛟</span>
          <span class="cabinet-action-title">Поддержка</span>
          <span class="cabinet-action-desc">Типовые кейсы, эскалации L2/L3, макросы ответов в почте и чате.</span>
          <span class="cabinet-card-foot">41 статья · 5 новых за неделю</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">🔒</span>
          <span class="cabinet-action-title">Безопасность и комплаенс</span>
          <span class="cabinet-action-desc">ИБ-политики, 152-ФЗ, учёт доступов, реагирование на инциденты.</span>
          <span class="cabinet-card-foot">18 статей · аудит 14.05.2026</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">📣</span>
          <span class="cabinet-action-title">Маркетинг и продажи</span>
          <span class="cabinet-action-desc">Питчи, кейсы клиентов, прайс-листы, ответы на тендеры.</span>
          <span class="cabinet-card-foot">13 материалов · сезонное обновление</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">👥</span>
          <span class="cabinet-action-title">HR и офис</span>
          <span class="cabinet-action-desc">Отпуска, ДМС, график, удалёнка, заказ пропусков и переговорок.</span>
          <span class="cabinet-card-foot">19 статей · актуализировано 01.05.2026</span>
        </article>
      </div>

      <h2 class="cabinet-section-heading">Недавно в каталоге</h2>
      <ul class="cabinet-feed">
        <li class="cabinet-feed-item">
          <span class="cabinet-feed-title">Runbook: падение платёжного шлюза</span>
          <span class="cabinet-feed-meta">Разработка · Мария С. · 14.05.2026 11:40</span>
        </li>
        <li class="cabinet-feed-item">
          <span class="cabinet-feed-title">Чек-лист онбординга Sales</span>
          <span class="cabinet-feed-meta">Онбординг · Андрей П. · 14.05.2026 09:15</span>
        </li>
        <li class="cabinet-feed-item">
          <span class="cabinet-feed-title">Обновление API v2.3 — breaking changes</span>
          <span class="cabinet-feed-meta">Продукт и API · Елена В. · 13.05.2026 16:02</span>
        </li>
        <li class="cabinet-feed-item">
          <span class="cabinet-feed-title">Шаблон ответа: типовая ошибка авторизации</span>
          <span class="cabinet-feed-meta">Поддержка · Линия 1 · 13.05.2026 14:30</span>
        </li>
        <li class="cabinet-feed-item">
          <span class="cabinet-feed-title">Памятка по работе с персональными данными</span>
          <span class="cabinet-feed-meta">Безопасность · Юридический отдел · 12.05.2026 10:00</span>
        </li>
      </ul>

      <h2 class="cabinet-section-heading">Дерево разделов</h2>
      <div class="cabinet-panel">
        <ul class="cabinet-tree">
          <li><strong>MindBase — корпоративная база</strong>
            <ul>
              <li>Онбординг
                <ul>
                  <li>Первый день и доступы</li>
                  <li>Инструменты (Git, CI, таск-трекер)</li>
                  <li>Кодстайл и review</li>
                </ul>
              </li>
              <li>Разработка и эксплуатация
                <ul>
                  <li>Архитектура и ADR</li>
                  <li>Гайд по Git / trunk-based</li>
                  <li>Дежурства и инциденты</li>
                  <li>Нагрузочное тестирование</li>
                </ul>
              </li>
              <li>Продукт
                <ul>
                  <li>Roadmap и релизы</li>
                  <li>Документация API</li>
                </ul>
              </li>
              <li>Клиентский успех
                <ul>
                  <li>Онбординг клиента</li>
                  <li>База знаний для саппорта</li>
                </ul>
              </li>
              <li>Корпоративный блок
                <ul>
                  <li>HR: отпуска, бенефиты</li>
                  <li>Закупки и офис</li>
                </ul>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      <div class="cabinet-tip">
        <strong>Поиск.</strong> По умолчанию учитываются заголовок, текст статьи и теги. Фильтр по разделу — в панели слева от результатов.
      </div>
    </main>
  </div>
</body>
</html>
