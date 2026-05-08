<?php

namespace App\Core;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class R2Storage {
    private $client;
    private $bucketName;
    private $publicUrl;

    /** @var R2Storage|null */
    private static $instance = null;

    /**
     * Tek S3Client örneği döndürür (her not yüklemesinde yeni AWS bağlantısı kurmamak için).
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function normalizeObjectKey(string $objectKey): string
    {
        $objectKey = trim($objectKey);
        if ($objectKey === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $objectKey)) {
            $path = (string)parse_url($objectKey, PHP_URL_PATH);
            $objectKey = ltrim($path, '/');
        }

        return ltrim($objectKey, '/');
    }

    public function __construct() {
        $envPath = ROOT . '/.env';
        $env = [];

        if (file_exists($envPath)) {
            // Dosyayı satır satır okuyoruz
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Yorum satırlarını (# veya !) atla
                if (strpos(trim($line), '#') === 0 || strpos(trim($line), '!') === 0) continue;

                // Anahtar ve değeri ayır (sadece ilk '=' işaretinden böler)
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Değerin etrafındaki tırnakları (") temizle
                $value = trim($value, '"\'');
                
                $env[$name] = $value;
            }
        }

        // Bilgileri alıyoruz
        $accountId = getenv('R2_ACCOUNT_ID') ?: ($env['R2_ACCOUNT_ID'] ?? '');
        $accessKeyId = getenv('R2_ACCESS_KEY_ID') ?: ($env['R2_ACCESS_KEY_ID'] ?? '');
        $accessKeySecret = getenv('R2_SECRET_ACCESS_KEY') ?: ($env['R2_SECRET_ACCESS_KEY'] ?? '');
        
        $this->bucketName = getenv('R2_BUCKET_NAME') ?: ($env['R2_BUCKET_NAME'] ?? '');
        $this->publicUrl = getenv('R2_PUBLIC_URL') ?: (defined('CDN_URL') ? CDN_URL : ($env['R2_PUBLIC_URL'] ?? ''));

        if (empty($this->bucketName)) {
            error_log('R2Storage: R2_BUCKET_NAME .env içinde tanımlı değil.');
            throw new \RuntimeException('R2 storage yapılandırması eksik.');
        }

        $this->client = new \Aws\S3\S3Client([
            'version'     => 'latest',
            'region'      => 'auto',
            'endpoint'    => "https://{$accountId}.r2.cloudflarestorage.com",
            'credentials' => [
                'key'    => $accessKeyId,
                'secret' => $accessKeySecret,
            ],
        ]);
    }

    /**
     * PDF dosyasını Cloudflare R2'ye yükler.
     * * @param string $tempFilePath Yüklenen dosyanın geçici yolu (tmp_name)
     * @param string $originalFileName Dosyanın orijinal adı
     * @return string|bool Başarılıysa dosya yolu (key), başarısızsa false döner
     */
    public function uploadPDF($tempFilePath, $originalFileName) {
        // Benzersiz ve güvenli bir dosya adı oluşturuyoruz
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $safeName = md5(uniqid('', true)) . '-' . time() . '.' . $extension;
        
        // R2 içindeki dizin yolu
        $objectKey = 'notlar/' . $safeName;

        try {
            $this->client->putObject([
                'Bucket'      => $this->bucketName,
                'Key'         => $objectKey,
                'SourceFile'  => $tempFilePath,
                'ContentType' => 'application/pdf',
                // Bucket dışa açıksa dosyaların okunabilmesi için ACL ayarı gerekebilir
                // Eğer Cloudflare panelinden bucket'ı public yaptıysan bunu yoruma alabilirsin
                // 'ACL'         => 'public-read' 
            ]);

            return $objectKey; 
        } catch (AwsException $e) {
            // Hata loglaması (Fezadan log sistemine veya error_log'a yazdırabilirsin)
            error_log("R2 Yükleme Hatası: " . $e->getMessage());
            return false;
        }
    }


