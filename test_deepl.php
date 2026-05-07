<?php
define('ROOT', __DIR__);

$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value, "\"'");
        }
    }
}

require_once ROOT . '/app/Core/ArtProvider.php';

class DeepLTester extends ArtProvider {
    public function fetchArtwork() { return null; }
    public function testTranslate($text) {
        $url = 'https://api-free.deepl.com/v2/translate';
        $data = http_build_query([
            'text' => $text,
            'source_lang' => 'EN',
            'target_lang' => 'TR'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: DeepL-Auth-Key ' . $this->deepLKey,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . "\n";
        } else {
            echo "HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
            echo "Response: " . $response . "\n";
        }
        curl_close($ch);

        return $this->getDeepLTranslation($text);
    }
}

$tester = new DeepLTester();

$sampleEng = "The Starry Night is an oil-on-canvas painting by the Dutch Post-Impressionist painter Vincent van Gogh. Painted in June 1889, it depicts the view from the east-facing window of his asylum room at Saint-Rémy-de-Provence, just before sunrise, with the addition of an imaginary village. It has been in the permanent collection of the Museum of Modern Art in New York City since 1941, acquired through the Lillie P. Bliss Bequest. Widely regarded as Van Gogh's magnum opus, The Starry Night is one of the most recognized paintings in Western art.";

echo "=== DEEPL ÇEVİRİ TESTİ ===\n\n";
echo "İNGİLİZCE ORİJİNAL METİN:\n";
echo $sampleEng . "\n\n";

echo "DEEPL TÜRKÇE ÇEVİRİSİ:\n";
$translated = $tester->testTranslate($sampleEng);
if ($translated) {
    echo $translated . "\n";
} else {
    echo "ÇEVİRİ BAŞARISIZ! Lütfen API anahtarınızı kontrol edin.\n";
}
