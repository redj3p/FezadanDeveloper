#!/bin/bash
set -e

# Composer bağımlılıklarını kur
cd /var/www/html && composer install --no-interaction 2>/dev/null || true

exec "$@"
