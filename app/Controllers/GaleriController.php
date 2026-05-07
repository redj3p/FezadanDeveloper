<?php

require_once ROOT . '/app/Core/Controller.php';
require_once ROOT . '/app/Core/Db.php';
require_once ROOT . '/app/Core/ArtProvider.php';

class GaleriController extends Controller {

    private function createArtSlug($title, $date) {
        $months = [
            '01' => 'ocak', '02' => 'subat', '03' => 'mart', '04' => 'nisan',
            '05' => 'mayis', '06' => 'haziran', '07' => 'temmuz', '08' => 'agustos',
            '09' => 'eylul', '10' => 'ekim', '11' => 'kasim', '12' => 'aralik'
        ];
        $parts = explode('-', $date);
        $dateStr = (int)$parts[2] . '-' . $months[$parts[1]] . '-' . $parts[0];

        $title = mb_strtolower($title, 'UTF-8');
        $title = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'i', 'ö', 'ç', 'I', 'Ğ', 'Ü', 'Ş', 'İ', 'Ö', 'Ç'],
            ['i', 'g', 'u', 's', 'i', 'o', 'c', 'i', 'g', 'u', 's', 'i', 'o', 'c'],
            $title
        );
        $title = preg_replace('/[^a-z0-9]/', '-', $title);
        $title = preg_replace('/-+/', '-', $title);
        $title = trim($title, '-');

        $slug = "fezadan-gunun-resmi-" . $dateStr . "-" . $title;
        return substr($slug, 0, 200);
    }

    public function index() {
        $pdo = Db::pdo();
        $today = date('Y-m-d');

        // Check if today's artwork exists
        $stmt = $pdo->prepare("SELECT * FROM daily_artworks WHERE date = ?");
        $stmt->execute([$today]);
        $todayArt = $stmt->fetch();

        // If not, fetch a new one
        if (!$todayArt) {
            $artwork = ArtProvider::getRandomArtwork();
            if ($artwork) {
                try {
                    $slug = $this->createArtSlug($artwork['title'], $today);
                    $insertStmt = $pdo->prepare("
                        INSERT IGNORE INTO daily_artworks 
                        (date, slug, title, artist, artist_bio, date_display, medium, dimensions, image_url, thumbnail_url, provider, external_id, external_url, description_en, description_tr, description_source, wikipedia_url, is_public_domain) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $today,
                        $slug,
                        $artwork['title'],
                        $artwork['artist'],
                        $artwork['artist_bio'],
                        $artwork['date_display'],
                        $artwork['medium'],
                        $artwork['dimensions'],
                        $artwork['image_url'],
                        $artwork['thumbnail_url'],
                        $artwork['provider'],
                        $artwork['external_id'],
                        $artwork['external_url'],
                        $artwork['description_en'],
                        $artwork['description_tr'],
                        $artwork['description_source'],
                        $artwork['wikipedia_url'],
                        $artwork['is_public_domain']
                    ]);
                    
                    // Fetch the newly inserted artwork
                    $stmt->execute([$today]);
                    $todayArt = $stmt->fetch();
                } catch (\Exception $e) {
                    error_log("Galeri error: " . $e->getMessage());
                }
            }

            // Fallback: if API failed or insert failed, get the latest available artwork
            if (!$todayArt) {
                $stmt = $pdo->query("SELECT * FROM daily_artworks ORDER BY date DESC LIMIT 1");
                $todayArt = $stmt->fetch();
            }
        }

        // Fetch recent past artworks for the grid
        $gridArtworks = [];
        if ($todayArt) {
            $stmtGrid = $pdo->prepare("SELECT * FROM daily_artworks WHERE date < ? ORDER BY date DESC LIMIT 30");
            $stmtGrid->execute([$todayArt['date']]);
            $gridArtworks = $stmtGrid->fetchAll();
        }

        $this->view('front/galeri', [
            'todayArt' => $todayArt,
            'gridArtworks' => $gridArtworks,
            'page_title' => 'Günün Sanat Eseri — FEZADAN Galeri',
            'page_description' => 'Her gün dünya müzelerinden rastgele seçilmiş yeni bir sanat eserini keşfedin.',
            'og_image' => $todayArt ? $todayArt['image_url'] : null,
            'page_robots' => 'index, follow'
        ]);
    }

    public function show($slug) {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare("SELECT * FROM daily_artworks WHERE slug = ?");
        $stmt->execute([$slug]);
        $art = $stmt->fetch();

        if (!$art) {
            header('Location: /galeri');
            exit;
        }

        // Pagination for prev/next based on date
        $prevStmt = $pdo->prepare("SELECT slug FROM daily_artworks WHERE date < ? ORDER BY date DESC LIMIT 1");
        $prevStmt->execute([$art['date']]);
        $prev = $prevStmt->fetchColumn();

        $nextStmt = $pdo->prepare("SELECT slug FROM daily_artworks WHERE date > ? ORDER BY date ASC LIMIT 1");
        $nextStmt->execute([$art['date']]);
        $next = $nextStmt->fetchColumn();

        $this->view('front/galeri_detail', [
            'art' => $art,
            'prevDate' => $prev,
            'nextDate' => $next,
            'page_title' => $art['title'] . ' - ' . $art['artist'] . ' — FEZADAN Galeri',
            'page_description' => substr(strip_tags($art['description_tr'] ?: $art['description_en']), 0, 150) . '...',
            'og_image' => $art['image_url'],
            'og_type' => 'article', // Using article for better sharing display
            'page_robots' => 'index, follow'
        ]);
    }

    public function archive() {
        $pdo = Db::pdo();
        
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page < 1) $page = 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $totalStmt = $pdo->query("SELECT COUNT(*) FROM daily_artworks");
        $total = $totalStmt->fetchColumn();
        $totalPages = ceil($total / $limit);

        $stmt = $pdo->prepare("SELECT * FROM daily_artworks ORDER BY date DESC LIMIT ? OFFSET ?");
        // PDO bindParam issue with limit/offset integers workaround
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $artworks = $stmt->fetchAll();

        $this->view('front/galeri_archive', [
            'artworks' => $artworks,
            'page' => $page,
            'totalPages' => $totalPages,
            'page_title' => 'Galeri Arşivi — FEZADAN',
            'page_description' => 'Geçmiş günlerin sanat eserleri arşivi.',
            'page_robots' => 'noindex, follow'
        ]);
    }
}
