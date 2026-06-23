FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && chown -R www-data:www-data /var/www/html
EXPOSE 80
