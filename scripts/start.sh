#!/usr/bin/env bash
# Run Laravel deployment scripts (migrate, seed, cache optimization)
/bin/bash /app/scripts/00-laravel-deploy.sh

# Hand over execution to the original entrypoint of webdevops/php-nginx
exec /entrypoint supervisord
