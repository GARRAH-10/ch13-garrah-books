FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.2-cli
RUN docker-php-ext-install pdo_mysql

WORKDIR /app
COPY . .
COPY --from=vendor /app/vendor ./vendor

EXPOSE 80
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80} -t public public/index.php"]
