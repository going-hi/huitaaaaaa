<?php

declare(strict_types=1);

require_once __DIR__ . '/roles.php';

/**
 * @param 'catalog'|'learning'|'documents'|'overview'|'base'|'profile'|'settings' $active
 */
function mb_cabinet_nav_class(string $active, string $key): string
{
    return $active === $key ? 'cabinet-nav-item active' : 'cabinet-nav-item';
}

/**
 * Боковое меню кабинета. $suffix выводится перед закрывающим тегом nav (например, подраздел «Статьи» на cabinet-base).
 *
 * @param 'catalog'|'learning'|'documents'|'overview'|'base'|'profile'|'settings' $active
 */
function mb_cabinet_nav_render(string $active, string $suffix = ''): void
{
    $c = static function (string $key) use ($active): string {
        return mb_cabinet_nav_class($active, $key);
    };
    ?>
      <nav class="cabinet-nav">
        <p class="cabinet-nav-label">Платформа</p>
        <a href="knowledge-catalog.php" class="<?= $c('catalog') ?>">Каталог знаний</a>
        <a href="learning-materials.php" class="<?= $c('learning') ?>">Обучающие материалы</a>
        <a href="documents.php" class="<?= $c('documents') ?>">Документы</a>
        <p class="cabinet-nav-label">Личный кабинет</p>
        <a href="cabinet.php" class="<?= $c('overview') ?>">Личный кабинет</a>
        <a href="cabinet-base.php" class="<?= $c('base') ?>">Моя база знаний</a>
        <p class="cabinet-nav-label">Аккаунт</p>
        <a href="cabinet-profile.php" class="<?= $c('profile') ?>">Профиль</a>
        <a href="cabinet-settings.php" class="<?= $c('settings') ?>">Настройки</a>
        <?php if (mb_is_admin()): ?>
        <p class="cabinet-nav-label">Администрирование</p>
        <a href="admin-users.php" class="cabinet-nav-item">Пользователи</a>
        <a href="admin-access.php" class="cabinet-nav-item">Группы доступа</a>
        <?php endif; ?>
        <?= $suffix ?>
      </nav>
    <?php
}

/**
 * Боковая панель каталога: меню + дерево разделов.
 *
 * @param list<array<string,mixed>> $tree
 * @param 'catalog'|'learning'|'documents'|'overview'|'base'|'profile'|'settings' $active
 */
function mb_catalog_sidebar_render(array $tree, string $active = 'catalog', ?string $activeCategorySlug = null, string $suffix = ''): void
{
    require_once __DIR__ . '/knowledge.php';
    ?>
    <aside class="cabinet-sidebar cabinet-sidebar--catalog">
      <div class="cabinet-sidebar-block">
        <p class="cabinet-sidebar-title">Меню</p>
        <?php mb_cabinet_nav_render($active, $suffix); ?>
      </div>
      <div class="cabinet-sidebar-block cabinet-sidebar-block--tree">
        <p class="cabinet-sidebar-title">Разделы базы</p>
        <div class="kb-tree-wrap">
          <?= mb_render_category_tree($tree, 0, $activeCategorySlug) ?>
        </div>
      </div>
    </aside>
    <?php
}
