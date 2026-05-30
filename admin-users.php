<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/roles.php';
require_once __DIR__ . '/lib/cabinet-nav.php';
require_once __DIR__ . '/lib/cabinet-layout.php';
mb_require_admin();
$user = mb_current_user();

$filterRaw = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));
$filterQ = mb_strtolower($filterRaw, 'UTF-8');

$error = null;
$success = mb_flash_take('cabinet_notice');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
    $uid = (int) ($_POST['user_id'] ?? 0);
    if (isset($_POST['remove_member']) && $uid > 0) {
        $err = mb_user_remove_from_workspace($uid);
        if ($err !== null) {
            $error = $err;
        } else {
            mb_flash_set('cabinet_notice', 'Пользователь удалён из базы.');
            $redirectUrl = 'admin-users.php';
            if ($filterRaw !== '') {
                $redirectUrl .= '?' . http_build_query(['q' => $filterRaw]);
            }
            header('Location: ' . $redirectUrl, true, 302);
            exit;
        }
    } elseif ($uid > 0) {
        $role = (string) ($_POST['role'] ?? MB_ROLE_USER);
        $err = mb_user_set_role($uid, $role);
        if ($err !== null) {
            $error = $err;
        } else {
            $gids = isset($_POST['group_ids']) && is_array($_POST['group_ids']) ? array_map('intval', $_POST['group_ids']) : [];
            mb_user_set_groups($uid, $gids);
            mb_flash_set('cabinet_notice', 'Пользователь обновлён.');
            $redirectUrl = 'admin-users.php';
            if ($filterRaw !== '') {
                $redirectUrl .= '?' . http_build_query(['q' => $filterRaw]);
            }
            header('Location: ' . $redirectUrl, true, 302);
            exit;
        }
    }
}

$users = mb_users_list();
$groups = mb_access_groups_list();

$adminUserSearchBlob = static function (array $u, array $groups): string {
    $assignedGroups = array_values(array_filter(
        $groups,
        static fn (array $g): bool => in_array((int) $g['id'], $u['group_ids'], true)
    ));

    return mb_strtolower(trim(
        $u['name'] . ' '
        . $u['email'] . ' '
        . mb_role_label($u['role']) . ' '
        . implode(' ', array_map(static fn (array $g): string => $g['name'], $assignedGroups))
    ), 'UTF-8');
};

$visibleUserCount = count(array_filter(
    $users,
    static fn (array $u): bool => $filterQ === '' || mb_strpos($adminUserSearchBlob($u, $groups), $filterQ) !== false
));

