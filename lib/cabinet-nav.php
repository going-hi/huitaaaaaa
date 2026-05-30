<?php

declare(strict_types=1);

require_once __DIR__ . '/roles.php';

/**
 * @param 'catalog'|'learning'|'documents'|'overview'|'profile'|'settings' $active
 */
function mb_cabinet_nav_class(string $active, string $key): string
{
    return $active === $key ? 'cabinet-nav-item active' : 'cabinet-nav-item';
}

/**
 * Боковое меню кабинета. $suffix — дополнительные пункты перед закрывающим тегом nav.
 *
 * @param 'catalog'|'learning'|'documents'|'overview'|'profile'|'settings' $active
 */
function mb_cabinet_nav_render(string $active, string $suffix = ''): void
{
    $c = static function (string $key) use ($active): string {
        return mb_cabinet_nav_class($active, $key);
    };
    ?>
      <nav class="cabinet-nav" aria-label="Основное меню">
        <p class="cabinet-nav-label">Платформа</p>
        <a href="knowledge-catalog.php" class="<?= $c('catalog') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">📚</span>
          <span class="cabinet-nav-text">Каталог знаний</span>
        </a>
        <a href="learning-materials.php" class="<?= $c('learning') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">🎓</span>
          <span class="cabinet-nav-text">Обучающие материалы</span>
        </a>
        <a href="documents.php" class="<?= $c('documents') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">📄</span>
          <span class="cabinet-nav-text">Документы</span>
        </a>
        <p class="cabinet-nav-label">Личный кабинет</p>
        <a href="cabinet.php" class="<?= $c('overview') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">🏠</span>
          <span class="cabinet-nav-text">Личный кабинет</span>
        </a>
        <p class="cabinet-nav-label">Аккаунт</p>
        <a href="cabinet-profile.php" class="<?= $c('profile') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">👤</span>
          <span class="cabinet-nav-text">Профиль</span>
        </a>
        <a href="cabinet-settings.php" class="<?= $c('settings') ?>">
          <span class="cabinet-nav-icon" aria-hidden="true">⚙️</span>
          <span class="cabinet-nav-text">Настройки</span>
        </a>
        <?php if (mb_is_admin()): ?>
        <p class="cabinet-nav-label">Администрирование</p>
        <a href="admin-users.php" class="cabinet-nav-item">
          <span class="cabinet-nav-icon" aria-hidden="true">👥</span>
          <span class="cabinet-nav-text">Пользователи</span>
        </a>
        <a href="admin-access.php" class="cabinet-nav-item">
          <span class="cabinet-nav-icon" aria-hidden="true">🔐</span>
          <span class="cabinet-nav-text">Группы доступа</span>
        </a>
        <?php endif; ?>
        <?= $suffix ?>
      </nav>
    <?php
}
