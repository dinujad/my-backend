#!/bin/sh
set -e

cd /var/www/html

# Ensure public/storage → storage/app/public (safe on every container start).
php artisan storage:link --force >/dev/null 2>&1 || true

exec apache2-foreground
