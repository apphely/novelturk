</main>

<?php webnovel_render_ad('footer'); ?>


<style>
@media (max-width: 768px) {
    .nt-footer {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
        padding-bottom: 2rem !important;
    }
    .nt-footer-text {
        text-align: center !important;
        margin-bottom: 1rem !important;
    }
    .nt-footer-copyright {
        text-align: center !important;
        margin: 0 !important;
    }
}
@media (min-width: 769px) {
    .nt-footer {
        padding-left: 2rem !important;
        padding-right: 2rem !important;
        padding-bottom: 2rem !important;
    }
}
</style>

<footer class="bg-accent text-white text-center nt-footer" style="background-color: var(--accent); padding-bottom: 2rem; padding-left: 4rem; padding-right: 4rem;">
    <div class="max-w-screen-xl mx-auto py-4 px-2">
        <!-- Logo Section -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <a href="<?php echo home_url(); ?>">
                <img src="https://blogger.googleusercontent.com/img/a/AVvXsEgm71FrQ0cBNrdwoaTb_2qWAyNu0uGyhSxhtvojgN6LUM4Lv-bGwg8bVvpZK7dZkZ8Td7E42YYQRtcWlasu4Ox9Iv-PHv2EL0mW6U-4PV15SVJdnziS0TWL3w5iuCIKUIrR2cxdtyHmx16CFi59zm5M6AzAiZ2NT6ALM--lcIa9K5jx9YqihLPw396HzD6r=s500" alt="<?php bloginfo('name'); ?>" style="height: 3.5rem; width: auto; max-width: 200px;">
            </a>
            <div style="text-align: left;">
                <h1 style="font-size: 1.875rem; font-weight: bold; margin: 0; position: relative;">
                    <a href="<?php echo home_url(); ?>" style="color: white; text-decoration: none;">NT Novel Türk</a><sup style="color: #fecc00; font-size: 0.875rem; position: absolute; top: 0;">v2</sup>
                </h1>
                <p style="color: #fecc00; margin: 0; font-size: 0.875rem;">Türkçe Novel Oku</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav style="margin-bottom: 1.5rem;">
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 1.25rem;">
                <li><a href="<?php echo home_url('/novel-evrenleri/'); ?>" style="color: white; text-decoration: none;">Novel Evrenleri</a></li>
                <li><a href="<?php echo home_url('/alfabetik-liste/'); ?>" style="color: white; text-decoration: none;">Alfabetik Liste</a></li>
                <li><a href="<?php echo home_url('/destek/'); ?>" style="color: white; text-decoration: none;">Destek</a></li>
                <li><a href="<?php echo home_url('/terimler/'); ?>" style="color: white; text-decoration: none;">Terimler</a></li>
                <li><a href="<?php echo home_url('/hakkimda/'); ?>" style="color: white; text-decoration: none;">Hakkımda</a></li>
                <li><a href="<?php echo home_url('/iletisim/'); ?>" style="color: white; text-decoration: none;">İletişim</a></li>
                <li><a href="<?php echo home_url('/manga-webtoon-iyilestirme/'); ?>" style="color: white; text-decoration: none;">Manga/Webtoon İyileştirilmiş Sahneler</a></li>
            </ul>
        </nav>

        <!-- Policy Links -->
        <nav style="margin-bottom: 1.5rem;">
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 1.25rem;">
                <li><a href="<?php echo home_url('/gizlilik-politikasi/'); ?>" style="color: white; text-decoration: none;">Gizlilik Politikası</a></li>
                <li><a href="<?php echo home_url('/kullanim-kosullari/'); ?>" style="color: white; text-decoration: none;">Kullanım Koşulları</a></li>
                <li><a href="<?php echo home_url('/cerez-politikasi/'); ?>" style="color: white; text-decoration: none;">Çerez Politikası</a></li>
            </ul>
        </nav>

        <!-- Legal Disclaimer -->
        <p class="nt-footer-text" style="opacity: 0.5; margin-bottom: 1rem; font-size: 0.875rem; line-height: 1.6; text-align: center;">
            Yasal Uyarı: <?php bloginfo('name'); ?> sunucumuzda herhangi bir dosya saklamaz, yalnızca 3. taraf hizmetlerde barındırılan medyaya bağlantı veririz. Türkiye'de resmî olarak satışa sunulan novel ve benzeri eserleri satın alarak, ilgili sanatçılara ve yayıncılara destek olmanızı önemle tavsiye ederiz. Buradaki içerikler, orijinal eserlerin tanıtımına katkı sağlama amacı taşımaktadır. Tüm içerikler, bağlı olmayan üçüncü şahıslar tarafından sağlanmaktadır. Telif haklarını ihlal ettiğini düşündüğünüz herhangi bir içerikle karşılaşmanız durumunda, lütfen benimle iletişime geçiniz.<br>
            <?php bloginfo('name'); ?> - Türkçe Novel Oku
        </p>

        <!-- Copyright -->
        <p class="nt-footer-copyright" style="opacity: 0.5; margin: 0; font-size: 0.875rem; text-align: center;">
            Telif Hakkı &copy; 2023-<script>document.write(new Date().getFullYear())</script> <?php bloginfo('name'); ?>
        </p>
    </div>
