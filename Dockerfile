FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpq-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip mbstring curl xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080
CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8080}
