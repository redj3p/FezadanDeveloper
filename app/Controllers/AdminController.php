<?php
class AdminController extends Controller
{

    // Database bağlantı fonksiyonu
    /** @deprecated Yeni kodda doğrudan Db::pdo() kullanın. */
    private function getPDO()
    {
        return Db::pdo();
    }

    /** CSRF verify gerektirmeyen istisnai metodlar (login: session henüz açılmamış olabilir) */
    private static $csrfExempt = ['login'];

    /** GET ile çağrılması yasaklanan, yalnızca POST kabul edilen yazma uçları */
    private static $writeMethods = [
        'login','logout','store','update','delete','publish',
        'storeCategory','deleteCategory',
        'storePatch','patchDelete',
        'authorStore','authorDelete',
        'storeNote','updateNote','deleteNote',
        'updatePassword','uploadContentImage',
    ];

    public function __construct()
    {
        // CLI (cron / artisan benzeri scriptler) auth kontrolünü atlasın.
        if (PHP_SAPI === 'cli') {
            return;
        }

        $currentMethod = $_GET['url'] ?? '';

        if (!isset($_SESSION['admin_logged_in']) && !in_array($currentMethod, ['admin', 'admin/login'])) {
            header('Location: /admin');
            exit;
        }

        // POST tabanlı yazma uçlarında CSRF doğrulaması
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $segments = explode('/', trim($currentMethod, '/'));
            $action   = isset($segments[1]) ? $segments[1] : '';
            $camel    = $action ? lcfirst(str_replace('-', '', ucwords($action, '-'))) : '';

            $isWrite = in_array($action, self::$writeMethods, true)
                    || in_array($camel,  self::$writeMethods, true);

            if ($isWrite
                && !in_array($action, self::$csrfExempt, true)
                && !in_array($camel,  self::$csrfExempt, true)) {
                Csrf::verify();
            }

            // Login için: token üretilmiş olmalı, yoksa CSRF zorunlu
            if ((in_array($action, self::$csrfExempt, true) || in_array($camel, self::$csrfExempt, true))
                && !empty($_SESSION['csrf_token'])) {
                Csrf::verify();
            }
        }
    }

    /** Yazma uçlarında GET ile çağrılmayı engelle */
    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit;
        }
    }

    // Admin giriş sayfası
    public function index()
    {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: /admin/dashboard');
            exit;
        }
        $this->view('admin/login');
    }

    // Admin girişi
    public function login()
    {
        $this->requirePost();

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // MAHREMİYET ODAKLI IP HASHLEME
        $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $dailySalt = date('Y-m-d') . APP_SALT; 
        $ipHash = hash('sha256', $userIp . $dailySalt);

        try {
            $pdo = $this->getPDO();

            // 1. ESKİ KAYITLARI TEMİZLE: 3 saatten eski denemeleri sil
            $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL 3 HOUR");

            // 2. KONTROL: Bu hash ile son 3 saatte kaç hatalı giriş yapılmış?
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ?");
            $stmtCount->execute([$ipHash]);
            $attempts = $stmtCount->fetchColumn();

            // 3. ENGEL: 3 hata varsa engelle
            if ($attempts >= 3) {
                header('Location: /admin?error=locked');
                exit;
            }

            // 4. DOĞRULAMA
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                
                // Başarılı giriş: Bu hash'e ait hatalı denemeleri temizle
                $pdo->prepare("DELETE FROM login_attempts WHERE ip_hash = ?")->execute([$ipHash]);

                // Session fixation koruması: oturum kimliğini yenile, eski içerikleri sıfırla
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['admin_name'] = $user['name'];

                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

                header('Location: /admin/dashboard');
                exit;
            }
            else {
                // Başarısız giriş: Hashlenmiş IP'yi kaydet
                $stmtFail = $pdo->prepare("INSERT INTO login_attempts (ip_hash) VALUES (?)");
                $stmtFail->execute([$ipHash]);

                header('Location: /admin?error=1');
                exit;
            }
        }
        catch (\PDOException $e) {
            throw new \Exception("Giriş Hatası: " . $e->getMessage());
        }
    }

    // Admin arayüzü
    public function dashboard()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = $this->getPDO();

            // Makale arama
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $whereSql = "1=1";
            $params = [];

            if (!empty($search)) {
                // id= ile arama
                if (stripos($search, 'id=') === 0) {
                    $searchId = (int)substr($search, 3);
                    $whereSql .= " AND a.id = :id";
                    $params[':id'] = $searchId;
                }
                else {
                    // Title search
                    $whereSql .= " AND a.title LIKE :search";
                    $params[':search'] = "%$search%";
                }
            }

            // Sıralama
            $sort = $_GET['sort'] ?? 'id';
            $order = $_GET['order'] ?? 'DESC';

            $allowedSorts = [
                'id' => 'a.id',
                'title' => 'a.title',
                'reads' => 'a.reads',
                'author' => 'author_name',
                'category' => 'MIN(c.name)'
            ];

            if (!array_key_exists($sort, $allowedSorts)) {
                $sort = 'id';
            }

            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            $orderBySQL = $allowedSorts[$sort];

            // Sayfalandırma
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10; // Article per page
            $offset = ($page - 1) * $limit;

            // Toplam makale sayısı
            $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT a.id) FROM articles a WHERE $whereSql");
            $countStmt->execute($params);
            $totalArticles = $countStmt->fetchColumn();
            $totalPages = ceil($totalArticles / $limit);
            if ($page > $totalPages && $totalPages > 0)
                $page = $totalPages;

            // Makale bilgisini çek
            $sql = "SELECT a.*, au.name as author_name, GROUP_CONCAT(c.name SEPARATOR ', ') as category_names 
                    FROM articles a
                    LEFT JOIN article_categories ac ON a.id = ac.article_id
                    LEFT JOIN categories c ON ac.category_id = c.id
                    LEFT JOIN authors au ON a.author_id = au.id
                    WHERE $whereSql
                    GROUP BY a.id
                    ORDER BY $orderBySQL $order
                    LIMIT $limit OFFSET $offset";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Yama notları
            try {
                $patches = $pdo->query("SELECT * FROM patch_notes ORDER BY created_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);
            }
            catch (\PDOException $e) {
                $patches = [];
            }

            // İstatistikler
            $totalRealCount = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
            $totalDrafts = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();
            $totalReads = (int)$pdo->query("SELECT SUM(`reads`) FROM articles")->fetchColumn();

            $stats = [
                'total_articles' => $totalRealCount,
                'total_drafts'   => $totalDrafts, 
                'total_reads' => $totalReads,
                'system_status' => 'Aktif',
                'last_login' => date('H:i')
            ];

            $this->view('admin/dashboard', [
                'stats' => $stats,
                'articles' => $articles,
                'patches' => $patches,
                'pagination' => [
                    'current' => $page,
                    'total' => $totalPages,
                    'search' => $search
                ],
                'sort' => [
                    'column' => $sort,
                    'order' => $order
                ]
            ]);

        }
        catch (\PDOException $e) {
            throw new \Exception("Dashboard Hatası: " . $e->getMessage());
        }
    }

    // --- Yama Notları ---

    // Yama notu oluşturma sayfası
    public function createPatch()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }
        $this->view('admin/create-patch');
    }

    // Kaydetme
    public function storePatch()
    {
        $this->requirePost();

        $title = $_POST['title'] ?? 'Sistem Güncellemesi';
        $content = $_POST['content'] ?? '';
        $author = $_SESSION['admin_user'] ?? 'Admin';

        try {
            $pdo = $this->getPDO();

            // Önce geçici değerle insert et, sonra LAST_INSERT_ID() ile güncelle (race-safe)
            $stmt = $pdo->prepare("INSERT INTO patch_notes (version, title, content, author) VALUES ('', ?, ?, ?)");
            $stmt->execute([$title, $content, $author]);
            $newId = (int)$pdo->lastInsertId();
            $pdo->prepare("UPDATE patch_notes SET version = ? WHERE id = ?")
                ->execute(['1.' . $newId, $newId]);

            header('Location: /admin/dashboard?status=patch_added');
        }
        catch (\PDOException $e) {
            throw new \Exception("Yama Notu Kayıt Hatası: " . $e->getMessage());
        }
    }

    // Yama notu silme
    public function patchDelete()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();
                $stmt = $pdo->prepare("DELETE FROM patch_notes WHERE id = ?");
                $stmt->execute([$id]);
            }
            catch (\PDOException $e) {
                throw new \Exception("Yama Notu Silme Hatası: " . $e->getMessage());
            }
        }
        header('Location: /admin/dashboard?status=patch_deleted');
    }


    // --- Makale yönetimi ---

    // Yeni makale oluşturma sayfası
    public function create()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = $this->getPDO();
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $authors = $pdo->query("SELECT id, name FROM authors ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

            $this->view('admin/create', [
                'authors' => $authors,
                'categories' => $categories
            ]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Veri Çekilemedi: " . $e->getMessage());
        }
    }

    // Makale kaydetme
    public function store()
    {
        $this->requirePost();

        $title              = trim($_POST['title'] ?? '') ?: 'Adsiz';
        $desc               = $_POST['desc'] ?? '';
        $content            = $_POST['content'] ?? '';
        $refs               = $_POST['refs'] ?? '';
        $author_id          = isset($_POST['author_id']) ? (int)$_POST['author_id'] : 0;
        $selectedCategories = $_POST['categories'] ?? [];
        $image_db_path      = '';
        $status             = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $storedPath = Upload::saveImageToR2($_FILES['cover_image'], 'uploads', 'cover_');
            if ($storedPath !== null) {
                $image_db_path = $storedPath;
            }
        }

        try {
            $pdo = $this->getPDO();

            // Yazar varlık kontrolü
            $author_id = $this->validateAuthorId($pdo, $author_id);
            // Kategori whitelist
            $selectedCategories = $this->validateCategoryIds($pdo, $selectedCategories);

            $slug = $this->uniqueSlug($pdo, 'articles', $this->createSlug($title));

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, short_desc, content, refs, image_url, author_id, status) VALUES (:title, :slug, :desc, :content, :refs, :img, :author_id, :status)");
            $stmt->execute([
                ':title' => $title, ':slug' => $slug, ':desc' => $desc,
                ':content' => $content,
                ':refs' => $refs,
                ':img' => $image_db_path, ':author_id' => $author_id ?: null,
                ':status' => $status
            ]);
            $articleId = $pdo->lastInsertId();

            if (!empty($selectedCategories)) {
                $stmtCat = $pdo->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($selectedCategories as $catId) {
                    $stmtCat->execute([$articleId, $catId]);
                }
            }

            $pdo->commit();
            $this->markSitemapDirty();
            $msg = ($status === 'draft') ? 'draft_saved' : 'success';
            header('Location: /admin/dashboard?status=' . $msg);
        }
        catch (\PDOException $e) {
            if ($image_db_path) {
                $this->safeUnlinkUpload($image_db_path);
            }
            throw new \Exception("Makale Kayıt Hatası: " . $e->getMessage());
        }
    }

    /** FK validation: var olan author_id'yi geri döndürür; aksi halde 0. */
    private function validateAuthorId(\PDO $pdo, int $id): int
    {
        if ($id <= 0) return 0;
        $stmt = $pdo->prepare("SELECT id FROM authors WHERE id = ?");
        $stmt->execute([$id]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    /** FK validation: yalnızca DB'de mevcut olan kategori id'lerini döndürür. */
    private function validateCategoryIds(\PDO $pdo, $ids): array
    {
        if (!is_array($ids) || empty($ids)) return [];
        $clean = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        if (empty($clean)) return [];
        $place = implode(',', array_fill(0, count($clean), '?'));
        $stmt  = $pdo->prepare("SELECT id FROM categories WHERE id IN ($place)");
        $stmt->execute($clean);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    // Düzenleme sayfası
    public function edit()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/dashboard');
            exit;
        }

        try {
            $pdo = $this->getPDO();

            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$article) {
                http_response_code(404);
                $this->view('errors/404_article');
                exit;
            }

            $stmtCat = $pdo->prepare("SELECT category_id FROM article_categories WHERE article_id = ?");
            $stmtCat->execute([$id]);
            $selectedCategories = $stmtCat->fetchAll(\PDO::FETCH_COLUMN);

            $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $authors    = $pdo->query("SELECT id, name FROM authors ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

            $this->view('admin/edit', [
                'article'            => $article,
                'categories'         => $categories,
                'authors'            => $authors,
                'selectedCategories' => $selectedCategories
            ]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Veri Hatası: " . $e->getMessage());
        }
    }

    // Makale güncelleme
    public function update()
    {
        $this->requirePost();

        $id                 = (int)($_POST['id'] ?? 0);
        $title              = trim($_POST['title'] ?? '') ?: 'Adsiz';
        $desc               = $_POST['desc'] ?? '';
        $content            = $_POST['content'] ?? '';
        $refs               = $_POST['refs'] ?? '';
        $author_id          = isset($_POST['author_id']) ? (int)$_POST['author_id'] : 0;
        $selectedCategories = $_POST['categories'] ?? [];
        $current_image      = $_POST['current_image'] ?? '';
        $image_db_path      = $current_image;
        $oldImagePath       = $current_image;
        $status             = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        if ($id <= 0) {
            header('Location: /admin/dashboard?status=invalid');
            exit;
        }

        $newImageStored = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $storedPath = Upload::saveImageToR2($_FILES['cover_image'], 'uploads', 'cover_');
            if ($storedPath !== null) {
                $image_db_path  = $storedPath;
                $newImageStored = $storedPath;
            }
        }

        try {
            $pdo = $this->getPDO();
            $author_id          = $this->validateAuthorId($pdo, $author_id);
            $selectedCategories = $this->validateCategoryIds($pdo, $selectedCategories);

            $slug = $this->uniqueSlug($pdo, 'articles', $this->createSlug($title), $id);

            $pdo->beginTransaction();

            $sql = "UPDATE articles SET title = ?, slug = ?, short_desc = ?, content = ?, refs = ?, author_id = ?, image_url = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $desc, $content, $refs, $author_id ?: null, $image_db_path, $status, $id]);

            $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?")->execute([$id]);

            if (!empty($selectedCategories)) {
                $stmtCat = $pdo->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($selectedCategories as $catId) {
                    $stmtCat->execute([$id, $catId]);
                }
            }

            $pdo->commit();

            // Yeni resim yüklendiyse eski dosyayı disk'ten kaldır
            if ($newImageStored !== null && $oldImagePath !== '' && $oldImagePath !== $image_db_path) {
                $this->safeUnlinkUpload($oldImagePath);
            }

            $this->markSitemapDirty();
            header('Location: /admin/dashboard?status=updated');
        }
        catch (\PDOException $e) {
            if ($newImageStored !== null) $this->safeUnlinkUpload($newImageStored);
            throw new \Exception("Güncelleme Hatası: " . $e->getMessage());
        }
    }

    /** Yalnızca /uploads/ altındaki dosyaları silmeye izin ver (path traversal koruması). */
    private function safeUnlinkUpload(string $relativePath): bool
    {
        if ($relativePath === '' || strpos($relativePath, '/uploads/') !== 0) return false;
        if (strpos($relativePath, '..') !== false) return false;

        try {
            require_once ROOT . '/app/Core/R2Storage.php';
            $r2 = \App\Core\R2Storage::instance();
            $objectKey = ltrim($relativePath, '/');
            $ok = $r2->deleteFile($objectKey);
            $webpKey = preg_replace('/\.(jpe?g|png)$/i', '.webp', $objectKey);
            if ($webpKey !== $objectKey) {
                $r2->deleteFile($webpKey);
            }
            return $ok;
        } catch (\Throwable $e) {
            $abs  = realpath(ROOT . '/public_html' . $relativePath);
            $base = realpath(ROOT . '/public_html/uploads');
            if ($abs === false || $base === false) return false;
            if (strpos($abs, $base) !== 0) return false;
            $ok = @unlink($abs);
            $webpAbs = preg_replace('/\.[^.]+$/', '.webp', $abs);
            if ($webpAbs !== $abs && is_file($webpAbs)) @unlink($webpAbs);
            return $ok;
        }
    }

    /**
     * Sitemap üretimi.
     *
     * - lastmod artık `updated_at` (yoksa created_at) kullanıyor → makale güncellendiğinde Google geri gelir.
     * - Makaleler dışında: yazar profilleri ve kategori sayfaları da indexlenir.
     * - Atomik yazım (tempnam → rename) ile yarım dosya görünmez.
     * - Cron için public bir entry point: AdminController'dan değil cron/generate-sitemap.php'den de çağrılabilir.
     */
    public function generateSitemap()
    {
        try {
            $pdo = $this->getPDO();

            $articles   = $pdo->query("SELECT slug, created_at, COALESCE(updated_at, created_at) AS lastmod FROM articles WHERE status = 'published' ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $notes      = $pdo->query("SELECT slug, created_at, COALESCE(updated_at, created_at) AS lastmod FROM notes ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $authors    = $pdo->query("SELECT DISTINCT au.slug FROM authors au JOIN articles a ON a.author_id = au.id WHERE a.status = 'published'")->fetchAll(\PDO::FETCH_ASSOC);
            $categories = $pdo->query("SELECT DISTINCT c.id FROM categories c JOIN article_categories ac ON ac.category_id = c.id JOIN articles a ON ac.article_id = a.id WHERE a.status = 'published'")->fetchAll(\PDO::FETCH_ASSOC);

            $base      = defined('SITE_URL')       ? rtrim(SITE_URL, '/')       : 'https://fezadan.org';
            $notesBase = defined('NOTES_SITE_URL') ? rtrim(NOTES_SITE_URL, '/') : 'https://notlar.fezadan.org';
            $todayIso  = date('Y-m-d');

            // --- A. ANA SİTE SİTEMAP ---
            $xmlMain  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlMain .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            $staticPages = [
                ['loc' => $base . '/',          'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => $base . '/makaleler', 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => $base . '/hakkinda',  'priority' => '0.5', 'changefreq' => 'monthly'],
                ['loc' => $base . '/manifesto', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ];
            foreach ($staticPages as $sp) {
                $xmlMain .= "  <url><loc>{$sp['loc']}</loc><lastmod>{$todayIso}</lastmod><changefreq>{$sp['changefreq']}</changefreq><priority>{$sp['priority']}</priority></url>" . PHP_EOL;
            }
            foreach ($articles as $article) {
                $lastMod = date('Y-m-d', strtotime($article['lastmod'] ?? $article['created_at']));
                $loc     = htmlspecialchars($base . '/makale/' . $article['slug'], ENT_XML1);
                $xmlMain .= "  <url><loc>{$loc}</loc><lastmod>{$lastMod}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>" . PHP_EOL;
            }
            foreach ($categories as $cat) {
                $loc = htmlspecialchars($base . '/makaleler?cat=' . (int)$cat['id'], ENT_XML1);
                $xmlMain .= "  <url><loc>{$loc}</loc><lastmod>{$todayIso}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>" . PHP_EOL;
            }
            foreach ($authors as $au) {
                if (empty($au['slug'])) continue;
                $loc = htmlspecialchars($base . '/yazar/' . $au['slug'], ENT_XML1);
                $xmlMain .= "  <url><loc>{$loc}</loc><lastmod>{$todayIso}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>" . PHP_EOL;
            }
            $xmlMain .= '</urlset>';
            $this->writeAtomic(ROOT . '/public_html/sitemap_main.xml', $xmlMain);

            // --- B. NOTLAR SİTESİ SİTEMAP ---
            $xmlNotes  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlNotes .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
            $xmlNotes .= "  <url><loc>{$notesBase}/</loc><lastmod>{$todayIso}</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>" . PHP_EOL;
            foreach ($notes as $note) {
                $lastMod = date('Y-m-d', strtotime($note['lastmod'] ?? $note['created_at']));
                $loc     = htmlspecialchars($notesBase . '/not/' . $note['slug'], ENT_XML1);
                $xmlNotes .= "  <url><loc>{$loc}</loc><lastmod>{$lastMod}</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>" . PHP_EOL;
            }
            $xmlNotes .= '</urlset>';
            $this->writeAtomic(ROOT . '/public_html/sitemap_notes.xml', $xmlNotes);
        }
        catch (\Exception $e) {
            error_log('Sitemap üretim hatası: ' . $e->getMessage());
        }
    }

    /**
     * Sitemap'in yeniden üretilmesi gerektiğini işaretle.
     * cron/generate-sitemap.php her N dakikada bir bu flag'e bakıp üretimi gerçekleştirir.
     * Inline üretim yerine bu pattern admin yanıt sürelerini hızlandırır.
     */
    private function markSitemapDirty(): void
    {
        @touch(sys_get_temp_dir() . '/fezadan-sitemap.dirty');
    }

    /** Yarım dosya yazımını engellemek için atomik yazım. */
    private function writeAtomic(string $target, string $content): void
    {
        $dir  = dirname($target);
        $tmp  = tempnam($dir, 'sm_');
        if ($tmp === false) {
            file_put_contents($target, $content);
            return;
        }
        file_put_contents($tmp, $content);
        @chmod($tmp, 0644);
        rename($tmp, $target);
    }

    // Makale silme
    public function delete()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();

                $stmt = $pdo->prepare("SELECT image_url FROM articles WHERE id = ?");
                $stmt->execute([$id]);
                $article = $stmt->fetch(\PDO::FETCH_ASSOC);

                $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);

                if ($article && !empty($article['image_url'])) {
                    $this->safeUnlinkUpload($article['image_url']);
                }

                $this->markSitemapDirty();
            }
            catch (\PDOException $e) {
                throw new \Exception("Makale Silme Hatası: " . $e->getMessage());
            }
        }
        header('Location: /admin/dashboard?status=deleted');
    }

    // Taslak yayınlama
    public function publish()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();
                $pdo->prepare("UPDATE articles SET status = 'published' WHERE id = ?")
                    ->execute([$id]);
                $this->markSitemapDirty();
            } catch (\PDOException $e) {
                throw new \Exception("Yayınlama Hatası: " . $e->getMessage());
            }
        }
        header('Location: /admin/dashboard?status=published');
        exit;
    }

    // Çıkış yapma
    public function logout()
    {
        $this->requirePost();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: /');
        exit;
    }

    // Admin profil sayfası
    public function profile()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }
        $this->view('admin/profile');
    }

    // Admin şifre değiştirme
    public function updatePassword()
    {
        $this->requirePost();

        $old_pass     = $_POST['old_password'] ?? '';
        $new_pass     = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';
        $username     = $_SESSION['admin_user'] ?? '';

        try {
            $pdo = $this->getPDO();
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user || !password_verify($old_pass, $user['password'])) {
                header('Location: /admin/profile?status=wrong_pass');
                exit;
            }

            if ($new_pass !== $confirm_pass) {
                header('Location: /admin/profile?status=mismatch');
                exit;
            }

            // Şifre politikası: en az 12 karakter, en az bir harf + bir rakam
            if (mb_strlen($new_pass, 'UTF-8') < 12
                || !preg_match('/[A-Za-z]/', $new_pass)
                || !preg_match('/\d/', $new_pass)) {
                header('Location: /admin/profile?status=weak');
                exit;
            }

            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")
                ->execute([$hashed_pass, $user['id']]);

            header('Location: /admin/profile?status=success');
            exit;
        }
        catch (\PDOException $e) {
            throw new \Exception("Güncelleme Hatası: " . $e->getMessage());
        }
    }

    // --- Kategori yönetimi---

    public function categories()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = $this->getPDO();

            $sql = "SELECT c.*,
                    (SELECT COUNT(*) FROM article_categories ac
                     JOIN articles a ON ac.article_id = a.id
                     WHERE ac.category_id = c.id AND a.status = 'published') as article_count,
                    (SELECT COUNT(*) FROM note_categories nc
                     JOIN notes n ON nc.note_id = n.id
                     WHERE nc.category_id = c.id) as note_count
                    FROM categories c
                    ORDER BY c.name ASC";

            $categories = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            $this->view('admin/categories', ['categories' => $categories]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Kategori Listesi Hatası: " . $e->getMessage());
        }
    }

    public function storeCategory()
    {
        $this->requirePost();

        $name = mb_strtoupper(trim($_POST['name'] ?? ''), 'UTF-8');
        if ($name === '') {
            header('Location: /admin/categories?status=empty');
            exit;
        }

        try {
            $pdo  = $this->getPDO();
            $slug = $this->uniqueSlug($pdo, 'categories', $this->createSlug($name));
            $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)")
                ->execute([$name, $slug]);
            header('Location: /admin/categories?status=success');
            exit;
        }
        catch (\PDOException $e) {
            throw new \Exception("Kategori Kayıt Hatası: " . $e->getMessage());
        }
    }

    public function deleteCategory()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            }
            catch (\PDOException $e) {
                throw new \Exception("Silme Hatası: " . $e->getMessage());
            }
        }
        header('Location: /admin/categories?status=deleted');
        exit;
    }

    // --- Yazar yönetimi---

    // Yazarları listele
    public function authors()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = $this->getPDO();
            $stmt = $pdo->query("SELECT * FROM authors ORDER BY name ASC");
            $authors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $artStmt = $pdo->query("SELECT id, title, author_id FROM articles ORDER BY created_at DESC");
            $all_author_articles = $artStmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->view('admin/authors', [
                'authors' => $authors,
                'all_author_articles' => $all_author_articles
            ]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Yazar Listesi Hatası: " . $e->getMessage());
        }
    }

    // Yazar kaydet/güncelle
    public function authorStore()
    {
        $this->requirePost();

        $id           = (int)($_POST['id'] ?? 0);
        $name         = trim($_POST['name'] ?? 'isimsiz') ?: 'isimsiz';
        $bio          = trim($_POST['bio'] ?? '');
        $oldImagePath = trim($_POST['current_image'] ?? '');
        $image_path   = $oldImagePath;
        $twitter      = trim($_POST['twitter'] ?? '');
        $instagram    = trim($_POST['instagram'] ?? '');
        $website      = trim($_POST['website'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $featured_str = implode(',', array_map('intval', (array)($_POST['featured'] ?? [])));

        $newImageStored = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $storedPath = Upload::saveImageToR2($_FILES['image'], 'uploads/authors', 'author_');
            if ($storedPath !== null) {
                $image_path     = $storedPath;
                $newImageStored = $storedPath;
            }
        }

        try {
            $pdo  = $this->getPDO();
            $slug = $this->uniqueSlug($pdo, 'authors', $this->createSlug($name), $id ?: null);

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE authors SET name = ?, slug = ?, bio = ?, image_url = ?, twitter = ?, instagram = ?, website = ?, email = ?, featured_articles = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $bio, $image_path, $twitter, $instagram, $website, $email, $featured_str, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO authors (name, slug, bio, image_url, twitter, instagram, website, email, featured_articles) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $bio, $image_path, $twitter, $instagram, $website, $email, $featured_str]);
            }

            // Yeni resim yüklendiyse eskiyi temizle
            if ($newImageStored !== null && $oldImagePath !== '' && $oldImagePath !== $image_path) {
                $this->safeUnlinkUpload($oldImagePath);
            }

            header('Location: /admin/authors?status=success');
            exit;
        }
        catch (\PDOException $e) {
            if ($newImageStored !== null) $this->safeUnlinkUpload($newImageStored);
            throw new \Exception("Yazar Kayıt Hatası: " . $e->getMessage());
        }
    }

    // Yazar silme
    public function authorDelete()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();
                $stmt = $pdo->prepare("SELECT image_url FROM authors WHERE id = ?");
                $stmt->execute([$id]);
                $img = $stmt->fetchColumn();

                $pdo->prepare("DELETE FROM authors WHERE id = ?")->execute([$id]);

                if ($img) {
                    $this->safeUnlinkUpload($img);
                }
            }
            catch (\PDOException $e) {
                throw new \Exception("Yazar Silme Hatası: " . $e->getMessage());
            }
        }
        header('Location: /admin/authors?status=deleted');
        exit;
    }

    // Summernote resim yükleme — her durumda JSON döner.
    public function uploadContentImage()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['admin_logged_in'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Yükleme hatası.']);
            exit;
        }

        $storedPath = Upload::saveImageToR2($_FILES['file'], 'uploads/content', 'content_');
        if ($storedPath === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Geçersiz veya çok büyük dosya.']);
            exit;
        }

        echo json_encode(['success' => true, 'url' => Upload::assetUrl($storedPath)]);
        exit;
    }

    // --- Notlar ---

    public function addNote()
    {
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }

        try {
            $pdo = $this->getPDO();
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

            $notes = $pdo->query("SELECT n.*, GROUP_CONCAT(c.name SEPARATOR ', ') as category_names
                                  FROM notes n
                                  LEFT JOIN note_categories nc ON n.id = nc.note_id
                                  LEFT JOIN categories c ON nc.category_id = c.id
                                  GROUP BY n.id
                                  ORDER BY n.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);

            $stats = $pdo->query("SELECT COUNT(*) as total_count, SUM(file_size) as total_size FROM notes")->fetch(\PDO::FETCH_ASSOC);

            $this->view('admin/add-note', [
                'categories' => $categories,
                'notes'      => $notes,
                'stats'      => $stats,
            ]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Not Listesi Hatası: " . $e->getMessage());
        }
    }

    public function storeNote()
    {
        $this->requirePost();

        $title              = trim($_POST['title'] ?? '');
        $desc               = $_POST['description'] ?? '';
        $uploader_name      = $_SESSION['admin_name'] ?? 'Admin';
        $selectedCategories = $_POST['categories'] ?? [];
        $lang               = $_POST['lang'] ?? 'TR';

        if (empty($_FILES['pdf_file']['name']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            header('Location: /admin/add-note?error=no_file');
            exit;
        }

        $r2Path = null;
        try {
            $pdo  = $this->getPDO();
            $selectedCategories = $this->validateCategoryIds($pdo, $selectedCategories);

            // Türkçe karakter güvenli slug + benzersizlik
            $slug = $this->uniqueSlug($pdo, 'notes', $this->createSlug($title));

            require_once ROOT . '/app/Core/R2Storage.php';
            $r2 = \App\Core\R2Storage::instance();
            $r2Path = $r2->uploadPDF($_FILES['pdf_file']['tmp_name'], $slug);

            if (!$r2Path) {
                throw new \Exception("Cloudflare R2 yükleme hatası oluştu.");
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO notes (title, slug, description, r2_path, uploader_name, file_size, lang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $desc, $r2Path, $uploader_name, (int)$_FILES['pdf_file']['size'], $lang]);
            $noteId = (int)$pdo->lastInsertId();

            if (!empty($selectedCategories)) {
                $stmtCat = $pdo->prepare("INSERT INTO note_categories (note_id, category_id) VALUES (?, ?)");
                foreach ($selectedCategories as $catId) {
                    $stmtCat->execute([$noteId, $catId]);
                }
            }

            $pdo->commit();
            $this->markSitemapDirty();

            header('Location: /admin/add-note?status=success');
            exit;
        }
        catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // R2'ye yüklenmiş ama DB'ye yazılamamış dosyayı temizle (yetim önleme)
            if ($r2Path) {
                try {
                    if (!isset($r2)) {
                        require_once ROOT . '/app/Core/R2Storage.php';
                        $r2 = \App\Core\R2Storage::instance();
                    }
                    $r2->deletePDF($r2Path);
                } catch (\Throwable $cleanupErr) {
                    error_log("R2 cleanup failed: " . $cleanupErr->getMessage());
                }
            }
            error_log("Note Upload Error: " . $e->getMessage());
            header('Location: /admin/add-note?error=system_failure');
            exit;
        }
    }

    public function deleteNote()
    {
        $this->requirePost();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo = $this->getPDO();
                $stmt = $pdo->prepare("SELECT r2_path FROM notes WHERE id = ?");
                $stmt->execute([$id]);
                $note = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($note) {
                    // Önce R2'den dene; başarısız olursa DB kaydı kalır (kullanıcı tekrar deneyebilir)
                    require_once ROOT . '/app/Core/R2Storage.php';
                    $r2 = \App\Core\R2Storage::instance();
                    $r2->deletePDF($note['r2_path']);

                    $pdo->prepare("DELETE FROM notes WHERE id = ?")->execute([$id]);
                    $this->markSitemapDirty();
                }
            }
            catch (\Exception $e) {
                error_log("Note Delete Error: " . $e->getMessage());
                header('Location: /admin/add-note?error=delete_failed');
                exit;
            }
        }
        header('Location: /admin/add-note?status=deleted');
        exit;
    }

    public function editNote()
    {
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location: /admin/add-note?error=note_not_found'); exit; }

        try {
            $pdo = $this->getPDO();

            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
            $stmt->execute([$id]);
            $note = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$note) {
                http_response_code(404);
                header('Location: /admin/add-note?error=note_not_found');
                exit;
            }

            $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

            $noteCatsStmt = $pdo->prepare("SELECT category_id FROM note_categories WHERE note_id = ?");
            $noteCatsStmt->execute([$id]);
            $noteCategoryIds = $noteCatsStmt->fetchAll(\PDO::FETCH_COLUMN);

            $this->view('admin/edit-note', [
                'note'            => $note,
                'categories'      => $cats,
                'noteCategoryIds' => $noteCategoryIds,
            ]);
        }
        catch (\PDOException $e) {
            throw new \Exception("Veritabanı Hatası: " . $e->getMessage());
        }
    }

    public function updateNote()
    {
        $this->requirePost();

        $id                 = (int)($_POST['id'] ?? 0);
        $title              = trim($_POST['title'] ?? '');
        $desc               = $_POST['description'] ?? '';
        $lang               = $_POST['lang'] ?? 'TR';
        $selectedCategories = $_POST['categories'] ?? [];

        if ($id <= 0 || $title === '') {
            header('Location: /admin/add-note?error=missing_data');
            exit;
        }

        try {
            $pdo = $this->getPDO();
            $selectedCategories = $this->validateCategoryIds($pdo, $selectedCategories);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE notes SET title = ?, description = ?, lang = ? WHERE id = ?");
            $stmt->execute([$title, $desc, $lang, $id]);

            $pdo->prepare("DELETE FROM note_categories WHERE note_id = ?")->execute([$id]);

            if (!empty($selectedCategories)) {
                $stmtCat = $pdo->prepare("INSERT INTO note_categories (note_id, category_id) VALUES (?, ?)");
                foreach ($selectedCategories as $catId) {
                    $stmtCat->execute([$id, $catId]);
                }
            }

            $pdo->commit();
            $this->markSitemapDirty();

            header('Location: /admin/add-note?status=updated');
            exit;
        }
        catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log("Note Update Error: " . $e->getMessage());
            throw new \Exception("Sistem Hatası: " . $e->getMessage());
        }
    }

    // --- Galeri ---

    public function galeri()
    {
        if (!isset($_SESSION['admin_logged_in'])) { header('Location: /admin'); exit; }

        try {
            $pdo = $this->getPDO();
            
            $today = date('Y-m-d');
            $stmtToday = $pdo->prepare("SELECT * FROM daily_artworks WHERE date = ?");
            $stmtToday->execute([$today]);
            $todayArt = $stmtToday->fetch(\PDO::FETCH_ASSOC);

            $stmtAll = $pdo->query("SELECT * FROM daily_artworks ORDER BY date DESC");
            $allArtworks = $stmtAll->fetchAll(\PDO::FETCH_ASSOC);

            $this->view('admin/galeri', [
                'todayArt' => $todayArt,
                'allArtworks' => $allArtworks
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Galeri Hatası: " . $e->getMessage());
        }
    }

    public function refreshDailyArt()
    {
        $this->requirePost();
        
        try {
            $pdo = $this->getPDO();
            $today = date('Y-m-d');
            
            // Delete today's artwork
            $stmt = $pdo->prepare("DELETE FROM daily_artworks WHERE date = ?");
            $stmt->execute([$today]);
            
            // Fetch a new one
            require_once ROOT . '/app/Core/ArtProvider.php';
            $artwork = \ArtProvider::getRandomArtwork();
            
            if ($artwork) {
                // Slug generation
                $months = ['01' => 'ocak', '02' => 'subat', '03' => 'mart', '04' => 'nisan', '05' => 'mayis', '06' => 'haziran', '07' => 'temmuz', '08' => 'agustos', '09' => 'eylul', '10' => 'ekim', '11' => 'kasim', '12' => 'aralik'];
                $parts = explode('-', $today);
                $dateStr = (int)$parts[2] . '-' . $months[$parts[1]] . '-' . $parts[0];
                $titleStr = mb_strtolower($artwork['title'], 'UTF-8');
                $titleStr = str_replace(['ı', 'ğ', 'ü', 'ş', 'i', 'ö', 'ç', 'I', 'Ğ', 'Ü', 'Ş', 'İ', 'Ö', 'Ç'], ['i', 'g', 'u', 's', 'i', 'o', 'c', 'i', 'g', 'u', 's', 'i', 'o', 'c'], $titleStr);
                $titleStr = preg_replace('/[^a-z0-9]/', '-', $titleStr);
                $titleStr = trim(preg_replace('/-+/', '-', $titleStr), '-');
                $slug = substr("fezadan-gunun-resmi-" . $dateStr . "-" . $titleStr, 0, 200);

                $insertStmt = $pdo->prepare("
                    INSERT INTO daily_artworks 
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
            }
            
            header('Location: /admin/galeri?status=refreshed');
            exit;
        } catch (\Exception $e) {
            error_log("Galeri Refresh Error: " . $e->getMessage());
            header('Location: /admin/galeri?error=refresh_failed');
            exit;
        }
    }

    public function updateArtDescription()
    {
        $this->requirePost();
        
        $id = (int)($_POST['id'] ?? 0);
        $descTr = $_POST['description_tr'] ?? '';
        
        if ($id <= 0) {
            header('Location: /admin/galeri?error=invalid_id');
            exit;
        }
        
        try {
            $pdo = $this->getPDO();
            $stmt = $pdo->prepare("UPDATE daily_artworks SET description_tr = ?, description_source = 'manual' WHERE id = ?");
            $stmt->execute([$descTr, $id]);
            
            header('Location: /admin/galeri?status=updated');
            exit;
        } catch (\PDOException $e) {
            error_log("Galeri Update Error: " . $e->getMessage());
            header('Location: /admin/galeri?error=update_failed');
            exit;
        }
    }
}
