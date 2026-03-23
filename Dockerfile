FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    php8.1 php8.1-cli php8.1-common \
    php8.1-pdo php8.1-mysql php8.1-sqlite3 \
    php8.1-mbstring php8.1-xml php8.1-zip \
    php8.1-gd php8.1-bcmath php8.1-curl \
    curl unzip git \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN touch database/database.sqlite

RUN cp .env.example .env \
    && php8.1 artisan key:generate \
    && php8.1 artisan migrate --force --seed

EXPOSE 8080

CMD ["php8.1", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
