<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/workspace.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';

mb_require_login();
$user = mb_current_user();
$error = null;
$success = mb_flash_take('cabinet_notice');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
    if (isset($_POST['switch_workspace_id'])) {
        $wsId = (int) $_POST['switch_workspace_id'];
        if (mb_workspace_set_current($wsId, (int) $user['id'])) {
            header('Location: cabinet.php', true, 302);
            exit;
        }
        $error = 'Не удалось переключить базу.';
    } elseif (isset($_POST['create_workspace'])) {
        $title = trim((string) ($_POST['workspace_title'] ?? ''));
        $result = mb_workspace_create((int) $user['id'], $title !== '' ? $title : ('База ' . $user['name']));
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            mb_workspace_set_current((int) $result['id'], (int) $user['id']);
            mb_flash_set('cabinet_notice', 'База знаний создана.');
            header('Location: cabinet.php', true, 302);
            exit;
        }
    }
}

$list = mb_workspaces_for_user((int) $user['id']);

mb_cabinet_head('Мои базы знаний');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('overview');
?>
      <h1 class="cabinet-page-title">Мои базы знаний</h1>
      <p class="cabinet-page-lead">У каждой команды или проекта — отдельная база. Создайте свою или попросите администратора добавить вас по email.</p>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <?php if ($success !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p>
      <?php endif; ?>

      <?php if ($list !== []): ?>
      <h2 class="cabinet-section-heading">Ваши базы</h2>
      <div class="cabinet-panel">
        <ul class="cabinet-feed">
          <?php foreach ($list as $ws): ?>
          <li class="cabinet-feed-item cabinet-feed-item--row">
            <form method="post" action="workspaces.php" class="cabinet-inline-form">
              <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
              <input type="hidden" name="switch_workspace_id" value="<?= (int) $ws['id'] ?>">
              <button type="submit" class="cabinet-table-link cabinet-table-link--button"><?= mb_h($ws['title']) ?></button>
            </form>
            <?php $appRole = mb_workspace_role_to_app($ws['role']); ?>
            <span class="<?= mb_h(mb_role_badge_class($appRole)) ?>"><?= mb_h(mb_role_label($appRole)) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Создать новую базу</h2>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post" action="workspaces.php">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <input type="hidden" name="create_workspace" value="1">
          <label class="form-label">
            <span>Название</span>
            <input type="text" name="workspace_title" class="form-input" placeholder="Например: Команда разработки" maxlength="255" required>
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Создать базу</button>
          </div>
        </form>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('overview');
