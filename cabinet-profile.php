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
  <title>Профиль — MindBase</title>
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
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('profile'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Профиль</h1>
      <p class="cabinet-page-lead">Данные из аккаунта (сохранение профиля в демо не подключено).</p>

      <div class="cabinet-panel">
        <form class="cabinet-form" action="#" method="get" onsubmit="return false;">
          <label class="form-label">
            <span>Отображаемое имя</span>
            <input type="text" name="name" class="form-input" value="<?= mb_h($user['name']) ?>" autocomplete="name">
          </label>
          <label class="form-label">
            <span>Email</span>
            <input type="email" name="email" class="form-input form-input-readonly" value="<?= mb_h($user['email']) ?>" readonly title="Задаётся при регистрации">
          </label>
          <label class="form-label">
            <span>Должность (необязательно)</span>
            <input type="text" name="role" class="form-input" placeholder="Например, ведущий разработчик">
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
          </div>
        </form>
      </div>

      <h2 class="cabinet-section-heading">Аватар</h2>
      <div class="cabinet-panel cabinet-panel--muted">
        <p class="cabinet-muted-text">Загрузка фото не реализована — сухая вёрстка.</p>
      </div>
    </main>
  </div>
</body>
</html>
