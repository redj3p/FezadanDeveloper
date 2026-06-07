<?php
// ─── Basic Configuration & Dynamic Localization ──────────────────────────────────────────
$siteBase    = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://fezadan.org';
$isSubdomain = $isSubdomain ?? false;   // Is it furkan.fezadan.org or local subdomain?

$lang = App::getLang();
$isEn = $lang === 'EN';

// Localization Strings
$t_all       = $isEn ? 'All Works' : 'Tüm Çalışmalar';
$t_photos    = $isEn ? 'Photography' : 'Fotoğraf';
$t_drawings  = $isEn ? 'Illustration' : 'Çizim & İllüstrasyon';
$t_noItems   = $isEn ? 'No works added yet. Check back soon.' : 'Henüz çalışma eklenmedi.';
$t_zoom      = $isEn ? 'Open Full Size' : 'Tam Boyut Aç';
$t_contact   = $isEn ? 'Get in Touch' : 'İletişime Geç';
$t_follow    = $isEn ? 'Follow' : 'Takip Et';
$t_following = $isEn ? 'Following' : 'Takip Ediliyor';
$t_works     = $isEn ? 'Works' : 'Eserler';
$t_available = $isEn ? 'Available for work' : 'Yeni projelere açık';
$t_copied    = $isEn ? 'Copied to clipboard!' : 'Bağlantı kopyalandı!';

$page_title       = 'Furkan Şen — Portfolio';
$page_description = $isEn
    ? 'Photography and illustration works by Furkan Şen.'
    : 'Furkan Şen\'in fotoğraf ve çizim çalışmaları.';
$page_canonical   = 'https://furkan.fezadan.org';
$og_url           = $page_canonical;
$og_type          = 'website';

// Subdomain needs its own header/footer, while main domain shares common layout.
if (!$isSubdomain) {
    require_once ROOT . '/app/Views/inc/header.php';
}
?>

<?php if ($isSubdomain): ?>
<!DOCTYPE html>
<html lang="<?= $isEn ? 'en' : 'tr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($og_url) ?>">
    <meta property="og:type"        content="website">
    <meta name="theme-color" content="#FEF9E1">
    <link rel="stylesheet" href="/assets/css/fonts.css">
    <link rel="stylesheet" href="/assets/css/portfolio.css">
</head>
<body class="fk-portfolio-wrapper" data-theme="light">
<?php else: ?>
    <!-- Scoped Portfolio Design System & Styling -->
    <link rel="stylesheet" href="/assets/css/portfolio.css">
<?php endif; ?>

