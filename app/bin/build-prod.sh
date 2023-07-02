#!/bin/bash
SCRIPTPATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
cd "${SCRIPTPATH}"/.. || exit

composer install --no-interaction --no-dev --optimize-autoloader -v
php bin/console d:m:m -n
