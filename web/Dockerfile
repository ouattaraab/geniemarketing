# =============================================================================
# GÉNIE MARKETING Mag — Image production Laravel (PHP-FPM 8.2 + Vite build)
# =============================================================================
# Build : docker build -t ghcr.io/geniemag/web:latest -f Dockerfile .
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1 : dépendances Composer (sans dev)
# -----------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# -----------------------------------------------------------------------------
# Stage 2 : build front (Vite)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3 : runtime PHP-FPM
# -----------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS runtime

RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libzip-dev \
        oniguruma-dev \
        mysql-client \
        supervisor \
        tini \
    && docker-php-ext-install \
        bcmath \
        exif \
        intl \
        opcache \
        pdo_mysql \
        pcntl \
        gd \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Config PHP production
COPY infra/docker/prod/php.ini /usr/local/etc/php/conf.d/gm.ini
COPY infra/docker/prod/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY infra/docker/prod/php-fpm.conf /usr/local/etc/php-fpm.d/zz-gm.conf

# Supervisor pilote php-fpm + queue worker
COPY infra/docker/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Code app
COPY . /var/www/html
# Dépendances + assets
COPY --from=vendor  /app/vendor         /var/www/html/vendor
COPY --from=assets  /app/public/build   /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["/sbin/tini", "--"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf", "-n"]
