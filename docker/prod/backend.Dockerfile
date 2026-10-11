# Production image for Render: Laravel code baked in, nginx + php-fpm in one
# container under supervisord (Render runs one container per service).
# Build context is the repo root: docker build -f docker/prod/backend.Dockerfile .
FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_pgsql \
        bcmath \
        intl \
        zip \
        gd \
        redis \
        opcache \
        pcntl \
    && apk add --no-cache nginx supervisor

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Dependencies first so the layer is cached until composer.lock changes.
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY backend/ ./
RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/prod/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/prod/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/prod/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/prod/start.sh /usr/local/bin/start

# Render injects PORT (10000 by default); start.sh writes it into nginx.conf.
EXPOSE 10000
CMD ["start"]
