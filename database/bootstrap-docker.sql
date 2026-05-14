-- Выполняется из mysql-bootstrap через сокет (root@localhost).
-- Читать имена пароля синхронно с MYSQL_USER/MYSQL_PASSWORD в docker-compose.yml.

CREATE DATABASE IF NOT EXISTS mindbase
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'mindbase'@'%' IDENTIFIED BY 'mindbase';
CREATE USER IF NOT EXISTS 'mindbase'@'localhost' IDENTIFIED BY 'mindbase';
GRANT ALL PRIVILEGES ON mindbase.* TO 'mindbase'@'%';
GRANT ALL PRIVILEGES ON mindbase.* TO 'mindbase'@'localhost';
FLUSH PRIVILEGES;
