#!/bin/bash
set -e

# config.php'yi lokal ayarlarla değiştir
cat > /var/www/html/app/Config/config.php << 'EOF'
<?php
define('SITE_URL', 'http://localhost:8000');
define('DB_HOST', 'db');
define('DB_NAME', 'fezadano5_site');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');
$appSalt = getenv('APP_SECURITY_SALT') ?: 'local-dev-change-me';
define('APP_SALT', $appSalt);
define('CDN_URL', getenv('CDN_URL') ?: SITE_URL);
EOF

# HTTPS header'larını lokalde devre dışı bırak
sed -i "s/header('Strict-Transport-Security:/\/\/ header('Strict-Transport-Security:/" /var/www/html/public_html/index.php
sed -i 's/header("Content-Security-Policy:/\/\/ header("Content-Security-Policy:/' /var/www/html/public_html/index.php

# Composer bağımlılıklarını kur
cd /var/www/html && composer install --no-interaction 2>/dev/null || true

exec "$@"
