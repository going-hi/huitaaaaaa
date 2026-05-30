<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/seo.php';

$user = mb_current_user();
$loginCabinet = 'login.php?' . http_build_query(['next' => 'cabinet.php']);
$pageUrl = mb_seo_absolute_url('/');

$faqItems = [
    [
        'question' => 'Нужна ли кредитная карта для регистрации?',
        'answer' => 'Нет. Регистрация и использование MindBase полностью бесплатны, карта не требуется.',
    ],
    [
        'question' => 'Сколько участников и статей можно добавить?',
        'answer' => 'Без ограничений. Вы можете приглашать любое количество участников и создавать сколько угодно статей и разделов.',
    ],
    [
        'question' => 'Можно ли экспортировать данные?',
        'answer' => 'Да. Вы в любой момент можете экспортировать контент в Markdown и использовать его вне платформы.',
    ],
    [
        'question' => 'Подходит ли MindBase для личного использования?',
        'answer' => 'Да. Можно вести личную базу знаний, а позже пригласить коллег и превратить её в командную.',
    ],
    [
        'question' => 'Чем MindBase отличается от Notion и Confluence?',
        'answer' => 'MindBase — бесплатная специализированная база знаний с фокусом на структуру разделов, группы доступа, документы и обучение без платных тарифов и лимитов.',
    ],
];

