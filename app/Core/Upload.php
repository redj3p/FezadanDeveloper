<?php
/**
 * Görsel yükleme sertleştirme yardımcı sınıfı.
 * - Extension whitelist
 * - getimagesize ile gerçek MIME doğrulama
 * - GD ile yeniden işleme (polyglot/EXIF payload kırma)
 */
class Upload
{
    /** @var array<string,string> */
    private static $allowed = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];

    /**
     * Yüklenen $file'ı ($_FILES tek girdi) doğrular ve hedef dizine kaydeder.
     * Başarılı: dosya adı (string) döner. Hata: null.
     *
     * @param array $file       $_FILES['x']
     * @param string $destDir   Mutlak dizin (sonunda / olabilir)
     * @param string $prefix    uniqid prefix
     * @param int $maxBytes     Maksimum dosya boyutu (varsayılan 5MB)
     */
    public static function saveImage(array $file, string $destDir, string $prefix = 'img_', int $maxBytes = 5242880): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return null;
        if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return null;

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!isset(self::$allowed[$ext])) return null;

        $info = @getimagesize($file['tmp_name']);
        if (!$info || !isset($info['mime'])) return null;
        if ($info['mime'] !== self::$allowed[$ext]) {
            // Bazı kameralarda jpg/jpeg uyuşmazlığı tolere edilsin
            if (!($info['mime'] === 'image/jpeg' && in_array($ext, ['jpg', 'jpeg'], true))) {
                return null;
            }
        }

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        $newName = uniqid($prefix, true) . '.' . $ext;
        $destPath = rtrim($destDir, '/') . '/' . $newName;

        // GD reprocess (polyglot kır)
        if (function_exists('imagecreatefromstring')) {
            $raw = @file_get_contents($file['tmp_name']);
            if ($raw !== false) {
                $im = @imagecreatefromstring($raw);
                if ($im !== false) {
                    $ok = false;
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            $ok = @imagejpeg($im, $destPath, 88);
                            break;
                        case 'png':
                            @imagesavealpha($im, true);
                            $ok = @imagepng($im, $destPath, 6);
                            break;
                        case 'webp':
                            @imagesavealpha($im, true);
                            $ok = function_exists('imagewebp') ? @imagewebp($im, $destPath, 88) : false;
                            break;
                        case 'gif':
                            $ok = @imagegif($im, $destPath);
                            break;
                    }
                    if ($ok && $ext !== 'webp' && $ext !== 'gif') {
                        // Yan yana WebP varyant — view'lar <picture><source type="image/webp">
                        // ile servis ederek modern tarayıcılarda %25-35 daha küçük indirme.
                        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $destPath);
                        if (function_exists('imagewebp')) {
                            if ($ext === 'png') {
                                @imagesavealpha($im, true);
                            }
                            @imagewebp($im, $webpPath, 82);
                        } elseif (class_exists('Imagick')) {
                            try {
                                $iw = new \Imagick($destPath);
                                $iw->setImageFormat('webp');
                                $iw->setImageCompressionQuality(82);
                                $iw->setOption('webp:method', '6');
                                $iw->writeImage($webpPath);
                                $iw->clear();
                                $iw->destroy();
                            } catch (\Throwable $e) {
                                // sessiz geç — orijinal görsel zaten kaydedildi
                            }
                        }
                    }
                    @imagedestroy($im);
                    if ($ok) return $newName;
                    // GD ile kaydedemediysek fallback move_uploaded_file'a geç
                }
            }
        }

        // Fallback: doğrulanmış dosyayı taşı
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return $newName;
        }
        return null;
    }


    public static function saveImageToR2(array $file, string $folder = 'uploads', string $prefix = 'img_', int $maxBytes = 5242880): ?string
    {
        $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fezadan_uploads_' . bin2hex(random_bytes(6));
        if (!@mkdir($tmpDir, 0700, true) && !is_dir($tmpDir)) {
            return null;
        }

        $stored = self::saveImage($file, $tmpDir, $prefix, $maxBytes);
        if ($stored === null) {
            @rmdir($tmpDir);
            return null;
        }

        $sourcePath = $tmpDir . DIRECTORY_SEPARATOR . $stored;
        $folder = trim($folder, '/');
        $objectKey = $folder . '/' . $stored;
        $contentType = self::$allowed[strtolower(pathinfo($stored, PATHINFO_EXTENSION))] ?? 'application/octet-stream';

        try {
            require_once ROOT . '/app/Core/R2Storage.php';
            $r2 = \App\Core\R2Storage::instance();
            $uploaded = $r2->uploadFile($sourcePath, $objectKey, $contentType);
            if (!$uploaded) {
                self::cleanupTempUpload($tmpDir, $stored);
                return null;
            }

            $webpPath = preg_replace('/\.[^.]+$/', '.webp', $sourcePath);
            if ($webpPath !== $sourcePath && is_file($webpPath)) {
                $webpKey = preg_replace('/\.[^.]+$/', '.webp', $objectKey);
                $r2->uploadFile($webpPath, $webpKey, 'image/webp');
            }

            self::cleanupTempUpload($tmpDir, $stored);
            return '/' . $objectKey;
        } catch (\Throwable $e) {
            error_log('R2 görsel yükleme hatası: ' . $e->getMessage());
            self::cleanupTempUpload($tmpDir, $stored);
            return null;
        }
    }

    private static function cleanupTempUpload(string $tmpDir, string $stored): void
    {
        $sourcePath = $tmpDir . DIRECTORY_SEPARATOR . $stored;
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $sourcePath);
        if (is_file($sourcePath)) @unlink($sourcePath);
        if ($webpPath !== $sourcePath && is_file($webpPath)) @unlink($webpPath);
        @rmdir($tmpDir);
    }

    /**
     * Verilen `/uploads/foo.jpg` yoluna karşılık WebP varyantı varsa relative URL döner,
     * yoksa null. View'larda <picture> üretirken kullanılır.
     */
    public static function webpVariant(string $relativePath): ?string
    {
        $rel = '/' . ltrim($relativePath, '/');
        $webpRel = preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel);
        if ($webpRel === $rel) return null; // jpg/png değildi
        $abs = ($_SERVER['DOCUMENT_ROOT'] ?? '') . $webpRel;
        if ($abs && is_file($abs)) return $webpRel;
        return null;
    }

    public static function assetUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') return '';
        if (preg_match('#^https?://#i', $path)) return $path;
        if (strpos($path, '//') === 0) return 'https:' . $path;

        $rel = '/' . ltrim($path, '/');
        $base = defined('CDN_URL') && CDN_URL !== '' ? rtrim(CDN_URL, '/') : '';
        if ($base === '') {
            $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        }
        return $base !== '' ? $base . $rel : $rel;
    }

    public static function webpUrl(string $relativePath): ?string
    {
        $webp = self::webpVariant($relativePath);
        if ($webp) return self::assetUrl($webp);

        $path = trim($relativePath);
        if (preg_match('#^https?://#i', $path)) {
            $webpUrl = preg_replace('/\.(jpe?g|png)(\?.*)?$/i', '.webp$2', $path);
            return $webpUrl !== $path ? $webpUrl : null;
        }

        $rel = '/' . ltrim($path, '/');
        $webpRel = preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel);
        if ($webpRel === $rel) return null;

        $cdn = defined('CDN_URL') ? rtrim(CDN_URL, '/') : '';
        $site = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        if ($cdn !== '' and $cdn !== $site) {
            return self::assetUrl($webpRel);
        }
        return null;
    }
}
