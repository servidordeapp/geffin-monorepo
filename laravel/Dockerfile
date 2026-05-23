# syntax=docker/dockerfile:1.7

# =========================================================================
# Stage 1 — Composer dependencies
# =========================================================================
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# =========================================================================
# Stage 2 — Frontend assets (Vite + Tailwind)
# =========================================================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# =========================================================================
# Stage 3 — Runtime (PHP-FPM, non-root)
# =========================================================================
FROM php:8.4-fpm-alpine AS runtime

ARG APP_USER=app
ARG APP_UID=1000
ARG APP_GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_MEMORY_LIMIT=512M

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-data-full \
        icu-libs \
        libpng \
        libjpeg-turbo \
        libwebp \
        freetype \
        libzip \
        oniguruma \
        tzdata \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Composer binary (for runtime tasks like `composer dump`)
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Non-root user
RUN addgroup -g ${APP_GID} ${APP_USER} \
    && adduser -D -u ${APP_UID} -G ${APP_USER} -s /bin/bash ${APP_USER}

# PHP / FPM tuning
COPY <<'EOF' /usr/local/etc/php/conf.d/zz-app.ini
memory_limit=${PHP_MEMORY_LIMIT}
upload_max_filesize=50M
post_max_size=50M
max_execution_time=60
date.timezone=UTC

opcache.enable=1
opcache.enable_cli=0
opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}
opcache.max_accelerated_files=${PHP_OPCACHE_MAX_ACCELERATED_FILES}
opcache.memory_consumption=${PHP_OPCACHE_MEMORY_CONSUMPTION}
opcache.interned_strings_buffer=16
opcache.jit=tracing
opcache.jit_buffer_size=64M
EOF

COPY <<'EOF' /usr/local/etc/php-fpm.d/zz-app.conf
[www]
user = app
group = app
listen = 0.0.0.0:9000
pm = dynamic
pm.max_children = 25
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500
clear_env = no
catch_workers_output = yes
decorate_workers_output = no
EOF

WORKDIR /var/www/html

# Copy app source, vendor and built assets
COPY --chown=${APP_USER}:${APP_USER} . .
COPY --from=vendor  --chown=${APP_USER}:${APP_USER} /app/vendor ./vendor
COPY --from=assets  --chown=${APP_USER}:${APP_USER} /app/public/build ./public/build

# Storage + cache permissions
RUN mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache \
    && chown -R ${APP_USER}:${APP_USER} storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY --chmod=755 <<'EOF' /usr/local/bin/docker-entrypoint
#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Generate APP_KEY only if both .env and the process env lack one
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --ansi
fi

php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan event:cache   || true

exec "$@"
EOF

USER ${APP_USER}

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php-fpm -t || exit 1

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm", "-F"]
