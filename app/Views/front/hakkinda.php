<?php
$lang = App::getLang();
$isEn = ($lang === 'EN');

$siteBase = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://fezadan.org';
$page_title       = $isEn ? 'About | FEZADAN' : 'Hakkında | FEZADAN';
$page_description = $isEn 
    ? 'About FEZADAN — a declaration, team and goals of an independent publication on science, aesthetics, and thought.'
    : 'FEZADAN hakkında — bilim, estetik ve fikir üzerine bağımsız bir yayının manifestosu, ekibi ve amaçları.';
$page_canonical   = $siteBase . '/hakkinda';
$og_url           = $page_canonical;
require_once ROOT . '/app/Views/inc/header.php';
?>

<style>
    .grid-layout {
        display: grid;
        grid-template-columns: 1fr;
        min-height: 80vh;
        border-bottom: 1px solid var(--line-color);
    }
    @media (min-width: 1024px) {
        .grid-layout { grid-template-columns: 40% 60%; }
    }
    
    .sidebar {
        position: relative;
        overflow: hidden;
        background-color: var(--bg-secondary);
        border-right: 1px solid var(--text-main);
        padding: 4rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    @media (max-width: 1023px) { 
        .sidebar { border-right: none; border-bottom: 1px solid var(--text-main); } 
    }

    .content-area {
        padding: 4rem 2rem;
        max-width: 800px;
    }

    .animate-spin-slow { animation: spin 10s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    /* Gece modu için sadece ana ızgara çizgisi kalibrasyonu */
    [data-theme="dark"] .grid-layout {
        border-color: var(--text-main) !important;
    }
</style>

<main id="main-content" class="flex-grow w-full max-w-[1920px] mx-auto">
    
    <div class="grid-layout">
        <aside class="sidebar">
            <canvas id="hakkinda-canvas" class="absolute inset-0 w-full h-full pointer-events-none z-0 opacity-25"></canvas>
            <div class="relative z-10 flex flex-col justify-between h-full w-full">
                <div>
                    <h1 class="font-syne text-5xl md:text-7xl font-bold uppercase leading-[0.9] text-[var(--text-main)] mb-6">
                        <?= $isEn ? 'Mission<br>Statement' : 'Misyon<br>Tanımı' ?>
                    </h1>
                    <div class="w-16 h-1 bg-[var(--text-main)] opacity-50 mb-6"></div>
                </div>
                
                <div class="mt-12 hidden lg:block">
                    <svg width="100" height="100" viewBox="0 0 100 100" class="animate-spin-slow text-[var(--text-main)] opacity-70">
                        <path d="M0,50 a50,50 0 1,1 0,1 z" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="10 5"/>
                        <circle cx="50" cy="50" r="10" fill="currentColor"/>
                    </svg>
                </div>
            </div>
        </aside>

        <article class="content-area">
            <div class="space-y-6 text-lg leading-relaxed opacity-90 text-[var(--text-main)]">
                <?php if ($isEn): ?>
                <p>
                    fezadan.org is an autonomous digital resistance space built on the principles of open source philosophy and the freedom of knowledge, standing against surveillance capitalism. Today, the internet is dominated by surveillance capitalism, which unilaterally usurps private human experience and converts it into behavioral data for profit, viewing users as commodities for sale. We completely reject this hierarchical panopticon system established by corporations through surveillance.
                </p>
                <p>
                    At the heart of our philosophy lie two great digital freedom legacies: First, as Aaron Swartz advocated in the "Guerilla Open Access Manifesto" against greedy entities that lock up all scientific and cultural heritage of humanity in private property, the fact that sharing knowledge is not a theft, but a moral imperative. Second is the principle of "transparency for the strong, privacy for the weak" upon which the Cypherpunk movement is based. As the Cypherpunk manifesto emphasizes, we cannot expect governments or giant, faceless corporations to grant us privacy and freedom out of their own goodwill; we must defend our privacy ourselves with the code we write, the texts we author, and the open systems we build.
                </p>
                <?php else: ?>
                <p>
                    fezadan.org, açık kaynak felsefesi ve bilginin özgürlüğü ilkeleri üzerine inşa edilmiş, gözetim kapitalizmine karşı duran özerk bir dijital direniş uzayıdır. Günümüzde internet, özel insan deneyimini tek taraflı olarak gasp edip kâr amaçlı davranışsal veriye dönüştüren, kullanıcıları satılık birer meta olarak gören gözetim kapitalizminin tahakkümü altındadır. Bizler, şirketlerin gözetim yoluyla kurduğu bu hiyerarşik panoptikon sistemini tümüyle reddediyoruz.
                </p>
                <p>
                    Felsefemizin merkezinde iki büyük dijital özgürlük mirası yatmaktadır: İnsanlığın tüm bilimsel ve kültürel mirasını özel mülkiyete hapseden açgözlü yapılara karşı Aaron Swartz'ın "Gerilla Açık Erişim Manifestosu"nda savunduğu üzere, bilgiyi paylaşmanın bir hırsızlık değil, ahlaki bir zorunluluk olduğu gerçeğidirİkincisi ise Cypherpunk hareketinin temel aldığı "güçlüler için şeffaflık, zayıflar için mahremiyet" ilkesidir Şifrepunk manifestosunun vurguladığı gibi, hükümetlerin veya dev, yüzsüz şirketlerin kendi lütuflarıyla bize mahremiyet ve özgürlük bahşetmesini bekleyemeyiz; kendi mahremiyetimizi kendi yazdığımız kodlarla, yazdığımız yazılarla, kurduğumuz açık sistemlerle bizzat kendimiz savunmalıyız.
                </p>
                <?php endif; ?>
            </div>

            <div class="mt-16">
                <h3 class="font-syne text-xl font-bold mb-4 text-[var(--text-main)]">
                    <?= $isEn ? 'CONTACT CHANNELS' : 'İLETİŞİM KANALLARI' ?>
                </h3>
                <div class="flex flex-col gap-2 text-sm font-mono text-[var(--text-main)]">
                    <a href="mailto:info@fezadan.org" class="hover:text-[var(--text-accent)] transition-colors">→ info@fezadan.org</a>
                    <a href="https://www.twitter.com/fezadanorg" class="hover:text-[var(--text-accent)] transition-colors">→ twitter.com/fezadanorg</a>
                    <a href="https://www.instagram.com/fezadanorg/" class="hover:text-[var(--text-accent)] transition-colors">→ instagram.com/fezadanorg</a>
                </div>
            </div>
        </article>
    </div>

</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById('hakkinda-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    let width = canvas.width = canvas.parentElement.offsetWidth;
    let height = canvas.height = canvas.parentElement.offsetHeight;
    
    const resizeObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
            width = canvas.width = entry.contentRect.width;
            height = canvas.height = entry.contentRect.height;
        }
    });
    resizeObserver.observe(canvas.parentElement);
    
    const particles = [];
    const maxParticles = 35;
    
    function getThemeColor() {
        const style = getComputedStyle(document.documentElement);
        return style.getPropertyValue('--text-main').trim() || '#6D2323';
    }
    
    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.35;
            this.vy = (Math.random() - 0.5) * 0.35;
            this.radius = Math.random() * 2 + 1.2;
        }
        
        update() {
            this.x += this.vx;
            this.y += this.vy;
            
            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = getThemeColor();
            ctx.fill();
        }
    }
    
    for (let i = 0; i < maxParticles; i++) {
        particles.push(new Particle());
    }
    
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        const color = getThemeColor();
        
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
            
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                
                if (dist < 110) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = color;
                    ctx.globalAlpha = (1 - (dist / 110)) * 0.18;
                    ctx.lineWidth = 0.7;
                    ctx.stroke();
                    ctx.globalAlpha = 1.0;
                }
            }
        }
        
        requestAnimationFrame(animate);
    }
    
    animate();
});
</script>

<?php 
require_once ROOT . '/app/Views/inc/footer.php'; 
?>
