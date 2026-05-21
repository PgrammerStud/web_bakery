cat > entrypoint.sh << 'EOF'
#!/bin/bash
set -e

echo "Clearing and warming up cache..."
php bin/console cache:clear --env=prod --no-debug
php bin/console doctrine:cache:clear-metadata --flush || true
php bin/console cache:warmup --env=prod --no-debug

echo "Starting PHP-FPM..."
php-fpm -F &
PHP_PID=$!

echo "Waiting for PHP-FPM to start..."
sleep 2

echo "Starting Nginx..."
nginx -g "daemon off;"

wait $PHP_PID
EOF