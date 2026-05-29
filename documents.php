<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$docs = mb_documents_list();
$stats = mb_documents_stats();
$folders = mb_document_folders();
$lastSync = $docs !== [] ? mb_format_datetime($docs[0]['updated_at']) : '—';

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
      <p class="cabinet-page-lead">Регламенты, шаблоны и служебные файлы организации (метаданные в базе).</p>

      <div class="cabinet-meta-strip" aria-label="Сводка по документам">
        <span class="cabinet-pill"><strong><?= (int) $stats['files'] ?></strong> файлов</span>
        <span class="cabinet-pill"><strong><?= (int) $stats['folders'] ?></strong> папок</span>
        <span class="cabinet-pill">Занято <strong><?= mb_h(mb_format_bytes((int) $stats['bytes'])) ?></strong></span>
        <span class="cabinet-pill cabinet-pill--accent">Обновлено · <?= mb_h($lastSync) ?></span>
      </div>

      <div class="cabinet-panel cabinet-panel--table">
        <div class="cabinet-table-wrap">
          <table class="cabinet-table">
            <thead>
              <tr>
                <th>Название</th>
                <th>Тип</th>
                <th>Размер</th>
                <th>Ответственный</th>
                <th>Обновлено</th>
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

      <div class="cabinet-tip">
        <strong>Примечание.</strong> В учебном стенде хранятся метаданные документов. Файлы можно привязать к статьям каталога.
      </div>
    </main>
  </div>
</body>
</html>