$seoTitle = 'MindBase — бесплатная база знаний для команд и компаний';
$seoDescription = 'MindBase — бесплатная платформа корпоративной базы знаний: статьи в Markdown, поиск, роли, документы и обучение. Быстрый старт для команд любого размера. Разработано ООО «Инним».';
$seoKeywords = [
    'база знаний',
    'корпоративная wiki',
    'wiki для команды',
    'база знаний компании',
    'управление знаниями',
    'документация команды',
    'MindBase',
    'бесплатная база знаний',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
mb_seo_render_head([
    'title' => $seoTitle,
    'description' => $seoDescription,
    'keywords' => $seoKeywords,
    'path' => '/',
    'image' => 'og-image.png',
    'json_ld' => mb_seo_landing_json_ld($pageUrl, $faqItems),
]);
?>
  <?php mb_seo_render_favicons(); ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <a class="skip-link" href="#main-content">Перейти к содержимому</a>
  <div class="noise" aria-hidden="true"></div>

  <header class="header">
    <nav class="nav container" aria-label="Основная навигация">
      <a href="index.php" class="logo" aria-label="MindBase — на главную">
        <img src="logo-icon.png" alt="Логотип MindBase" class="logo-img" width="36" height="36">
        <span>MindBase</span>
      </a>
      <ul class="nav-links">
        <li><a href="index.php#features">Возможности</a></li>
        <li><a href="index.php#audience">Для кого</a></li>
        <li><a href="index.php#how">Как это работает</a></li>
        <li><a href="index.php#faq">Вопросы</a></li>
        <?php if ($user !== null): ?>
        <li><a href="cabinet.php" class="btn btn-ghost">Личный кабинет</a></li>
        <li><a href="logout.php" class="btn btn-outline">Выйти</a></li>
        <?php else: ?>
        <li><a href="<?= mb_h($loginCabinet) ?>">Личный кабинет</a></li>
        <li><a href="login.php" class="btn btn-ghost">Войти</a></li>
        <li><a href="register.php" class="btn btn-primary">Начать бесплатно</a></li>
        <?php endif; ?>
      </ul>
      <button class="nav-toggle" aria-label="Открыть меню" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </header>

  <main id="main-content">
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-bg" aria-hidden="true">
        <div class="hero-gradient"></div>
        <div class="hero-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
      </div>
      <div class="container hero-inner">
        <p class="hero-badge">Бесплатная платформа базы знаний</p>
        <h1 class="hero-title" id="hero-title">
          Корпоративная база знаний<br>
          <span class="gradient-text">для всей команды</span>
        </h1>
        <p class="hero-desc">
          MindBase помогает создавать, структурировать и находить знания за секунды:
          документация, инструкции, runbook'и и лучшие практики в одном месте.
          Разработано <a href="#innim" class="innim-ref">ООО «Инним»</a>.
        </p>
        <div class="hero-actions">
          <?php if ($user !== null): ?>
          <a href="cabinet.php" class="btn btn-primary btn-lg">Личный кабинет</a>
          <?php else: ?>
          <a href="register.php" class="btn btn-primary btn-lg">Создать базу знаний бесплатно</a>
          <a href="login.php" class="btn btn-outline btn-lg">Войти</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="hero-visual" aria-hidden="true">
        <div class="mockup">
          <div class="mockup-bar">
            <span class="mockup-dot"></span>
            <span class="mockup-dot"></span>
            <span class="mockup-dot"></span>
            <span class="mockup-search">Поиск по базе знаний...</span>
          </div>
          <div class="mockup-content">
            <div class="mockup-sidebar">
              <div class="mockup-item active"></div>
              <div class="mockup-item"></div>
              <div class="mockup-item"></div>
              <div class="mockup-item"></div>
            </div>
            <div class="mockup-main">
              <div class="mockup-line w100"></div>
              <div class="mockup-line w80"></div>
              <div class="mockup-line w60"></div>
              <div class="mockup-line w90"></div>
              <div class="mockup-line w70"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section features" aria-labelledby="features-title">
      <div class="container">
        <p class="section-badge">Возможности</p>
        <h2 class="section-title" id="features-title">Всё для эффективной работы с базой знаний</h2>
        <p class="section-lead">Wiki-платформа с поиском, иерархией разделов и гибкими правами доступа для отделов и проектов.</p>
        <div class="features-grid">
          <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">🔍</div>
            <h3>Умный поиск</h3>
            <p>Полнотекстовый поиск по статьям с подсказками и фильтрами по разделам.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">📁</div>
            <h3>Иерархия и теги</h3>
            <p>Древовидная структура разделов, категории и теги для быстрой навигации.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">✏️</div>
            <h3>Редактор Markdown</h3>
            <p>Удобное форматирование статей, вставка кода и медиа для технической документации.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">👥</div>
            <h3>Роли и доступы</h3>
            <p>Группы доступа к разделам и документам для команд, отделов и проектов.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon" aria-hidden="true">📊</div>
            <h3>Документы и обучение</h3>
            <p>Файловый реестр, курсы с прогрессом и единый каталог материалов компании.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="audience" class="section audience" aria-labelledby="audience-title">
      <div class="container">
        <p class="section-badge">Для кого</p>
        <h2 class="section-title" id="audience-title">База знаний для команд любого профиля</h2>
        <div class="audience-grid">
          <article class="audience-card">
            <div class="audience-icon" aria-hidden="true">💻</div>
            <h3>Разработка</h3>
            <p>Документация API, гайды по онбордингу, архитектурные решения и runbook'и для дежурных.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon" aria-hidden="true">🎧</div>
            <h3>Поддержка</h3>
            <p>База ответов для клиентов, инструкции по типовым запросам и эскалациям.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon" aria-hidden="true">👔</div>
            <h3>HR и онбординг</h3>
            <p>Политики компании, чек-листы для новичков, описание процессов и корпоративные стандарты.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon" aria-hidden="true">📈</div>
            <h3>Маркетинг и продажи</h3>
            <p>Презентации продукта, скрипты продаж, кейсы и материалы для маркетинга.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="why" class="section why" aria-labelledby="why-title">
      <div class="container">
        <p class="section-badge">Почему MindBase</p>
        <h2 class="section-title" id="why-title">Просто, бесплатно и без скрытых условий</h2>
        <div class="why-grid">
          <div class="why-item">
            <span class="why-num" aria-hidden="true">1</span>
            <div class="why-item-text">
              <h3>Полностью бесплатно</h3>
              <p>Никаких платных тарифов и ограничений по числу статей или участников.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num" aria-hidden="true">2</span>
            <div class="why-item-text">
              <h3>Быстрый старт</h3>
              <p>Регистрация за минуту, первый раздел — за пять. Без сложной настройки.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num" aria-hidden="true">3</span>
            <div class="why-item-text">
              <h3>Ваши данные под контролем</h3>
              <p>Понятные настройки доступа, роли и возможность экспорта в любой момент.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num" aria-hidden="true">4</span>
            <div class="why-item-text">
              <h3>Рост вместе с вами</h3>
              <p>От личных заметок до корпоративной базы — масштабируйте без смены инструмента.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="how" class="section how" aria-labelledby="how-title">
      <div class="container">
        <p class="section-badge">Как это работает</p>
        <h2 class="section-title" id="how-title">Три шага к единой базе знаний</h2>
        <ol class="steps">
          <li class="step">
            <span class="step-num" aria-hidden="true">01</span>
            <h3>Создайте пространство</h3>
            <p>Зарегистрируйтесь и создайте рабочее пространство для своей команды или проекта.</p>
          </li>
          <li class="step">
            <span class="step-num" aria-hidden="true">02</span>
            <h3>Добавьте контент</h3>
            <p>Импортируйте документы, пишите статьи в Markdown-редакторе и организуйте разделы.</p>
          </li>
          <li class="step">
            <span class="step-num" aria-hidden="true">03</span>
            <h3>Находите и делитесь</h3>
            <p>Команда ищет ответы через поиск — знания всегда под рукой и актуальны.</p>
          </li>
        </ol>
      </div>
    </section>

    <section id="faq" class="section faq" aria-labelledby="faq-title">
      <div class="container">
        <p class="section-badge">Вопросы и ответы</p>
        <h2 class="section-title" id="faq-title">Частые вопросы о MindBase</h2>
        <div class="faq-list">
          <?php foreach ($faqItems as $faq): ?>
          <details class="faq-item">
            <summary class="faq-question"><?= mb_h($faq['question']) ?></summary>
            <p class="faq-answer"><?= mb_h($faq['answer']) ?></p>
          </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="innim" class="section innim-block" aria-labelledby="innim-title">
      <div class="container">
        <p class="section-badge">Партнёр</p>
        <h2 class="section-title" id="innim-title">Разработано в ООО «Инним»</h2>
        <div class="innim-content">
          <div class="innim-logo-wrap">
            <img src="innim-logo.png" alt="Логотип ООО Инним" class="innim-logo-img" width="48" height="48" loading="lazy">
            <span class="innim-logo">ИННИМ</span>
          </div>
          <p class="innim-desc">Платформа MindBase создана на базе ООО «Инним» — мы делаем инструменты для команд и управления знаниями.</p>
        </div>
      </div>
    </section>

    <section class="cta" aria-labelledby="cta-title">
      <div class="cta-bg" aria-hidden="true"></div>
      <div class="container cta-inner">
        <h2 id="cta-title">Готовы собрать базу знаний?</h2>
        <p>Платформа полностью бесплатна. Присоединяйтесь к командам, которые уже используют MindBase.</p>
        <div class="cta-actions">
          <?php if ($user !== null): ?>
          <a href="cabinet.php" class="btn btn-primary btn-lg">Личный кабинет</a>
          <?php else: ?>
          <a href="register.php" class="btn btn-primary btn-lg">Начать бесплатно</a>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container footer-inner">
      <div class="footer-brand">
        <a href="index.php" class="logo" aria-label="MindBase — на главную">
          <img src="logo-icon.png" alt="Логотип MindBase" class="logo-img" width="36" height="36" loading="lazy">
          MindBase
        </a>
        <p>Бесплатная платформа базы знаний для команд. Разработано ООО «Инним».</p>
        <a href="#innim" class="innim-logo-footer">
          <img src="innim-logo.png" alt="Логотип ИННИМ" class="innim-logo-footer-img" width="32" height="32" loading="lazy">
          <span>ИННИМ</span>
        </a>
      </div>
      <nav class="footer-links" aria-label="Ссылки в подвале">
        <div>
          <h4>Продукт</h4>
          <a href="index.php#features">Возможности</a>
          <a href="index.php#how">Как начать</a>
          <a href="index.php#faq">Вопросы</a>
          <?php if ($user !== null): ?>
          <a href="cabinet.php">Личный кабинет</a>
          <?php else: ?>
          <a href="register.php">Регистрация</a>
          <a href="<?= mb_h($loginCabinet) ?>">Личный кабинет</a>
          <?php endif; ?>
        </div>
        <div>
          <h4>Компания</h4>
          <a href="#innim">О разработчике</a>
          <a href="index.php#audience">Для кого</a>
        </div>
      </nav>
      <div class="footer-bottom">
        <p>© <?= date('Y') ?> MindBase. Сделано на базе ООО «Инним».</p>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
