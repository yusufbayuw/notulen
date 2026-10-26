FROM node:22-alpine AS frontend
WORKDIR /app
COPY apps/web/package.json ./
RUN npm install --ignore-scripts
COPY apps/web/vite.config.ts ./
COPY apps/web/resources ./resources
COPY apps/web/public ./public
RUN npm run build

FROM php:8.4-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libonig-dev libicu-dev && \
    docker-php-ext-install pdo_mysql pdo_sqlite pcntl zip mbstring bcmath && \
    rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY apps/web/composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --optimize-autoloader
COPY apps/web/ ./
COPY --from=frontend /app/public/build ./public/build
RUN mkdir -p storage/app/private storage/framework/cache storage/framework/views \
    storage/framework/sessions storage/logs bootstrap/cache && \
    chmod -R a+rwX storage bootstrap/cache && \
    php artisan package:discover --ansi
EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
