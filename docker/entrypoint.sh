#!/bin/sh
# docker/entrypoint.sh
# Attend que MySQL soit prêt, applique les migrations, puis démarre Laravel.
set -e

echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."

until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
  echo "[entrypoint] MySQL not ready yet, retrying in 2s..."
  sleep 2
done

echo "[entrypoint] MySQL is up. Running migrations..."
php artisan migrate --force

echo "[entrypoint] Caching config/routes..."
php artisan config:cache
php artisan route:cache

echo "[entrypoint] Starting Laravel..."
exec "$@"
