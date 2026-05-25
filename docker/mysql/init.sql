-- docker/mysql/init.sql
-- Créé automatiquement au premier démarrage du conteneur MySQL

CREATE DATABASE IF NOT EXISTS camwater_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON camwater_test.* TO 'camwater'@'%';
FLUSH PRIVILEGES;