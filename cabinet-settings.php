<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/workspace.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';

mb_require_login();
$user = mb_current_user();
$workspace = mb_workspace_get();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
    if (isset($_POST['workspace_title'])) {
        $err = mb_workspace_save((string) $_POST['workspace_title']);
        if ($err !== null) {
            $error = $err;
        } else {
            $success = 'Настройки сохранены.';
            $workspace = mb_workspace_get();
        }
    } elseif (isset($_POST['add_member']) && mb_workspace_can_manage()) {
        $err = mb_workspace_add_member_by_email(mb_ws_id(), (string) ($_POST['member_email'] ?? ''), MB_ROLE_USER);
        if ($err !== null) {
            $error = $err;
        } else {
            $success = 'Участник добавлен в базу.';
        }
    }
}

mb_cabinet_head('Настройки');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('settings');
?>
      <h1 class="cabinet-page-title">Настройки</h1>
      <p class="cabinet-page-lead">База: <?= mb_h($workspace['title']) ?>. Экспорт и участники.</p>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <?php if ($success !== null): ?>
      <p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Экспорт базы знаний</h2>
      <div class="cabinet-panel">
        <p class="cabinet-muted-text" style="margin-bottom: 16px;">Выгрузите статьи из разделов, к которым у вас есть доступ.</p>
        <div class="cabinet-inline-btns">
          <a href="export.php?format=md" class="btn btn-outline">Скачать Markdown</a>
          <a href="export.php?format=html" class="btn btn-outline">Скачать HTML</a>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Рабочее пространство</h2>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post" action="cabinet-settings.php">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <label class="form-label">
            <span>Название базы</span>
            <input type="text" name="workspace_title" class="form-input" value="<?= mb_h($workspace['title']) ?>" required maxlength="255" <?= mb_workspace_can_manage() ? '' : 'readonly' ?>>
          </label>
          <?php if (mb_workspace_can_manage()): ?>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
          </div>
          <?php endif; ?>
        </form>
      </div>

      <?php if (mb_workspace_can_manage()): ?>
      <h2 class="cabinet-section-heading">Участники</h2>
      <div class="cabinet-panel">
        <p class="cabinet-muted-text">Добавьте коллегу по email — он должен быть зарегистрирован в MindBase. После добавления база появится у него в «Мои базы».</p>
        <form class="cabinet-form" method="post" action="cabinet-settings.php" style="margin-top: 16px;">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <input type="hidden" name="add_member" value="1">
          <label class="form-label">
            <span>Email участника</span>
            <input type="email" name="member_email" class="form-input" placeholder="colleague@company.com" required>
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-outline">Добавить в базу</button>
          </div>
        </form>
        <p class="cabinet-muted-text" style="margin-top: 16px;">
          <a href="admin-users.php">Управление ролями участников</a> ·
          <a href="workspaces.php">Все мои базы</a>
        </p>
      </div>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('settings');