mb_cabinet_head('Пользователи');
mb_cabinet_header_render($user, 'Поиск...', false);
mb_cabinet_sidebar_open('admin-users');
?>
      <h1 class="cabinet-page-title">Пользователи и роли</h1>
      <p class="cabinet-page-lead">Участники текущей базы. Администратор может менять роли, группы доступа и удалять пользователей из базы.</p>
      <?php if ($error): ?><p class="auth-alert auth-alert--error"><?= mb_h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="auth-alert auth-alert--success"><?= mb_h($success) ?></p><?php endif; ?>

      <div class="cabinet-meta-strip admin-user-legend">
        <?php foreach (mb_valid_roles() as $role): ?>
        <span class="cabinet-pill"><span class="<?= mb_h(mb_role_badge_class($role)) ?>"><?= mb_h(mb_role_label($role)) ?></span></span>
        <?php endforeach; ?>
      </div>

      <h2 class="cabinet-section-heading">
        Список пользователей
        <span class="cabinet-section-count" data-admin-user-visible-count><?= (int) $visibleUserCount ?></span>
      </h2>
      <?php if ($users === []): ?>
      <p class="cabinet-muted-text">Пользователи не найдены.</p>
      <?php else: ?>
      <div class="admin-user-toolbar">
        <label class="form-label admin-user-search">
          <span>Поиск пользователя</span>
          <input
            type="search"
            id="admin-user-filter"
            name="q"
            class="form-input"
            data-admin-user-filter
            value="<?= mb_h($filterRaw) ?>"
            placeholder="Имя, email, роль или группа..."
            autocomplete="off"
          >
        </label>
      </div>
      <p class="cabinet-muted-text admin-user-filter-empty" data-admin-user-empty<?= $visibleUserCount === 0 && $filterQ !== '' ? '' : ' hidden' ?>>Никого не найдено. Попробуйте другой запрос.</p>
      <div class="admin-user-list" data-admin-user-list>
        <?php foreach ($users as $u):
            $isSelf = (int) $u['id'] === (int) $user['id'];
            $isOwner = !empty($u['is_owner']);
            $canRemove = !$isSelf && !$isOwner;
            $assignedGroups = array_values(array_filter(
                $groups,
                static fn (array $g): bool => in_array((int) $g['id'], $u['group_ids'], true)
            ));
            $searchBlob = $adminUserSearchBlob($u, $groups);
            $isVisible = $filterQ === '' || mb_strpos($searchBlob, $filterQ) !== false;
            ?>
        <article class="admin-user-card<?= $isSelf ? ' is-self' : '' ?><?= $isVisible ? '' : ' is-filter-hidden' ?>" data-admin-user-search="<?= mb_h($searchBlob) ?>">
          <form class="admin-user-card__form cabinet-form" method="post">
            <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <input type="hidden" name="q" value="<?= mb_h($filterRaw) ?>">

            <div class="admin-user-card__head">
              <div class="admin-user-card__identity">
                <h3 class="admin-user-card__name">
                  <?= mb_h($u['name']) ?>
                  <?php if ($isSelf): ?><span class="admin-user-card__you">это вы</span><?php endif; ?>
                </h3>
                <p class="admin-user-card__email"><?= mb_h($u['email']) ?></p>
                <?php if ($assignedGroups !== []): ?>
                <div class="admin-user-card__chips">
                  <?php foreach ($assignedGroups as $g): ?>
                  <span class="admin-user-card__chip"><?= mb_h($g['name']) ?></span>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="admin-user-card__groups-empty">Без групп доступа</p>
                <?php endif; ?>
              </div>
              <span class="<?= mb_h(mb_role_badge_class($u['role'])) ?>"><?= mb_h(mb_role_label($u['role'])) ?></span>
            </div>

            <div class="admin-user-card__fields">
              <label class="form-label admin-user-card__role">
                <span>Роль</span>
                <select name="role" class="form-input">
                  <?php foreach (mb_valid_roles() as $r): ?>
                  <option value="<?= mb_h($r) ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= mb_h(mb_role_label($r)) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>

              <?php if ($groups !== []): ?>
              <fieldset class="admin-user-card__groups">
                <legend>Группы доступа к материалам</legend>
                <div class="admin-user-card__group-grid">
                  <?php foreach ($groups as $g): ?>
                  <label class="form-check admin-user-card__group-check">
                    <input type="checkbox" name="group_ids[]" value="<?= (int) $g['id'] ?>"
                      <?= in_array((int) $g['id'], $u['group_ids'], true) ? 'checked' : '' ?>>
                    <span><?= mb_h($g['name']) ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
              </fieldset>
              <?php else: ?>
              <p class="cabinet-muted-text admin-user-card__no-groups">
                Группы ещё не созданы. <a href="admin-access.php" class="cabinet-text-link">Перейти к группам доступа</a>
              </p>
              <?php endif; ?>
            </div>

            <div class="cabinet-form-actions admin-user-card__actions">
              <button type="submit" class="btn btn-primary btn-sm">Сохранить изменения</button>
            </div>
          </form>
          <?php if ($canRemove): ?>
          <form class="admin-user-card__remove" method="post" onsubmit="return confirm(<?= json_encode('Удалить ' . $u['name'] . ' из этой базы?', JSON_UNESCAPED_UNICODE) ?>);">
            <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <input type="hidden" name="remove_member" value="1">
            <input type="hidden" name="q" value="<?= mb_h($filterRaw) ?>">
            <button type="submit" class="btn btn-danger btn-danger--soft btn-sm">Удалить из базы</button>
          </form>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <p class="cabinet-page-foot"><a href="admin-access.php" class="cabinet-text-link">К группам доступа →</a></p>
      <?php if ($users !== []): ?>
      <script>
      (function () {
        var input = document.getElementById('admin-user-filter');
        var list = document.querySelector('[data-admin-user-list]');
        if (!input || !list) {
          return;
        }
        var empty = document.querySelector('[data-admin-user-empty]');
        var count = document.querySelector('[data-admin-user-visible-count]');
        var cards = list.querySelectorAll('.admin-user-card[data-admin-user-search]');

        function normalize(value) {
          return (value || '').replace(/\s+/g, ' ').trim().toLocaleLowerCase('ru-RU');
        }

        function applyFilter() {
          var query = normalize(input.value);
          var visible = 0;
          cards.forEach(function (card) {
            var haystack = normalize(card.getAttribute('data-admin-user-search'));
            var match = query === '' || haystack.indexOf(query) !== -1;
            card.classList.toggle('is-filter-hidden', !match);
            if (match) {
              visible += 1;
            }
          });
          if (empty) {
            empty.hidden = visible > 0;
          }
          if (count) {
            count.textContent = String(visible);
          }
          var url = new URL(window.location.href);
          var raw = input.value.trim();
          if (raw) {
            url.searchParams.set('q', raw);
          } else {
            url.searchParams.delete('q');
          }
          window.history.replaceState(null, '', url.toString());
          document.querySelectorAll('input[name="q"][type="hidden"]').forEach(function (field) {
            field.value = raw;
          });
        }

        input.addEventListener('input', applyFilter);
        input.addEventListener('search', applyFilter);
        applyFilter();
      })();
      </script>
      <?php endif; ?>
<?php
mb_cabinet_sidebar_close();
mb_cabinet_foot('admin-users');
