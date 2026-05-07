<?php
define('ROOT', __DIR__);

// Load .env variables manually for CLI testing
$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, "\"'");
            $_ENV[$name] = $value;
        }
    }
}

require_once ROOT . '/app/Core/ArtProvider.php';
require_once ROOT . '/app/Core/ArtProviderCleveland.php';
require_once ROOT . '/app/Core/ArtProviderChicago.php';
require_once ROOT . '/app/Core/ArtProviderMet.php';

echo "========================================\n";
echo "1. CLEVELAND API TEST\n";
echo "========================================\n";
$cleveland = new ArtProviderCleveland();
$art1 = $cleveland->fetchArtwork();
if ($art1) {
    echo "[OK] Başlık: " . $art1['title'] . "\n";
    echo "[OK] Sanatçı: " . $art1['artist'] . "\n";
} else {
    echo "[HATA] Cleveland'dan veri çekilemedi.\n";
}

echo "\n========================================\n";
echo "2. CHICAGO API TEST\n";
echo "========================================\n";
$chicago = new ArtProviderChicago();
$art2 = $chicago->fetchArtwork();
if ($art2) {
    echo "[OK] Başlık: " . $art2['title'] . "\n";
    echo "[OK] Sanatçı: " . $art2['artist'] . "\n";
} else {
    echo "[HATA] Chicago'dan veri çekilemedi.\n";
}

echo "\n========================================\n";
echo "3. MET API TEST\n";
echo "========================================\n";
$met = new ArtProviderMet();
$art3 = $met->fetchArtwork();
if ($art3) {
    echo "[OK] Başlık: " . $art3['title'] . "\n";
    echo "[OK] Sanatçı: " . $art3['artist'] . "\n";
} else {
    echo "[HATA] MET'ten veri çekilemedi.\n";
}

echo "\n========================================\n";
echo "4. TAM AKIŞ (Wikipedia + DeepL + API Seçimi)\n";
echo "========================================\n";
echo "Lütfen bekleyin, API'ye gidiliyor...\n";
$full = ArtProvider::getRandomArtwork();

if ($full) {
    echo "\n[BAŞARILI] Eser bulundu ve işlendi!\n";
    echo "----------------------------------------\n";
    echo "Seçilen Müze : " . $full['provider'] . "\n";
    echo "Eser         : " . $full['title'] . "\n";
    echo "Sanatçı      : " . $full['artist'] . "\n";
    echo "Metin Kaynağı: " . $full['description_source'] . "\n";
    if ($full['wikipedia_url']) {
        echo "Wiki Linki   : " . $full['wikipedia_url'] . "\n";
    }
    echo "Çeviri (TR)  : " . mb_substr($full['description_tr'], 0, 150) . "...\n";
} else {
    echo "\n[HATA] Tam akış testi başarısız oldu.\n";
}
echo "========================================\n";
