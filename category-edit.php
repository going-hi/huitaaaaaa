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
mb_require_write();
$user = mb_current_user();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$category = ($id !== null && $id > 0) ? mb_category_by_id($id) : null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела.';
    } elseif (isset($_POST['delete_id'])) {
        $delId = (int) $_POST['delete_id'];
        $err = mb_category_delete($delId);
        if ($err !== null) {
            $error = $err;
        } else {
            mb_flash_set('cabinet_notice', 'Раздел удалён.');
            header('Location: knowledge-catalog.php', true, 302);
            exit;
        }
    } else {
        $postId = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $parentRaw = $_POST['parent_id'] ?? '';
        $parentId = $parentRaw === '' ? null : (int) $parentRaw;
        $groupIds = isset($_POST['group_ids']) && is_array($_POST['group_ids'])
            ? array_map('intval', $_POST['group_ids']) : [];
        $result = mb_category_save(
            $postId,
            $parentId,
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['icon'] ?? '📂'),
            (string) ($_POST['description'] ?? ''),
            (int) ($_POST['sort_order'] ?? 0),
            $groupIds
        );
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            mb_flash_set('cabinet_notice', $postId ? 'Раздел сохранён.' : 'Раздел создан.');
            header('Location: category.php?slug=' . rawurlencode($result['slug']), true, 302);
            exit;
        }
    }
}

$parents = mb_categories_list_all(null);
$groups = mb_access_groups_list();
$isAdmin = mb_is_admin();

mb_cabinet_head($category ? 'Редактирование раздела' : 'Новый раздел');
mb_cabinet_header_render($user, 'Поиск...');
?>
  <div class="cabinet-layout">
    <aside class="cabinet-sidebar">
      <div class="cabinet-sidebar-head"><h2 class="cabinet-sidebar-title">Навигация</h2></div>
      <?php mb_cabinet_nav_render('catalog'); ?>
    </aside>
    <main class="cabinet-main">
      <h1 class="cabinet-page-title"><?= $category ? 'Редактирование раздела' : 'Новый раздел' ?></h1>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($category): ?><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><?php endif; ?>
          <label class="form-label">
            <span>Название</span>
            <input type="text" name="name" class="form-input" required maxlength="255" value="<?= mb_h($category['name'] ?? '') ?>">
          </label>
          <label class="form-label">
            <span>Иконка (emoji)</span>
            <input type="text" name="icon" class="form-input" maxlength="16" value="<?= mb_h($category['icon'] ?? '📂') ?>">
          </label>
          <label class="form-label">
            <span>Родительский раздел</span>
            <select name="parent_id" class="form-input">
              <option value="">— Корневой раздел —</option>
              <?php foreach ($parents as $p):
                  if ($category && (int) $p['id'] === (int) $category['id']) {
                      continue;
                  }
                  if ($p['slug'] === 'help') {
                      continue;
                  }
                  ?>
              <option value="<?= (int) $p['id'] ?>" <?= ($category && $category['parent_id'] === (int) $p['id']) ? 'selected' : '' ?>><?= mb_h($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="form-label">
            <span>Описание</span>
            <textarea name="description" class="form-input" rows="3"><?= mb_h($category['description'] ?? '') ?></textarea>
          </label>
          <label class="form-label">
            <span>Порядок сортировки</span>
            <input type="number" name="sort_order" class="form-input" min="0" max="999" value="<?= (int) ($category['sort_order'] ?? 50) ?>">
          </label>
          <?php if ($isAdmin): ?>
          <fieldset class="cabinet-fieldset">
            <legend>Группы доступа</legend>
            <p class="cabinet-muted-text">Пустой список — раздел виден всем авторизованным пользователям.</p>
            <?php foreach ($groups as $g): ?>
            <label class="form-check">
              <input type="checkbox" name="group_ids[]" value="<?= (int) $g['id'] ?>"
                <?= ($category && in_array((int) $g['id'], $category['group_ids'], true)) ? 'checked' : '' ?>>
              <?= mb_h($g['name']) ?>
            </label>
            <?php endforeach; ?>
          </fieldset>
          <?php endif; ?>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="<?= $category ? 'category.php?slug=' . rawurlencode($category['slug']) : 'knowledge-catalog.php' ?>" class="btn btn-ghost">Отмена</a>
          </div>
        </form>
        <?php if ($category && mb_is_admin()): ?>
        <form method="post" class="cabinet-danger-form" onsubmit="return confirm('Удалить раздел «<?= mb_h($category['name']) ?>»?');">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <input type="hidden" name="delete_id" value="<?= (int) $category['id'] ?>">
          <button type="submit" class="btn btn-outline btn-danger">Удалить раздел</button>
        </form>
        <?php elseif ($category && mb_can_write()): ?>
        <form method="post" class="cabinet-danger-form" onsubmit="return confirm('Удалить пустой раздел?');">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <input type="hidden" name="delete_id" value="<?= (int) $category['id'] ?>">
          <button type="submit" class="btn btn-outline btn-danger">Удалить (если пустой)</button>
        </form>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
