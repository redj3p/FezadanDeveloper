<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$target = __DIR__ . '/scripts/deploy-logic.php';

if (file_exists($target)) {
    echo "Basarili: Script dosyasi bulundu. Calistiriliyor...\n";
    require_once $target;
} else {
    die("HATA: $target bulunamadı. Lütfen dosya isminin 'deploy-logic.php' olduğundan emin ol.");
}