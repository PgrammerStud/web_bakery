$content = @'
#!/bin/bash
set -e

echo "Clearing and warming up cache..."
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

echo "Starting PHP-FPM..."
php-fpm -F &
PHP_PID=$!

echo "Waiting for PHP-FPM to start..."
sleep 2

echo "Starting Nginx..."
nginx -g "daemon off;"

wait $PHP_PID
'@

$content = $content -replace "`r`n", "`n"
[System.IO.File]::WriteAllText("$PWD\entrypoint.sh", $content)