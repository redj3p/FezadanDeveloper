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


    public static function saveImageToR2(array $file, string $folder = 'uploads', string $prefix = 'img_', int $maxBytes = 5242880, string $slugBase = ''): ?string
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

        $seed = trim($slugBase) !== ''
            ? $slugBase
            : (string)pathinfo((string)($file['name'] ?? ''), PATHINFO_FILENAME);
        $webpName = self::buildWebpName($seed, $prefix);
        $webpUploadPath = $tmpDir . DIRECTORY_SEPARATOR . $webpName;

        $sourceExt = strtolower((string)pathinfo($sourcePath, PATHINFO_EXTENSION));
        $derivedWebpPath = preg_replace('/\.[^.]+$/', '.webp', $sourcePath);

        $webpReady = false;
        if ($sourceExt === 'webp') {
            $webpReady = @copy($sourcePath, $webpUploadPath);
        } elseif ($derivedWebpPath !== $sourcePath && is_file($derivedWebpPath)) {
            $webpReady = @copy($derivedWebpPath, $webpUploadPath);
        }

        if (!$webpReady) {
            $webpReady = self::convertToWebp($sourcePath, $webpUploadPath);
        }

        if (!$webpReady || !is_file($webpUploadPath)) {
            self::cleanupTempUpload($tmpDir);
            return null;
        }

        $objectKey = ($folder !== '' ? $folder . '/' : '') . $webpName;

        try {
            require_once ROOT . '/app/Core/R2Storage.php';
            $r2 = \App\Core\R2Storage::instance();
            $uploaded = $r2->uploadFile($webpUploadPath, $objectKey, 'image/webp');
            if (!$uploaded) {
                self::cleanupTempUpload($tmpDir);
                return null;
            }

            self::cleanupTempUpload($tmpDir);
            return '/' . $objectKey;
        } catch (\Throwable $e) {
            error_log('R2 görsel yükleme hatası: ' . $e->getMessage());
            self::cleanupTempUpload($tmpDir);
            return null;
        }
    }

    private static function buildWebpName(string $seed, string $fallbackPrefix): string
    {
        $base = self::slugify($seed);
        if ($base === '') {
            $fallback = trim($fallbackPrefix, "_ \t\n\r\0\x0B");
            $base = self::slugify($fallback);
        }
        if ($base === '') {
            $base = 'image';
        }

        try {
            $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        } catch (\Throwable $e) {
            $suffix = substr(sha1(uniqid('', true)), 0, 8);
        }

        return $base . '-' . $suffix . '.webp';
    }

    private static function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $find = ['Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı'];
        $replace = ['c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i'];
        $value = strtolower(str_replace($find, $replace, $value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string)$value, '-');

        return $value;
    }

    private static function convertToWebp(string $sourcePath, string $destPath, int $quality = 82): bool
    {
        if (!is_file($sourcePath)) {
            return false;
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $raw = @file_get_contents($sourcePath);
            if ($raw !== false) {
                $im = @imagecreatefromstring($raw);
                if ($im !== false) {
                    if (function_exists('imagepalettetotruecolor')) {
                        @imagepalettetotruecolor($im);
                    }
                    @imagesavealpha($im, true);
                    $ok = @imagewebp($im, $destPath, $quality);
                    @imagedestroy($im);
                    if ($ok) {
                        return true;
                    }
                }
            }
        }

        if (class_exists('Imagick')) {
            try {
                $iw = new \Imagick($sourcePath);
                $iw->setImageFormat('webp');
                $iw->setImageCompressionQuality($quality);
                $iw->setOption('webp:method', '6');
                $iw->stripImage();
                $ok = $iw->writeImage($destPath);
                $iw->clear();
                $iw->destroy();
                return (bool)$ok;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    private static function cleanupTempUpload(string $tmpDir): void
    {
        $files = @glob($tmpDir . DIRECTORY_SEPARATOR . '*');
        if (is_array($files)) {
            foreach ($files as $filePath) {
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }
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
