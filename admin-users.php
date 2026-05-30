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
mb_cabinet_sidebar_open('admin-users');
?>
      <h1 class="cabinet-page-title">Пользователи и роли</h1>
      <p class="cabinet-page-lead">Администратор — полный доступ. Редактор — статьи и разделы. Пользователь — только чтение и скачивание.</p>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>

      <div class="cabinet-meta-strip admin-user-legend">
        <?php foreach (mb_valid_roles() as $role): ?>
        <span class="cabinet-pill"><span class="<?= mb_h(mb_role_badge_class($role)) ?>"><?= mb_h(mb_role_label($role)) ?></span></span>
        <?php endforeach; ?>
      </div>

      <h2 class="cabinet-section-heading">Список пользователей <span class="cabinet-section-count"><?= count($users) ?></span></h2>
      <?php if ($users === []): ?>
      <p class="cabinet-muted-text">Пользователи не найдены.</p>
      <?php else: ?>
      <div class="admin-user-list">
        <?php foreach ($users as $u):
            $isSelf = (int) $u['id'] === (int) $user['id'];
            $assignedGroups = array_values(array_filter(
                $groups,
                static fn (array $g): bool => in_array((int) $g['id'], $u['group_ids'], true)
            ));
            ?>
        <article class="admin-user-card<?= $isSelf ? ' is-self' : '' ?>">
          <form class="admin-user-card__form cabinet-form" method="post">
            <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">

            <div class="admin-user-card__head">
              <div class="admin-user-card__identity">
                <h3 class="admin-user-card__name">
                  <?= mb_h($u['name']) ?>
                  <?php if ($isSelf): ?><span class="admin-user-card__you">это вы</span><?php endif; ?>
                </h3>
                <p class="admin-user-card__email"><?= mb_h($u['email']) ?></p>
                <?php if ($assignedGroups !== []): ?>
                <div class="admin-user-card__chips">
                  <?php foreach ($assignedGroups as $g): ?>
                  <span class="admin-user-card__chip"><?= mb_h($g['name']) ?></span>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="admin-user-card__groups-empty">Без групп доступа</p>
                <?php endif; ?>
              </div>
              <span class="<?= mb_h(mb_role_badge_class($u['role'])) ?>"><?= mb_h(mb_role_label($u['role'])) ?></span>
            </div>

            <div class="admin-user-card__fields">
              <label class="form-label admin-user-card__role">
                <span>Роль</span>
                <select name="role" class="form-input">
                  <?php foreach (mb_valid_roles() as $r): ?>
                  <option value="<?= mb_h($r) ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= mb_h(mb_role_label($r)) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>

              <?php if ($groups !== []): ?>
              <fieldset class="admin-user-card__groups">
                <legend>Группы доступа к материалам</legend>
                <div class="admin-user-card__group-grid">
                  <?php foreach ($groups as $g): ?>
                  <label class="form-check admin-user-card__group-check">
                    <input type="checkbox" name="group_ids[]" value="<?= (int) $g['id'] ?>"
                      <?= in_array((int) $g['id'], $u['group_ids'], true) ? 'checked' : '' ?>>
                    <span><?= mb_h($g['name']) ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
              </fieldset>
              <?php else: ?>
              <p class="cabinet-muted-text admin-user-card__no-groups">
                Группы ещё не созданы. <a href="admin-access.php" class="cabinet-text-link">Перейти к группам доступа</a>
              </p>
              <?php endif; ?>
            </div>

            <div class="cabinet-form-actions">
              <button type="submit" class="btn btn-primary btn-sm">Сохранить изменения</button>
            </div>
          </form>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <p class="cabinet-page-foot"><a href="admin-access.php" class="cabinet-text-link">К группам доступа →</a></p>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('admin-users');
