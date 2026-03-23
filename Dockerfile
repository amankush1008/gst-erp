FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libxml2-dev \
    curl unzip git zip \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite \
       mbstring zip gd bcmath xml \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

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
ENV DB_DATABASE=/var/www/html/database/database.sqlite
ENV SESSION_DRIVER=file
ENV CACHE_DRIVER=file
ENV LOG_CHANNEL=stderr

RUN php artisan key:generate --force \
    && php artisan migrate --force --seed

EXPOSE 80

CMD ["apache2-foreground"]