<div class="fk-portfolio-wrapper" id="fk-portfolio-root" data-theme="light">
    <!-- ═══════════════════════════════════════════════════════════
         INTERACTIVE PARTICLES CANVAS BACKDROP
         ═══════════════════════════════════════════════════════════ -->
    <div class="portfolio-bg-animation">
        <div class="ambient-glow orb-1"></div>
        <div class="ambient-glow orb-2"></div>
        <div class="ambient-glow orb-3"></div>
        <canvas id="canvas-bg"></canvas>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SLIDESHOW SECTION (Cover, About, Thank You Slaytları)
         ═══════════════════════════════════════════════════════════ -->
    <!-- Slide 1: Typography Cover -->
    <section class="fk-slide fk-cover-slide" id="fk-slide-1">
        <div class="fk-title-stacked fk-center" style="margin-bottom: 2rem;">
            <h1 class="fk-portfolio-bg-text">portfolio</h1>
            <span class="fk-signature-stacked">furkan şen</span>
        </div>
        <a href="mailto:<?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?>" class="fk-cover-footer-link">
            <?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?>
        </a>

        <!-- Scroll down helper to Slide 2 -->
        <div class="fk-scroll-down-arrow" id="fk-scroll-to-s2" title="<?= $isEn ? 'Scroll to about' : 'Hakkımda kısmına kaydır' ?>">
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.58rem; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.6;"><?= $isEn ? 'About' : 'Hakkımda' ?></span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
        </div>
    </section>

    <!-- Slide 2: Project Portfolio Layout -->
    <section class="fk-slide" id="fk-slide-2">
        <!-- Slide Header (Top metadata) -->
        <div class="fk-slide-header">
            <span>Creative Presentation</span>
            <span><?= date('d M, Y') ?></span>
        </div>

        <div class="fk-slide2-grid">
            <!-- Top Left: Title -->
            <div class="fk-slide2-title-area">
                <h2 class="fk-slide2-title"><?= $isEn ? 'PROJECT<br>PORTFOLIO' : 'PROJE<br>PORTFOLYOSU' ?></h2>
            </div>

            <!-- Top Right: Image Frame -->
            <div class="fk-slide2-img-top-right">
                <div class="fk-slide-img-frame aspect-16-10 fk-about-portrait-frame">
                    <?php 
                    $authorImg = !empty($author['image_url']) ? htmlspecialchars(Upload::assetUrl($author['image_url']), ENT_QUOTES, 'UTF-8') : '';
                    if ($authorImg): 
                    ?>
                        <img class="fk-about-portrait-img" src="<?= $authorImg ?>" alt="<?= htmlspecialchars($author['name'] ?? 'Furkan Şen') ?>">
                    <?php else: ?>
                        <div class="fk-avatar-fallback">FŞ</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom Left: Landscape Image Frame -->
            <div class="fk-slide2-img-bottom-left">
                <div class="fk-slide-img-frame aspect-21-9">
                    <?php if (!empty($items) && isset($items[0]['image_url'])): ?>
                        <img src="<?= htmlspecialchars(Upload::assetUrl($items[0]['image_url'])) ?>" alt="Featured Artwork">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1483737186981-a657508cf3a8?auto=format&fit=crop&w=800&q=80" alt="Showcase Visual">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom Right: Text Block -->
            <div class="fk-slide2-text-area">
                <p class="fk-slide-desc-text">
                    <?= !empty($author['bio']) ? nl2br(htmlspecialchars($author['bio'])) : ($isEn ? 'Collecting moments, drawing quiet thoughts. A visual archive of aesthetics between science and art.' : 'Anları topluyor, sessiz düşünceleri çiziyorum. Bilim ve sanat arasındaki estetiğin görsel bir arşivi.') ?>
                </p>
            </div>
        </div>

        <!-- Slide Footer -->
        <div class="fk-slide-footer">
            <a href="https://github.com/shenfurkan" target="_blank" rel="noopener">@shenfurkan</a>
            <a href="https://fezadan.org" target="_blank" rel="noopener">fezadan.org</a>
            <a href="mailto:<?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?>"><?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?></a>
        </div>

        <!-- Scroll down helper to Gallery -->
        <div class="fk-scroll-down-arrow" id="fk-scroll-to-gallery" title="<?= $isEn ? 'Scroll to gallery' : 'Galeriye kaydır' ?>">
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.58rem; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.6;"><?= $isEn ? 'Gallery' : 'Galeri' ?></span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         VERTICAL GALLERY SECTION (Section 3)
         ═══════════════════════════════════════════════════════════ -->
    <section class="fk-gallery-section" id="gallery-section">
        <h2 class="fk-gallery-heading">curated works</h2>
        
        <?php if (!empty($items)):
            $photoCount   = count(array_filter($items, fn($i) => $i['type'] === 'photo'));
            $drawingCount = count(array_filter($items, fn($i) => $i['type'] === 'drawing'));
        ?>
            <!-- Filters pills navigation -->
            <nav class="fk-filters" aria-label="Category filter" role="tablist">
                <button class="fk-filter-btn active" data-filter="all" role="tab" aria-selected="true">
                    <span><?= $t_all ?></span>
                    <span class="fk-filter-badge"><?= count($items) ?></span>
                </button>
                <button class="fk-filter-btn" data-filter="photo" role="tab" aria-selected="false">
                    <span><?= $t_photos ?></span>
                    <span class="fk-filter-badge"><?= $photoCount ?></span>
                </button>
                <button class="fk-filter-btn" data-filter="drawing" role="tab" aria-selected="false">
                    <span><?= $t_drawings ?></span>
                    <span class="fk-filter-badge"><?= $drawingCount ?></span>
                </button>
            </nav>

            <!-- Responsive multi-column grid list -->
            <div class="fk-grid" id="fk-grid" role="list">
                <?php foreach ($items as $idx => $item):
                    $title   = htmlspecialchars($isEn ? ($item['title_en'] ?? $item['title_tr']) : $item['title_tr'], ENT_QUOTES, 'UTF-8');
                    $desc    = htmlspecialchars($isEn ? ($item['description_en'] ?? $item['description_tr'] ?? '') : ($item['description_tr'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $imgUrl  = htmlspecialchars(Upload::assetUrl($item['image_url']), ENT_QUOTES, 'UTF-8');
                    $typeLabel = $item['type'] === 'drawing' ? $t_drawings : $t_photos;
                ?>
                <article
                    class="fk-card"
                    data-type="<?= $item['type'] ?>"
                    data-index="<?= $idx ?>"
                    data-image="<?= $imgUrl ?>"
                    data-title="<?= $title ?>"
                    data-description="<?= $desc ?>"
                    tabindex="0"
                    role="listitem button"
                    aria-label="<?= $title ?>"
                    style="animation-delay: <?= min($idx * 60, 600) ?>ms"
                >
                    <div class="fk-card-img">
                        <img
                            src="<?= $imgUrl ?>"
                            alt="<?= $title ?>"
                            loading="<?= $idx < 4 ? 'eager' : 'lazy' ?>"
                            decoding="async"
                        >
                        <!-- Behance hover layout overlay -->
                        <div class="fk-card-overlay">
                            <span class="fk-card-overlay-tag"><?= $typeLabel ?></span>
                            <h2 class="fk-card-overlay-title"><?= $title ?></h2>
                        </div>
                        
                        <!-- Floating Zoom Icon -->
                        <div class="fk-card-zoom-icon" title="<?= $t_zoom ?>">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                            </svg>
                        </div>
                    </div>
                    <!-- Minimalist card caption bar underneath -->
                    <div class="fk-card-caption">
                        <div class="fk-card-info">
                            <h3 class="fk-card-title"><?= $title ?></h3>
                            <span class="fk-card-type"><?= $typeLabel ?></span>
                        </div>
                        <div class="fk-card-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="fk-empty"><?= $t_noItems ?></div>
        <?php endif; ?>
    </section>

    <!-- Slide 3: Thank You Layout -->
    <section class="fk-slide fk-thank-you-slide" id="fk-slide-3">
        <!-- Slide Header (Top right metadata) -->
        <div class="fk-slide-header" style="justify-content: flex-end;">
            <div class="fk-slide3-header-right">
                <span><?= date('d M, Y') ?></span>
                <span>Creative Presentation</span>
                <span>Presented By: <strong><?= htmlspecialchars($author['name'] ?? 'Furkan Şen') ?></strong></span>
            </div>
        </div>

        <div class="fk-slide3-content">
            <div class="fk-title-stacked fk-left" style="margin-bottom: 2rem;">
                <h1 class="fk-portfolio-bg-text" style="font-size: clamp(3.5rem, 10vw, 10rem); white-space: nowrap;">furkan şen</h1>
                <span class="fk-signature-stacked">thank you.</span>
            </div>
            <p class="fk-slide3-p">
                Thank you for taking the time to explore my creative portfolio. If you have any projects, ideas, or collaborations in mind, let's create something meaningful together.
            </p>
        </div>

        <!-- Slide Footer -->
        <div class="fk-slide-footer">
            <a href="https://github.com/shenfurkan" target="_blank" rel="noopener">@shenfurkan</a>
            <a href="https://fezadan.org" target="_blank" rel="noopener">fezadan.org</a>
            <a href="mailto:<?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?>"><?= htmlspecialchars($author['email'] ?? 'furkan@fezadan.org') ?></a>
            <span>Istanbul, TR</span>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         STANDALONE LOCAL FOOTER BLOCK
         ═══════════════════════════════════════════════════════════ -->
    <?php if ($isSubdomain): ?>
        <!-- Floating Theme Control Button -->
        <div style="position: fixed; bottom: 2.5rem; right: 2.5rem; z-index: 1000;">
            <button class="fk-theme-toggle" id="theme-toggle-btn" aria-label="Toggle Theme" title="Toggle Theme">
                <svg id="theme-icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg id="theme-icon-moon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
        </div>

        <footer class="fk-footer">
            <div class="fk-footer-left">© <?= date('Y') ?> Furkan Şen. <?= $isEn ? 'All rights reserved.' : 'Tüm hakları saklıdır.' ?></div>
            <div class="fk-footer-right">
                <a href="https://fezadan.org" target="_blank" rel="noopener">
                    <span>fezadan.org</span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </a>
            </div>
        </footer>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     PREMIUM FULLSCREEN LIGHTBOX PORTAL
     ═══════════════════════════════════════════════════════════ -->
<div id="fk-lightbox" class="fk-lb" aria-modal="true" role="dialog" aria-label="Image Viewer">
    <div class="fk-lb-bg" id="fk-lb-bg"></div>
    <div class="fk-lb-bar" id="fk-lb-bar" style="width:0%"></div>

    <!-- Top control bar -->
    <div class="fk-lb-topbar">
        <div class="fk-lb-topbar-title" id="fk-lb-top-title"></div>
        <div class="fk-lb-topbar-right">
            <a href="#" id="fk-lb-open" class="fk-lb-btn" target="_blank" rel="noopener" title="<?= $t_zoom ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                <span><?= $isEn ? 'Full size' : 'Tam Boyut' ?></span>
            </a>
            <button class="fk-lb-close-btn" id="fk-lb-close" aria-label="Close" title="<?= $isEn ? 'Close' : 'Kapat' ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <!-- Middle Stage -->
    <div class="fk-lb-stage">
        <div class="fk-lb-img-wrap">
            <img id="fk-lb-img" src="" alt="Active Artwork">
            <div class="fk-lb-spinner" id="fk-lb-spinner"></div>
        </div>
    </div>

    <!-- Navigations -->
    <button class="fk-lb-nav-btn fk-lb-nav-prev" id="fk-lb-prev" aria-label="Previous" title="<?= $isEn ? 'Previous' : 'Önceki' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button class="fk-lb-nav-btn fk-lb-nav-next" id="fk-lb-next" aria-label="Next" title="<?= $isEn ? 'Next' : 'Sonraki' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>

    <!-- Navigation dots strip -->
    <div class="fk-lb-dots" id="fk-lb-dots" aria-hidden="true"></div>

    <!-- Bottom Info bar -->
    <div class="fk-lb-infobar">
        <div>
            <div class="fk-lb-info-title" id="fk-lb-title"></div>
            <div class="fk-lb-info-desc" id="fk-lb-desc"></div>
        </div>
        <div class="fk-lb-info-right">
            <button class="fk-lb-copy-btn" id="fk-lb-copy">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                <span id="fk-copy-text"><?= $isEn ? 'Copy link' : 'Bağlantıyı Kopyala' ?></span>
            </button>
            <div class="fk-lb-counter" id="fk-lb-counter"></div>
        </div>
    </div>
</div>

<!-- Copy Success Toast Notification -->
<div class="fk-toast" id="fk-toast-alert" role="alert">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    <span><?= $t_copied ?></span>
</div>

<!-- ═══════════════════════════════════════════════════════════
     FRONTEND BEHAVIORS & INTERACTIVITY SCRIPTS
     ═══════════════════════════════════════════════════════════ -->
<script>
    window.FkPortfolioConfig = {
        lang: '<?= $lang ?>',
        tFollow: '<?= $t_follow ?>',
        tFollowing: '<?= $t_following ?>',
        tCopied: '<?= $t_copied ?>'
    };
</script>
<script src="/assets/js/portfolio.js" defer></script>

<?php
if ($isSubdomain): ?>
</body>
</html>
<?php else:
    require_once ROOT . '/app/Views/inc/footer.php';
endif; ?>
