FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    curl zip unzip git libzip-dev libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN touch database/database.sqlite

RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan migrate --force --seed

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
