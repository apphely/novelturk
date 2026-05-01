</main>

<?php webnovel_render_ad('footer'); ?>

<?php if (is_front_page()) : ?>
<!-- Site Yorumları Section -->
<div id="SiteYorumlari" style="padding:40px 20px; border-top:2px solid var(--border);">
    <div class="nt-container" style="max-width:900px; margin:0 auto;">
        <div class="comments-area" style="margin-top:0;">
            <h3 class="comments-title" style="font-size:20px; font-weight:700; margin-bottom:24px; color:var(--text-main);">💬 Site Yorumları</h3>

            <?php
            $site_comments = get_comments(array(
                'post_id' => 0,
                'status' => 'approve'
            ));

            if (!empty($site_comments)) {
                ?>
                <ul class="comment-list" style="list-style:none; padding:0; margin:0; margin-bottom:32px;">
                    <?php
                    foreach ($site_comments as $comment) {
                        $GLOBALS['comment'] = $comment;
                        $is_admin = user_can($comment->user_id, 'manage_options');
                        $likes = (int)get_comment_meta($comment->comment_ID, '_comment_likes', true);
                        ?>
                        <li id="comment-<?php echo $comment->comment_ID; ?>" style="margin-bottom:16px;">
                            <article id="div-comment-<?php echo $comment->comment_ID; ?>" class="comment-body" style="display:flex; gap:16px; background:var(--bg-card); padding:16px; border-radius:8px; border:1px solid var(--border);">
                                <div class="comment-author vcard" style="flex-shrink:0;">
                                    <?php echo get_avatar($comment, 48, '', '', array('style'=>'border-radius:50%;')); ?>
                                </div>
                                <div class="comment-content" style="flex:1;">
                                    <div class="comment-meta" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                        <div style="font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                                            <?php echo get_comment_author_link($comment); ?>
                                            <?php if($is_admin): ?>
                                                <span style="background:#2563eb; color:#fff; font-size:11px; padding:2px 6px; border-radius:4px;">Yönetici</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:12px; color:var(--text-dim);">
                                            <?php echo get_comment_date('d.m.Y H:i', $comment); ?>
                                        </div>
                                    </div>
                                    <div style="font-size:14px; line-height:1.6; color:var(--text-main);">
                                        <?php echo wp_kses_post(nl2br($comment->comment_content)); ?>
                                    </div>
                                </div>
                            </article>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            }
            ?>

            <?php
            comment_form(array(
                'post_id' => 0,
                'title_reply' => '<span style="font-size:18px; font-weight:700; color:var(--text-main);">Bir Yorum Bırak</span>',
                'class_form' => 'comment-form-custom',
                'comment_notes_before' => '',
                'submit_button' => '<button type="submit" id="%2$s" class="%3$s" style="background:#2563eb; color:#fff; border:none; padding:8px 24px; border-radius:8px; font-weight:700; cursor:pointer;">%4$s</button>',
                'submit_field' => '<p class="form-submit" style="margin-top:16px;">%1$s %2$s</p>',
                'comment_field' => '<p class="comment-form-comment" style="margin-top:12px; margin-bottom:12px;"><textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required="required" placeholder="Ne düşünüyorsun?" style="width:100%; border-radius:8px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main); padding:12px; outline:none; resize:vertical;"></textarea></p>',
            ));
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="bg-accent text-white text-center" style="background-color: var(--accent);">
    <div class="max-w-screen-xl mx-auto py-8 px-2">
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
        <p style="opacity: 0.5; margin-bottom: 1rem; font-size: 0.875rem; line-height: 1.6;">
            Yasal Uyarı: <?php bloginfo('name'); ?> sunucumuzda herhangi bir dosya saklamaz, yalnızca 3. taraf hizmetlerde barındırılan medyaya bağlantı veririz. Türkiye'de resmî olarak satışa sunulan novel ve benzeri eserleri satın alarak, ilgili sanatçılara ve yayıncılara destek olmanızı önemle tavsiye ederiz. Buradaki içerikler, orijinal eserlerin tanıtımına katkı sağlama amacı taşımaktadır. Tüm içerikler, bağlı olmayan üçüncü şahıslar tarafından sağlanmaktadır. Telif haklarını ihlal ettiğini düşündüğünüz herhangi bir içerikle karşılaşmanız durumunda, lütfen benimle iletişime geçiniz.<br>
            <?php bloginfo('name'); ?> - Türkçe Novel Oku
        </p>

        <!-- Copyright -->
        <p style="opacity: 0.5; margin: 0; font-size: 0.875rem;">
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
