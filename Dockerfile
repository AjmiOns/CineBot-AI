# Dockerfile — Backend Laravel (CineBot AI)
#
# Image simple basée sur php-cli + `artisan serve`, adaptée à une démo/
# soutenance. Pour une mise en production réelle, remplacer par une pile
# php-fpm + nginx (voir commentaire en bas de fichier).

FROM php:8.2-cli

# ── Dépendances système + extensions PHP ────────────────────────────────
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libonig-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# ── Composer ─────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie séparée de composer.json/lock d'abord : le cache Docker évite de
# refaire `composer install` à chaque changement de code applicatif.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ─────────────────────────────────────────────────────────────────────────
# Pour la production : remplacer FROM par `php:8.2-fpm`, ajouter une image
# nginx séparée pointant vers `public/`, et retirer `artisan serve` (non
# prévu pour de la charge en production — un seul processus, pas de
# reverse proxy, pas de gestion fine des workers).
# ─────────────────────────────────────────────────────────────────────────
