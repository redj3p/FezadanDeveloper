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
