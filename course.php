<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$courseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$course = $courseId > 0 ? mb_course_by_id($courseId, $user['id']) : null;
if ($course === null) {
    header('Location: learning-materials.php', true, 302);
    exit;
}

$error = null;
$notice = mb_flash_take('cabinet_notice');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id'])) {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела. Обновите страницу.';
    } else {
        $error = mb_course_lesson_complete($user['id'], (int) $_POST['lesson_id']);
        if ($error === null) {
            mb_flash_set('cabinet_notice', 'Урок отмечен как пройденный.');
            header('Location: course.php?id=' . $courseId, true, 302);
            exit;
        }
    }
}

$course = mb_course_by_id($courseId, $user['id']);
$lessons = mb_course_lessons_list($courseId, $user['id']);
$completed = 0;
foreach ($lessons as $l) {
    if ($l['is_completed']) {
        $completed++;
    }
}
$p = (int) $course['progress_percent'];

mb_cabinet_head($course['title']);
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('learning');
?>
      <nav class="cabinet-breadcrumb">
        <a href="learning-materials.php">Обучение</a>
        <span>/</span>
        <span><?= mb_h($course['title']) ?></span>
      </nav>

      <?php if ($notice !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($notice) ?></p>
      <?php endif; ?>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>

      <div class="course-header">
        <span class="cabinet-tag <?= mb_h(mb_course_type_class($course['course_type'])) ?>"><?= mb_h(mb_course_type_label($course['course_type'])) ?></span>
        <h1 class="cabinet-page-title"><?= mb_h($course['title']) ?></h1>
        <p class="cabinet-page-lead"><?= mb_h($course['description']) ?></p>
        <div class="cabinet-meta-strip">
          <span class="cabinet-pill"><?= mb_h($course['author_label']) ?></span>
          <span class="cabinet-pill"><?= mb_h(mb_format_duration((int) $course['duration_minutes'])) ?></span>
          <span class="cabinet-pill cabinet-pill--accent"><?= $completed ?> / <?= count($lessons) ?> уроков</span>
        </div>
        <div class="course-progress-block">
          <div class="cabinet-progress course-progress-block__bar" aria-label="Прогресс <?= $p ?>%">
            <span style="width:<?= $p ?>%"></span>
          </div>
          <span class="cabinet-course-progress-label"><?= $p >= 100 ? 'Курс завершён' : ('Прогресс ' . $p . '%') ?></span>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Уроки курса</h2>
      <?php if ($lessons === []): ?>
      <p class="cabinet-muted-text">Уроки ещё не добавлены. Запустите <code class="inline-code">php database/seed.php</code>.</p>
      <?php else: ?>
      <div class="cabinet-panel cabinet-panel--table cabinet-panel--wide">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data">
            <thead>
              <tr>
                <th class="col-num">№</th>
                <th class="col-title">Урок</th>
                <th class="col-duration">Время</th>
                <th class="col-status">Статус</th>
                <th class="col-actions">Действия</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lessons as $i => $lesson): ?>
              <tr class="<?= $lesson['is_completed'] ? 'is-done' : '' ?>">
                <td class="col-num" data-label="№"><?= $i + 1 ?></td>
                <td class="col-title" data-label="Урок">
                  <span class="cabinet-table-title"><?= mb_h($lesson['title']) ?></span>
                  <?php if ($lesson['description'] !== ''): ?>
                  <span class="cabinet-table-sub"><?= mb_h($lesson['description']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="col-duration" data-label="Время"><?= mb_h(mb_format_duration((int) $lesson['duration_minutes'])) ?></td>
                <td class="col-status" data-label="Статус">
                  <?php if ($lesson['is_completed']): ?>
                  <span class="status-badge status-badge--done">Пройден</span>
                  <?php else: ?>
                  <span class="status-badge">Не начат</span>
                  <?php endif; ?>
                </td>
                <td class="col-actions cabinet-table-actions" data-label="">
                  <?php if ($lesson['article_slug'] !== null): ?>
                  <a href="article.php?slug=<?= rawurlencode($lesson['article_slug']) ?>" class="btn btn-outline btn-sm">Материал</a>
                  <?php endif; ?>
                  <?php if (!$lesson['is_completed']): ?>
                  <form method="post" class="doc-inline-form">
                    <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
                    <input type="hidden" name="lesson_id" value="<?= (int) $lesson['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Пройдено</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <p class="cabinet-muted-text" style="margin-top: 1.5rem;">
        <a href="learning-materials.php" class="cabinet-text-link">← Все курсы</a>
      </p>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('learning');
