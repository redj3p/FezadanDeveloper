<?php
$host = 'db'; // Run from docker container
$db   = 'fezadano5_site';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS daily_artworks (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        date             DATE NOT NULL UNIQUE,
        title            VARCHAR(255) NOT NULL,
        artist           VARCHAR(255) NOT NULL,
        artist_bio       TEXT NULL,
        date_display     VARCHAR(100) NULL,
        medium           VARCHAR(255) NULL,
        dimensions       VARCHAR(255) NULL,
        image_url        VARCHAR(500) NOT NULL,
        thumbnail_url    VARCHAR(500) NULL,
        provider         VARCHAR(50) NOT NULL,
        external_id      VARCHAR(100) NOT NULL,
        external_url     VARCHAR(500) NULL,
        description_en   TEXT NULL,
        description_tr   TEXT NULL,
        description_source ENUM('wikipedia','museum','template','manual') DEFAULT 'template',
        wikipedia_url    VARCHAR(500) NULL,
        is_public_domain  TINYINT(1) DEFAULT 1,
        created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_date (date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "Table daily_artworks created successfully.\n";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
