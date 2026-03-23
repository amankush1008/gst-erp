FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    php8.1 php8.1-cli php8.1-common \
    php8.1-pdo php8.1-sqlite3 \
    php8.1-mbstring php8.1-xml php8.1-zip \
    php8.1-gd php8.1-bcmath php8.1-curl \
    curl unzip git \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN touch database/database.sqlite

RUN php8.1 artisan key:generate --no-ansi || true
RUN php8.1 artisan migrate --force --seed --no-ansi || true

EXPOSE 8080

CMD ["php8.1", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```

Commit karo ✅

---

## Render pe Environment Variables add karo

Render → gst-erp → **Environment** tab → **Add from .env** ya ek ek karo:
```
APP_NAME=GST ERP
APP_ENV=production
APP_KEY=base64:kLrNEjCyMqRtPwXzVbUhGdFsAoJeIiTu=
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
SESSION_DRIVER=file
CACHE_DRIVER=file
LOG_CHANNEL=stderr
```

**Save Changes** dabao ✅

---

## GitHub pe `.env` file bhi banao

GitHub → **Add file** → **Create new file** → naam: `.env`

Yeh content paste karo:
```
APP_NAME="GST ERP"
APP_ENV=production
APP_KEY=base64:kLrNEjCyMqRtPwXzVbUhGdFsAoJeIiTu=
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
SESSION_DRIVER=file
CACHE_DRIVER=file
LOG_CHANNEL=stderr
