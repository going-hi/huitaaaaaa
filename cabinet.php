<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();
$cabinetNotice = mb_flash_take('cabinet_notice');
$stats = mb_dashboard_stats();
$feed = mb_activity_feed(4);

mb_cabinet_head('Личный кабинет');
mb_cabinet_header_render($user, 'Быстрый поиск...');
?>
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
        <p class="cabinet-greeting">Здравствуйте, <?= mb_h($user['name']) ?></p>
        <h1 class="cabinet-page-title">Личный кабинет</h1>
        <p class="cabinet-page-lead">Сводка по базе знаний: <?= (int) $stats['articles'] ?> статей, <?= (int) $stats['views_week'] ?> просмотров за 7 дней.</p>

        <div class="cabinet-stats-grid cabinet-stats-grid--4">
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value"><?= (int) $stats['articles'] ?></span>
            <span class="cabinet-stat-label">Статей в базе</span>
          </article>
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value"><?= (int) $stats['categories'] ?></span>
            <span class="cabinet-stat-label">Разделов с материалами</span>
          </article>
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value"><?= (int) $stats['team'] ?></span>
            <span class="cabinet-stat-label">Участников команды</span>
          </article>
          <article class="cabinet-stat-card">
            <span class="cabinet-stat-value"><?= (int) $stats['views_week'] ?></span>
            <span class="cabinet-stat-label">Просмотров за 7 дней</span>
          </article>
        </div>

        <h2 class="cabinet-section-heading">Лента активности</h2>
        <ul class="cabinet-feed cabinet-feed--compact">
          <?php foreach ($feed as $item): ?>
          <li class="cabinet-feed-item">
            <a href="<?= mb_h($item['url']) ?>" class="cabinet-feed-title" style="text-decoration:none;color:inherit"><?= mb_h($item['title']) ?></a>
            <span class="cabinet-feed-meta"><?= mb_h($item['meta']) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>

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
            <span class="cabinet-action-desc">Справка и навигация по разделам</span>
          </a>
          <a href="article-edit.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">✏️</span>
            <span class="cabinet-action-title">Новая статья</span>
            <span class="cabinet-action-desc">Добавить материал в каталог</span>
          </a>
          <a href="cabinet-settings.php" class="cabinet-action-card">
            <span class="cabinet-action-icon">⚙️</span>
            <span class="cabinet-action-title">Настройки</span>
            <span class="cabinet-action-desc">Экспорт данных и параметры базы</span>
          </a>
        </div>

        <div class="cabinet-tip">
          <strong>Совет.</strong> В строке поиска вверху можно искать сразу по всей базе. Для узких тем откройте <a href="knowledge-catalog.php">каталог знаний</a>.
        </div>
      </div>
    </main>
  </div>
<?php mb_cabinet_foot('overview'); ?>
