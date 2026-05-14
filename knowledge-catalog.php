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
      <p class="cabinet-page-lead">Структура разделов и материалов вашей организации. Ниже — демонстрационное дерево и карточки тем.</p>

      <div class="cabinet-actions-grid cabinet-actions-grid--catalog">
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">📂</span>
          <span class="cabinet-action-title">Онбординг</span>
          <span class="cabinet-action-desc">Инструкции для новых сотрудников, доступы, первые шаги в команде.</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">⚙️</span>
          <span class="cabinet-action-title">Продукт и API</span>
          <span class="cabinet-action-desc">Описание сервисов, контракты API, схемы интеграций и лимиты.</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">🛟</span>
          <span class="cabinet-action-title">Поддержка</span>
          <span class="cabinet-action-desc">Типовые кейсы, эскалации, шаблоны ответов для клиентской линии.</span>
        </article>
        <article class="cabinet-action-card cabinet-action-card--static">
          <span class="cabinet-action-icon">🔒</span>
          <span class="cabinet-action-title">Безопасность</span>
          <span class="cabinet-action-desc">Политики, работа с персональными данными, инциденты и аудит.</span>
        </article>
      </div>

      <h2 class="cabinet-section-heading">Иерархия (пример)</h2>
      <div class="cabinet-panel">
        <ul class="cabinet-tree">
          <li><strong>Корень</strong>
            <ul>
              <li>Онбординг
                <ul>
                  <li>Первый день</li>
                  <li>Инструменты</li>
                </ul>
              </li>
              <li>Разработка
                <ul>
                  <li>Гайд по Git</li>
                  <li>Code review</li>
                </ul>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      <div class="cabinet-tip">
        <strong>Подсказка.</strong> Полнофункциональный каталог с правами и фильтрами можно подключить на следующем этапе проекта.
      </div>
    </main>
  </div>
</body>
</html>
