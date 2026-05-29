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

$error = null;
$success = mb_flash_take('cabinet_notice');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && mb_can_write()) {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела.';
    } elseif (isset($_POST['delete_doc_id'])) {
        $error = mb_document_delete((int) $_POST['delete_doc_id']);
        if ($error === null) {
            $success = 'Документ удалён.';
        }
    } elseif (isset($_FILES['file'])) {
        $groupIds = isset($_POST['group_ids']) && is_array($_POST['group_ids']) ? array_map('intval', $_POST['group_ids']) : [];
        $res = mb_document_upload(
            $user['id'],
            $_FILES['file'],
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['owner_label'] ?? $user['name']),
            (string) ($_POST['folder_path'] ?? '/'),
            $groupIds
        );
        if (isset($res['error'])) {
            $error = $res['error'];
        } else {
            $success = 'Файл загружен.';
        }
    }
}

$docs = mb_documents_list();
$stats = mb_documents_stats();
$folders = mb_document_folders();
$groups = mb_access_groups_list();
$lastSync = $docs !== [] ? mb_format_datetime($docs[0]['updated_at']) : '—';
$canWrite = mb_can_write();
$isAdmin = mb_is_admin();

mb_cabinet_head('Документы');
mb_cabinet_header_render($user, 'Поиск по документам...');
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head">
        <h2 class="cabinet-sidebar-title">Навигация</h2>
      </div>
      <?php mb_cabinet_nav_render('documents'); ?>
    </aside>

    <main class="cabinet-main">
      <h1 class="cabinet-page-title">Документы</h1>
      <p class="cabinet-page-lead">Регламенты и файлы с контролем доступа по группам.</p>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>

      <div class="cabinet-meta-strip" aria-label="Сводка по документам">
        <span class="cabinet-pill"><strong><?= (int) $stats['files'] ?></strong> файлов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['folders'] ?></strong> папок</span>
        <span class="cabinet-pill">Занято <strong><?= mb_h(mb_format_bytes((int) $stats['bytes'])) ?></strong></span>
        <span class="cabinet-pill cabinet-pill--accent">Обновлено · <?= mb_h($lastSync) ?></span>
      </div>

      <?php if ($canWrite): ?>
      <h2 class="cabinet-section-heading">Загрузить документ</h2>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post" enctype="multipart/form-data">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <label class="form-label"><span>Название</span><input type="text" name="title" class="form-input" required maxlength="500"></label>
          <label class="form-label"><span>Файл</span><input type="file" name="file" class="form-input" required accept=".pdf,.doc,.docx,.txt,.xlsx,.csv,.md"></label>
          <label class="form-label"><span>Ответственный</span><input type="text" name="owner_label" class="form-input" value="<?= mb_h($user['name']) ?>"></label>
          <label class="form-label"><span>Папка</span><input type="text" name="folder_path" class="form-input" placeholder="/юридические/" value="/"></label>
          <?php if ($isAdmin): ?>
          <fieldset class="cabinet-fieldset">
            <legend>Группы доступа (пусто = для всех)</legend>
            <?php foreach ($groups as $g): ?>
            <label class="form-check"><input type="checkbox" name="group_ids[]" value="<?= (int) $g['id'] ?>"> <?= mb_h($g['name']) ?></label>
            <?php endforeach; ?>
          </fieldset>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
      </div>
      <?php endif; ?>

      <h2 class="cabinet-section-heading">Файлы</h2>
      <div class="cabinet-panel cabinet-panel--table">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table cabinet-table--actions">
            <thead>
              <tr>
                <th>Название</th>
                <th>Тип</th>
                <th>Размер</th>
                <th>Ответственный</th>
                <th>Обновлено</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($docs as $d): ?>
              <tr>
                <td><?= mb_h($d['title']) ?></td>
                <td><?= mb_h($d['file_type']) ?></td>
                <td><?= mb_h(mb_format_bytes((int) $d['size_bytes'])) ?></td>
                <td><?= mb_h($d['owner_label']) ?></td>
                <td><?= mb_h(mb_format_date_short($d['updated_at'])) ?></td>
                <td class="cabinet-table-actions">
                  <?php if (!empty($d['has_file'])): ?>
                  <a href="document-download.php?id=<?= (int) $d['id'] ?>" class="btn btn-primary btn-sm">Скачать</a>
                  <?php else: ?>
                  <span class="cabinet-muted-text">Нет файла</span>
                  <?php endif; ?>
                  <?php if ($canWrite): ?>
                  <form method="post" style="display:inline" onsubmit="return confirm('Удалить документ?');">
                    <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
                    <input type="hidden" name="delete_doc_id" value="<?= (int) $d['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm">Удалить</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <h2 class="cabinet-section-heading">Папки</h2>
      <div class="cabinet-folder-chips" aria-label="Папки">
        <?php foreach ($folders as $path): ?>
        <span class="cabinet-folder-chip"><?= mb_h($path) ?></span>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</body>
</html>
