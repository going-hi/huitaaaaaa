<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';

mb_user_logout();
header('Location: index.php', true, 302);
exit;
