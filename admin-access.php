<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_admin();
$user = mb_current_user();

$error = null;
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
    if (isset($_POST['delete_group_id'])) {
        mb_access_group_delete((int) $_POST['delete_group_id']);
        $success = 'Группа удалена.';
    } else {
        $gid = isset($_POST['group_id']) && $_POST['group_id'] !== '' ? (int) $_POST['group_id'] : null;
        $res = mb_access_group_save($gid, (string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''));
        if (isset($res['error'])) {
            $error = $res['error'];
        } else {
            $success = 'Группа сохранена.';
        }
    }
}

$groups = mb_access_groups_list();
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editGroup = null;
foreach ($groups as $g) {
    if ($editId !== null && (int) $g['id'] === $editId) {
        $editGroup = $g;
    }
}

mb_cabinet_head('Группы доступа');
mb_cabinet_header_render($user, 'Поиск...', false);
mb_cabinet_sidebar_open('admin-access');
?>
      <h1 class="cabinet-page-title">Группы доступа</h1>
      <p class="cabinet-page-lead">Привязывайте группы к разделам в форме редактирования раздела. Пользователь видит материалы только своих групп.</p>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>

      <h2 class="cabinet-section-heading"><?= $editGroup ? 'Редактирование группы' : 'Новая группа' ?></h2>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($editGroup): ?><input type="hidden" name="group_id" value="<?= (int) $editGroup['id'] ?>"><?php endif; ?>
          <div class="cabinet-form-grid">
            <label class="form-label">
              <span>Название</span>
              <input type="text" name="name" class="form-input" required maxlength="255" value="<?= mb_h($editGroup['name'] ?? '') ?>">
            </label>
            <label class="form-label">
              <span>Описание</span>
              <input type="text" name="description" class="form-input" maxlength="500" value="<?= mb_h($editGroup['description'] ?? '') ?>">
            </label>
          </div>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary"><?= $editGroup ? 'Сохранить изменения' : 'Создать группу' ?></button>
            <?php if ($editGroup): ?>
            <a href="admin-access.php" class="btn btn-ghost">Отмена</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <h2 class="cabinet-section-heading">Список групп <span class="cabinet-section-count"><?= count($groups) ?></span></h2>
      <?php if ($groups === []): ?>
      <p class="cabinet-muted-text">Пока нет групп. Создайте первую выше.</p>
      <?php else: ?>
      <div class="access-group-list">
        <?php foreach ($groups as $g):
            $isEditing = $editId !== null && (int) $g['id'] === $editId;
            ?>
        <article class="access-group-card<?= $isEditing ? ' is-editing' : '' ?>">
          <div class="access-group-card__body">
            <h3 class="access-group-card__title"><?= mb_h($g['name']) ?></h3>
            <?php if ($g['description'] !== ''): ?>
            <p class="access-group-card__desc"><?= mb_h($g['description']) ?></p>
            <?php else: ?>
            <p class="access-group-card__desc access-group-card__desc--empty">Без описания</p>
            <?php endif; ?>
            <p class="access-group-card__meta"><code class="inline-code"><?= mb_h($g['slug']) ?></code></p>
          </div>
          <div class="access-group-card__actions">
            <a href="admin-access.php?edit=<?= (int) $g['id'] ?>" class="btn btn-outline btn-sm">Изменить</a>
            <form method="post" class="access-group-card__delete" onsubmit="return confirm(<?= json_encode('Удалить группу «' . $g['name'] . '»?', JSON_UNESCAPED_UNICODE) ?>);">
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="delete_group_id" value="<?= (int) $g['id'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm">Удалить</button>
            </form>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <p class="cabinet-page-foot"><a href="admin-users.php" class="cabinet-text-link">← К пользователям</a></p>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('admin-access');
