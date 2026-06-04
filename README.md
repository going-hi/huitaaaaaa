# MindBase — платформа базы знаний

Веб-приложение на PHP + MySQL: регистрация, каталог статей, поиск, обучение с прогрессом, документы, экспорт.

## Быстрый старт (Docker)

```bash
cp .env.example .env   # задайте MYSQL_ROOT_PASSWORD и MYSQL_PASSWORD
docker compose up -d --build
docker compose exec php php database/seed.php
```

Откройте http://localhost:8081 (локально, Docker) или https://mindbase-innim.ru (production за nginx).

phpMyAdmin: https://mindbase-innim.ru/phpmyadmin/ (или SSH-туннель на `127.0.0.1:8080`).

### HTTPS (nginx + Let's Encrypt на сервере)

Docker **не** занимает порт 80/443 — только `127.0.0.1:8081`. Nginx на хосте принимает HTTPS.

1. `git pull` и `docker compose up -d --build`
2. Установить nginx + certbot: `sudo apt install -y nginx certbot python3-certbot-nginx`
3. Скопировать конфиг:
   ```bash
   sudo cp docker/nginx/mindbase-innim.ru.conf /etc/nginx/sites-available/mindbase-innim.ru
   sudo ln -sf /etc/nginx/sites-available/mindbase-innim.ru /etc/nginx/sites-enabled/
   sudo rm -f /etc/nginx/sites-enabled/default
   sudo nginx -t && sudo systemctl reload nginx
   ```
4. Проверка: `curl -I http://mindbase-innim.ru` → ответ от nginx
5. Сертификат:
   ```bash
   sudo certbot --nginx -d mindbase-innim.ru --register-unsafely-without-email --agree-tos
   ```
6. В `docker-compose.yml`: `MB_SITE_URL: https://mindbase-innim.ru`, затем `docker compose up -d php`

Если certbot пишет `bind() to 0.0.0.0:80 failed` — порт 80 занят Docker. Выполните `sudo ss -tlnp | grep ':80 '` и убедитесь, что после `git pull` php слушает **8081**, не 80.

### phpMyAdmin

**URL:** https://mindbase-innim.ru/phpmyadmin/

**MySQL:** логин/пароль из `.env` (`MYSQL_USER` / `MYSQL_PASSWORD` или `root` / `MYSQL_ROOT_PASSWORD`)

**SSH-туннель** (локальный доступ без nginx):

```bash
ssh -L 8080:127.0.0.1:8080 user@сервер
```

После этого открой http://localhost:8080

### SEO

Публичный URL в `docker-compose.yml`:

```yaml
MB_SITE_URL: http://mindbase-innim.ru
```

После включения HTTPS на сервере смените на `https://...`.

Публичные SEO-файлы:

- `/robots.txt` — правила индексации (закрывает личный кабинет и админку)
- `/sitemap.xml` — карта публичных страниц (главная, регистрация)

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
- `docker/nginx/mindbase-innim.ru.conf` — пример nginx для HTTPS
- `lib/knowledge.php` — работа с контентом
- `lib/auth.php` — пользователи и сессии
