# MindBase — платформа базы знаний

Веб-приложение на PHP + MySQL: регистрация, каталог статей, поиск, обучение с прогрессом, документы, экспорт.

## Быстрый старт (Docker)

```bash
docker compose up -d --build
docker compose exec php php database/seed.php
```

Откройте http://localhost (локально) или http://mindbase-innim.ru (production).

phpMyAdmin: http://localhost:8080 (на сервере — порт 8080 или SSH-туннель, см. ниже).

**Два шага входа:**

1. **HTTP-пароль** (защита самого phpMyAdmin): логин `pma`, пароль `MindBasePma!`
2. **MySQL** (форма phpMyAdmin): `mindbase` / `mindbase` или `root` / `root`

Сменить HTTP-пароль: `htpasswd -B docker/phpmyadmin/.htpasswd pma`, затем `docker compose up -d phpmyadmin`.

**SSH-туннель** (без открытия 8080 наружу):

```bash
ssh -L 8080:127.0.0.1:8080 user@сервер
```

После этого открой http://localhost:8080

### SEO и продвижение

Публичный URL сайта задан в `docker-compose.yml`:

```yaml
MB_SITE_URL: http://mindbase-innim.ru
```

Используется для canonical, Open Graph и sitemap. Локально можно временно переопределить через `environment` сервиса `php`.

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
- `lib/knowledge.php` — работа с контентом
- `lib/auth.php` — пользователи и сессии
