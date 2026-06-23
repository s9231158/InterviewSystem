#!/usr/bin/env bash
# Restore files from backup to bypass VOLUME shadowing
echo "Restoring application files from backup..."
cp -R /var/www/html_backup/. /var/www/html/

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force
