#!/bin/sh
# Container entrypoint on Render. The free plan has no pre-deploy command, so
# migrations and the admin seed run here; both are safe to repeat on every boot.
set -e

sed -i "s/__PORT__/${PORT:-10000}/" /etc/nginx/http.d/default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force

# artisan ran as root; hand anything it wrote back to php-fpm's user.
chown -R www-data:www-data storage bootstrap/cache

exec supervisord -c /etc/supervisord.conf
