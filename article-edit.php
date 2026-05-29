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
$prefillCategory = isset($_GET['category']) ? (int) $_GET['category'] : null;

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$article = null;
if ($id !== null && $id > 0) {
    $slugRow = mb_article_by_id($id);
    if ($slugRow !== null) {
        $article = mb_article_by_slug($slugRow['slug']);
    }
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        $error = 'Сессия устарела. Обновите страницу.';
    } else {
        $postId = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $title = (string) ($_POST['title'] ?? '');
        $excerpt = (string) ($_POST['excerpt'] ?? '');
        $body = (string) ($_POST['body'] ?? '');
        $isHelp = !empty($_POST['is_help']);
        $result = mb_article_save($postId > 0 ? $postId : null, $user['id'], $categoryId, $title, $excerpt, $body, $isHelp);
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            mb_flash_set('cabinet_notice', $postId ? 'Статья сохранена.' : 'Статья создана.');
            header('Location: article.php?slug=' . rawurlencode($result['slug']), true, 302);
            exit;
        }
    }
}

$allCategories = [];
foreach (mb_categories_list(null) as $top) {
    $allCategories[] = $top;
    foreach (mb_categories_list((int) $top['id']) as $sub) {
        $allCategories[] = $sub;
    }
}
if ($prefillCategory && !$article) {
    $article = ['category_id' => $prefillCategory];
}

mb_cabinet_head($article ? 'Редактирование' : 'Новая статья');
mb_cabinet_header_render($user, 'Поиск...');
mb_cabinet_sidebar_open('catalog');
?>
      <h1 class="cabinet-page-title"><?= $article ? 'Редактирование статьи' : 'Новая статья' ?></h1>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <div class="cabinet-panel">
        <form class="cabinet-form" method="post" action="article-edit.php<?= $article ? '?id=' . (int) $article['id'] : '' ?>">
          <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
          <?php if ($article): ?><input type="hidden" name="id" value="<?= (int) $article['id'] ?>"><?php endif; ?>
          <label class="form-label">
            <span>Раздел</span>
            <select name="category_id" class="form-input" required>
              <?php foreach ($allCategories as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= ($article && isset($article['category_id']) && (int) $article['category_id'] === (int) $c['id']) ? 'selected' : '' ?>>
                <?= $c['parent_id'] ? '— ' : '' ?><?= mb_h($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="form-label">
            <span>Заголовок</span>
            <input type="text" name="title" class="form-input" required maxlength="500" value="<?= mb_h($article['title'] ?? '') ?>">
          </label>
          <label class="form-label">
            <span>Краткое описание</span>
            <input type="text" name="excerpt" class="form-input" maxlength="500" value="<?= mb_h($article['excerpt'] ?? '') ?>">
          </label>
          <label class="form-label">
            <span>Текст (Markdown)</span>
            <textarea name="body" class="form-input" rows="14" required><?= mb_h($article['body'] ?? '') ?></textarea>
          </label>
          <label class="form-label" style="flex-direction:row;align-items:center;gap:8px">
            <input type="checkbox" name="is_help" value="1" <?= !empty($article['is_help']) ? 'checked' : '' ?>>
            <span>Статья справки (раздел «Моя база»)</span>
          </label>
          <div class="cabinet-form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="<?= $article ? 'article.php?slug=' . rawurlencode($article['slug']) : 'knowledge-catalog.php' ?>" class="btn btn-ghost">Отмена</a>
          </div>
        </form>
      </div>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('catalog');
?>
