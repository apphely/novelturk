<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php wp_head(); ?>
    <script>
        // Dark mode initialization (before paint)
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- NovelTurk-Style Navbar -->
<style>
    .site-header {
        position: sticky;
        top: 0;
        z-index: 999;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    <?php if (is_singular('chapter')) : ?>
    .site-header {
        position: static;
        top: auto;
    }
    <?php endif; ?>
</style>
<nav class="site-header bg-accent dark:bg-gray-950" id="site-header">
    <div class="header-container">
        <!-- Mobil: Sol taraftaki Arama butonu (mobil arama çubuğunu açar) -->
        <button class="header-icon-btn mobile-search-toggle" id="mobile-search-toggle" type="button" aria-label="Ara" aria-controls="mobile-search-bar" aria-expanded="false">
            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        </button>

        <!-- Logo -->
        <a href="<?php echo home_url(); ?>" class="site-logo">
            <?php 
            $logo_id = get_option('webnovel_logo');
            $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
            if ($logo_url) : 
            ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="custom-logo">
            <?php elseif (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <span class="logo-text"><?php echo get_bloginfo('name'); ?></span>
            <?php endif; ?>
        </a>

        <!-- Desktop Search -->
        <div class="header-search-wrap" id="header-search-wrap">
            <form class="search-form" action="<?php echo home_url(); ?>" method="get" role="search">
                <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                <input type="text" name="s" placeholder="Novel ara..." value="<?php echo get_search_query(); ?>" autocomplete="off">
                <input type="hidden" name="post_type" value="novel">
                <button type="button" class="search-close" id="search-close" aria-label="Kapat">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                </button>
            </form>
            <div class="live-search-results" id="live-search-results" hidden></div>
        </div>

        <!-- Right Actions -->
        <div class="header-actions">
            <!-- Kitaplık (Bookmarks) Dropdown -->
            <div class="header-dropdown-wrap">
                <button class="header-icon-btn" id="kitaplik-btn" title="Kitaplık" type="button">
                    <span class="badge" id="bookmark-count-badge" style="display:none;">0</span>
                    <svg height="20" viewBox="0 0 24 24" width="20" fill="currentColor"><path clip-rule="evenodd" d="M21 11.098v4.993c0 3.096 0 4.645-.734 5.321c-.35.323-.792.526-1.263.58c-.987.113-2.14-.907-4.445-2.946c-1.02-.901-1.529-1.352-2.118-1.47a2.225 2.225 0 0 0-.88 0c-.59.118-1.099.569-2.118 1.47c-2.305 2.039-3.458 3.059-4.445 2.945a2.238 2.238 0 0 1-1.263-.579C3 20.736 3 19.188 3 16.091v-4.994C3 6.81 3 4.666 4.318 3.333C5.636 2 7.758 2 12 2c4.243 0 6.364 0 7.682 1.332C21 4.665 21 6.81 21 11.098M8.25 6A.75.75 0 0 1 9 5.25h6a.75.75 0 0 1 0 1.5H9A.75.75 0 0 1 8.25 6" fill-rule="evenodd"/></svg>
                </button>
                <div class="header-dropdown" id="kitaplik-dropdown">
                    <div class="dropdown-header">Kitaplık <a href="<?php echo home_url('/bookmark'); ?>">Tümü</a></div>
                    <div id="kitaplik-list" class="dropdown-list">
                        <p class="empty-msg">Henüz takip yok.</p>
                    </div>
                </div>
            </div>

            <!-- Geçmiş (History) Dropdown -->
            <div class="header-dropdown-wrap">
                <button class="header-icon-btn" id="history-btn" title="Okuma Geçmişi" type="button">
                    <svg height="20" viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M13 3a9 9 0 0 0-9 9H2l3.89 3.89l.07.14L10 12H7a7 7 0 1 1 7 7a7.07 7.07 0 0 1-6-3.22l-1.44 1.44A9 9 0 1 0 13 3m0 5v5l4.28 2.54l.72-1.21l-3.5-2.08V8z"/></svg>
                </button>
                <div class="header-dropdown" id="history-dropdown">
                    <div class="dropdown-header">Geçmiş</div>
                    <div id="history-list" class="dropdown-list">
                        <p class="empty-msg">Henüz geçmiş yok.</p>
                    </div>
                    <a href="<?php echo home_url('/gecmis/'); ?>" class="dropdown-footer-link">Gelişmiş Geçmiş</a>
                </div>
            </div>

            <!-- Bağış -->
            <a href="<?php echo home_url('/bagis/'); ?>" class="header-icon-btn hidden-mobile" title="Bağış">
                <svg height="20" viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M11 2.5A1.5 1.5 0 0 1 12.5 4v2.573l1.83-.915a1.5 1.5 0 1 1 1.34 2.684L12.5 9.927v.646l1.83-.915a1.5 1.5 0 0 1 1.34 2.684l-3.17 1.585v4.203c.467-.194.98-.47 1.493-.843c1.156-.841 2.155-2.064 2.552-3.65a1.5 1.5 0 1 1 2.91.727c-.603 2.413-2.104 4.19-3.698 5.35C14.202 20.843 12.4 21.5 11 21.5A1.5 1.5 0 0 1 9.5 20v-4.573l-1.83.915a1.5 1.5 0 1 1-1.34-2.684l3.17-1.585v-.646l-1.83.915a1.5 1.5 0 1 1-1.34-2.684L9.5 8.073V4A1.5 1.5 0 0 1 11 2.5"/></svg>
            </a>

            <!-- Dark/Light Toggle -->
            <button class="header-icon-btn" id="theme-toggle" title="Görünüm Modu" type="button">
                <span class="theme-icon-dark" id="theme-toggle-dark-icon" style="display:none;">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path clip-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd"/></svg>
                </span>
                <span class="theme-icon-light" id="theme-toggle-light-icon" style="display:none;">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                </span>
            </button>

            <!-- 3-Dot Dropdown -->
            <div class="header-dropdown-wrap">
                <button class="header-icon-btn" id="header-dropdown-btn" title="Menü" type="button">
                    <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                </button>
                <div class="header-dropdown" id="header-dropdown">
                    <a href="<?php echo home_url('/bookmark'); ?>">
                        <svg height="18" viewBox="0 0 24 24" width="18" fill="currentColor"><path clip-rule="evenodd" d="M21 11.098v4.993c0 3.096 0 4.645-.734 5.321c-.35.323-.792.526-1.263.58c-.987.113-2.14-.907-4.445-2.946c-1.02-.901-1.529-1.352-2.118-1.47a2.225 2.225 0 0 0-.88 0c-.59.118-1.099.569-2.118 1.47c-2.305 2.039-3.458 3.059-4.445 2.945a2.238 2.238 0 0 1-1.263-.579C3 20.736 3 19.188 3 16.091v-4.994C3 6.81 3 4.666 4.318 3.333C5.636 2 7.758 2 12 2c4.243 0 6.364 0 7.682 1.332C21 4.665 21 6.81 21 11.098M8.25 6A.75.75 0 0 1 9 5.25h6a.75.75 0 0 1 0 1.5H9A.75.75 0 0 1 8.25 6" fill-rule="evenodd"/></svg>
                        Kitaplık
                    </a>
                    <a href="<?php echo home_url('/random-novel/'); ?>">
                        <svg height="18" viewBox="0 0 512 512" width="18" fill="currentColor"><path d="M255.76 44.764c-6.176 0-12.353 1.384-17.137 4.152L85.87 137.276c-9.57 5.536-9.57 14.29 0 19.826l152.753 88.36c9.57 5.536 24.703 5.536 34.272 0l152.753-88.36c9.57-5.535 9.57-14.29 0-19.825l-152.753-88.36c-4.785-2.77-10.96-4.153-17.135-4.153m-.824 53.11c9.013.097 17.117 2.162 24.31 6.192c4.92 2.758 8.143 5.903 9.666 9.438c1.473 3.507 1.56 8.13.26 13.865l-1.6 5.706c-1.06 4.083-1.28 7.02-.66 8.81c.57 1.764 1.983 3.278 4.242 4.544l3.39 1.898l-33.235 18.62l-3.693-2.067c-4.118-2.306-6.744-4.912-7.883-7.82c-1.188-2.935-.99-7.603.594-14.005l1.524-5.748c.887-3.423.973-6.23.26-8.418c-.653-2.224-2.134-3.983-4.444-5.277c-3.515-1.97-7.726-2.676-12.63-2.123c-4.956.526-10.072 2.268-15.35 5.225c-4.972 2.785-9.487 6.272-13.55 10.46c-4.112 4.162-7.64 8.924-10.587 14.288L171.9 138.21c5.318-5.34 10.543-10.01 15.676-14.013c5.134-4 10.554-7.6 16.262-10.8c14.976-8.39 28.903-13.38 41.78-14.967a68.57 68.57 0 0 1 9.32-.557zm50.757 56.7l26.815 15.024l-33.235 18.62l-26.816-15.023l33.236-18.62zM75.67 173.84c-5.753-.155-9.664 4.336-9.664 12.28v157.696c0 11.052 7.57 24.163 17.14 29.69l146.93 84.848c9.57 5.526 17.14 1.156 17.14-9.895V290.76c0-11.052-7.57-24.16-17.14-29.688l-146.93-84.847c-2.69-1.555-5.225-2.327-7.476-2.387zm360.773.002c-2.25.06-4.783.83-7.474 2.385l-146.935 84.847c-9.57 5.527-17.14 18.638-17.14 29.69v157.7c0 11.05 7.57 15.418 17.14 9.89L428.97 373.51c9.57-5.527 17.137-18.636 17.137-29.688v-157.7c0-7.942-3.91-12.432-9.664-12.278z"/></svg>
                        Rastgele Novel
                    </a>
                    <hr class="dropdown-divider">
                    <a href="<?php echo get_post_type_archive_link('novel'); ?>">
                        <svg height="18" viewBox="0 0 24 24" width="18" fill="currentColor"><path d="M4.979 9.685C2.993 8.891 2 8.494 2 8s.993-.89 2.979-1.685l2.808-1.123C9.773 4.397 10.767 4 12 4c1.234 0 2.227.397 4.213 1.192l2.808 1.123C21.007 7.109 22 7.506 22 8s-.993.89-2.979 1.685l-2.808 1.124C14.227 11.603 13.233 12 12 12c-1.234 0-2.227-.397-4.213-1.191z"/><path d="m5.766 10l-.787.315C2.993 11.109 2 11.507 2 12c0 .493.993.89 2.979 1.685l2.808 1.124C9.773 15.603 10.767 16 12 16c1.234 0 2.227-.397 4.213-1.191l2.808-1.124C21.007 12.891 22 12.493 22 12c0-.493-.993-.89-2.979-1.685L18.234 10l-2.021.809C14.227 11.603 13.233 12 12 12c-1.234 0-2.227-.397-4.213-1.191z" opacity=".7"/><path d="m5.766 14l-.787.315C2.993 15.109 2 15.507 2 16c0 .494.993.89 2.979 1.685l2.808 1.124C9.773 19.603 10.767 20 12 20c1.234 0 2.227-.397 4.213-1.192l2.808-1.123C21.007 16.891 22 16.494 22 16c0-.493-.993-.89-2.979-1.685L18.234 14l-2.021.809C14.227 15.603 13.233 16 12 16c-1.234 0-2.227-.397-4.213-1.191z" opacity=".4"/></svg>
                        Novel Listesi
                    </a>
                    <a href="<?php echo home_url('/categories'); ?>">
                        <svg height="18" viewBox="0 0 24 24" width="18" fill="currentColor"><path d="M10 3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm10 0h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM10 13H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1zm10 0h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1z"/></svg>
                        Kategoriler
                    </a>
                    <a href="<?php echo home_url('/bagis/'); ?>">
                        <svg height="18" viewBox="0 0 24 24" width="18" fill="currentColor"><path d="M11 2.5A1.5 1.5 0 0 1 12.5 4v2.573l1.83-.915a1.5 1.5 0 1 1 1.34 2.684L12.5 9.927v.646l1.83-.915a1.5 1.5 0 0 1 1.34 2.684l-3.17 1.585v4.203c.467-.194.98-.47 1.493-.843c1.156-.841 2.155-2.064 2.552-3.65a1.5 1.5 0 1 1 2.91.727c-.603 2.413-2.104 4.19-3.698 5.35C14.202 20.843 12.4 21.5 11 21.5A1.5 1.5 0 0 1 9.5 20v-4.573l-1.83.915a1.5 1.5 0 1 1-1.34-2.684l3.17-1.585v-.646l-1.83.915a1.5 1.5 0 1 1-1.34-2.684L9.5 8.073V4A1.5 1.5 0 0 1 11 2.5"/></svg>
                        Bağış
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobil inline arama çubuğu — overlay yok, header'ın üzerine binip yerini alır -->
    <div class="mobile-search-bar" id="mobile-search-bar" hidden>
        <form class="mobile-search-bar-form" action="<?php echo home_url(); ?>" method="get" role="search">
            <button type="button" class="mobile-search-bar-back" id="mobile-search-bar-back" aria-label="Kapat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </button>
            <input type="text" name="s" id="mobile-search-bar-input" placeholder="Novel ara..." autocomplete="off">
            <input type="hidden" name="post_type" value="novel">
            <button type="submit" class="mobile-search-bar-submit" aria-label="Ara">
                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            </button>
        </form>
    </div>
</nav>

<!-- Mobile Slide Menu -->
<div class="mobile-menu" id="mobile-menu">
    <div class="mobile-menu-header">
        <?php if ($logo_url) : ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="mobile-logo" style="max-height: 40px;">
        <?php else : ?>
            <span class="mobile-menu-title"><?php echo get_bloginfo('name'); ?></span>
        <?php endif; ?>
        <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Kapat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <!-- Mobile Search -->
    <form class="mobile-search-form" action="<?php echo home_url(); ?>" method="get">
        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input type="text" name="s" placeholder="Novel ara..." value="<?php echo get_search_query(); ?>">
        <input type="hidden" name="post_type" value="novel">
    </form>
    <a href="<?php echo home_url(); ?>">Ana Sayfa</a>
    <a href="<?php echo get_post_type_archive_link('novel'); ?>">Romanlar</a>
    <a href="<?php echo home_url('/bookmark'); ?>">Kitaplık</a>
    <a href="<?php echo home_url('/categories'); ?>">Kategoriler</a>
    <a href="<?php echo home_url('/random-novel/'); ?>">🎲 Rastgele</a>
    <a href="<?php echo home_url('/bagis/'); ?>">💝 Bağış</a>
</div>
<div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

<?php
// Site-wide announcement bar (header altı, kutusuz)
// Tema Ayarları → "Site Duyurusu" alanı boşsa render edilmez
$nt_announcement = trim((string) get_option('webnovel_novel_announcement', ''));
if ($nt_announcement !== '') : ?>
    <div class="site-announcement"><svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 14 14"><g fill="none" stroke="#ff4757" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"><path d="m7.182 3.747l3.857 6.681m-.202-.351l-9.51 2.209L.72 11.23l6.667-7.132"/><path d="m3.396 11.805l.524.907a1.421 1.421 0 1 0 2.455-1.433l-.084-.145m1.153-9.297V.583m4.803 6.057H13.5m-12.111 0h1.253m.519-4.283l.886.887m6.793 0l.886-.887"/></g></svg><?php echo wp_kses_post($nt_announcement); ?></div>
<?php endif; ?>

<main class="site-main">
