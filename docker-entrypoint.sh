#!/bin/bash

if [ ! -d "/var/www/vendor" ]; then
  composer install --optimize-autoloader
fi

exec "$@"
