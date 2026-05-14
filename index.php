<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth.php';

$user = mb_current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MindBase — Платформа базы знаний</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="noise"></div>

  <header class="header">
    <nav class="nav container">
      <a href="index.php" class="logo">
        <img src="logo.png" alt="MindBase" class="logo-img">
        <span>MindBase</span>
      </a>
      <ul class="nav-links">
        <li><a href="index.php#features">Возможности</a></li>
        <li><a href="index.php#audience">Для кого</a></li>
        <li><a href="index.php#how">Как это работает</a></li>
        <li><a href="index.php#faq">Вопросы</a></li>
        <?php if ($user !== null): ?>
        <li><a href="cabinet.php" class="btn btn-ghost">Кабинет</a></li>
        <li><a href="logout.php" class="btn btn-outline">Выйти</a></li>
        <?php else: ?>
        <li><a href="login.php" class="btn btn-ghost">Войти</a></li>
        <li><a href="register.php" class="btn btn-primary">Начать бесплатно</a></li>
        <?php endif; ?>
      </ul>
      <button class="nav-toggle" aria-label="Меню">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div class="hero-bg">
        <div class="hero-gradient"></div>
        <div class="hero-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
      </div>
      <div class="container hero-inner">
        <h1 class="hero-title">
          Вся информация команды<br>
          <span class="gradient-text">в одном месте</span>
        </h1>
        <p class="hero-desc">
          Создавайте, структурируйте и находите знания за секунды.
          Единая база для документации, инструкций и лучших практик.
          Сделано на базе <a href="#innim" class="innim-ref">ООО Инним</a>.
        </p>
        <div class="hero-actions">
          <?php if ($user !== null): ?>
          <a href="cabinet.php" class="btn btn-primary btn-lg">Открыть кабинет</a>
          <?php else: ?>
          <a href="register.php" class="btn btn-primary btn-lg">Создать базу знаний</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="hero-visual">
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

    <section id="features" class="section features">
      <div class="container">
        <p class="section-badge">Возможности</p>
        <h2 class="section-title">Всё для эффективной работы с знаниями</h2>
        <div class="features-grid">
          <article class="feature-card">
            <div class="feature-icon">🔍</div>
            <h3>Умный поиск</h3>
            <p>Семантический и полнотекстовый поиск с подсказками и фильтрами.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">📁</div>
            <h3>Иерархия и теги</h3>
            <p>Древовидная структура, категории и теги для быстрой навигации.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">✏️</div>
            <h3>Редактор Markdown</h3>
            <p>Удобное форматирование, вставка кода и медиа.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>Роли и доступы</h3>
            <p>Гибкое управление правами для команд и отделов.</p>
          </article>
          <article class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Аналитика</h3>
            <p>Популярные статьи, поисковые запросы и пробелы в знаниях.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="audience" class="section audience">
      <div class="container">
        <p class="section-badge">Для кого</p>
        <h2 class="section-title">Подходит командам любого профиля</h2>
        <div class="audience-grid">
          <article class="audience-card">
            <div class="audience-icon">💻</div>
            <h3>Разработка</h3>
            <p>Документация API, гайды по онбордингу, архитектурные решения и runbook'и для дежурных.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon">🎧</div>
            <h3>Поддержка</h3>
            <p>База ответов для клиентов, инструкции по типовым запросам и эскалациям.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon">👔</div>
            <h3>HR и онбординг</h3>
            <p>Политики компании, чек-листы для новичков, описание процессов и корпоративные стандарты.</p>
          </article>
          <article class="audience-card">
            <div class="audience-icon">📈</div>
            <h3>Маркетинг и продажи</h3>
            <p>Презентации продукта, скрипты продаж, кейсы и материалы для маркетинга.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="why" class="section why">
      <div class="container">
        <p class="section-badge">Почему MindBase</p>
        <h2 class="section-title">Просто, бесплатно и без скрытых условий</h2>
        <div class="why-grid">
          <div class="why-item">
            <span class="why-num">1</span>
            <div class="why-item-text">
              <h3>Полностью бесплатно</h3>
              <p>Никаких платных тарифов и ограничений по числу статей или участников.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num">2</span>
            <div class="why-item-text">
              <h3>Быстрый старт</h3>
              <p>Регистрация за минуту, первый раздел — за пять. Без сложной настройки.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num">3</span>
            <div class="why-item-text">
              <h3>Ваши данные под контролем</h3>
              <p>Понятные настройки доступа, роли и возможность экспорта в любой момент.</p>
            </div>
          </div>
          <div class="why-item">
            <span class="why-num">4</span>
            <div class="why-item-text">
              <h3>Рост вместе с вами</h3>
              <p>От личных заметок до корпоративной базы — масштабируйте без смены инструмента.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="how" class="section how">
      <div class="container">
        <p class="section-badge">Как это работает</p>
        <h2 class="section-title">Три шага к единой базе знаний</h2>
        <div class="steps">
          <div class="step">
            <span class="step-num">01</span>
            <h3>Создайте пространство</h3>
            <p>Зарегистрируйтесь и создайте рабочее пространство для своей команды или проекта.</p>
          </div>
          <div class="step">
            <span class="step-num">02</span>
            <h3>Добавьте контент</h3>
            <p>Импортируйте документы, пишите статьи в удобном редакторе или подключайте интеграции.</p>
          </div>
          <div class="step">
            <span class="step-num">03</span>
            <h3>Находите и делитесь</h3>
            <p>Команда ищет ответы через поиск, всё всегда под рукой.</p>
          </div>
        </div>
      </div>
    </section>

    <section id="faq" class="section faq">
      <div class="container">
        <p class="section-badge">Вопросы и ответы</p>
        <h2 class="section-title">Частые вопросы</h2>
        <div class="faq-list">
          <details class="faq-item">
            <summary class="faq-question">Нужна ли кредитная карта для регистрации?</summary>
            <p class="faq-answer">Нет. Регистрация и использование MindBase полностью бесплатны, карта не требуется.</p>
          </details>
          <details class="faq-item">
            <summary class="faq-question">Сколько участников и статей можно добавить?</summary>
            <p class="faq-answer">Без ограничений. Вы можете приглашать любое количество участников и создавать сколько угодно статей и разделов.</p>
          </details>
          <details class="faq-item">
            <summary class="faq-question">Можно ли экспортировать данные?</summary>
            <p class="faq-answer">Да. Вы в любой момент можете экспортировать контент в удобном формате и использовать его вне платформы.</p>
          </details>
          <details class="faq-item">
            <summary class="faq-question">Подходит ли MindBase для личного использования?</summary>
            <p class="faq-answer">Да. Можно вести личную базу знаний, а позже пригласить коллег и превратить её в командную.</p>
          </details>
        </div>
      </div>
    </section>

    <section id="innim" class="section innim-block">
      <div class="container">
        <p class="section-badge">Партнёр</p>
        <h2 class="section-title">Разработано в ООО «Инним»</h2>
        <div class="innim-content">
          <div class="innim-logo-wrap">
            <img src="innim-logo.png" alt="ООО Инним" class="innim-logo-img">
            <span class="innim-logo">ИННИМ</span>
          </div>
          <p class="innim-desc">Платформа MindBase создана на базе ООО «Инним» — мы делаем инструменты для команд и управления знаниями.</p>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="cta-bg"></div>
      <div class="container cta-inner">
        <h2>Готовы собрать базу знаний?</h2>
        <p>Платформа полностью бесплатна. Присоединяйтесь к командам, которые уже используют MindBase.</p>
        <div class="cta-actions">
          <?php if ($user !== null): ?>
          <a href="cabinet.php" class="btn btn-primary btn-lg">Перейти в кабинет</a>
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
        <a href="index.php" class="logo">
          <img src="logo.png" alt="MindBase" class="logo-img">
          MindBase
        </a>
        <p>Платформа базы знаний для команд. Сделано на базе ООО Инним.</p>
        <a href="#innim" class="innim-logo-footer">
          <img src="innim-logo.png" alt="ИННИМ" class="innim-logo-footer-img">
          <span>ИННИМ</span>
        </a>
      </div>
      <div class="footer-links">
        <div>
          <h4>Продукт</h4>
          <a href="index.php#features">Возможности</a>
          <a href="#">Интеграции</a>
          <a href="#">Документация</a>
        </div>
        <div>
          <h4>Компания</h4>
          <a href="#">О нас</a>
          <a href="#">Блог</a>
          <a href="#">Карьера</a>
          <a href="#">Контакты</a>
        </div>
        <div>
          <h4>Правовое</h4>
          <a href="#">Политика конфиденциальности</a>
          <a href="#">Условия использования</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2026 MindBase. Сделано на базе ООО Инним.</p>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
