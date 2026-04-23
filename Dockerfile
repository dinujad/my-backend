FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev curl \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring zip exif intl

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN composer install --no-dev --optimize-autoloader --no-interaction

# 🔥 CRITICAL FIX
RUN echo '<Directory /var/www/html/public>' >> /etc/apache2/apache2.conf \
    && echo 'AllowOverride All' >> /etc/apache2/apache2.conf \
    && echo 'Require all granted' >> /etc/apache2/apache2.conf \
    && echo '</Directory>' >> /etc/apache2/apache2.conf

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80