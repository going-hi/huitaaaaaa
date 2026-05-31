<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/seo.php';

$user = mb_current_user();
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
    [
        'question' => 'Можно ли использовать MindBase как корпоративную wiki?',
        'answer' => 'Да. MindBase — это корпоративная wiki с разделами, ролями и поиском: подходит для внутренней документации, регламентов и базы ответов поддержки.',
    ],
    [
        'question' => 'Подходит ли MindBase как альтернатива Notion для команды?',
        'answer' => 'Да, если нужна именно база знаний и wiki, а не универсальный редактор. MindBase бесплатен, заточен под статьи, доступы и поиск по документации команды.',
    ],
];

$seoTitle = 'MindBase — бесплатная корпоративная wiki и база знаний для команд';
$seoDescription = 'MindBase — бесплатная платформа корпоративной wiki и базы знаний: Markdown-статьи, полнотекстовый поиск, роли, документы и обучение. Без тарифов и лимитов — альтернатива Notion и Confluence. Регистрация за минуту.';
$seoKeywords = [
    // Ядро — база знаний / wiki
    'база знаний',
    'корпоративная wiki',
    'wiki для команды',
    'база знаний компании',
    'корпоративная база знаний',
    'внутренняя wiki',
    'wiki для бизнеса',
    'wiki онлайн',
    'wiki для компании',
    'корпоративная документация',
    'внутренняя документация',
    'документация команды',
    'база знаний онлайн',
    'база знаний бесплатно',
    'бесплатная wiki',
    'бесплатная база знаний',
    'создать базу знаний',
    'создать wiki для команды',
    'корпоративный портал знаний',
    'единая база знаний',
    'централизованная документация',
    'wiki система',
    'система wiki',
    'корпоративная wiki система',
    'онлайн wiki',
    'веб wiki',
    'wiki платформа',
    'платформа базы знаний',
    'MindBase',
    'mindbase',
    'mindbase wiki',
    'mindbase база знаний',
    // Knowledge Management
    'управление знаниями',
    'knowledge management',
    'KM система',
    'система управления знаниями',
    'корпоративное управление знаниями',
    'knowledge base',
    'knowledge base на русском',
    'корпоративная knowledge base',
    'knowledge management system',
    'KMS',
    'система KM',
    'управление корпоративными знаниями',
    'накопление знаний в компании',
    'сохранение экспертизы',
    'передача знаний в команде',
    'обмен знаниями в команде',
    'корпоративная память организации',
    'организационные знания',
    // Альтернативы конкурентам
    'альтернатива Notion',
    'альтернатива Confluence',
    'альтернатива Notion для команды',
    'альтернатива Confluence бесплатно',
    'замена Confluence',
    'замена Notion для документации',
    'Notion для wiki',
    'Confluence аналог',
    'Confluence аналог бесплатный',
    'Notion аналог бесплатный',
    'бесплатная альтернатива Notion',
    'бесплатная альтернатива Confluence',
    'wiki вместо Notion',
    'wiki вместо Confluence',
    'корпоративная wiki без подписки',
    'wiki без ежемесячной оплаты',
    'бесплатный Confluence',
    'бесплатный Notion для команды',
    'Slite аналог',
    'GitBook аналог',
    'Outline wiki аналог',
    'BookStack аналог',
    'MediaWiki для бизнеса',
    // По отделам и сценариям
    'wiki для IT',
    'база знаний для разработчиков',
    'документация для разработчиков',
    'API документация команды',
    'архитектурная документация',
    'runbook wiki',
    'runbook база знаний',
    'база знаний поддержки',
    'wiki для техподдержки',
    'база ответов поддержки',
    'FAQ для команды',
    'шаблоны ответов поддержки',
    'wiki для HR',
    'база знаний HR',
    'онбординг сотрудников wiki',
    'база знаний для продаж',
    'скрипты продаж wiki',
    'wiki для маркетинга',
    'регламенты компании онлайн',
    'внутренние регламенты',
    'корпоративные политики',
    'инструкции для сотрудников',
    'SOP документация',
    'база знаний для стартапа',
    'wiki для малого бизнеса',
    'wiki для агентства',
    'база знаний для удалённой команды',
    'документация удалённой команды',
    'wiki для проектной команды',
    'база знаний проекта',
    // Функции продукта
    'Markdown wiki',
    'статьи в Markdown',
    'Markdown редактор для команды',
    'полнотекстовый поиск',
    'поиск по документации',
    'поиск по базе знаний',
    'иерархия разделов',
    'древовидная структура wiki',
    'каталог знаний',
    'категории wiki',
    'теги для статей',
    'роли и права доступа',
    'группы доступа',
    'гранулярные права wiki',
    'мультитенантность',
    'рабочие пространства',
    'workspace база знаний',
    'несколько баз знаний',
    'изоляция данных команд',
    'файловый реестр',
    'загрузка документов',
    'хранение PDF wiki',
    'скачивание документов',
    'экспорт Markdown',
    'экспорт wiki',
    'выгрузка базы знаний',
    'курсы обучения',
    'внутреннее обучение',
    'LMS для компании',
    'онбординг курсы',
    'прогресс обучения',
    'дашборд активности',
    'счётчик просмотров статей',
    'адаптивная wiki',
    'мобильная база знаний',
    // Коммерческие запросы
    'бесплатная wiki для бизнеса',
    'бесплатная база знаний без лимитов',
    'wiki без подписки',
    'база знаний без оплаты',
    'wiki без кредитной карты',
    'создать wiki бесплатно',
    'зарегистрировать базу знаний',
    'бесплатный KM инструмент',
    'wiki для команды бесплатно',
    'SaaS база знаний бесплатно',
    'облачная wiki',
    'облачная база знаний',
    'хостинг wiki',
    'wiki в облаке',
    'корпоративная wiki Россия',
    'база знаний для российских компаний',
    'wiki на русском языке',
    'русская wiki платформа',
    // Long-tail
    'как создать базу знаний для команды бесплатно',
    'как организовать корпоративную wiki',
    'где хранить документацию команды',
    'как заменить Confluence бесплатно',
    'чем заменить Notion для документации',
    'как вести внутреннюю документацию компании',
    'система для хранения регламентов и инструкций',
    'платформа для обмена знаниями в команде',
    'как не потерять знания при уходе сотрудника',
    'централизованное хранение экспертизы команды',
    'wiki для онбординга новых сотрудников',
    'база знаний для службы поддержки клиентов',
    'runbook для DevOps команды онлайн',
    'где хранить API документацию для команды',
    'как структурировать корпоративные знания',
    'корпоративная wiki с правами доступа по отделам',
    'база знаний с поиском по тексту статей',
    'wiki с экспортом в markdown',
    'платформа для внутреннего обучения сотрудников',
    'создать wiki для стартапа',
    'база знаний для удалённой команды разработчиков',
    'альтернатива confluence для малого бизнеса',
    'notion или wiki для документации команды',
    'knowledge management для малого и среднего бизнеса',
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
    <div class="container site-header-inner">
      <a href="index.php" class="logo" aria-label="MindBase — на главную">
        <img src="logo-icon.png" alt="Логотип MindBase" class="logo-img" width="36" height="36">
        <span>MindBase</span>
      </a>
      <nav class="site-nav" id="site-nav" aria-label="Основная навигация">
        <ul class="nav-links">
          <li><a href="index.php#about">О платформе</a></li>
          <li><a href="index.php#features">Возможности</a></li>
          <li><a href="index.php#audience">Для кого</a></li>
          <li><a href="index.php#how">Как это работает</a></li>
          <li><a href="index.php#faq">Вопросы</a></li>
          <?php if ($user !== null): ?>
          <li><a href="cabinet.php" class="btn btn-ghost">Личный кабинет</a></li>
          <li><a href="logout.php" class="btn btn-outline">Выйти</a></li>
          <?php else: ?>
          <li><a href="login.php" class="btn btn-ghost">Войти</a></li>
          <li><a href="register.php" class="btn btn-primary">Начать бесплатно</a></li>
          <?php endif; ?>
        </ul>
      </nav>
      <button type="button" class="nav-toggle" aria-label="Открыть меню" aria-expanded="false" aria-controls="site-nav">
        <span></span><span></span><span></span>
      </button>
    </div>
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
        <p class="hero-badge">Бесплатная корпоративная wiki и база знаний</p>
        <h1 class="hero-title" id="hero-title">
          Бесплатная база знаний<br>
          <span class="gradient-text">для команд и компаний</span>
        </h1>
        <p class="hero-desc">
          <strong>MindBase</strong> — платформа управления знаниями для бизнеса: корпоративная wiki,
          техническая документация, инструкции, runbook'и и лучшие практики в одном месте.
          Полнотекстовый поиск, Markdown-статьи и гибкие права доступа — без платных тарифов.
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
      <figure class="hero-visual">
        <div class="mockup" role="img" aria-label="Интерфейс MindBase: поиск по базе знаний, разделы и статьи">
          <div class="mockup-bar">
            <span class="mockup-dot" aria-hidden="true"></span>
            <span class="mockup-dot" aria-hidden="true"></span>
            <span class="mockup-dot" aria-hidden="true"></span>
            <span class="mockup-search">Поиск по базе знаний...</span>
          </div>
          <div class="mockup-content">
            <div class="mockup-sidebar" aria-hidden="true">
              <div class="mockup-item active"></div>
              <div class="mockup-item"></div>
              <div class="mockup-item"></div>
              <div class="mockup-item"></div>
            </div>
            <div class="mockup-main" aria-hidden="true">
              <div class="mockup-line w100"></div>
              <div class="mockup-line w80"></div>
              <div class="mockup-line w60"></div>
              <div class="mockup-line w90"></div>
              <div class="mockup-line w70"></div>
            </div>
          </div>
        </div>
      </figure>
    </section>

    <section id="about" class="section about" aria-labelledby="about-title">
      <div class="container">
        <div class="about-grid">
          <header class="about-intro">
            <p class="section-badge">О платформе</p>
            <h2 class="section-title" id="about-title">Онлайн-база знаний и корпоративная wiki для бизнеса</h2>
            <p class="about-text">
              MindBase — бесплатная платформа для команд: документация, регламенты и экспертиза
              в одной структуре с поиском и правами доступа. Без подписок, лимитов и долгого внедрения.
            </p>
            <p class="about-text about-text-muted">
              Подходит как wiki для IT, поддержки, HR и продаж — альтернатива Notion и Confluence,
              когда нужна именно корпоративная база знаний, а не универсальный редактор.
            </p>
          </header>
          <div class="about-highlights">
            <article class="about-card">
              <span class="about-card-icon" aria-hidden="true">📚</span>
              <div>
                <h3>Wiki и разделы</h3>
                <p>Иерархия, Markdown-статьи и каталог материалов компании.</p>
              </div>
            </article>
            <article class="about-card">
              <span class="about-card-icon" aria-hidden="true">🔍</span>
              <div>
                <h3>Поиск</h3>
                <p>Полнотекстовый поиск по документации команды за секунды.</p>
              </div>
            </article>
            <article class="about-card">
              <span class="about-card-icon" aria-hidden="true">👥</span>
              <div>
                <h3>Доступы</h3>
                <p>Роли, группы и несколько рабочих пространств.</p>
              </div>
            </article>
            <article class="about-card">
              <span class="about-card-icon" aria-hidden="true">📎</span>
              <div>
                <h3>Документы</h3>
                <p>Реестр файлов, курсы и экспорт в Markdown.</p>
              </div>
            </article>
            <article class="about-card about-card-accent">
              <span class="about-card-icon" aria-hidden="true">✨</span>
              <div>
                <h3>Бесплатно</h3>
                <p>Без лимитов на участников, статьи и рабочие пространства.</p>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section features" aria-labelledby="features-title">
      <div class="container">
        <header class="section-header">
          <p class="section-badge">Возможности</p>
          <h2 class="section-title" id="features-title">Инструменты корпоративной базы знаний</h2>
          <p class="section-lead">Wiki-платформа с поиском, иерархией разделов и гибкими правами доступа для отделов и проектов.</p>
        </header>
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
        <header class="section-header">
          <p class="section-badge">Для кого</p>
          <h2 class="section-title" id="audience-title">Wiki и база знаний для команд любого профиля</h2>
        </header>
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
        <header class="section-header">
          <p class="section-badge">Почему MindBase</p>
          <h2 class="section-title" id="why-title">Простая корпоративная wiki без скрытых условий</h2>
        </header>
        <div class="why-grid">
          <article class="why-item">
            <span class="why-num" aria-hidden="true">1</span>
            <div class="why-item-text">
              <h3>Полностью бесплатно</h3>
              <p>Никаких платных тарифов и ограничений по числу статей или участников.</p>
            </div>
          </article>
          <article class="why-item">
            <span class="why-num" aria-hidden="true">2</span>
            <div class="why-item-text">
              <h3>Быстрый старт</h3>
              <p>Регистрация за минуту, первый раздел — за пять. Без сложной настройки.</p>
            </div>
          </article>
          <article class="why-item">
            <span class="why-num" aria-hidden="true">3</span>
            <div class="why-item-text">
              <h3>Ваши данные под контролем</h3>
              <p>Понятные настройки доступа, роли и возможность экспорта в любой момент.</p>
            </div>
          </article>
          <article class="why-item">
            <span class="why-num" aria-hidden="true">4</span>
            <div class="why-item-text">
              <h3>Рост вместе с вами</h3>
              <p>От личных заметок до корпоративной базы — масштабируйте без смены инструмента.</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section id="how" class="section how" aria-labelledby="how-title">
      <div class="container">
        <header class="section-header">
          <p class="section-badge">Как это работает</p>
          <h2 class="section-title" id="how-title">Три шага к единой базе знаний компании</h2>
        </header>
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
        <header class="section-header">
          <p class="section-badge">Вопросы и ответы</p>
          <h2 class="section-title" id="faq-title">Частые вопросы о корпоративной wiki MindBase</h2>
        </header>
        <div class="faq-list">
          <?php foreach ($faqItems as $faq): ?>
          <details class="faq-item">
            <summary class="faq-question"><?= mb_h($faq['question']) ?></summary>
            <div class="faq-answer">
              <p><?= mb_h($faq['answer']) ?></p>
            </div>
          </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="innim" class="section innim-block" aria-labelledby="innim-title">
      <div class="container">
        <header class="section-header">
          <p class="section-badge">Партнёр</p>
          <h2 class="section-title" id="innim-title">Разработано в ООО «Инним»</h2>
        </header>
        <article class="innim-content">
          <div class="innim-logo-wrap">
            <img src="innim-logo.png" alt="Логотип ООО Инним" class="innim-logo-img" width="48" height="48" loading="lazy">
            <span class="innim-logo">ИННИМ</span>
          </div>
          <p class="innim-desc">Платформа MindBase создана на базе <strong>ООО «Инним»</strong> — мы делаем инструменты для команд и управления знаниями.</p>
        </article>
      </div>
    </section>

    <aside class="cta" aria-labelledby="cta-title">
      <div class="cta-bg" aria-hidden="true"></div>
      <div class="container cta-inner">
        <h2 id="cta-title">Готовы создать корпоративную базу знаний?</h2>
        <p>Бесплатная wiki для команды — регистрация за минуту, без карты и скрытых тарифов.</p>
        <div class="cta-actions">
          <?php if ($user !== null): ?>
          <a href="cabinet.php" class="btn btn-primary btn-lg">Личный кабинет</a>
          <?php else: ?>
          <a href="register.php" class="btn btn-primary btn-lg">Создать бесплатную базу знаний</a>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </main>

  <footer class="footer">
    <div class="container footer-inner">
      <div class="footer-brand">
        <a href="index.php" class="logo" aria-label="MindBase — на главную">
          <img src="logo-icon.png" alt="Логотип MindBase — база знаний для команд" class="logo-img" width="36" height="36" loading="lazy">
          MindBase
        </a>
        <p>Бесплатная корпоративная wiki и база знаний для команд. Разработано ООО «Инним».</p>
        <a href="#innim" class="innim-logo-footer">
          <img src="innim-logo.png" alt="Логотип ИННИМ" class="innim-logo-footer-img" width="32" height="32" loading="lazy">
          <span>ИННИМ</span>
        </a>
      </div>
      <nav class="footer-links" aria-label="Ссылки в подвале">
        <div>
          <p class="footer-heading">Продукт</p>
          <a href="index.php#about">О платформе</a>
          <a href="index.php#features">Возможности</a>
          <a href="index.php#how">Как начать</a>
          <a href="index.php#faq">Вопросы</a>
          <?php if ($user !== null): ?>
          <a href="cabinet.php">Личный кабинет</a>
          <?php else: ?>
          <a href="register.php">Регистрация</a>
          <a href="login.php">Войти</a>
          <?php endif; ?>
        </div>
        <div>
          <p class="footer-heading">Компания</p>
          <a href="#innim">О разработчике</a>
          <a href="index.php#audience">Для кого</a>
        </div>
      </nav>
      <div class="footer-bottom">
        <p><small>© <?= date('Y') ?> MindBase — бесплатная база знаний для команд.</small></p>
        <address class="footer-address">Разработано <a href="#innim">ООО «Инним»</a></address>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
