<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/seo.php';

if (mb_current_user() !== null) {
    header('Location: ' . mb_login_redirect_target($_GET['next'] ?? null), true, 302);
    exit;
}

$nextSafe = mb_login_redirect_target($_GET['next'] ?? null);
$nextQuery = '?next=' . rawurlencode($nextSafe);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        mb_flash_set('login_error', 'Сессия устарела или форма отправлена повторно. Попробуйте ещё раз.');
        header('Location: login.php' . $nextQuery, true, 302);
        exit;
    }
    $login = isset($_POST['login']) ? trim((string) $_POST['login']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $nextPost = mb_login_redirect_target(isset($_POST['next']) ? (string) $_POST['next'] : null);
    $err = mb_user_login($login, $password);
    if ($err !== null) {
        mb_flash_set('login_error', $err);
        mb_flash_set('login_email', $login);
        header('Location: login.php' . $nextQuery, true, 302);
        exit;
    }
    header('Location: ' . $nextPost, true, 302);
    exit;
}

$error = mb_flash_take('login_error');
$oldEmail = mb_flash_take('login_email') ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
mb_seo_render_head([
    'title' => 'Вход — MindBase',
    'description' => 'Войдите в личный кабинет MindBase для доступа к корпоративной базе знаний вашей команды.',
    'path' => 'login.php',
    'robots' => 'noindex, follow',
]);
?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
  <div class="noise"></div>

  <header class="header header-auth">
    <nav class="nav container">
      <a href="index.php" class="logo">
        <img src="logo-icon.png" alt="MindBase" class="logo-img">
        <span>MindBase</span>
      </a>
      <a href="index.php" class="btn btn-ghost">На главную</a>
    </nav>
  </header>

  <main class="auth-main">
    <div class="auth-card">
      <h1 class="auth-title">Вход в аккаунт</h1>
      <p class="auth-desc">Email и пароль, указанные при регистрации</p>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error" role="alert"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <form class="auth-form" action="login.php<?= mb_h($nextQuery) ?>" method="post" id="login-form" autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= mb_h($nextSafe) ?>">
        <label class="form-label">
          <span>Email</span>
          <input type="email" name="login" class="form-input" placeholder="you@example.com" required value="<?= mb_h($oldEmail) ?>" autocomplete="username">
        </label>
        <label class="form-label">
          <span>Пароль</span>
          <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Войти</button>
      </form>
      <p class="auth-footer">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
      </p>
    </div>
  </main>
</body>
</html>
