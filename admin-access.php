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
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Администрирование</h2></div>
      <?php mb_cabinet_nav_render('settings'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Группы доступа</h1>
      <p class="cabinet-page-lead">Привязывайте группы к разделам в форме редактирования раздела. Пользователь видит материалы только своих групп.</p>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($editGroup): ?><input type="hidden" name="group_id" value="<?= (int) $editGroup['id'] ?>"><?php endif; ?>
          <label class="form-label"><span>Название</span><input type="text" name="name" class="form-input" required value="<?= mb_h($editGroup['name'] ?? '') ?>"></label>
          <label class="form-label"><span>Описание</span><input type="text" name="description" class="form-input" value="<?= mb_h($editGroup['description'] ?? '') ?>"></label>
          <button type="submit" class="btn btn-primary"><?= $editGroup ? 'Обновить' : 'Создать группу' ?></button>
        </form>
      </div>
      <ul class="cabinet-feed">
        <?php foreach ($groups as $g): ?>
        <li class="cabinet-feed-item cabinet-feed-item--row">
          <span><strong><?= mb_h($g['name']) ?></strong> — <?= mb_h($g['description']) ?></span>
          <span>
            <a href="admin-access.php?edit=<?= (int) $g['id'] ?>" class="btn btn-ghost btn-sm">Изменить</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Удалить группу?');">
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="delete_group_id" value="<?= (int) $g['id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm">Удалить</button>
            </form>
          </span>
        </li>
        <?php endforeach; ?>
      </ul>
      <p><a href="admin-users.php" class="btn btn-ghost">К пользователям</a></p>
    </main>
  </div>
</body>
</html>
