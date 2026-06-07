document.addEventListener('DOMContentLoaded', () => {

    /* ── Image Failbacks & Monogram Generation ── */
    const avatarImg = document.querySelector('.fk-about-portrait-img');
    const avatarContainer = document.querySelector('.fk-about-portrait-frame');
    if (avatarContainer && avatarImg) {
        avatarImg.addEventListener('error', () => {
            avatarImg.style.display = 'none';
            avatarContainer.classList.add('has-fallback');
            if (!avatarContainer.querySelector('.fk-avatar-fallback')) {
                const fallback = document.createElement('div');
                fallback.className = 'fk-avatar-fallback';
                fallback.textContent = 'FŞ';
                avatarContainer.appendChild(fallback);
            }
        });
        if (avatarImg.complete && avatarImg.naturalWidth === 0) {
            avatarImg.dispatchEvent(new Event('error'));
        }
    }

    /* ── Light / Dark Theme Toggle Controller ── */
    const themeBtn = document.getElementById('theme-toggle-btn');
    const rootWrapper = document.getElementById('fk-portfolio-root');
    const sunIcon = document.getElementById('theme-icon-sun');
    const moonIcon = document.getElementById('theme-icon-moon');
    
    if (themeBtn && rootWrapper) {
        let activeTheme = localStorage.getItem('fk-portfolio-theme') || 'light';
        
        const applyTheme = (theme) => {
            rootWrapper.setAttribute('data-theme', theme);
            if (theme === 'dark') {
                if (sunIcon) sunIcon.style.display = 'block';
                if (moonIcon) moonIcon.style.display = 'none';
                const metaTheme = document.querySelector('meta[name="theme-color"]');
                if (metaTheme) metaTheme.setAttribute('content', '#120707');
            } else {
                if (sunIcon) sunIcon.style.display = 'none';
                if (moonIcon) moonIcon.style.display = 'block';
                const metaTheme = document.querySelector('meta[name="theme-color"]');
                if (metaTheme) metaTheme.setAttribute('content', '#FEF9E1');
            }
        };

        applyTheme(activeTheme);

        themeBtn.addEventListener('click', () => {
            activeTheme = activeTheme === 'light' ? 'dark' : 'light';
            localStorage.setItem('fk-portfolio-theme', activeTheme);
            applyTheme(activeTheme);
        });
    }

    /* ── Smooth Scrolling for Vertical Slides ── */
    const scrollToS2Btn = document.getElementById('fk-scroll-to-s2');
    const scrollToGalleryBtn = document.getElementById('fk-scroll-to-gallery');

    const s2Target = document.getElementById('fk-slide-2');
    const galleryTarget = document.getElementById('gallery-section');

    if (scrollToS2Btn && s2Target) {
        scrollToS2Btn.addEventListener('click', () => {
            s2Target.scrollIntoView({ behavior: 'smooth' });
        });
    }
    if (scrollToGalleryBtn && galleryTarget) {
        scrollToGalleryBtn.addEventListener('click', () => {
            galleryTarget.scrollIntoView({ behavior: 'smooth' });
        });
    }

    /* ── Dynamic Follow Action Button ── */
    const followBtn = document.getElementById('fk-follow-btn');
    const followSvg = document.getElementById('fk-follow-svg');
    const followText = document.getElementById('fk-follow-text');
    if (followBtn) {
        let isFollowing = localStorage.getItem('isFollowingFurkan') === 'true';
        
        const updateFollowState = (animate) => {
            const config = window.FkPortfolioConfig || {};
            const tFollowing = config.tFollowing || 'Following';
            const tFollow = config.tFollow || 'Follow';

            if (isFollowing) {
                if (followText) followText.textContent = tFollowing;
                followBtn.classList.add('is-active');
                followBtn.style.background = 'var(--accent-glow)';
                followBtn.style.borderColor = 'var(--accent)';
                followBtn.style.color = 'var(--accent)';
                if (followSvg) {
                    followSvg.style.fill = 'var(--accent)';
                    if (animate) {
                        followSvg.style.transform = 'scale(1.3)';
                        setTimeout(() => followSvg.style.transform = '', 250);
                    }
                }
            } else {
                if (followText) followText.textContent = tFollow;
                followBtn.classList.remove('is-active');
                followBtn.style.background = '';
                followBtn.style.borderColor = '';
                followBtn.style.color = '';
                if (followSvg) {
                    followSvg.style.fill = 'none';
                }
            }
        };

        updateFollowState(false);

        followBtn.addEventListener('click', () => {
            isFollowing = !isFollowing;
            localStorage.setItem('isFollowingFurkan', isFollowing);
            updateFollowState(true);
        });
    }

    /* ── IntersectionObserver: Card Entrance Animations ── */
    const allCards = Array.from(document.querySelectorAll('.fk-card'));
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('in-view');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.04, rootMargin: '0px 0px -20px 0px' });
    allCards.forEach(c => io.observe(c));

    /* ── Gallery Filters Mechanism ── */
    const filterBtns = document.querySelectorAll('.fk-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => { 
                b.classList.remove('active'); 
                b.setAttribute('aria-selected','false'); 
            });
            btn.classList.add('active');
            btn.setAttribute('aria-selected','true');
            
            const filter = btn.dataset.filter;
            
            allCards.forEach(card => {
                const isMatch = filter === 'all' || card.dataset.type === filter;
                if (isMatch) {
                    card.classList.remove('filtered-out');
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                } else {
                    card.classList.add('filtered-out');
                }
            });
        });
    });

    /* ── Fullscreen Lightbox system ── */
    const lb         = document.getElementById('fk-lightbox');
    const lbImg      = document.getElementById('fk-lb-img');
    const lbTitle    = document.getElementById('fk-lb-title');
    const lbDesc     = document.getElementById('fk-lb-desc');
    const lbTopTitle = document.getElementById('fk-lb-top-title');
    const lbCounter  = document.getElementById('fk-lb-counter');
    const lbBar      = document.getElementById('fk-lb-bar');
    const lbDots     = document.getElementById('fk-lb-dots');
    const lbSpinner  = document.getElementById('fk-lb-spinner');
    const lbOpen     = document.getElementById('fk-lb-open');
    const lbClose    = document.getElementById('fk-lb-close');
    const lbBg       = document.getElementById('fk-lb-bg');
    const lbPrev     = document.getElementById('fk-lb-prev');
    const lbNext     = document.getElementById('fk-lb-next');
    const lbCopy     = document.getElementById('fk-lb-copy');
    const toast      = document.getElementById('fk-toast-alert');

    let activeIdx  = -1;
    let visible    = [];
    let touchX     = 0;

    const getVisible = () => allCards.filter(c => !c.classList.contains('filtered-out'));

    const buildDots = (total, current) => {
        if (!lbDots) return;
        lbDots.innerHTML = '';
        if (total <= 1) return;
        const maxDots = 10;
        const show = Math.min(total, maxDots);
        for (let i = 0; i < show; i++) {
            const d = document.createElement('div');
            d.className = 'fk-lb-dot' + (i === current ? ' active' : '');
            lbDots.appendChild(d);
        }
    };

    const showItem = (idx) => {
        if (idx < 0 || idx >= visible.length) return;
        activeIdx = idx;

        const card   = visible[idx];
        const imgUrl = card.dataset.image;
        const title  = card.dataset.title;
        const desc   = card.dataset.description;

        if (lbImg) lbImg.classList.remove('loaded');
        if (lbSpinner) lbSpinner.style.display = 'block';

        if (lbTitle) lbTitle.textContent    = title;
        if (lbDesc) lbDesc.textContent     = desc || 'Visual Art by Furkan Şen';
        if (lbTopTitle) lbTopTitle.textContent = title;
        if (lbOpen) lbOpen.href            = imgUrl;
        if (lbCounter) lbCounter.textContent  = (idx + 1) + ' / ' + visible.length;

        const pct = visible.length > 1 ? ((idx + 1) / visible.length) * 100 : 100;
        if (lbBar) lbBar.style.width = pct + '%';

        buildDots(visible.length, idx);

        const img = new Image();
        img.onload = img.onerror = () => {
            if (lbImg) {
                lbImg.src = imgUrl;
                lbImg.alt = title;
            }
            if (lbSpinner) lbSpinner.style.display = 'none';
            requestAnimationFrame(() => {
                if (lbImg) lbImg.classList.add('loaded');
            });
        };
        img.src = imgUrl;

        if (lbPrev) lbPrev.style.display = visible.length > 1 ? 'flex' : 'none';
        if (lbNext) lbNext.style.display = visible.length > 1 ? 'flex' : 'none';
    };

    const openLightbox = (card) => {
        visible   = getVisible();
        activeIdx = visible.indexOf(card);
        if (lb) lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        showItem(activeIdx);
        if (lbClose) lbClose.focus();
    };

    const closeLightbox = () => {
        if (lb) lb.classList.remove('open');
        document.body.style.overflow = '';
        setTimeout(() => { 
            if (lbImg) {
                lbImg.src = ''; 
                lbImg.classList.remove('loaded'); 
            }
            if (lbBar) lbBar.style.width = '0%'; 
        }, 300);
        activeIdx = -1;
    };

    const showPrev = () => showItem((activeIdx - 1 + visible.length) % visible.length);
    const showNext = () => showItem((activeIdx + 1) % visible.length);

    allCards.forEach(card => {
        card.addEventListener('click', () => openLightbox(card));
        card.addEventListener('keydown', e => { 
            if (e.key === 'Enter' || e.key === ' ') { 
                e.preventDefault(); 
                openLightbox(card); 
            } 
        });
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbBg) lbBg.addEventListener('click', closeLightbox);
    if (lbPrev) lbPrev.addEventListener('click', showPrev);
    if (lbNext) lbNext.addEventListener('click', showNext);

    document.addEventListener('keydown', e => {
        if (!lb || !lb.classList.contains('open')) return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  showPrev();
        if (e.key === 'ArrowRight') showNext();
    });

    if (lb) {
        lb.addEventListener('touchstart', e => { touchX = e.changedTouches[0].clientX; }, { passive: true });
        lb.addEventListener('touchend',   e => {
            const dx = e.changedTouches[0].clientX - touchX;
            if (Math.abs(dx) > 60) {
                dx < 0 ? showNext() : showPrev();
            }
        }, { passive: true });
    }

    if (lbCopy) {
        lbCopy.addEventListener('click', () => {
            if (activeIdx < 0) return;
            const link = visible[activeIdx].dataset.image;
            
            navigator.clipboard.writeText(link).then(() => {
                if (toast) {
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 2500);
                }
            }).catch(err => {
                console.error('Could not copy link: ', err);
            });
        });
    }

    // ─── Space Constellations Backdrop Animation ───
    class SpaceParticles {
        constructor(canvasId) {
            this.canvas = document.getElementById(canvasId);
            if (!this.canvas) return;
            this.ctx = this.canvas.getContext('2d');
            this.particles = [];
            this.mouse = { x: null, y: null, radius: 180 };
            this.particleCount = 50;
            this.maxDistance = 120;
            
            this.init();
            this.animate();
            
            window.addEventListener('resize', () => this.init());
            
            document.addEventListener('mousemove', (e) => {
                this.mouse.x = e.clientX;
                this.mouse.y = e.clientY;
            });
            document.addEventListener('mouseleave', () => {
                this.mouse.x = null;
                this.mouse.y = null;
            });
        }
        
        init() {
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;
            this.particles = [];
            
            const area = this.canvas.width * this.canvas.height;
            this.particleCount = Math.min(Math.floor(area / 16000), 100);
            
            for (let i = 0; i < this.particleCount; i++) {
                this.particles.push(new Particle(this.canvas.width, this.canvas.height));
            }
        }
        
        animate() {
            const isDark = rootWrapper && rootWrapper.getAttribute('data-theme') === 'dark';
            const r = isDark ? 244 : 109;
            const g = isDark ? 63 : 35;
            const b = isDark ? 94 : 35;
            const particleColor = `rgba(${r}, ${g}, ${b}, ${isDark ? 0.25 : 0.15})`;

            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            for (let i = 0; i < this.particles.length; i++) {
                this.particles[i].update(this.canvas.width, this.canvas.height);
                this.particles[i].draw(this.ctx, particleColor);
                
                if (this.mouse.x !== null) {
                    const dx = this.mouse.x - this.particles[i].x;
                    const dy = this.mouse.y - this.particles[i].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < this.mouse.radius) {
                        const alpha = (1 - dist / this.mouse.radius) * 0.12;
                        this.ctx.strokeStyle = isDark ? `rgba(251, 191, 36, ${alpha})` : `rgba(197, 168, 128, ${alpha})`;
                        this.ctx.lineWidth = 1.0;
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.mouse.x, this.mouse.y);
                        this.ctx.lineTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.stroke();
                        
                        this.particles[i].x += dx * 0.005;
                        this.particles[i].y += dy * 0.005;
                    }
                }
                
                for (let j = i + 1; j < this.particles.length; j++) {
                    const dx = this.particles[i].x - this.particles[j].x;
                    const dy = this.particles[i].y - this.particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    
                    if (dist < this.maxDistance) {
                        const alpha = (1 - dist / this.maxDistance) * 0.06;
                        this.ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                        this.ctx.lineWidth = 0.5;
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                        this.ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(() => this.animate());
        }
    }

    class Particle {
        constructor(w, h) {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.vx = (Math.random() - 0.5) * 0.35;
            this.vy = (Math.random() - 0.5) * 0.35;
            this.radius = Math.random() * 2 + 0.8;
        }
        
        update(w, h) {
            this.x += this.vx;
            this.y += this.vy;
            
            if (this.x < 0 || this.x > w) this.vx = -this.vx;
            if (this.y < 0 || this.y > h) this.vy = -this.vy;
        }
        
        draw(ctx, color) {
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    new SpaceParticles('canvas-bg');

});
