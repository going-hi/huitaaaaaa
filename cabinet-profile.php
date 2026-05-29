<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();
$profile = mb_user_get($user['id']) ?? $user;
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела. Обновите страницу.';
    } else {
        $name = (string) ($_POST['name'] ?? '');
        $role = isset($_POST['role']) ? (string) $_POST['role'] : null;
        $err = mb_user_update_profile($user['id'], $name, $role);
        if ($err !== null) {
            $error = $err;
        } else {
            $success = 'Профиль сохранён.';
            $profile = mb_user_get($user['id']) ?? $profile;
            $user = mb_current_user() ?? $user;
        }
    }
}

mb_cabinet_head('Профиль');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('profile');
?>
      <h1 class="cabinet-page-title">Профиль</h1>
      <p class="cabinet-page-lead">Имя и должность отображаются в статьях и ленте активности.</p>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <?php if ($success !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p>
      <?php endif; ?>

      <div class="cabinet-panel">
        <form class="cabinet-form" method="post" action="cabinet-profile.php">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <label class="form-label">
            <span>Отображаемое имя</span>
            <input type="text" name="name" class="form-input" value="<?= mb_h($profile['name']) ?>" required autocomplete="name">
          </label>
          <label class="form-label">
            <span>Email</span>
            <input type="email" class="form-input form-input-readonly" value="<?= mb_h($profile['email']) ?>" readonly>
          </label>
          <label class="form-label">
            <span>Роль в системе</span>
            <input type="text" class="form-input form-input-readonly" value="<?= mb_h(mb_role_label(mb_user_role())) ?>" readonly>
          </label>
          <label class="form-label">
            <span>Должность (необязательно)</span>
            <input type="text" name="role" class="form-input" placeholder="Например, ведущий разработчик" value="<?= mb_h($profile['role_title'] ?? '') ?>">
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
          </div>
        </form>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('profile');
?>
