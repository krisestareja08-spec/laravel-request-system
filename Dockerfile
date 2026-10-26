# Stage 1: install PHP dependencies from composer.lock
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --ignore-platform-reqs

# Stage 2: build the frontend assets from package-lock.json
FROM node:22-alpine3.22 AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# Stage 3: Laravel running on PHP-FPM
FROM php:8.2-fpm-alpine3.22 AS app
WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql

COPY --from=vendor /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && rm /usr/bin/composer

# the code stays owned by root, only storage and bootstrap/cache are writable
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# Stage 4: Nginx that only serves the public folder
FROM nginxinc/nginx-unprivileged:1.28-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
EXPOSE 8080
