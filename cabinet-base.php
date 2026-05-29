<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/knowledge.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_login();
$user = mb_current_user();

$helpArticles = mb_help_articles();
$firstSlug = $helpArticles[0]['slug'] ?? 'welcome-help';
$activeSlug = isset($_GET['article']) ? trim((string) $_GET['article']) : $firstSlug;

$navHtml = '<p class="cabinet-nav-label">Статьи справки</p>';
foreach ($helpArticles as $h) {
    $cls = $activeSlug === $h['slug']
        ? 'cabinet-nav-item cabinet-nav-item--sub active'
        : 'cabinet-nav-item cabinet-nav-item--sub';
    $navHtml .= '<a href="cabinet-base.php?article=' . rawurlencode($h['slug']) . '" class="' . $cls . '">' . mb_h($h['title']) . '</a>';
}

mb_cabinet_head('Моя база знаний');
mb_cabinet_header_render($user, 'Поиск по базе знаний...', false);
mb_cabinet_sidebar_open('base', $navHtml);
?>
      <?php foreach ($helpArticles as $h):
          $full = mb_article_by_slug($h['slug']);
          if ($full === null) {
              continue;
          }
          $visible = $activeSlug === $h['slug'];
          ?>
      <article id="<?= mb_h($h['slug']) ?>" class="cabinet-article<?= $visible ? ' is-visible' : '' ?>">
        <h1><?= mb_h($full['title']) ?></h1>
        <p class="cabinet-article-meta">Справка · <?= mb_h(mb_relative_date($full['updated_at'])) ?></p>
        <div class="cabinet-article-body"><?= mb_markdown_to_html($full['body']) ?></div>
        <p style="margin-top:1.5rem"><a href="article.php?slug=<?= rawurlencode($full['slug']) ?>" class="btn btn-ghost btn-sm">Открыть полностью</a></p>
      </article>
      <?php endforeach; ?>
      <?php if ($helpArticles === []): ?>
      <p class="cabinet-muted-text">Справка не загружена. Запустите <code>php database/seed.php</code>.</p>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('base');
?>
