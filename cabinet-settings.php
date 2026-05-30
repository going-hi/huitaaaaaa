<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();
$workspace = mb_workspace_get();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['workspace_title'])) {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела.';
    } else {
        $err = mb_workspace_save((string) $_POST['workspace_title']);
        if ($err !== null) {
            $error = $err;
        } else {
            $success = 'Настройки сохранены.';
            $workspace = mb_workspace_get();
        }
    }
}

mb_cabinet_head('Настройки');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('settings');
?>
      <h1 class="cabinet-page-title">Настройки</h1>
      <p class="cabinet-page-lead">Экспорт контента и название рабочего пространства.</p>
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
            <input type="text" name="workspace_title" class="form-input" value="<?= mb_h($workspace['title']) ?>" required maxlength="255">
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
          </div>
        </form>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('settings');
?>
