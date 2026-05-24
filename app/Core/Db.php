<?php
/**
 * Tek PDO bağlantısı.
 *
 * Her controller'ın yeni `new PDO(...)` açmasını engeller — TCP/auth handshake
 * her istekte tek sefer olur. Persistent connection değil; tek-istek-tek-bağlantı.
 *
 * Kullanım:
 *   $pdo = Db::pdo();
 */
class Db
{
    /** @var \PDO|null */
    private static $pdo = null;

    public static function pdo(): \PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            self::$pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            // Self-healing migration for 'articles' table
            try {
                // Check if 'lang' column exists
                $query = self::$pdo->query("SHOW COLUMNS FROM `articles` LIKE 'lang'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `articles` ADD COLUMN `lang` VARCHAR(10) NOT NULL DEFAULT 'TR'");
                }
                // Check if 'meta_keywords' column exists
                $query = self::$pdo->query("SHOW COLUMNS FROM `articles` LIKE 'meta_keywords'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `articles` ADD COLUMN `meta_keywords` TEXT NULL");
                }
                // Check if 'seo_title' column exists
                $query = self::$pdo->query("SHOW COLUMNS FROM `articles` LIKE 'seo_title'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `articles` ADD COLUMN `seo_title` VARCHAR(255) NULL");
                }
                // Check if 'seo_description' column exists
                $query = self::$pdo->query("SHOW COLUMNS FROM `articles` LIKE 'seo_description'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `articles` ADD COLUMN `seo_description` TEXT NULL");
                }
                // Check if 'translation_of' column exists
                $query = self::$pdo->query("SHOW COLUMNS FROM `articles` LIKE 'translation_of'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `articles` ADD COLUMN `translation_of` INT NULL DEFAULT NULL");
                }
                // Check if 'username_hash' column exists on login_attempts
                $query = self::$pdo->query("SHOW COLUMNS FROM `login_attempts` LIKE 'username_hash'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("ALTER TABLE `login_attempts` ADD COLUMN `username_hash` VARCHAR(64) NULL DEFAULT NULL");
                }
                
                // Check if 'portfolio_items' table exists
                $query = self::$pdo->query("SHOW TABLES LIKE 'portfolio_items'");
                if ($query->rowCount() === 0) {
                    self::$pdo->exec("CREATE TABLE `portfolio_items` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `title_tr` VARCHAR(255) NOT NULL,
                        `title_en` VARCHAR(255) NULL,
                        `description_tr` TEXT NULL,
                        `description_en` TEXT NULL,
                        `image_url` VARCHAR(255) NOT NULL,
                        `type` ENUM('photo', 'drawing') NOT NULL DEFAULT 'photo',
                        `display_order` INT DEFAULT 0,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                }
            } catch (\Exception $e) {
                error_log("Database self-healing migration failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /** Sadece test/ayarsızlık durumları için bağlantıyı sıfırla. */
    public static function reset(): void
    {
        self::$pdo = null;
    }
}
