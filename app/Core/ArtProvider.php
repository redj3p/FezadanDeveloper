<?php

abstract class ArtProvider {
    protected $deepLKey;
    protected $geminiKey;

    public function __construct() {
        $this->deepLKey = $_ENV['DEEPL_API_KEY'] ?? getenv('DEEPL_API_KEY');
        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
    }

    abstract public function fetchArtwork();

    protected function sanitizeId($id) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
    }

    protected function getDeepLTranslation($text) {
        if (empty($this->deepLKey) || empty(trim($text))) {
            return null;
        }

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
        curl_close($ch);

        if ($response) {
            $json = json_decode($response, true);
            if (isset($json['translations'][0]['text'])) {
                return $json['translations'][0]['text'];
            }
        }
        return null;
    }

    /**
     * Gemini ile müze kalitesinde Türkçe açıklama üret.
     * Rate limit (429) gelirse 25sn bekleyip tekrar dener (max 2 deneme).
     * Bozuk/kısa çıktı gelirse null döner → DeepL'e düşer.
     */
    protected function getGeminiDescription($title, $artist, $date, $medium, $descriptionEn) {
        if (empty($this->geminiKey) || empty(trim($descriptionEn))) return null;

        $prompt = "GÖREV: Aşağıdaki İngilizce metni Türkçeye çevir ve müze kataloğu formatında yeniden yaz.\n\n"
                . "KRİTİK KURALLAR:\n"
                . "- SADECE verilen metindeki bilgileri kullan.\n"
                . "- Metinde olmayan hiçbir bilgi, tarih, yorum veya detay EKLEME.\n"
                . "- Bilgi uydurma, tahmin etme veya çıkarım yapma.\n"
                . "- Akıcı, doğal Türkçe kullan.\n"
                . "- Sadece çevrilmiş metni yaz, başka açıklama ekleme.\n\n"
                . "ESER BİLGİSİ:\n"
                . "Eser: {$title}\n"
                . "Sanatçı: {$artist}\n\n"
                . "ÇEVRİLECEK METİN:\n{$descriptionEn}";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500
            ]
        ]);

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-goog-api-key: ' . $this->geminiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Rate limit → bekle ve tekrar dene
            if ($httpCode === 429) {
                error_log("Gemini rate limit, 25sn bekleniyor (deneme {$attempt}/2)");
                sleep(25);
                continue;
            }

            if ($httpCode !== 200 || !$response) {
                error_log("Gemini hata: HTTP {$httpCode}");
                return null;
            }

            $json = json_decode($response, true);
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            // Kalite kontrolü: en az 50 karakter, Türkçe karakter içermeli
            if ($text && mb_strlen(trim($text)) >= 50 && preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $text)) {
                return trim($text);
            }

            error_log("Gemini çıktısı yetersiz, DeepL'e düşülüyor");
            return null;
        }

        return null;
    }

    protected function searchWikipedia($title, $artist) {
        $artist = trim((string)$artist);
        if ($this->isUnknownArtist($artist)) {
            return null;
        }

        $query = urlencode('"' . $artist . '" artist painter');
        $searchUrl = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={$query}&utf8=&format=json&srlimit=1";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FezadanBot/1.0 (https://fezadan.org)');
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $json = json_decode($response, true);
            if (!empty($json['query']['search'])) {
                $pageTitle = $json['query']['search'][0]['title'];
                if (!$this->isLikelyArtistPageTitle($pageTitle, $artist)) {
                    return null;
                }
                
                // Fetch summary
                $extractUrl = "https://en.wikipedia.org/w/api.php?action=query&titles=" . urlencode($pageTitle) . "&prop=extracts&exintro=true&explaintext=true&format=json";
                $ch2 = curl_init();
                curl_setopt($ch2, CURLOPT_URL, $extractUrl);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch2, CURLOPT_USERAGENT, 'FezadanBot/1.0 (https://fezadan.org)');
                $response2 = curl_exec($ch2);
                curl_close($ch2);

                if ($response2) {
                    $json2 = json_decode($response2, true);
                    if (!empty($json2['query']['pages'])) {
                        $pages = $json2['query']['pages'];
                        $page = reset($pages);
                        if (isset($page['extract']) && !empty(trim($page['extract']))
                            && $this->isLikelyArtistExtract($page['extract'], $artist)) {
                            return [
                                'text' => $page['extract'],
                                'url' => "https://en.wikipedia.org/wiki/" . str_replace(' ', '_', $pageTitle)
                            ];
                        }
                    }
                }
            }
        }
        return null;
    }

    protected function generateTemplateDescription($title, $artist, $date, $medium, $museum) {
        $parts = [];
        if (!$this->isUnknownArtist((string)$artist)) {
            $parts[] = "Sanatçı: {$artist}.";
        }
        if (!empty($date)) {
            $parts[] = "Tarih: {$date}.";
        }
        if (!empty($medium)) {
            $parts[] = "Teknik: {$medium}.";
        }
        $parts[] = "Eser {$museum} koleksiyonunda yer almaktadır.";
        return implode(' ', $parts);
    }

    protected function isUnknownArtist(string $artist): bool {
        $artist = trim(mb_strtolower($artist, 'UTF-8'));
        if ($artist === '') return true;
        $unknown = ['bilinmeyen sanatçı', 'unknown artist', 'unknown', 'anonymous', 'anonim', 'maker unknown', 'unidentified artist'];
        return in_array($artist, $unknown, true);
    }

    protected function isLikelyArtistPageTitle(string $pageTitle, string $artist): bool {
        $title = mb_strtolower($pageTitle, 'UTF-8');
        $artist = mb_strtolower($artist, 'UTF-8');
        if ($title === $artist) return true;
        if (strpos($title, $artist) !== false) return true;
        $tokens = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $artist));
        $tokens = array_values(array_filter($tokens, fn($t) => mb_strlen($t, 'UTF-8') >= 3));
        if (empty($tokens)) return false;
        $matches = 0;
        foreach ($tokens as $token) {
            if (strpos($title, $token) !== false) $matches++;
        }
        return $matches >= min(2, count($tokens));
    }

    protected function isLikelyArtistExtract(string $extract, string $artist): bool {
        $text = mb_strtolower($extract, 'UTF-8');
        if (!$this->isLikelyArtistPageTitle($extract, $artist)) return false;
        $signals = ['artist', 'painter', 'sculptor', 'printmaker', 'engraver', 'illustrator', 'calligrapher', 'photographer', 'sanatçı', 'ressam'];
        foreach ($signals as $signal) {
            if (strpos($text, $signal) !== false) return true;
        }
        return false;
    }
    
    // Orchestrator function to fetch and build the full data array
    public static function getRandomArtwork() {
        $rand = rand(1, 100);
        if ($rand <= 50) {
            require_once ROOT . '/app/Core/ArtProviderCleveland.php';
            $provider = new ArtProviderCleveland();
        } elseif ($rand <= 80) {
            require_once ROOT . '/app/Core/ArtProviderChicago.php';
            $provider = new ArtProviderChicago();
        } else {
            require_once ROOT . '/app/Core/ArtProviderMet.php';
            $provider = new ArtProviderMet();
        }
        
        $artwork = null;
        for ($i = 0; $i < 3; $i++) {
            $artwork = $provider->fetchArtwork();
            if ($artwork) break;
        }
        
        if (!$artwork) {
            // fallback if all 3 tries fail
            require_once ROOT . '/app/Core/ArtProviderCleveland.php';
            $provider = new ArtProviderCleveland();
            $artwork = $provider->fetchArtwork();
        }
        
        if ($artwork) {
            // Apply Description Logic
            $descTr = null;
            $descSource = 'template';
            $wikiUrl = null;
            
            // Layer 1: Wikipedia
            $wikiData = $provider->searchWikipedia($artwork['title'], $artwork['artist']);
            $descEn = $artwork['description_en'] ?? null;
            if ($wikiData) {
                $wikiUrl = $wikiData['url'];
                if (empty($artwork['artist_bio'])) {
                    $artwork['artist_bio'] = $wikiData['text'];
                }
            }
            
            // Layer 2: Gemini (müze kalitesinde Türkçe üretim)
            $descTr = $provider->getGeminiDescription(
                $artwork['title'],
                $artwork['artist'],
                $artwork['date_display'],
                $artwork['medium'],
                $descEn ?? ''
            );
            if ($descTr) {
                $descSource = 'museum';
            }
            
            // Layer 3: DeepL (Gemini başarısız olduysa)
            if (!$descTr && !empty($artwork['description_en'])) {
                $descTr = $provider->getDeepLTranslation($artwork['description_en']);
                if ($descTr) $descSource = 'museum';
            }
            
            // Layer 4: Template (son çare)
            if (!$descTr) {
                $descTr = $provider->generateTemplateDescription(
                    $artwork['title'], 
                    $artwork['artist'], 
                    $artwork['date_display'], 
                    $artwork['medium'], 
                    $artwork['provider']
                );
            }
            
            $artwork['description_tr'] = $descTr;
            $artwork['description_source'] = $descSource;
            $artwork['wikipedia_url'] = $wikiUrl;
            
            return $artwork;
        }
        
        return null;
    }
}
