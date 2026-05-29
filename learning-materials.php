<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
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

mb_cabinet_head('Обучение');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('learning');
?>
      <?php if ($notice !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($notice) ?></p>
      <?php endif; ?>

      <h1 class="cabinet-page-title">Обучение</h1>
      <p class="cabinet-page-lead">Курсы команды. Ваш средний прогресс: <strong><?= (int) $stats['avg_progress'] ?>%</strong>.</p>

      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><strong><?= (int) $stats['courses'] ?></strong> курсов</span>
        <span class="cabinet-pill cabinet-pill--accent">Прогресс <?= (int) $stats['avg_progress'] ?>%</span>
      </div>

      <div class="cabinet-course-list">
        <?php foreach ($courses as $course):
            $p = (int) $course['progress_percent'];
            $next = min(100, $p + 25);
            ?>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag <?= mb_h(mb_course_type_class($course['course_type'])) ?>"><?= mb_h(mb_course_type_label($course['course_type'])) ?></span>
            <h3 class="cabinet-course-name"><?= mb_h($course['title']) ?></h3>
            <p class="cabinet-course-desc"><?= mb_h($course['description']) ?></p>
            <p class="cabinet-course-author"><?= mb_h($course['author_label']) ?> · <?= mb_h(mb_format_duration((int) $course['duration_minutes'])) ?></p>
          </div>
          <div class="cabinet-course-side">
            <div class="cabinet-progress" aria-label="Прогресс <?= $p ?>%"><span style="width:<?= $p ?>%"></span></div>
            <span class="cabinet-course-progress-label"><?= $p >= 100 ? 'Завершено' : ($p . '%') ?></span>
            <?php if ($p < 100): ?>
            <form method="post" class="cabinet-course-progress-form">
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
              <input type="hidden" name="progress" value="<?= $next ?>">
              <button type="submit" class="btn btn-primary btn-sm">+25%</button>
            </form>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <h2 class="cabinet-section-heading">Связанные материалы в каталоге</h2>
      <div class="cabinet-chips">
        <a href="knowledge-catalog.php" class="cabinet-chip-link">Весь каталог</a>
        <a href="category.php?slug=onboarding" class="cabinet-chip-link">Онбординг</a>
        <a href="category.php?slug=dev" class="cabinet-chip-link">Разработка</a>
        <a href="article.php?slug=checklist-sales-onboarding" class="cabinet-chip-link">Чек-лист Sales</a>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('learning');
