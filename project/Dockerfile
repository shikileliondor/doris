# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

FROM node:${NODE_VERSION}-bookworm-slim AS node

# -----------------------------------------------------------------------------
# Base: PHP + Apache, shared by the build and runtime stages
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache-bookworm AS base

RUN docker-php-ext-install opcache pcntl \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite headers \
    && sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# Build: Composer dependencies + front-end assets
# (the Wayfinder Vite plugin calls `php artisan`, so Node runs next to PHP)
# -----------------------------------------------------------------------------
FROM base AS build

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN composer dump-autoload --optimize --no-dev

ARG VITE_APP_NAME=Laravel
ENV VITE_APP_NAME=${VITE_APP_NAME}

# Wayfinder inspects the schema while generating types: use a throwaway SQLite DB.
RUN touch database/database.sqlite \
    && php artisan migrate --force --no-interaction \
    && npm run build \
    && rm database/database.sqlite

# -----------------------------------------------------------------------------
# Runtime
# -----------------------------------------------------------------------------
FROM base AS app

COPY . .
COPY --from=build /var/www/html/vendor ./vendor
COPY --from=build /var/www/html/bootstrap/cache ./bootstrap/cache
COPY --from=build /var/www/html/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/app-entrypoint \
    && chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p /data /var/www/.config \
        storage/app/private storage/app/public \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        storage/logs \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data /data /var/www/.config storage bootstrap/cache

ENV DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    LOG_CHANNEL=stderr

USER www-data

EXPOSE 8080

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
