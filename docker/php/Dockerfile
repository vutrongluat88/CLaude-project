# Backend runtime: PHP 8.4 (Laravel 13 requires ^8.3)
FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_mysql \
        bcmath \
        intl \
        zip \
        gd \
        redis \
        opcache \
        pcntl

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Run as the host user so files written into the bind mount stay editable.
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S app 2>/dev/null || true; \
    adduser -u ${UID} -G $(getent group ${GID} | cut -d: -f1) -S -D -h /home/app app 2>/dev/null || true; \
    mkdir -p /home/app/.composer && chown -R ${UID}:${GID} /home/app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html
USER ${UID}:${GID}

EXPOSE 9000
CMD ["php-fpm"]
