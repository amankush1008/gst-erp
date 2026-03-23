#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════════
# GST ERP – Full Installation & Deployment Script
# Usage: bash deploy.sh [fresh|update|production]
# ═══════════════════════════════════════════════════════════════════════════════

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}✓ $1${NC}"; }
warn()  { echo -e "${YELLOW}⚠ $1${NC}"; }
error() { echo -e "${RED}✗ $1${NC}"; exit 1; }
step()  { echo -e "\n${YELLOW}──── $1 ────${NC}"; }

MODE=${1:-fresh}
PHP=${PHP_BIN:-php}
COMPOSER=${COMPOSER_BIN:-composer}

echo ""
echo "  ⚡ GST ERP Deployment"
echo "  Mode: ${MODE}"
echo ""

# ── Check PHP version ──────────────────────────────────────────────────────────
step "Checking requirements"
PHP_VER=$($PHP -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if (( $(echo "$PHP_VER < 8.2" | bc -l) )); then
    error "PHP 8.2+ required. Found: $PHP_VER"
fi
log "PHP $PHP_VER"

for ext in pdo_mysql mbstring openssl tokenizer xml ctype json bcmath gd zip; do
    $PHP -m | grep -q "$ext" && log "ext-$ext" || warn "ext-$ext not found (may be needed)"
done

# ── Install dependencies ───────────────────────────────────────────────────────
step "Installing Composer dependencies"
if [ "$MODE" = "production" ]; then
    $COMPOSER install --optimize-autoloader --no-dev --no-interaction
else
    $COMPOSER install --no-interaction
fi
log "Composer packages installed"

# ── Environment setup ─────────────────────────────────────────────────────────
step "Environment configuration"
if [ ! -f ".env" ]; then
    cp .env.example .env
    $PHP artisan key:generate
    log "Generated .env from .env.example"
    warn "Please edit .env and set DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo ""
    echo "  Then re-run: bash deploy.sh update"
    exit 0
else
    log ".env exists"
fi

# ── Database ──────────────────────────────────────────────────────────────────
step "Database"
if [ "$MODE" = "fresh" ]; then
    warn "Running fresh migration (drops existing tables)..."
    $PHP artisan migrate:fresh --force
    $PHP artisan db:seed --force
    log "Database migrated and seeded"
    echo ""
    echo "  Demo login: demo@gsterp.com / password"
elif [ "$MODE" = "update" ]; then
    $PHP artisan migrate --force
    log "Migrations run"
else
    $PHP artisan migrate --force
    log "Migrations run"
fi

# ── Storage ───────────────────────────────────────────────────────────────────
step "Storage & permissions"
$PHP artisan storage:link 2>/dev/null || warn "Storage link already exists"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
log "Storage configured"

# ── Cache ─────────────────────────────────────────────────────────────────────
step "Cache optimization"
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear

if [ "$MODE" = "production" ]; then
    $PHP artisan config:cache
    $PHP artisan route:cache
    $PHP artisan view:cache
    log "Production caches built"
else
    log "Development mode – caches cleared"
fi

# ── Nginx config hint ─────────────────────────────────────────────────────────
if [ "$MODE" = "production" ]; then
    step "Nginx configuration hint"
    cat << 'NGINX'
  server {
      listen 80;
      server_name yourdomain.com;
      root /var/www/gst-erp/public;
      index index.php;

      location / {
          try_files $uri $uri/ /index.php?$query_string;
      }

      location ~ \.php$ {
          fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
          fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
          include fastcgi_params;
      }

      location ~ /\.ht { deny all; }
  }
NGINX
fi

echo ""
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✅  Deployment complete!              ${NC}"
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo ""
echo "  Start dev server:  php artisan serve"
echo "  Queue worker:      php artisan queue:work"
echo "  Schedule:          * * * * * php /path/to/artisan schedule:run"
echo ""
