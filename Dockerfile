FROM richarvey/nginx-php-fpm:3.1.6

# Set working directory back to standard path
WORKDIR /var/www/html

COPY . .

# Install dependencies during build time
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create a backup copy of the fully built application to bypass VOLUME shadowing
RUN cp -R /var/www/html /var/www/html_backup

# Fix permissions in the backup directory
RUN chown -R nginx:nginx /var/www/html_backup/storage /var/www/html_backup/bootstrap/cache \
    && chmod -R 775 /var/www/html_backup/storage /var/www/html_backup/bootstrap/cache

# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
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
