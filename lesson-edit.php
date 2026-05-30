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

$lessonId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
$lesson = ($lessonId !== null && $lessonId > 0) ? mb_course_lesson_get($lessonId) : null;

if ($lesson !== null) {
    $courseId = (int) $lesson['course_id'];
} elseif ($courseId <= 0) {
    header('Location: learning-materials.php', true, 302);
    exit;
}

$course = mb_course_get($courseId);
if ($course === null) {
    header('Location: learning-materials.php', true, 302);
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела.';
    } elseif (isset($_POST['delete_id'])) {
        $err = mb_course_lesson_delete((int) $_POST['delete_id']);
        if ($err !== null) {
            $error = $err;
        } else {
            mb_flash_set('cabinet_notice', 'Урок удалён.');
            header('Location: course-edit.php?id=' . $courseId, true, 302);
            exit;
        }
    } else {
        $postId = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $articleSlug = trim((string) ($_POST['article_slug'] ?? ''));
        $result = mb_course_lesson_save(
            $postId,
            $courseId,
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['description'] ?? ''),
            $articleSlug !== '' ? $articleSlug : null,
            (int) ($_POST['duration_minutes'] ?? 5),
            (int) ($_POST['sort_order'] ?? 0)
        );
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            mb_flash_set('cabinet_notice', $postId ? 'Урок сохранён.' : 'Урок добавлен.');
            header('Location: course-edit.php?id=' . $courseId, true, 302);
            exit;
        }
    }
}

$isEdit = $lesson !== null;
$articleOptions = mb_article_slug_options();

mb_cabinet_head($isEdit ? 'Редактирование урока' : 'Новый урок');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('learning');
?>
      <nav class="cabinet-breadcrumb">
        <a class="cabinet-breadcrumb-link" href="learning-materials.php">Обучение</a>
        <span class="cabinet-breadcrumb-sep">/</span>
        <a class="cabinet-breadcrumb-link" href="course-edit.php?id=<?= $courseId ?>"><?= mb_h($course['title']) ?></a>
        <span class="cabinet-breadcrumb-sep">/</span>
        <span class="cabinet-breadcrumb-current"><?= $isEdit ? 'Урок' : 'Новый урок' ?></span>
      </nav>

      <h1 class="cabinet-page-title"><?= $isEdit ? 'Редактирование урока' : 'Новый урок' ?></h1>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>

      <div class="cabinet-panel">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $lesson['id'] ?>"><?php endif; ?>
          <label class="form-label">
            <span>Название</span>
            <input type="text" name="title" class="form-input" required maxlength="255" value="<?= mb_h($lesson['title'] ?? '') ?>">
          </label>
          <label class="form-label">
            <span>Краткое описание</span>
            <input type="text" name="description" class="form-input" maxlength="500" value="<?= mb_h($lesson['description'] ?? '') ?>">
          </label>
          <label class="form-label">
            <span>Статья каталога (материал урока)</span>
            <select name="article_slug" class="form-input">
              <option value="">— без привязки —</option>
              <?php foreach ($articleOptions as $opt): ?>
              <option value="<?= mb_h($opt['slug']) ?>" <?= ($lesson['article_slug'] ?? '') === $opt['slug'] ? 'selected' : '' ?>><?= mb_h($opt['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <div class="cabinet-form-grid">
            <label class="form-label">
              <span>Длительность (мин)</span>
              <input type="number" name="duration_minutes" class="form-input" min="1" max="999" value="<?= (int) ($lesson['duration_minutes'] ?? 5) ?>">
            </label>
            <label class="form-label">
              <span>Порядок</span>
              <input type="number" name="sort_order" class="form-input" min="0" value="<?= (int) ($lesson['sort_order'] ?? 0) ?>">
            </label>
          </div>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="course-edit.php?id=<?= $courseId ?>" class="btn btn-ghost">Отмена</a>
            <?php if ($isEdit): ?>
            <button type="submit" name="delete_id" value="<?= (int) $lesson['id'] ?>" class="btn btn-outline" formnovalidate onclick="return confirm('Удалить урок?');">Удалить</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('learning');
