FROM webdevops/php-apache:8.2

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN touch database/database.sqlite \
    && chmod 777 database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache

ENV APP_NAME="GST ERP"
ENV APP_ENV=production
ENV APP_KEY=base64:kLrNEjCyMqRtPwXzVbUhGdFsAoJeIiTu=
ENV APP_DEBUG=true
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/app/database/database.sqlite
ENV SESSION_DRIVER=file
ENV CACHE_DRIVER=file
ENV LOG_CHANNEL=stderr
ENV WEB_DOCUMENT_ROOT=/app/public

RUN php artisan key:generate --force \
    && php artisan migrate --force --seed

EXPOSE 80

CMD ["/entrypoint", "supervisord"]
