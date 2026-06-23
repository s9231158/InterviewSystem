FROM webdevops/php-nginx:8.4

WORKDIR /app

COPY . .

# Remove local bootstrap caches to allow auto-discovery in production environment
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Install dependencies during build time
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Fix permissions for Laravel storage and cache directories
RUN mkdir -p storage/{logs,app/public,framework/{cache/data,sessions,testing,views}} \
    && chown -R application:application storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Make the start scripts executable
RUN chmod +x scripts/start.sh scripts/00-laravel-deploy.sh

# Webdevops Image config
ENV WEB_DOCUMENT_ROOT /app/public
ENV WEB_DOCUMENT_INDEX index.php

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV TELESCOPE_ENABLED false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/app/scripts/start.sh"]