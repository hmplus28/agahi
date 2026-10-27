# syntax=docker/dockerfile:1

# ─────────────────────────────────────────────────────────────
# 1) assets — build Vite assets from source
# ─────────────────────────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ─────────────────────────────────────────────────────────────
# 2) vendor — production composer dependencies
# ─────────────────────────────────────────────────────────────
FROM php:8.3-cli-alpine AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd intl pdo_pgsql zip pcntl

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ─────────────────────────────────────────────────────────────
# 3) app — php-fpm runtime
# ─────────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd intl pdo_pgsql zip opcache pcntl

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-agahi.ini

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache public \
    && chmod +x docker/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php-fpm"]

# ─────────────────────────────────────────────────────────────
# 4) web — nginx serving static files + fastcgi proxy
# ─────────────────────────────────────────────────────────────
FROM nginx:1.27-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

# public/storage must resolve inside the web image as well;
# the shared "app_public" volume provides the real files.
RUN mkdir -p /var/www/html/storage/app/public \
    && ln -s ../storage/app/public /var/www/html/public/storage

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
