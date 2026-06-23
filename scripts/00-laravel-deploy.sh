#!/usr/bin/env bash
# Ensure web server has write permissions to storage and cache
echo "Setting folder permissions..."
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..." > /var/www/html/storage/logs/deploy.log
php artisan migrate --force >> /var/www/html/storage/logs/deploy.log 2>&1

echo "Seeding database..." >> /var/www/html/storage/logs/deploy.log
php artisan db:seed --force >> /var/www/html/storage/logs/deploy.log 2>&1
