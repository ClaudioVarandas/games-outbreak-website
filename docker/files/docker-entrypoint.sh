#!/usr/bin/env bash

set -e

composer install
composer dump-autoload

php artisan migrate

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

if [ "$env" != "local" ]; then
    echo "Caching configuration..."
    (cd /var/www/html && php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache)
fi

if [ "$role" = "app" ]; then

    exec "$@"

elif [ "$role" = "scheduler" ]; then

    echo "* * * * * www-data /usr/local/bin/php /var/www/html/artisan schedule:run >> /dev/null 2>&1"  >> /etc/cron.d/laravel-scheduler
    chmod 0644 /etc/cron.d/laravel-scheduler

    while [ true ]
    do
      php /var/www/html/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
