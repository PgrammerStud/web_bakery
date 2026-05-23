#!/bin/bash
set -e

echo "Running Symfony cache clear & warmup..."
php /app/bin/console cache:clear --env=prod --no-debug
php /app/bin/console cache:warmup --env=prod --no-debug

echo "Compiling assets..."
php /app/bin/console importmap:install --no-interaction
php /app/bin/console asset-map:compile --no-interaction

echo "Fixing permissions..."
chown -R www-data:www-data /app/var
chmod -R 775 /app/var

echo "Running database migrations..."
php /app/bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "Starting PHP-FPM..."
php-fpm -F &
PHP_PID=$!

sleep 2

echo "Starting Nginx..."
nginx -g "daemon off;"

wait $PHP_PID