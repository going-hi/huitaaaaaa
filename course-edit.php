<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
mb_require_write();
$user = mb_current_user();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$course = ($id !== null && $id > 0) ? mb_course_get($id) : null;
$error = null;
$notice = mb_flash_take('cabinet_notice');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела.';
    } elseif (isset($_POST['delete_id'])) {
        $err = mb_course_delete((int) $_POST['delete_id']);
        if ($err !== null) {
            $error = $err;
        } else {
            mb_flash_set('cabinet_notice', 'Курс удалён.');
            header('Location: learning-materials.php', true, 302);
            exit;
        }
    } elseif (isset($_POST['delete_lesson_id'])) {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $err = mb_course_lesson_delete((int) $_POST['delete_lesson_id']);
        if ($err !== null) {
            $error = $err;
        } else {
            mb_flash_set('cabinet_notice', 'Урок удалён.');
            header('Location: course-edit.php?id=' . $courseId, true, 302);
            exit;
        }
    } else {
        $postId = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $result = mb_course_save(
            $postId,
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['description'] ?? ''),
            (string) ($_POST['course_type'] ?? 'doc'),
            (int) ($_POST['duration_minutes'] ?? 0),
            (string) ($_POST['author_label'] ?? $user['name']),
            (int) ($_POST['sort_order'] ?? 0)
        );
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            mb_flash_set('cabinet_notice', $postId ? 'Курс сохранён.' : 'Курс создан.');
            header('Location: course-edit.php?id=' . (int) $result['id'], true, 302);
            exit;
        }
    }
}

$isEdit = $course !== null;
$courseId = $isEdit ? (int) $course['id'] : 0;
$lessons = $isEdit ? mb_course_lessons_admin_list($courseId) : [];

mb_cabinet_head($isEdit ? 'Редактирование курса' : 'Новый курс');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('learning');
?>
      <nav class="cabinet-breadcrumb">
        <a class="cabinet-breadcrumb-link" href="learning-materials.php">Обучение</a>
        <?php if ($isEdit): ?>
        <span class="cabinet-breadcrumb-sep">/</span>
        <a class="cabinet-breadcrumb-link" href="course.php?id=<?= $courseId ?>"><?= mb_h($course['title']) ?></a>
        <span class="cabinet-breadcrumb-sep">/</span>
        <span class="cabinet-breadcrumb-current">Редактирование</span>
        <?php else: ?>
        <span class="cabinet-breadcrumb-sep">/</span>
        <span class="cabinet-breadcrumb-current">Новый курс</span>
        <?php endif; ?>
      </nav>

      <h1 class="cabinet-page-title"><?= $isEdit ? 'Редактирование курса' : 'Новый курс' ?></h1>
      <?php if ($notice !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($notice) ?></p>
      <?php endif; ?>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>

      <div class="cabinet-panel">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $courseId ?>"><?php endif; ?>
          <div class="cabinet-form-grid">
            <label class="form-label">
              <span>Название</span>
              <input type="text" name="title" class="form-input" required maxlength="255" value="<?= mb_h($course['title'] ?? '') ?>">
            </label>
            <label class="form-label">
              <span>Тип курса</span>
              <select name="course_type" class="form-input">
                <?php foreach (mb_course_types() as $type): ?>
                <option value="<?= mb_h($type) ?>" <?= ($course['course_type'] ?? 'doc') === $type ? 'selected' : '' ?>><?= mb_h(mb_course_type_label($type)) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="form-label">
              <span>Автор / команда</span>
              <input type="text" name="author_label" class="form-input" maxlength="120" value="<?= mb_h($course['author_label'] ?? $user['name']) ?>">
            </label>
            <label class="form-label">
              <span>Длительность (мин)</span>
              <input type="number" name="duration_minutes" class="form-input" min="0" max="9999" value="<?= (int) ($course['duration_minutes'] ?? 0) ?>">
            </label>
            <label class="form-label">
              <span>Порядок сортировки</span>
              <input type="number" name="sort_order" class="form-input" min="0" value="<?= (int) ($course['sort_order'] ?? 0) ?>">
            </label>
          </div>
          <label class="form-label">
            <span>Описание</span>
            <textarea name="description" class="form-input" rows="4" maxlength="5000"><?= mb_h($course['description'] ?? '') ?></textarea>
          </label>
          <p class="cabinet-muted-text">Длительность можно указать вручную или оставить 0 — тогда она пересчитается по сумме уроков.</p>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="<?= $isEdit ? 'course.php?id=' . $courseId : 'learning-materials.php' ?>" class="btn btn-ghost">Отмена</a>
            <?php if ($isEdit): ?>
            <button type="submit" name="delete_id" value="<?= $courseId ?>" class="btn btn-outline" formnovalidate onclick="return confirm('Удалить курс и все уроки?');">Удалить курс</button>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <?php if ($isEdit): ?>
      <h2 class="cabinet-section-heading">Уроки курса <span class="cabinet-section-count"><?= count($lessons) ?></span></h2>
      <?php if ($lessons === []): ?>
      <p class="cabinet-muted-text">Уроков пока нет. Добавьте первый урок.</p>
      <?php else: ?>
      <div class="cabinet-panel cabinet-panel--table cabinet-panel--wide">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--data">
            <thead>
              <tr>
                <th class="col-num">№</th>
                <th class="col-title">Урок</th>
                <th class="col-duration">Время</th>
                <th class="col-actions">Действия</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lessons as $i => $lesson): ?>
              <tr>
                <td class="col-num" data-label="№"><?= $i + 1 ?></td>
                <td class="col-title" data-label="Урок">
                  <span class="cabinet-table-title"><?= mb_h($lesson['title']) ?></span>
                  <?php if ($lesson['description'] !== ''): ?>
                  <span class="cabinet-table-sub"><?= mb_h($lesson['description']) ?></span>
                  <?php endif; ?>
                  <?php if ($lesson['article_slug'] !== null): ?>
                  <span class="cabinet-table-sub">Статья: <?= mb_h($lesson['article_slug']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="col-duration" data-label="Время"><?= mb_h(mb_format_duration((int) $lesson['duration_minutes'])) ?></td>
                <td class="col-actions cabinet-table-actions" data-label="">
                  <a href="lesson-edit.php?id=<?= (int) $lesson['id'] ?>" class="btn btn-outline btn-sm">Изменить</a>
                  <form method="post" class="doc-inline-form" onsubmit="return confirm('Удалить урок?');">
                    <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <input type="hidden" name="delete_lesson_id" value="<?= (int) $lesson['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm">Удалить</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
      <p class="cabinet-page-foot">
        <a href="lesson-edit.php?course_id=<?= $courseId ?>" class="btn btn-primary btn-sm">+ Добавить урок</a>
      </p>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('learning');
