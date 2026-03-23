FROM serversideup/php:8.2-fpm-apache

USER root

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --ignore-platform-reqs \
    --no-scripts \
    --no-autoloader

RUN composer dump-autoload \
    --optimize \
    --no-scripts \
    --ignore-platform-reqs

RUN touch database/database.sqlite \
    && chmod 777 database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache

ENV APP_NAME="GST ERP"
ENV APP_ENV=production
ENV APP_KEY=base64:kLrNEjCyMqRtPwXzVbUhGdFsAoJeIiTu=
ENV APP_DEBUG=true
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/www/html/database/database.sqlite
ENV SESSION_DRIVER=file
ENV CACHE_DRIVER=file
ENV LOG_CHANNEL=stderr

RUN php artisan key:generate --force
RUN php artisan migrate --force --seed

EXPOSE 80
