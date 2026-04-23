FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    curl \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mysqli mbstring zip exif intl pcntl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Point Apache + PHP image config at Laravel's web root (both files ship paths to /var/www/html).
RUN set -eux; \
    sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/conf-available/docker-php.conf; \
    apache2ctl configtest

RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create the public/storage symlink so uploaded files (products, gallery, etc.)
# are accessible via /storage/... URLs (storage/app/public → public/storage).
RUN php artisan storage:link --force

EXPOSE 80

# Coolify probes the first exposed port; give Laravel time to boot on cold start.
HEALTHCHECK --interval=15s --timeout=5s --start-period=60s --retries=5 \
    CMD curl -fsS "http://127.0.0.1/up" >/dev/null || exit 1

CMD ["apache2-foreground"]
