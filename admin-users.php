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
    $uid = (int) ($_POST['user_id'] ?? 0);
    if ($uid > 0) {
        $role = (string) ($_POST['role'] ?? MB_ROLE_USER);
        $err = mb_user_set_role($uid, $role);
        if ($err !== null) {
            $error = $err;
        } else {
            $gids = isset($_POST['group_ids']) && is_array($_POST['group_ids']) ? array_map('intval', $_POST['group_ids']) : [];
            mb_user_set_groups($uid, $gids);
            $success = 'Пользователь обновлён.';
        }
    }
}

$users = mb_users_list();
$groups = mb_access_groups_list();

mb_cabinet_head('Пользователи');
mb_cabinet_header_render($user, 'Поиск...', false);
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Администрирование</h2></div>
      <?php mb_cabinet_nav_render('settings'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Пользователи и роли</h1>
      <p class="cabinet-page-lead">Администратор — полный доступ. Редактор — статьи и разделы. Пользователь — только чтение и скачивание.</p>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>
      <?php foreach ($users as $u): ?>
      <div class="cabinet-panel cabinet-user-card">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
          <div class="cabinet-user-card-head">
            <strong><?= mb_h($u['name']) ?></strong>
            <span class="cabinet-muted-text"><?= mb_h($u['email']) ?></span>
          </div>
          <label class="form-label">
            <span>Роль</span>
            <select name="role" class="form-input">
              <?php foreach (mb_valid_roles() as $r): ?>
              <option value="<?= mb_h($r) ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= mb_h(mb_role_label($r)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <fieldset class="cabinet-fieldset">
            <legend>Группы доступа к материалам</legend>
            <?php foreach ($groups as $g): ?>
            <label class="form-check">
              <input type="checkbox" name="group_ids[]" value="<?= (int) $g['id'] ?>"
                <?= in_array((int) $g['id'], $u['group_ids'], true) ? 'checked' : '' ?>>
              <?= mb_h($g['name']) ?>
            </label>
            <?php endforeach; ?>
          </fieldset>
          <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
        </form>
      </div>
      <?php endforeach; ?>
    </main>
  </div>
</body>
</html>
