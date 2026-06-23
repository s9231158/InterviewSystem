#!/usr/bin/env bash
# Restore files from backup to bypass VOLUME shadowing
echo "Restoring application files from backup..."
cp -R /var/www/html_backup/. /var/www/html/

# Ensure web server has write permissions to storage and cache
echo "Setting folder permissions..."
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force
