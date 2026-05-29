<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['progress'])) {
    if (mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        mb_course_update_progress($user['id'], (int) $_POST['course_id'], (int) $_POST['progress']);
        mb_flash_set('cabinet_notice', 'Прогресс сохранён.');
    }
    header('Location: learning-materials.php', true, 302);
    exit;
}

$stats = mb_learning_stats($user['id']);
$courses = mb_courses_list($user['id']);
$notice = mb_flash_take('cabinet_notice');
$completed = 0;
foreach ($courses as $c) {
    if ((int) $c['progress_percent'] >= 100) {
        $completed++;
    }
}

$tracks = [
    ['Руководитель', 'Постановка целей, теги релизов, связь статей с задачами.', 'knowledge-catalog.php'],
    ['Разработчик', 'ADR, runbook, дежурства — в каталоге раздел «Разработка».', 'category.php?slug=dev'],
    ['Поддержка', 'Макросы и эскалации в разделе «Поддержка».', 'category.php?slug=support'],
    ['Новичок', 'Чек-лист онбординга для старта в команде.', 'article.php?slug=checklist-sales-onboarding'],
];

mb_cabinet_head('Обучающие материалы');
mb_cabinet_header_render($user, 'Поиск...');
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head">
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('learning'); ?>
    </aside>

    <main class="cabinet-main">
      <?php if ($notice !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($notice) ?></p>
      <?php endif; ?>

      <div class="learn-hero">
        <div class="learn-hero__text">
          <h1 class="cabinet-page-title">Обучающие материалы</h1>
          <p class="cabinet-page-lead">Курсы с фиксацией прогресса. Завершено <?= (int) $completed ?> из <?= count($courses) ?>.</p>
        </div>
        <div class="learn-hero__ring" aria-label="Общий прогресс <?= (int) $stats['avg_progress'] ?>%">
          <svg viewBox="0 0 120 120" class="learn-ring">
            <circle cx="60" cy="60" r="52" class="learn-ring__bg"></circle>
            <circle cx="60" cy="60" r="52" class="learn-ring__fg" style="stroke-dashoffset: <?= 326.7 * (1 - $stats['avg_progress'] / 100) ?>"></circle>
          </svg>
          <span class="learn-ring__value"><?= (int) $stats['avg_progress'] ?>%</span>
        </div>
      </div>

      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><strong><?= (int) $stats['courses'] ?></strong> курсов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['lessons'] ?></strong> модулей</span>
        <span class="cabinet-pill cabinet-pill--accent"><strong><?= (int) $completed ?></strong> завершено</span>
      </div>

      <h2 class="cabinet-section-heading">Ваши курсы</h2>
      <?php if ($courses === []): ?>
      <div class="cabinet-empty-state"><p>Курсы пока не добавлены. Запустите seed.</p></div>
      <?php else: ?>
      <div class="learn-grid">
        <?php foreach ($courses as $course):
            $p = (int) $course['progress_percent'];
            $status = $p >= 100 ? 'done' : ($p > 0 ? 'progress' : 'new');
            ?>
        <article class="learn-card learn-card--<?= $status ?>">
          <div class="learn-card__head">
            <span class="cabinet-tag <?= mb_h(mb_course_type_class($course['course_type'])) ?>"><?= mb_h(mb_course_type_label($course['course_type'])) ?></span>
            <span class="learn-card__time"><?= mb_h(mb_format_duration((int) $course['duration_minutes'])) ?></span>
          </div>
          <h3 class="learn-card__title"><?= mb_h($course['title']) ?></h3>
          <p class="learn-card__desc"><?= mb_h($course['description']) ?></p>
          <p class="learn-card__author"><?= mb_h($course['author_label']) ?></p>
          <div class="learn-card__progress">
            <div class="cabinet-progress learn-card__bar" aria-hidden="true"><span style="width:<?= $p ?>%"></span></div>
            <span class="learn-card__percent"><?= $p >= 100 ? 'Завершено' : ($p > 0 ? $p . '%' : 'Не начато') ?></span>
          </div>
          <div class="learn-card__actions">
            <?php foreach ([0, 25, 50, 75, 100] as $step): ?>
            <form method="post" class="learn-progress-form" data-course-progress>
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
              <input type="hidden" name="progress" value="<?= $step ?>">
              <button type="submit" class="learn-progress-btn<?= $p === $step ? ' is-active' : '' ?>" title="<?= $step ?>%"><?= $step ?>%</button>
            </form>
            <?php endforeach; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Треки по ролям</h2>
      <div class="learn-tracks">
        <?php foreach ($tracks as [$title, $desc, $url]): ?>
        <a href="<?= mb_h($url) ?>" class="learn-track-card">
          <span class="learn-track-card__title"><?= mb_h($title) ?></span>
          <span class="learn-track-card__desc"><?= mb_h($desc) ?></span>
          <span class="learn-track-card__arrow">→</span>
        </a>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
<?php mb_cabinet_foot('learning'); ?>
