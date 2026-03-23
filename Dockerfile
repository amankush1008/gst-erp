FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    curl zip unzip git libzip-dev libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd bcmath xml

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN cp .env.example .env && php artisan key:generate

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```

Commit karo → Render pe wapas jaao:

- **Runtime: Docker** rehne do ✅ (ab Dockerfile hai)
- **Branch: main** ✅
- **Region: Oregon** ✅
- Neeche scroll karo → **Environment Variables** add karo → **"Create Web Service"** dabao

---

## Environment Variables

Neeche scroll karo → **"Advanced"** → **"Add Environment Variable"**:
```
APP_NAME=GST ERP
APP_ENV=production
APP_DEBUG=true
APP_KEY=base64:kLrNEjCyMqRtPwXzVbUhGdFsAoJeIiTu=
SESSION_DRIVER=file
CACHE_DRIVER=file
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
