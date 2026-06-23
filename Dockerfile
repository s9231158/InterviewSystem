FROM richarvey/nginx-php-fpm:3.1.6

# Set working directory to a non-volume directory
WORKDIR /var/www/app

COPY . .

# Install dependencies during build time
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Fix permissions for Laravel storage and cache directories
RUN chown -R nginx:nginx /var/www/app/storage /var/www/app/bootstrap/cache \
    && chmod -R 775 /var/www/app/storage /var/www/app/bootstrap/cache

# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/app/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel config
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

CMD ["/start.sh"]
