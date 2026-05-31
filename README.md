# MindBase — платформа базы знаний

Веб-приложение на PHP + MySQL: регистрация, каталог статей, поиск, обучение с прогрессом, документы, экспорт.

## Быстрый старт (Docker)

```bash
docker compose up -d --build
docker compose exec php php database/seed.php
```

| Среда | Адрес |
|-------|--------|
| Локально | http://localhost |
| Production | https://mindbase-innim.ru |

HTTPS на production: **Caddy** автоматически получает и обновляет сертификаты **Let's Encrypt**.

### HTTPS (production)

1. DNS **A-запись**: `mindbase-innim.ru` → IP сервера  
2. Открыты порты **80** и **443** на сервере и в панели хостинга  
3. Запуск: `docker compose up -d --build`  
4. Caddy сам выпустит сертификат (1–2 минуты после первого запроса)

Сертификаты хранятся в Docker-томе `caddy_data`, продление автоматическое.

Публичный URL для SEO:

```yaml
MB_SITE_URL: https://mindbase-innim.ru
```

### phpMyAdmin

**Production (HTTPS):** https://pma.mindbase-innim.ru — нужна A-запись `pma` → IP сервера.

**SSH-туннель** (порт 8080 только на localhost сервера):

```bash
ssh -L 8080:127.0.0.1:8080 user@сервер
```

Затем http://localhost:8080

**Два шага входа:**

1. **HTTP-пароль**: логин `pma`, пароль `MindBasePma!`
2. **MySQL**: `mindbase` / `mindbase` или `root` / `root`

Сменить HTTP-пароль: `htpasswd -B docker/phpmyadmin/.htpasswd pma`, затем `docker compose up -d phpmyadmin`.

### SEO

Публичные SEO-файлы:

- `/robots.txt` — правила индексации (закрывает личный кабинет и админку)
- `/sitemap.xml` — карта публичных страниц (главная, регистрация)

На главной: meta description, Open Graph, Twitter Cards, JSON-LD (Organization, WebSite, SoftwareApplication, FAQPage).

**Учётки:**

| Email | Пароль | Роль |
|-------|--------|------|
| admin@mindbase.local | admin12345 | Администратор — полный доступ |
| editor@mindbase.local | editor12345 | Редактор — статьи, разделы, загрузка файлов |
| demo@mindbase.local | demo12345 | Пользователь — только чтение (группа «Разработка») |

## Локально без Docker

1. MySQL 8, база `mindbase`, пользователь с правами на неё.
2. Переменные окружения или правка `db.php`: `MYSQL_HOST`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`.
3. `php database/seed.php`
4. Встроенный сервер PHP (порт 80 на macOS/Linux обычно требует `sudo`): `sudo php -S 0.0.0.0:80` из корня проекта.

## Возможности

- Каталог знаний с разделами и статьями (Markdown)
- Поиск по заголовку и тексту
- Создание и редактирование статей (`article-edit.php`)
- Обучающие курсы с сохранением прогресса
- Реестр документов (метаданные)
- Экспорт всех статей в Markdown / HTML
- Профиль и название рабочего пространства
- Роли: администратор / редактор / пользователь
- Группы доступа к разделам и документам
- Скачивание и загрузка документов
- Создание и удаление разделов
- Поиск по статьям с фильтром по разделу

## Структура

- `database/tables.sql` — схема БД
- `database/seed.php` — демо-данные
- `docker/caddy/Caddyfile` — HTTPS и reverse proxy
- `lib/knowledge.php` — работа с контентом
- `lib/auth.php` — пользователи и сессии
