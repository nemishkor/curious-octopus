#!/bin/sh
set -ex

chown -R www-data:www-data .

if [ -f "composer.lock" ]; then
    if [ "${APP_ENV}" = "prod" ]; then
        sudo -u www-data composer dump-env prod
    fi
    sudo -u www-data composer install --optimize-autoloader -v
    if [ -f "bin/console" ]; then
        sudo -u www-data bin/console c:c
        sudo -u www-data bin/console d:m:m -n
    fi
fi

supervisord -c /etc/supervisor/supervisor.conf
