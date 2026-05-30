<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/seo.php';

if (mb_current_user() !== null) {
    header('Location: cabinet.php', true, 302);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mb_csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        mb_flash_set('register_error', 'Сессия устарела или форма отправлена повторно. Попробуйте ещё раз.');
        header('Location: register.php', true, 302);
        exit;
    }
    $name = isset($_POST['name']) ? (string) $_POST['name'] : '';
    $email = isset($_POST['email']) ? (string) $_POST['email'] : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $password2 = isset($_POST['password2']) ? (string) $_POST['password2'] : '';
    $err = mb_user_register($name, $email, $password, $password2);
    if ($err !== null) {
        mb_flash_set('register_error', $err);
        mb_flash_set('register_name', trim($name));
        mb_flash_set('register_email', trim($email));
        header('Location: register.php', true, 302);
        exit;
    }
    $emailLogin = strtolower(trim($email));
    $loginErr = mb_user_login($emailLogin, $password);
    if ($loginErr !== null) {
        mb_flash_set('register_error', 'Аккаунт создан, но автоматический вход не удался. Войдите вручную.');
        header('Location: login.php', true, 302);
        exit;
    }
    mb_flash_set('cabinet_notice', 'Добро пожаловать! Аккаунт успешно создан.');
    header('Location: cabinet.php', true, 302);
    exit;
}

$error = mb_flash_take('register_error');
$oldName = mb_flash_take('register_name') ?? '';
$oldEmail = mb_flash_take('register_email') ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
mb_seo_render_head([
    'title' => 'Регистрация — создать бесплатную базу знаний MindBase',
    'description' => 'Зарегистрируйтесь в MindBase за минуту и создайте бесплатную корпоративную базу знаний для команды: статьи, разделы, поиск и доступы.',
    'path' => 'register.php',
    'robots' => 'index, follow',
]);
?>
  <?php mb_seo_render_favicons(); ?>
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
      <h1 class="auth-title">Регистрация</h1>
      <p class="auth-desc">Пароль не короче 8 символов, с буквой и цифрой</p>
      <?php if ($error !== null): ?>
      <p class="auth-alert auth-alert--error" role="alert"><?= mb_h($error) ?></p>
      <?php endif; ?>
      <form class="auth-form" action="register.php" method="post" id="register-form" autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= mb_h(mb_csrf_token()) ?>">
        <label class="form-label">
          <span>Имя</span>
          <input type="text" name="name" class="form-input" placeholder="Как к вам обращаться" required minlength="2" maxlength="120" value="<?= mb_h($oldName) ?>" autocomplete="name">
        </label>
        <label class="form-label">
          <span>Email</span>
          <input type="email" name="email" class="form-input" placeholder="you@example.com" required value="<?= mb_h($oldEmail) ?>" autocomplete="email">
        </label>
        <label class="form-label">
          <span>Пароль</span>
          <input type="password" name="password" class="form-input" placeholder="Буквы и цифры, от 8 символов" required minlength="8" maxlength="128" autocomplete="new-password">
        </label>
        <label class="form-label">
          <span>Повторите пароль</span>
          <input type="password" name="password2" class="form-input" placeholder="••••••••" required minlength="8" maxlength="128" autocomplete="new-password">
        </label>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Зарегистрироваться</button>
      </form>
      <p class="auth-footer">
        Уже есть аккаунт? <a href="login.php">Войти</a>
      </p>
    </div>
  </main>
</body>
</html>
