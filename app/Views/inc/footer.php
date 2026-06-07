<style>
    [data-theme="dark"] .dynamic-footer-border {
        border-top-color: var(--text-main) !important;
    }
</style>

<footer class="py-10 px-6 md:px-12 flex flex-col md:flex-row justify-between items-center text-[10px] uppercase tracking-widest opacity-60 text-[var(--text-main)] border-t border-[var(--line-color)] dynamic-footer-border mt-auto">
    <div class="mb-4 md:mb-0 flex flex-col md:flex-row items-center gap-2 md:gap-4">
        <span>FEZADAN</span>

        <span class="hidden md:inline opacity-50">|</span>
        <a href="/privacy" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Privacy Policy</a>
        <span class="hidden md:inline opacity-50">|</span>
        <a href="/sitemap.xml" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Sitemap</a>
        <span class="hidden md:inline opacity-50">|</span>
        <a href="https://github.com/shenfurkan/Fezadan" rel="noopener" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Source Code</a>
        <span class="hidden md:inline opacity-50">|</span>
        <?php 
            $host = str_replace('www.', '', $_SERVER['HTTP_HOST'] ?? '');
            $portfolio_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https://' : 'http://';
            $portfolio_url .= (strpos($host, 'furkan.') === 0 ? $host : 'furkan.' . $host);
        ?>
        <a href="<?php echo $portfolio_url; ?>" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Portfolio</a>
        <span class="hidden md:inline opacity-50">|</span>
        <a href="/rss" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">RSS</a>
    </div>
    <div class="flex gap-6 items-center">
        <a href="https://www.instagram.com/fezadanorg" target="_blank" rel="noopener noreferrer" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Instagram</a>
        <a href="https://www.twitter.com/fezadanorg" target="_blank" rel="noopener noreferrer" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">Twitter</a>
        <a href="mailto:info@fezadan.org" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300">info@fezadan.org</a>
        <span class="opacity-50">|</span>
        <div class="inline-flex gap-1">
            <a href="<?= getLanguageSwitchUrl('TR') ?>" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300 <?= App::getLang() === 'TR' ? 'text-[var(--text-accent)] font-bold' : 'opacity-70' ?>">TR</a>
            <span class="opacity-30">/</span>
            <a href="<?= getLanguageSwitchUrl('EN') ?>" class="hover:text-[#E1C89E] hover:opacity-100 transition-all duration-300 <?= App::getLang() === 'EN' ? 'text-[var(--text-accent)] font-bold' : 'opacity-70' ?>">EN</a>
        </div>
    </div>
</footer>

</body>
</html>