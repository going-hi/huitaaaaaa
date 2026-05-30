<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$courses = mb_courses_list($user['id']);
$stats = mb_learning_stats($user['id']);
if ($courses !== []) {
    $sumProgress = 0;
    foreach ($courses as $c) {
        $sumProgress += (int) $c['progress_percent'];
    }
    $stats['avg_progress'] = (int) round($sumProgress / count($courses));
    $stats['courses'] = count($courses);
}
$notice = mb_flash_take('cabinet_notice');

mb_cabinet_head('Обучение');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('learning', '', 'cabinet-main--wide');
?>
      <?php if ($notice !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($notice) ?></p>
      <?php endif; ?>

      <h1 class="cabinet-page-title">Обучение</h1>
      <p class="cabinet-page-lead">Курсы с уроками и привязкой к статьям каталога. Прогресс сохраняется для каждого пользователя.</p>

      <div class="cabinet-meta-strip">
        <span class="cabinet-pill"><strong><?= (int) $stats['courses'] ?></strong> курсов</span>
        <span class="cabinet-pill cabinet-pill--accent">Средний прогресс <?= (int) $stats['avg_progress'] ?>%</span>
        <?php if (mb_can_write()): ?>
        <a href="course-edit.php" class="btn btn-primary btn-sm">+ Курс</a>
        <?php endif; ?>
      </div>

      <?php if ($courses === []): ?>
      <p class="cabinet-muted-text">
        Курсов пока нет.
        <?php if (mb_can_write()): ?>
        <a href="course-edit.php" class="cabinet-text-link">Создайте первый курс</a>.
        <?php else: ?>
        Обратитесь к редактору или администратору.
        <?php endif; ?>
      </p>
      <?php else: ?>
      <div class="cabinet-panel cabinet-panel--table cabinet-panel--wide">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data">
            <thead>
              <tr>
                <th class="col-title">Курс</th>
                <th class="col-type">Тип</th>
                <th class="col-duration">Длительность</th>
                <th class="col-author">Автор</th>
                <th class="col-progress">Прогресс</th>
                <th class="col-actions"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($courses as $course):
                  $p = (int) $course['progress_percent'];
                  ?>
              <tr>
                <td class="col-title" data-label="Курс">
                  <a href="course.php?id=<?= (int) $course['id'] ?>" class="cabinet-table-link"><?= mb_h($course['title']) ?></a>
                  <span class="cabinet-table-sub"><?= mb_h($course['description']) ?></span>
                </td>
                <td class="col-type" data-label="Тип">
                  <span class="cabinet-tag <?= mb_h(mb_course_type_class($course['course_type'])) ?>"><?= mb_h(mb_course_type_label($course['course_type'])) ?></span>
                </td>
                <td class="col-duration" data-label="Длительность"><?= mb_h(mb_format_duration((int) $course['duration_minutes'])) ?></td>
                <td class="col-author" data-label="Автор"><?= mb_h($course['author_label']) ?></td>
                <td class="col-progress" data-label="Прогресс">
                  <div class="cabinet-progress cabinet-progress--inline" aria-label="Прогресс <?= $p ?>%">
                    <span style="width:<?= $p ?>%"></span>
                  </div>
                  <span class="cabinet-table-sub"><?= $p >= 100 ? 'Завершён' : ($p . '%') ?></span>
                </td>
                <td class="col-actions" data-label="">
                  <a href="course.php?id=<?= (int) $course['id'] ?>" class="btn btn-primary btn-sm">Открыть</a>
                  <?php if (mb_can_write()): ?>
                  <a href="course-edit.php?id=<?= (int) $course['id'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('learning');
