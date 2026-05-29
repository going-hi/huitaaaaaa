<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$article = $slug !== '' ? mb_article_by_slug($slug) : null;
if ($article === null) {
    http_response_code(404);
    mb_cabinet_head('Статья не найдена');
    mb_cabinet_header_render($user, 'Поиск...');
    echo '<main class="cabinet-main" style="max-width:900px;margin:2rem auto;padding:0 1.5rem"><h1>Статья не найдена</h1><p><a href="knowledge-catalog.php">Вернуться в каталог</a></p></main></body></html>';
    exit;
}

mb_article_record_view((int) $article['id'], $user['id']);
$canEdit = mb_can_write();

mb_cabinet_head($article['title']);
mb_cabinet_header_render($user, 'Поиск по базе...');
?>
  <main class="cabinet-main" style="max-width:820px;margin:0 auto;padding:2rem 1.5rem 4rem">
    <nav class="cabinet-breadcrumb" style="margin-bottom:1rem;font-size:0.9rem;color:var(--text-muted,#94a3b8)">
      <a href="knowledge-catalog.php">Каталог</a>
      · <a href="category.php?slug=<?= rawurlencode($article['category_slug']) ?>"><?= mb_h($article['category_name']) ?></a>
    </nav>
    <article class="cabinet-article is-visible">
      <h1><?= mb_h($article['title']) ?></h1>
      <p class="cabinet-article-meta">
        <?= mb_h($article['category_name']) ?> · <?= mb_h($article['author_name']) ?> · <?= mb_h(mb_format_datetime($article['updated_at'])) ?>
        <?php if ($article['tags'] !== []): ?>
        · <?= mb_h(implode(', ', $article['tags'])) ?>
        <?php endif; ?>
      </p>
      <?php if ($article['excerpt'] !== ''): ?>
      <p class="cabinet-page-lead"><?= mb_h($article['excerpt']) ?></p>
      <?php endif; ?>
      <div class="cabinet-article-body">
        <?= mb_markdown_to_html($article['body']) ?>
      </div>
      <div class="cabinet-form-actions" style="margin-top:2rem">
        <?php if ($canEdit): ?>
        <a href="article-edit.php?id=<?= (int) $article['id'] ?>" class="btn btn-outline">Редактировать</a>
        <?php endif; ?>
        <a href="knowledge-catalog.php" class="btn btn-ghost">К каталогу</a>
      </div>
    </article>
  </main>
</body>
</html>