    public function uploadFile(string $sourceFile, string $objectKey, string $contentType): ?string {
        try {
            $objectKey = ltrim($objectKey, '/');
            $this->client->putObject([
                'Bucket'       => $this->bucketName,
                'Key'          => $objectKey,
                'SourceFile'   => $sourceFile,
                'ContentType'  => $contentType,
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
            return $objectKey;
        } catch (AwsException $e) {
            error_log("R2 Dosya Yükleme Hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * PDF dosyasını Cloudflare R2'den siler.
     */
    public function deletePDF($objectKey) {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key'    => $objectKey,
            ]);
            return true;
        } catch (\Aws\Exception\AwsException $e) {
            error_log("R2 Silme Hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteFile(string $objectKey): bool {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key'    => ltrim($objectKey, '/'),
            ]);
            return true;
        } catch (AwsException $e) {
            error_log("R2 Dosya Silme Hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dosyanın tam public URL'sini döndürür
     */
    public function getFileUrl($objectKey) {
        if (preg_match('#^https?://#i', (string)$objectKey)) {
            return (string)$objectKey;
        }
        return rtrim($this->publicUrl, '/') . '/' . $this->normalizeObjectKey((string)$objectKey);
    }

    public function streamView($objectKey, $displayName = 'belge') {
        try {
            if (ob_get_level()) ob_end_clean();

            $normalizedKey = $this->normalizeObjectKey((string)$objectKey);
            if ($normalizedKey === '') {
                http_response_code(404);
                echo 'Belge bulunamadı.';
                exit;
            }

            $getParams = [
                'Bucket' => $this->bucketName,
                'Key'    => $normalizedKey,
            ];

            $statusCode = 200;
            $contentRange = null;
            $contentLength = null;

            $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
            if ($rangeHeader !== '' && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $matches)) {
                $head = $this->client->headObject([
                    'Bucket' => $this->bucketName,
                    'Key'    => $normalizedKey,
                ]);
                $fileSize = (int)($head['ContentLength'] ?? 0);

                if ($fileSize > 0) {
                    $start = $matches[1] === '' ? 0 : (int)$matches[1];
                    $end = $matches[2] === '' ? ($fileSize - 1) : (int)$matches[2];

                    if ($start >= $fileSize) {
                        http_response_code(416);
                        header('Content-Range: bytes */' . $fileSize);
                        exit;
                    }

                    $end = min($end, $fileSize - 1);
                    if ($end < $start) {
                        $end = $start;
                    }

                    $getParams['Range'] = 'bytes=' . $start . '-' . $end;
                    $statusCode = 206;
                    $contentRange = 'bytes ' . $start . '-' . $end . '/' . $fileSize;
                    $contentLength = $end - $start + 1;
                }
            }

            $result = $this->client->getObject($getParams);

            $safeName = str_replace([' ', '/', '\\'], '_', (string)$displayName) . '.pdf';
            $mimeType = (string)($result['ContentType'] ?? 'application/pdf');
            $finalLength = $contentLength ?? (int)($result['ContentLength'] ?? 0);

            http_response_code($statusCode);
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $safeName . '"');
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=300');

            if ($contentRange !== null) {
                header('Content-Range: ' . $contentRange);
            }
            if ($finalLength > 0) {
                header('Content-Length: ' . $finalLength);
            }

            echo $result['Body'];
            exit;
        } catch (\Aws\Exception\AwsException $e) {
            error_log("R2 Görüntüleme Hatası: " . $e->getMessage());
            http_response_code(500);
            echo "Belge şu anda görüntülenemiyor.";
            exit;
        }
    }

    /**
     * PDF dosyasını R2'den çeker ve doğrudan indirme olarak sunar.
     * @param string $objectKey R2 üzerindeki dosya yolu
     * @param string $displayName İndirilecek dosyanın adı
     */
    public function streamDownload($objectKey, $displayName) {
        try {
            if (ob_get_level()) ob_end_clean();

            $result = $this->client->getObject([
                'Bucket' => $this->bucketName,
                'Key'    => $this->normalizeObjectKey((string)$objectKey),
            ]);

            // Dosya adını temizleyelim ve .pdf uzantısını ekleyelim
            $safeName = str_replace([' ', '/', '\\'], '_', $displayName) . '.pdf';

            // Tarayıcıya "bu bir dosyadır, bunu indir" diyoruz
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $safeName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $result['ContentLength']);

            // Dosya içeriğini yazdır
            echo $result['Body'];
            exit;

        } catch (\Aws\Exception\AwsException $e) {
            error_log("R2 İndirme Hatası: " . $e->getMessage());
            http_response_code(500);
            echo "Dosya şu anda indirilemiyor.";
            exit;
        }
    }
}