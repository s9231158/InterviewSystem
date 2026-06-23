#!/usr/bin/env bash
# Ensure log and framework directories exist
mkdir -p /app/storage/{logs,app/public,framework/{cache/data,sessions,testing,views}}

# Ensure web server has write permissions to storage and cache
echo "Setting folder permissions..."
chown -R application:application /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..." > /app/storage/logs/deploy.log
php artisan migrate --force >> /app/storage/logs/deploy.log 2>&1

echo "Seeding database..." >> /app/storage/logs/deploy.log
php artisan db:seed --force >> /app/storage/logs/deploy.log 2>&1