</footer>

<!-- NovelTurk Interactions -->
<script>
(function() {
    'use strict';

    // --- Dark Mode Toggle ---
    var themeToggleBtn = document.getElementById('theme-toggle');
    var darkIcon = document.getElementById('theme-toggle-dark-icon');
    var lightIcon = document.getElementById('theme-toggle-light-icon');

    function updateToggleIcons() {
        if (themeToggleBtn && darkIcon && lightIcon) {
            if (document.documentElement.classList.contains('dark')) {
                darkIcon.style.display = 'flex';
                lightIcon.style.display = 'none';
            } else {
                darkIcon.style.display = 'none';
                lightIcon.style.display = 'flex';
            }
        }
    }
    updateToggleIcons();

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('color-theme', 'dark');
            } else {
                localStorage.setItem('color-theme', 'light');
            }
            updateToggleIcons();
        });
    }

    // --- Mobile Menu ---
    var mobileToggle = document.getElementById('mobile-menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    var mobileClose = document.getElementById('mobile-menu-close');
    var mobileOverlay = document.getElementById('mobile-menu-overlay');

    function openMobileMenu() {
        if (mobileMenu) mobileMenu.classList.add('active');
        if (mobileOverlay) mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        if (mobileMenu) mobileMenu.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openMobileMenu);
    if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileMenu);

    // --- Mobile Inline Search ---
    var mobileSearchBtn   = document.getElementById('mobile-search-toggle');
    var mobileSearchBar   = document.getElementById('mobile-search-bar');
    var mobileSearchBack  = document.getElementById('mobile-search-bar-back');
    var mobileSearchInput = document.getElementById('mobile-search-bar-input');

    function openMobileSearch() {
        if (!mobileSearchBar) return;
        mobileSearchBar.removeAttribute('hidden');
        mobileSearchBar.classList.add('is-active');
        if (mobileSearchBtn) mobileSearchBtn.setAttribute('aria-expanded', 'true');
        // Focus input after the slide-in transition
        setTimeout(function () { if (mobileSearchInput) mobileSearchInput.focus(); }, 50);
    }
    function closeMobileSearch() {
        if (!mobileSearchBar) return;
        mobileSearchBar.classList.remove('is-active');
        mobileSearchBar.setAttribute('hidden', '');
        if (mobileSearchBtn) mobileSearchBtn.setAttribute('aria-expanded', 'false');
    }

    if (mobileSearchBtn)  mobileSearchBtn.addEventListener('click', openMobileSearch);
    if (mobileSearchBack) mobileSearchBack.addEventListener('click', closeMobileSearch);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileSearchBar && mobileSearchBar.classList.contains('is-active')) {
            closeMobileSearch();
        }
    });

    // --- Bookmark Badge Count ---
    try {
        var follows = JSON.parse(localStorage.getItem('webnovel_follows') || '{}');
        var count = Object.keys(follows).length;
        var badge = document.getElementById('bookmark-count-badge');
        if (badge && count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else if (badge) {
            badge.style.display = 'none';
        }
    } catch(e) {}
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
