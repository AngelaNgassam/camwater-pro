# ============================================================
# Dockerfile — CAMWATER PRO (Laravel)
#
# Image multi-étapes :
#   1. builder     → installe les dépendances PHP + compile les extensions
#   2. production  → image finale légère pour le déploiement
#
# CHANGEMENTS :
#   - nodejs/npm retirés (frontend déjà buildé par le CI/CD)
#   - apk update remplacé par --no-cache (évite les erreurs réseau)
#   - Stage production : extensions PHP copiées depuis builder
#     au lieu d'être recompilées (plus rapide, plus fiable)
#   - Headers -dev retirés du stage production (inutiles au runtime)
# ============================================================

# ── Étape 1 : builder ────────────────────────────────────────
FROM php:8.3-fpm-alpine AS builder

# Ajout de $PHPIZE_DEPS (nécessaire pour compiler des extensions PECL comme redis)
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    bash \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    unzip

# Extensions PHP classiques + Installation de Redis via PECL
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier uniquement les fichiers de dépendances (optimisation cache Docker)
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader

# Copier le reste du code
COPY . .

# Autoloader optimisé
RUN composer dump-autoload --optimize --no-dev

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Étape 2 : production ─────────────────────────────────────
FROM php:8.3-fpm-alpine AS production

# Uniquement les bibliothèques RUNTIME (pas les -dev)
# Les extensions PHP sont copiées depuis le builder, pas recompilées
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libwebp \
    libzip \
    oniguruma \
    nginx \
    supervisor


RUN mkdir -p /var/log/supervisor

# Copier les extensions PHP compilées dans le builder
# Évite de recompiler et supprime le besoin des headers -dev
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

WORKDIR /var/www/html

# Copier l'application depuis le builder
COPY --from=builder --chown=www-data:www-data /var/www/html .

# Configuration Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor (gère Nginx + PHP-FPM)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/camwater.ini

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]