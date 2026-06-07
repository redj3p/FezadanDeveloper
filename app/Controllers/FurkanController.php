<?php

class FurkanController extends Controller
{
    private function adminPath(): string
    {
        return '/admin';
    }

    private function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /yonetim');
            exit;
        }
    }

    public function index()
    {
        $db = Db::pdo();
        $stmt = $db->query("SELECT * FROM portfolio_items ORDER BY display_order ASC, id DESC");
        $items = $stmt->fetchAll();

        if (empty($items)) {
            $items = self::getMockItems();
        }

        $authorStmt = $db->prepare("SELECT * FROM authors WHERE slug = ?");
        $authorStmt->execute(['furkan-sen']);
        $author = $authorStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$author) {
            $author = [
                'name' => 'Furkan Şen',
                'slug' => 'furkan-sen',
                'bio' => 'Görsel sanatçı ve fotoğrafçı. Işık, geometri ve minimalist kompozisyonlara odaklanır. Doğal yapılar ve insan müdahalesi arasındaki dinamik geçişleri yakalar.',
                'image_url' => '',
                'twitter' => '',
                'instagram' => '',
                'website' => '',
                'email' => 'contact@fezadan.org',
            ];
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isSubdomain = strpos($host, 'furkan.') === 0;

        $this->view('front/portfolio', [
            'items'       => $items,
            'isSubdomain' => $isSubdomain,
            'author'      => $author,
        ]);
    }

    private static function getMockItems()
    {
        $mock = [];
        $images = [
            [
                'title_tr' => 'Sessiz Orman Yolu',
                'title_en' => 'Silent Forest Path',
                'desc_tr' => 'Sabahın erken saatlerinde sisli bir orman yolunun huzuru.',
                'desc_en' => 'The serenity of a misty forest path in the early morning.',
                'img' => 'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Kozmik Yansımalar',
                'title_en' => 'Cosmic Reflections',
                'desc_tr' => 'Göz alıcı gece gökyüzü ve yıldızların su yüzeyindeki dansı.',
                'desc_en' => 'Spectacular night sky and the dance of stars on the water surface.',
                'img' => 'https://images.unsplash.com/photo-1506318137071-a8e063b4bec0?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Brütalist Geometri',
                'title_en' => 'Brutalist Geometry',
                'desc_tr' => 'Işık ve gölgenin beton yüzeylerdeki minimalist uyumu.',
                'desc_en' => 'Minimalist harmony of light and shadow on concrete surfaces.',
                'img' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Uzak Dağlar',
                'title_en' => 'Distant Mountains',
                'desc_tr' => 'Bulutların arasından yükselen görkemli zirvelerin görünümü.',
                'desc_en' => 'Majestic peaks rising through the surrounding clouds.',
                'img' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Okyanus Esintisi',
                'title_en' => 'Ocean Breeze',
                'desc_tr' => 'Kayalıklara vuran dalgaların çıkardığı o rahatlatıcı ritim.',
                'desc_en' => 'The soothing rhythm of waves crashing against the rocky shore.',
                'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Minimalist Çöl',
                'title_en' => 'Minimalist Desert',
                'desc_tr' => 'Sonsuz kum tepelerinin gün batımındaki sıcak tonları.',
                'desc_en' => 'Warm tones of endless sand dunes during golden hour.',
                'img' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Klasik Ayrıntı',
                'title_en' => 'Classic Detail',
                'desc_tr' => 'Tarihi bir yapının tavanındaki ince el işçiliği.',
                'desc_en' => 'Fine craftsmanship on the ceiling of a historical building.',
                'img' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Işık Hüzmesi',
                'title_en' => 'Rays of Light',
                'desc_tr' => 'Ağaçların arasından süzülerek toprağa ulaşan güneş ışıkları.',
                'desc_en' => 'Sunlight filtering through the dense canopy to the ground.',
                'img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Sisli Göl',
                'title_en' => 'Misty Lake',
                'desc_tr' => 'Sessiz ve hareketsiz duran suyun üzerindeki hafif sis bulutu.',
                'desc_en' => 'A light blanket of fog resting over the still, quiet water.',
                'img' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Monokrom Sokak',
                'title_en' => 'Monochrome Street',
                'desc_tr' => 'Yağmurlu bir akşamda şehir ışıklarının asfalttaki yansıması.',
                'desc_en' => 'Reflections of city lights on wet asphalt during a rainy evening.',
                'img' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Doğa Anatomisi',
                'title_en' => 'Nature Anatomy',
                'desc_tr' => 'Bir yaprağın üzerindeki mikro damarların geometrik yapısı.',
                'desc_en' => 'Geometric structure of micro veins on a green leaf.',
                'img' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Sessizlik',
                'title_en' => 'Quietness',
                'desc_tr' => 'Karlı bir kış gününde tek başına duran ağacın yalnızlığı.',
                'desc_en' => 'The solitude of a single tree standing in a snowy winter field.',
                'img' => 'https://images.unsplash.com/photo-1482862549707-f63cb32c5fd9?auto=format&fit=crop&w=1200&q=80',
                'type' => 'photo'
            ],
            [
                'title_tr' => 'Soyut Akış',
                'title_en' => 'Abstract Flow',
                'desc_tr' => 'Renklerin ve çizgilerin dinamik, akışkan birlikteliği.',
                'desc_en' => 'Dynamic and fluid combination of colors and lines.',
                'img' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Minimalist Çizgiler',
                'title_en' => 'Minimalist Lines',
                'desc_tr' => 'Az sayıda çizgi ile insan figürünün estetik ifadesi.',
                'desc_en' => 'Aesthetic representation of human figure with minimal lines.',
                'img' => 'https://images.unsplash.com/photo-1558591710-4b4a1ae0f04d?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Botanik İllüstrasyon',
                'title_en' => 'Botanical Illustration',
                'desc_tr' => 'Klasik tekniklerle çizilmiş detaylı çiçek desenleri.',
                'desc_en' => 'Detailed floral patterns drawn with classic techniques.',
                'img' => 'https://images.unsplash.com/photo-1579783928621-7a13d66a62d1?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Retro Fütürizm',
                'title_en' => 'Retro Futurism',
                'desc_tr' => '80\'lerin neon renkleri ve fütüristik çizgilerinin modern yorumu.',
                'desc_en' => 'Modern interpretation of 80s neon colors and futuristic lines.',
                'img' => 'https://images.unsplash.com/photo-1563089145-599997674d42?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Geometrik Rüya',
                'title_en' => 'Geometric Dream',
                'desc_tr' => 'Keskin geometrik şekillerin sürrealist bir kompozisyonu.',
                'desc_en' => 'A surrealist composition of sharp geometric shapes.',
                'img' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Klasik Portre',
                'title_en' => 'Classical Portrait',
                'desc_tr' => 'Karakalem tekniğiyle çalışılmış gölgeli bir yüz etüdü.',
                'desc_en' => 'A shaded face study worked with charcoal drawing technique.',
                'img' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Mimari Eskiz',
                'title_en' => 'Architectural Sketch',
                'desc_tr' => 'Antik bir tapınağın ince kalem çizgileriyle detaylandırılması.',
                'desc_en' => 'Detailed pen sketch of an ancient temple structure.',
                'img' => 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Sulu Boya Rüyası',
                'title_en' => 'Watercolor Dream',
                'desc_tr' => 'Yumuşak renk geçişleri ve rüya gibi sulu boya dokuları.',
                'desc_en' => 'Soft color transitions and dreamlike watercolor textures.',
                'img' => 'https://images.unsplash.com/photo-1576016770956-debb63d900ad?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Dijital Manzara',
                'title_en' => 'Digital Landscape',
                'desc_tr' => 'Vektörel çizim teknikleriyle oluşturulmuş minimalist manzara.',
                'desc_en' => 'Minimalist landscape created with vector drawing techniques.',
                'img' => 'https://images.unsplash.com/photo-1620641788421-7a1c342ea42e?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Sürreal Portre',
                'title_en' => 'Surreal Portrait',
                'desc_tr' => 'Duyguların sembolik imgelerle anlatıldığı dijital çizim.',
                'desc_en' => 'Digital drawing expressing emotions through symbolic imagery.',
                'img' => 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Karakter Tasarımı',
                'title_en' => 'Character Design',
                'desc_tr' => 'Çizgi roman ve animasyon tarzında dinamik bir karakter çalışması.',
                'desc_en' => 'Dynamic character study in comic and animation style.',
                'img' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ],
            [
                'title_tr' => 'Kentsel Keşif',
                'title_en' => 'Urban Exploration',
                'desc_tr' => 'Mürekkep ve tarama tekniğiyle oluşturulmuş detaylı sokak çizimi.',
                'desc_en' => 'Detailed street drawing created with ink and hatching technique.',
                'img' => 'https://images.unsplash.com/photo-1580136579312-94651dfd596d?auto=format&fit=crop&w=1200&q=80',
                'type' => 'drawing'
            ]
        ];

        foreach ($images as $idx => $img) {
            $mock[] = [
                'id' => $idx + 1,
                'title_tr' => $img['title_tr'],
                'title_en' => $img['title_en'],
                'description_tr' => $img['desc_tr'],
                'description_en' => $img['desc_en'],
                'image_url' => $img['img'],
                'type' => $img['type'],
                'display_order' => $idx
            ];
        }

        return $mock;
    }

    public function yonetim()
    {
        $this->checkAuth();

        $db = Db::pdo();
        $stmt = $db->query("SELECT * FROM portfolio_items ORDER BY display_order ASC, id DESC");
        $items = $stmt->fetchAll();

        $this->view('yonetim/portfolio_manager', [
            'items' => $items
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->adminPath());
            exit;
        }

        Csrf::verify();

        $titleTr       = trim($_POST['title_tr'] ?? '');
        $titleEn       = trim($_POST['title_en'] ?? '');
        $descriptionTr = trim($_POST['description_tr'] ?? '');
        $descriptionEn = trim($_POST['description_en'] ?? '');
        $type          = trim($_POST['type'] ?? 'photo');
        $displayOrder  = (int)($_POST['display_order'] ?? 0);

        if ($titleTr === '') {
            $_SESSION['error'] = 'Türkçe başlık girmek zorunludur.';
            header('Location: ' . $this->adminPath());
            exit;
        }

        if (!in_array($type, ['photo', 'drawing'], true)) {
            $type = 'photo';
        }

        if (empty($_FILES['image']['tmp_name'])) {
            $_SESSION['error'] = 'Görsel yüklemek zorunludur.';
            header('Location: ' . $this->adminPath());
            exit;
        }

        require_once ROOT . '/app/Core/Upload.php';
        $imageUrl = Upload::saveOriginalImageToR2($_FILES['image'], 'portfolio', 'port_', 20971520, $this->createSlug($titleTr));

        if (!$imageUrl) {
            $_SESSION['error'] = 'Görsel yüklenirken bir hata oluştu: ' . Upload::lastError();
            header('Location: ' . $this->adminPath());
            exit;
        }

        $db = Db::pdo();
        $stmt = $db->prepare("INSERT INTO portfolio_items (title_tr, title_en, description_tr, description_en, image_url, type, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$titleTr, $titleEn === '' ? null : $titleEn, $descriptionTr === '' ? null : $descriptionTr, $descriptionEn === '' ? null : $descriptionEn, $imageUrl, $type, $displayOrder]);

        if ($success) {
            $_SESSION['success'] = 'Portfolyo ögesi başarıyla eklendi.';
        } else {
            $_SESSION['error'] = 'Veritabanına kaydedilirken bir sorun oluştu.';
        }

        header('Location: ' . $this->adminPath());
        exit;
    }

    public function edit()
    {
        $this->checkAuth();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . $this->adminPath());
            exit;
        }

        $db = Db::pdo();
        $stmt = $db->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            header('Location: ' . $this->adminPath());
            exit;
        }

        $this->view('yonetim/portfolio_edit', ['item' => $item]);
    }

    public function update()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->adminPath());
            exit;
        }

        Csrf::verify();

        $id            = (int)($_POST['id'] ?? 0);
        $titleTr       = trim($_POST['title_tr'] ?? '');
        $titleEn       = trim($_POST['title_en'] ?? '');
        $descriptionTr = trim($_POST['description_tr'] ?? '');
        $descriptionEn = trim($_POST['description_en'] ?? '');
        $type          = trim($_POST['type'] ?? 'photo');
        $displayOrder  = (int)($_POST['display_order'] ?? 0);

        if ($id <= 0 || $titleTr === '') {
            $_SESSION['error'] = 'Geçersiz istek.';
            header('Location: ' . $this->adminPath());
            exit;
        }

        if (!in_array($type, ['photo', 'drawing'], true)) {
            $type = 'photo';
        }

        $db = Db::pdo();
        $stmt = $db->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            $_SESSION['error'] = 'Öge bulunamadı.';
            header('Location: ' . $this->adminPath());
            exit;
        }

        $imageUrl = $item['image_url'];

        if (!empty($_FILES['image']['tmp_name'])) {
            require_once ROOT . '/app/Core/Upload.php';
            $newUrl = Upload::saveOriginalImageToR2($_FILES['image'], 'portfolio', 'port_', 20971520, $this->createSlug($titleTr));
            if ($newUrl) {
                if (!empty($item['image_url'])) {
                    try {
                        require_once ROOT . '/app/Core/R2Storage.php';
                        $r2 = \App\Core\R2Storage::instance();
                        $r2->deleteFile($item['image_url']);
                    } catch (\Exception $e) {
                        error_log("Portfolio old image R2 deletion failure: " . $e->getMessage());
                    }
                }
                $imageUrl = $newUrl;
            }
        }

        $stmt = $db->prepare("UPDATE portfolio_items SET title_tr = ?, title_en = ?, description_tr = ?, description_en = ?, image_url = ?, type = ?, display_order = ? WHERE id = ?");
        $success = $stmt->execute([$titleTr, $titleEn === '' ? null : $titleEn, $descriptionTr === '' ? null : $descriptionTr, $descriptionEn === '' ? null : $descriptionEn, $imageUrl, $type, $displayOrder, $id]);

        if ($success) {
            $_SESSION['success'] = 'Portfolyo ögesi güncellendi.';
        } else {
            $_SESSION['error'] = 'Güncellenirken bir sorun oluştu.';
        }

        header('Location: ' . $this->adminPath());
        exit;
    }

    public function delete()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->adminPath());
            exit;
        }

        Csrf::verify();

        $id = (int)($_POST['id'] ?? 0);

        $db = Db::pdo();
        $stmt = $db->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            $_SESSION['error'] = 'Öge bulunamadı.';
            header('Location: ' . $this->adminPath());
            exit;
        }

        // Delete from R2 Storage
        if (!empty($item['image_url'])) {
            try {
                require_once ROOT . '/app/Core/R2Storage.php';
                $r2 = \App\Core\R2Storage::instance();
                $r2->deleteFile($item['image_url']);
            } catch (\Exception $e) {
                error_log("Portfolio image R2 deletion failure: " . $e->getMessage());
            }
        }

        // Delete from Database
        $stmt = $db->prepare("DELETE FROM portfolio_items WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            $_SESSION['success'] = 'Portfolyo ögesi başarıyla silindi.';
        } else {
            $_SESSION['error'] = 'Öge veritabanından silinirken bir hata oluştu.';
        }

        header('Location: ' . $this->adminPath());
        exit;
    }

    public function reorder()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        Csrf::verify();

        $orders = $_POST['orders'] ?? [];
        if (!is_array($orders)) {
            $orders = [];
        }

        $db = Db::pdo();

        $ids = array_keys($orders);
        if (empty($ids)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'updated' => 0]);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT id FROM portfolio_items WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $validIds = array_column($stmt->fetchAll(), 'id');
        $validIds = array_map('intval', $validIds);

        if (count($validIds) !== count($ids)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid item IDs submitted.']);
            exit;
        }

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE portfolio_items SET display_order = ? WHERE id = ?");
            foreach ($orders as $id => $orderVal) {
                if (!in_array((int)$id, $validIds, true)) continue;
                $stmt->execute([(int)$orderVal, (int)$id]);
            }
            $db->commit();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
