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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['progress'])) {
    if (mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $cid = (int) $_POST['course_id'];
        $prog = (int) $_POST['progress'];
        mb_course_update_progress($user['id'], $cid, $prog);
        mb_flash_set('cabinet_notice', 'Прогресс сохранён.');
    }
    header('Location: learning-materials.php', true, 302);
    exit;
}

$stats = mb_learning_stats($user['id']);
$courses = mb_courses_list($user['id']);
$notice = mb_flash_take('cabinet_notice');

mb_cabinet_head('Обучающие материалы');
mb_cabinet_header_render($user, 'Поиск по материалам...');
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
      <h1 class="cabinet-page-title">Обучающие материалы</h1>
      <p class="cabinet-page-lead">Программа адаптации и повышения квалификации с фиксацией прогресса.</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по обучению">
        <span class="cabinet-pill"><strong><?= (int) $stats['courses'] ?></strong> курсов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['lessons'] ?></strong> уроков</span>
        <span class="cabinet-pill cabinet-pill--accent">Ваш прогресс: <strong><?= (int) $stats['avg_progress'] ?>%</strong></span>
      </div>

      <h2 class="cabinet-section-heading">Витрина курсов</h2>
      <div class="cabinet-course-list">
        <?php foreach ($courses as $course):
            $p = (int) $course['progress_percent'];
            $label = $p >= 100 ? 'Завершено' : ($p > 0 ? $p . '%' : 'Не начато');
            ?>
        <article class="cabinet-course-row">
          <div class="cabinet-course-main">
            <span class="cabinet-tag <?= mb_h(mb_course_type_class($course['course_type'])) ?>"><?= mb_h(mb_course_type_label($course['course_type'])) ?></span>
            <h3 class="cabinet-course-name"><?= mb_h($course['title']) ?></h3>
            <p class="cabinet-course-desc"><?= mb_h($course['description']) ?></p>
            <p class="cabinet-course-author"><?= mb_h($course['author_label']) ?></p>
          </div>
          <div class="cabinet-course-side">
            <span class="cabinet-course-time"><?= mb_h(mb_format_duration((int) $course['duration_minutes'])) ?></span>
            <div class="cabinet-progress" aria-label="Прогресс <?= $p ?>%"><span style="width:<?= $p ?>%"></span></div>
            <span class="cabinet-course-progress-label"><?= mb_h($label) ?></span>
            <form method="post" class="cabinet-course-progress-form" style="margin-top:8px">
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
              <input type="hidden" name="progress" value="<?= min(100, $p + 25) ?>">
              <button type="submit" class="btn btn-ghost btn-sm"><?= $p >= 100 ? 'Пройдено' : '+25% прогресса' ?></button>
            </form>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <h2 class="cabinet-section-heading">Треки по ролям</h2>
      <div class="cabinet-panel">
        <ol class="cabinet-learning-list">
          <li><strong>Руководитель проекта.</strong> Постановка целей в базе, теги релизов, связывание задач со статьями.</li>
          <li><strong>Разработчик.</strong> Шаблоны ADR, дежурства, runbook в каталоге.</li>
          <li><strong>Поддержка L1/L2.</strong> Быстрый поиск макросов и эскалация в L3.</li>
          <li><strong>Новичок.</strong> <a href="article.php?slug=checklist-sales-onboarding">Чек-лист онбординга</a> в каталоге.</li>
        </ol>
      </div>
    </main>
  </div>
</body>
</html>
