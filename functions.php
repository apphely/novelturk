<?php
/**
 * Novel Türk Theme Functions
 *
 * @package NovelTurk
 * @version 3.0.0
 */

defined('ABSPATH') || exit;

// ============================================
// Theme Setup
// ============================================
function webnovel_setup() {
    // Theme supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'gallery', 'caption'));
    
    // Custom image sizes
    add_image_size('novel-cover', 400, 560, true);
    add_image_size('novel-cover-large', 600, 840, true);
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Ana Menü', 'webnovel-reader'),
        'footer'  => __('Alt Menü', 'webnovel-reader'),
    ));
}
add_action('after_setup_theme', 'webnovel_setup');

// ============================================
// Enqueue Styles & Scripts
// ============================================
function webnovel_scripts() {
    // Preconnect for Google Fonts (matches theme-layouts.xml)
    add_action('wp_head', function () {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    }, 1);

    // Primary font: Poppins (matches theme-layouts.xml line 1041)
    wp_enqueue_style('google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);

    // Reader font options - only on chapter pages (matches theme-layouts.xml lines 2728-2736)
    if (is_singular('chapter')) {
        wp_enqueue_style(
            'google-fonts-reader',
            'https://fonts.googleapis.com/css2?family=Amatic+SC&family=Chakra+Petch&family=Lobster&family=Merienda&family=Nunito&family=Quicksand&family=Roboto:wght@400;500;700&family=Shantell+Sans&family=Sriracha&display=swap',
            array(),
            null
        );
        wp_enqueue_style('cdnfonts-source-sans-pro', 'https://fonts.cdnfonts.com/css/source-sans-pro', array(), null);
    }

    // Theme stylesheet - cache bust
    wp_enqueue_style('webnovel-style', get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . '/style.css'));
    
    // Components CSS - separate file for new components
    wp_enqueue_style('webnovel-components', get_template_directory_uri() . '/css/components.css', array('webnovel-style'), filemtime(get_stylesheet_directory() . '/css/components.css'));
    
    // Reader script - cache bust
    wp_enqueue_script('webnovel-reader', get_template_directory_uri() . '/js/reader.js', array(), filemtime(get_stylesheet_directory() . '/js/reader.js'), true);

    // Novel/Chapter page script (toggleReadMore, searchList, <youtube-button>)
    if (is_singular('novel') || is_singular('chapter')) {
        wp_enqueue_script('webnovel-novel-page', get_template_directory_uri() . '/js/novel-page.js', array(), filemtime(get_stylesheet_directory() . '/js/novel-page.js'), true);
    }
    
    // Localize script for AJAX on chapter pages
    if (is_singular('chapter') || is_singular('novel')) {
        wp_localize_script('webnovel-reader', 'webnovelReader', array(
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('webnovel_reader_nonce'),
            'chapterId' => is_singular('chapter') ? get_the_ID() : 0,
        ));
    }
}
add_action('wp_enqueue_scripts', 'webnovel_scripts');

// Admin scripts
function webnovel_admin_scripts($hook) {
    if ($hook === 'toplevel_page_webnovel-bulk-upload') {
        wp_enqueue_script('webnovel-admin-upload', get_template_directory_uri() . '/js/admin-upload.js', array('jquery'), '1.0.0', true);
        wp_localize_script('webnovel-admin-upload', 'webnovelAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('webnovel_bulk_upload'),
        ));
    }
    
    if ($hook === 'toplevel_page_webnovel-settings') {
        wp_enqueue_media();
    }

    // Roman düzenleme ekranında "Ciltler / Ekstra" repeater için media uploader
    if (in_array($hook, array('post.php', 'post-new.php'), true)) {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'novel') {
            wp_enqueue_media();
        }
    }
}
add_action('admin_enqueue_scripts', 'webnovel_admin_scripts');

// ============================================
// Custom Post Type: Novel
// ============================================
function webnovel_register_novel_cpt() {
    $labels = array(
        'name'                  => 'Romanlar',
        'singular_name'         => 'Roman',
        'menu_name'             => 'Romanlar',
        'add_new'               => 'Yeni Ekle',
        'add_new_item'          => 'Yeni Roman Ekle',
        'edit_item'             => 'Roman Düzenle',
        'new_item'              => 'Yeni Roman',
        'view_item'             => 'Romanı Görüntüle',
        'search_items'          => 'Roman Ara',
        'not_found'             => 'Roman bulunamadı',
        'not_found_in_trash'    => 'Çöp kutusunda roman yok',
        'all_items'             => 'Tüm Romanlar',
        // Featured image relabeled as "Roman Kapağı" (cover)
        'featured_image'        => 'Roman Kapağı',
        'set_featured_image'    => 'Roman kapağını ayarla',
        'remove_featured_image' => 'Roman kapağını kaldır',
        'use_featured_image'    => 'Roman kapağı olarak kullan',
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'novel'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-book',
        'supports'           => array('title', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true,
    );
    
    register_post_type('novel', $args);
}
add_action('init', 'webnovel_register_novel_cpt');

// ============================================
// Custom Taxonomies: Origin (Ülke) & Type (Tür)
// ============================================
function webnovel_register_custom_taxonomies() {
    // Ülkeler (Çin, Kore, Japonya, Türkiye, Genel) — TEK seçim
    register_taxonomy('novel_origin', 'novel', array(
        'labels' => array(
            'name' => 'Ülkeler',
            'singular_name' => 'Ülke',
            'menu_name' => 'Ülkeler',
            'all_items' => 'Tüm Ülkeler',
            'edit_item' => 'Ülke Düzenle',
            'add_new_item' => 'Yeni Ülke Ekle',
            'search_items' => 'Ülke Ara',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'show_in_quick_edit' => false,
        'meta_box_cb' => 'webnovel_single_term_select_meta_box',
        'rewrite' => array('slug' => 'ulke'),
    ));

    // Türler (Novel, Light Novel, Web Novel, OneShot) — TEK seçim
    register_taxonomy('novel_type', 'novel', array(
        'labels' => array(
            'name' => 'Türler',
            'singular_name' => 'Tür',
            'menu_name' => 'Türler',
            'all_items' => 'Tüm Türler',
            'edit_item' => 'Tür Düzenle',
            'add_new_item' => 'Yeni Tür Ekle',
            'search_items' => 'Tür Ara',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'show_in_quick_edit' => false,
        'meta_box_cb' => 'webnovel_single_term_select_meta_box',
        'rewrite' => array('slug' => 'tur'),
    ));
}
add_action('init', 'webnovel_register_custom_taxonomies');

/**
 * Custom meta_box_cb that renders a single-select <select> instead of checkboxes.
 * Used by novel_origin and novel_type so only ONE term can be assigned.
 *
 * Submits as tax_input[$tax][] = [term_id], which WP processes via its standard
 * taxonomy save handler — no custom save logic needed.
 */
function webnovel_single_term_select_meta_box($post, $box) {
    $taxonomy = isset($box['args']['taxonomy']) ? $box['args']['taxonomy'] : '';
    $tax_obj  = get_taxonomy($taxonomy);
    if (!$tax_obj) return;

    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    if (is_wp_error($terms) || empty($terms)) {
        $not_found = !empty($tax_obj->labels->not_found) ? $tax_obj->labels->not_found : 'Henüz öğe yok.';
        echo '<p>' . esc_html($not_found) . '</p>';
        return;
    }

    $current     = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'ids'));
    $current_id  = (!is_wp_error($current) && !empty($current)) ? (int) $current[0] : 0;
    $select_name = 'tax_input[' . esc_attr($taxonomy) . '][]';
    $placeholder = sprintf('— %s seçin —', esc_html($tax_obj->labels->singular_name));
    ?>
    <p style="margin:6px 0;">
        <select name="<?php echo $select_name; ?>" style="width:100%;">
            <option value=""><?php echo $placeholder; ?></option>
            <?php foreach ($terms as $term) : ?>
                <option value="<?php echo (int) $term->term_id; ?>" <?php selected($current_id, (int) $term->term_id); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * Canonical term lists from "Kategori İsimleri.txt".
 * Single source of truth — used by the sync function below.
 */
function webnovel_taxonomy_spec() {
    return array(
        'novel_genre' => array(
            'Aksiyon', 'Bilim Kurgu', 'Doğaüstü', 'Dövüş Sanatları', 'Drama',
            'Ecchi', 'Fantastik', 'Gizem', 'Harem', 'Hayattan Kesitler',
            'Josei', 'Komedi', 'Korku', 'Macera', 'Mecha',
            'Müstehcen', 'Okul Hayatı', 'Olgun', 'Psikolojik', 'Romantizm',
            'Seinen', 'Shoujo', 'Shoujo Ai', 'Shounen', 'Shounen Ai',
            'Spor', 'Tarihi', 'Trajedi', 'Wuxia', 'Xianxia',
            'Xuanhuan', 'Yetişkin',
        ),
        'novel_origin' => array(
            'Çin', 'Genel', 'Japonya', 'Kore', 'Türkiye',
        ),
        'novel_type' => array(
            'Novel', 'Light Novel', 'Web Novel', 'OneShot',
        ),
    );
}

/**
 * Sync taxonomy terms with the spec list (add missing, remove out-of-spec).
 * Runs once per spec version via an option flag, but can be forced by
 * visiting any admin URL with ?webnovel_sync_taxonomies=1
 */
function webnovel_sync_taxonomies() {
    $spec_version = '2026-04-26-kategori-listesi-rewrite';
    $force = isset($_GET['webnovel_sync_taxonomies']) && current_user_can('manage_options');

    if (!$force && get_option('webnovel_taxonomy_spec_version') === $spec_version) {
        return;
    }

    foreach (webnovel_taxonomy_spec() as $taxonomy => $wanted) {
        if (!taxonomy_exists($taxonomy)) continue;

        // Add missing terms
        foreach ($wanted as $term_name) {
            if (!term_exists($term_name, $taxonomy)) {
                wp_insert_term($term_name, $taxonomy);
            }
        }

        // Remove terms not in spec
        $existing = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ));
        if (is_wp_error($existing)) continue;
        foreach ($existing as $term) {
            if (!in_array($term->name, $wanted, true)) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
    }

    update_option('webnovel_taxonomy_spec_version', $spec_version);

    // Rewrite flush'ı bir sonraki wp_loaded'a ertele — init sırasında flush
    // güvenilmez çünkü taksonomi rewrite'ları henüz tam yüklenmemiş olabilir.
    update_option('webnovel_needs_rewrite_flush', '1');

    if ($force && is_admin()) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Kategori/Ülke/Tür listesi senkronize edildi. URL kuralları yenileniyor…</strong></p></div>';
        });
    }
}

/**
 * Deferred rewrite flush — runs on wp_loaded (after all init hooks).
 * Triggered by webnovel_sync_taxonomies() setting the flag.
 */
function webnovel_maybe_flush_rewrites() {
    if (get_option('webnovel_needs_rewrite_flush') === '1') {
        flush_rewrite_rules();
        delete_option('webnovel_needs_rewrite_flush');
    }
}
add_action('wp_loaded', 'webnovel_maybe_flush_rewrites');
add_action('init', 'webnovel_sync_taxonomies', 20);

// ============================================
// Novel Status (Durum) — single source of truth
// ============================================
/**
 * Returns the canonical status key => label map.
 * Old keys (ongoing/completed/hiatus) are preserved so existing posts keep working;
 * "hiatus" is now relabeled "Süresizlik" per Kategori İsimleri.txt.
 */
function webnovel_get_novel_statuses() {
    return array(
        'upcoming'         => 'Çok Yakında',
        'ongoing'          => 'Devam Ediyor',
        'current'          => 'Güncel',
        'hiatus'           => 'Süresizlik',
        'completed'        => 'Tamamlandı',
        'abandoned'        => 'Terk Edildi',
        'awaiting_sponsor' => 'Sponsor bekliyor',
    );
}

function webnovel_get_status_label($key) {
    $map = webnovel_get_novel_statuses();
    return $map[$key] ?? $key;
}

// ============================================
// Custom Post Type: Chapter
// ============================================
function webnovel_register_chapter_cpt() {
    $labels = array(
        'name'               => 'Bölümler',
        'singular_name'      => 'Bölüm',
        'menu_name'          => 'Bölümler',
        'add_new'            => 'Yeni Ekle',
        'add_new_item'       => 'Yeni Bölüm Ekle',
        'edit_item'          => 'Bölüm Düzenle',
        'new_item'           => 'Yeni Bölüm',
        'view_item'          => 'Bölümü Görüntüle',
        'search_items'       => 'Bölüm Ara',
        'not_found'          => 'Bölüm bulunamadı',
        'not_found_in_trash' => 'Çöp kutusunda bölüm yok',
        'all_items'          => 'Tüm Bölümler',
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'chapter'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-media-text',
        'supports'           => array('title', 'editor', 'comments'),
        'show_in_rest'       => true,
    );
    
    register_post_type('chapter', $args);
}
add_action('init', 'webnovel_register_chapter_cpt');

// ============================================
// Custom Taxonomies
// ============================================
function webnovel_register_taxonomies() {
    // Kategoriler (Aksiyon, Bilim Kurgu, ...) — internal slug remains "novel_genre" for stability
    register_taxonomy('novel_genre', 'novel', array(
        'labels' => array(
            'name'          => 'Kategoriler',
            'singular_name' => 'Kategori',
            'search_items'  => 'Kategori Ara',
            'all_items'     => 'Tüm Kategoriler',
            'edit_item'     => 'Kategori Düzenle',
            'add_new_item'  => 'Yeni Kategori Ekle',
            'menu_name'     => 'Kategoriler',
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'kategori'),
        'show_admin_column' => true,
    ));
    
    // Tag taxonomy for novels
    register_taxonomy('novel_tag', 'novel', array(
        'labels' => array(
            'name'          => 'Etiketler',
            'singular_name' => 'Etiket',
            'search_items'  => 'Etiket Ara',
            'all_items'     => 'Tüm Etiketler',
            'edit_item'     => 'Etiket Düzenle',
            'add_new_item'  => 'Yeni Etiket Ekle',
            'menu_name'     => 'Etiketler',
        ),
        'hierarchical'  => false,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'tag'),
        'show_admin_column' => true,
    ));
}
add_action('init', 'webnovel_register_taxonomies');

// ============================================
// Novel Meta Boxes
// ============================================
function webnovel_add_novel_meta_boxes() {
    add_meta_box(
        'novel_details',
        'Roman Detayları',
        'webnovel_novel_details_callback',
        'novel',
        'normal',
        'high'
    );

    // Move "Roman Kapağı" (featured image) to the very top of the sidebar.
    // Core registers it as 'side, low' — we re-add at 'side, high' so it sits
    // above Yayın Durumu and the taxonomy boxes.
    $novel_obj = get_post_type_object('novel');
    $cover_label = $novel_obj && !empty($novel_obj->labels->featured_image)
        ? $novel_obj->labels->featured_image
        : 'Roman Kapağı';
    remove_meta_box('postimagediv', 'novel', 'side');
    add_meta_box('postimagediv', $cover_label, 'post_thumbnail_meta_box', 'novel', 'side', 'high');

    // Durum sidebar metabox — registered AFTER postimagediv so it appears below.
    add_meta_box(
        'novel_status_box',
        'Yayın Durumu',
        'webnovel_novel_status_callback',
        'novel',
        'side',
        'high'
    );

    // Not metabox — Excerpt panelinin üstünde
    add_meta_box(
        'novel_note_box',
        'Not (kırmızı uyarı)',
        'webnovel_novel_note_callback',
        'novel',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'webnovel_add_novel_meta_boxes');

/**
 * Standalone "Not" metabox — Excerpt'in üstünde gösterilir.
 */
function webnovel_novel_note_callback($post) {
    wp_nonce_field('webnovel_novel_note', 'webnovel_novel_note_nonce');
    $note = get_post_meta($post->ID, '_novel_note', true);
    ?>
    <p style="margin:6px 0;">
        <textarea id="novel_note" name="novel_note" rows="3" style="width:100%;" placeholder="Örn: Bu novelin 1-145. bölümleri ... katkı ile çevrildi."><?php echo esc_textarea($note); ?></textarea>
        <span class="description">Roman sayfasında özetin üstünde kırmızı renkte gösterilir.</span>
    </p>
    <?php
}

function webnovel_save_novel_note($post_id) {
    if (!isset($_POST['webnovel_novel_note_nonce']) || !wp_verify_nonce($_POST['webnovel_novel_note_nonce'], 'webnovel_novel_note')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['novel_note'])) {
        update_post_meta($post_id, '_novel_note', wp_kses_post(wp_unslash($_POST['novel_note'])));
    }
}
add_action('save_post_novel', 'webnovel_save_novel_note');

/**
 * Sidebar metabox: novel status (Durum).
 * Sits above the Ülkeler (novel_origin) taxonomy box thanks to 'high' priority.
 */
function webnovel_novel_status_callback($post) {
    wp_nonce_field('webnovel_novel_status', 'webnovel_novel_status_nonce');
    $status = get_post_meta($post->ID, '_novel_status', true) ?: 'ongoing';
    ?>
    <p style="margin:6px 0;">
        <label for="novel_status" style="display:block; font-weight:600; margin-bottom:6px;">Durum</label>
        <select id="novel_status" name="novel_status" style="width:100%;">
            <?php foreach (webnovel_get_novel_statuses() as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($status, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

function webnovel_save_novel_status($post_id) {
    if (!isset($_POST['webnovel_novel_status_nonce']) || !wp_verify_nonce($_POST['webnovel_novel_status_nonce'], 'webnovel_novel_status')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['novel_status'])) {
        $valid = array_keys(webnovel_get_novel_statuses());
        $val = sanitize_text_field(wp_unslash($_POST['novel_status']));
        if (in_array($val, $valid, true)) {
            update_post_meta($post_id, '_novel_status', $val);
        }
    }
}
add_action('save_post_novel', 'webnovel_save_novel_status');

/**
 * Shared admin styles for novel & chapter meta box grid layout.
 */
/**
 * Make the post content editor (#postdivrich) collapsible on the Novel edit
 * screen. Renders a metabox-style header above the editor with a chevron
 * toggle. Default state: collapsed (user typically uses Excerpt/Özet field).
 * Persists user preference in localStorage.
 */
function webnovel_collapsible_editor() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'novel') return;
    ?>
    <style>
        /* Hide WP's default screen-reader heading so we own the visual header */
        #postdivrich > .screen-reader-text { display: none; }

        #nt-editor-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
            user-select: none;
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
        }
        #nt-editor-bar:hover { background: #f6f7f7; }
        #nt-editor-bar .nt-editor-bar-hint {
            font-size: 12px;
            font-weight: normal;
            color: #646970;
        }
        #nt-editor-bar .nt-chev {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        #postdivrich.nt-collapsed #nt-editor-bar { border-radius: 4px; }
        #postdivrich.nt-collapsed #nt-editor-bar .nt-chev { transform: rotate(-90deg); }
        #postdivrich.nt-collapsed #wp-content-editor-tools,
        #postdivrich.nt-collapsed #wp-content-editor-container,
        #postdivrich.nt-collapsed > .post-status-info { display: none !important; }
    </style>
    <script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var wrap = document.getElementById('postdivrich');
            if (!wrap || document.getElementById('nt-editor-bar')) return;

            var bar = document.createElement('div');
            bar.id = 'nt-editor-bar';
            bar.innerHTML =
                '<span>İçerik <span class="nt-editor-bar-hint">(opsiyonel — Özet alanı tercih edilir)</span></span>' +
                '<span class="nt-chev"><svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M5 7l5 5 5-5z"/></svg></span>';
            wrap.insertBefore(bar, wrap.firstChild);

            var KEY = 'nt_novel_editor_collapsed';
            // Default: collapsed unless user has explicitly opened it before
            if (localStorage.getItem(KEY) !== '0') {
                wrap.classList.add('nt-collapsed');
            }

            bar.addEventListener('click', function () {
                var nowCollapsed = wrap.classList.toggle('nt-collapsed');
                localStorage.setItem(KEY, nowCollapsed ? '1' : '0');
            });
        });
    })();
    </script>
    <?php
}
add_action('admin_footer-post.php', 'webnovel_collapsible_editor');
add_action('admin_footer-post-new.php', 'webnovel_collapsible_editor');

/**
 * Yayınla panelinde "Güncellenme tarihi" satırı göster.
 */
function webnovel_show_modified_date_in_publish_box($post) {
    if (!$post || $post->post_type !== 'novel') return;
    if ($post->post_status === 'auto-draft') return;
    $modified = get_post_modified_time('j F Y, H:i', false, $post);
    if (!$modified) return;
    ?>
    <div class="misc-pub-section misc-pub-modified">
        <span class="dashicons dashicons-update" style="color:#787c82;"></span>
        Güncellenme tarihi: <strong><?php echo esc_html($modified); ?></strong>
    </div>
    <?php
}
add_action('post_submitbox_misc_actions', 'webnovel_show_modified_date_in_publish_box');

function webnovel_admin_metabox_styles() {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->post_type, array('novel', 'chapter'), true)) return;
    ?>
    <style>
        .nt-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px 24px; margin-top: 4px; }
        .nt-meta-grid .nt-meta-field { display: flex; flex-direction: column; gap: 4px; }
        .nt-meta-grid .nt-meta-field label { font-weight: 600; }
        .nt-meta-grid .nt-meta-field input, .nt-meta-grid .nt-meta-field select, .nt-meta-grid .nt-meta-field textarea { width: 100%; }
        .nt-meta-grid .nt-meta-field .description { font-size: 12px; color: #646970; }
        .nt-meta-grid .nt-meta-field-full { grid-column: 1 / -1; }
        .nt-meta-section-title { margin: 18px 0 6px; padding-bottom: 6px; border-bottom: 1px solid #dcdcde; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #1d2327; }

        /* Excerpt panelindeki yardım metnini gizle (sadece novel ekranında) */
        .post-type-novel #postexcerpt .inside > p { display: none; }

        /* Hide "Most Used" (En Çok Kullanılan) tab in hierarchical taxonomy meta boxes */
        .category-tabs li.hide-if-no-js { display: none !important; }
        /* Hide "Choose from the most used tags" link in non-hierarchical taxonomy meta boxes */
        [id^="tagsdiv-"] .tagcloud-link,
        [id^="tagsdiv-"] p.hide-if-no-js:has(.tagcloud-link) { display: none !important; }
    </style>
    <?php
}
add_action('admin_head', 'webnovel_admin_metabox_styles');

function webnovel_novel_details_callback($post) {
    wp_nonce_field('webnovel_novel_details', 'webnovel_novel_nonce');

    $author_name = get_post_meta($post->ID, '_novel_author', true);
    $alt_title = get_post_meta($post->ID, '_novel_alt_title', true);
    $editor = get_post_meta($post->ID, '_novel_editor', true);
    $translator = get_post_meta($post->ID, '_novel_translator', true);
    $illustrator = get_post_meta($post->ID, '_novel_illustrator', true);
    $supporters = get_post_meta($post->ID, '_novel_supporters', true);
    $note = get_post_meta($post->ID, '_novel_note', true);
    $yt_url = get_post_meta($post->ID, '_novel_youtube_url', true);
    $yt_label = get_post_meta($post->ID, '_novel_youtube_label', true);
    $related_raw = get_post_meta($post->ID, '_novel_related', true);
    $extras_raw  = get_post_meta($post->ID, '_novel_extras', true);
    $extras      = webnovel_parse_extras($extras_raw);
    ?>
    <h3 class="nt-meta-section-title" style="margin-top:4px;">Temel Bilgiler</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field">
            <label for="novel_author">Yazar(lar)</label>
            <input type="text" id="novel_author" name="novel_author" value="<?php echo esc_attr($author_name); ?>" placeholder="Örn: Yazar 1, Yazar 2">
            <span class="description">Birden fazla yazar varsa virgülle ayırın.</span>
        </div>
        <div class="nt-meta-field">
            <label for="novel_illustrator">Çizer(ler)</label>
            <input type="text" id="novel_illustrator" name="novel_illustrator" value="<?php echo esc_attr($illustrator); ?>" placeholder="Örn: Çizer 1, Çizer 2">
            <span class="description">Birden fazla çizer varsa virgülle ayırın.</span>
        </div>
        <div class="nt-meta-field nt-meta-field-full">
            <label for="novel_alt_title">Alternatif Başlık</label>
            <input type="text" id="novel_alt_title" name="novel_alt_title" value="<?php echo esc_attr($alt_title); ?>">
        </div>
    </div>

    <h3 class="nt-meta-section-title">Kredi</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field">
            <label for="novel_translator">Çevirmen</label>
            <input type="text" id="novel_translator" name="novel_translator" value="<?php echo esc_attr($translator); ?>" placeholder="Örn: Apphely">
        </div>
        <div class="nt-meta-field">
            <label for="novel_editor">Editör</label>
            <input type="text" id="novel_editor" name="novel_editor" value="<?php echo esc_attr($editor); ?>" placeholder="Örn: Apphely">
        </div>
        <div class="nt-meta-field nt-meta-field-full">
            <label for="novel_supporters">Destekçi(ler)</label>
            <input type="text" id="novel_supporters" name="novel_supporters" value="<?php echo esc_attr($supporters); ?>" placeholder="Örn: y0sm1r (1-150), Destekçi 2">
            <span class="description">Bu novelin çevirisini destekleyen kişiler. Birden fazla varsa virgülle ayırın. Yanına parantez ile bölüm aralığı yazabilirsiniz.</span>
        </div>
    </div>

    <h3 class="nt-meta-section-title">YouTube Butonu</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field">
            <label for="novel_youtube_url">YouTube Linki</label>
            <input type="url" id="novel_youtube_url" name="novel_youtube_url" value="<?php echo esc_attr($yt_url); ?>" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div class="nt-meta-field">
            <label for="novel_youtube_label">Buton Yazısı</label>
            <input type="text" id="novel_youtube_label" name="novel_youtube_label" value="<?php echo esc_attr($yt_label); ?>" placeholder="YouTube'da Dinle">
        </div>
    </div>

    <h3 class="nt-meta-section-title">İlgili Noveller</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field nt-meta-field-full">
            <textarea id="novel_related" name="novel_related" rows="4" style="font-family:monospace;" placeholder="Her satıra: Etiket | URL&#10;Aynı Evren | https://...&#10;Yan Novel | https://..."><?php echo esc_textarea($related_raw); ?></textarea>
            <span class="description">Her satırda <code>Etiket | URL</code> biçiminde girin (Aynı Evren, Yan Novel, Alternatif vb.).</span>
        </div>
    </div>

    <h3 class="nt-meta-section-title">Ciltler / Ekstra</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field nt-meta-field-full">
            <span class="description" style="margin-bottom:8px;">Görsellerin altında başlık ve (opsiyonel) bağlantı gösterilir. Roman sayfasında "Ciltler/Ekstra" sekmesinde listelenir.</span>
            <div id="nt-extras-list" class="nt-extras-list">
                <?php foreach ($extras as $idx => $ex) : ?>
                    <div class="nt-extra-row" data-index="<?php echo (int) $idx; ?>">
                        <div class="nt-extra-thumb">
                            <?php if (!empty($ex['image'])) : ?>
                                <img src="<?php echo esc_url($ex['image']); ?>" alt="">
                            <?php else : ?>
                                <span class="nt-extra-placeholder">Görsel yok</span>
                            <?php endif; ?>
                        </div>
                        <div class="nt-extra-fields">
                            <input type="hidden" class="nt-extra-image" name="novel_extras[<?php echo (int) $idx; ?>][image]" value="<?php echo esc_attr($ex['image']); ?>">
                            <input type="hidden" class="nt-extra-image-id" name="novel_extras[<?php echo (int) $idx; ?>][image_id]" value="<?php echo esc_attr($ex['image_id']); ?>">
                            <input type="text" class="nt-extra-title" name="novel_extras[<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr($ex['title']); ?>" placeholder="Başlık (örn: Cilt 1 - Eski Okul Binası)">
                            <input type="url" class="nt-extra-url" name="novel_extras[<?php echo (int) $idx; ?>][url]" value="<?php echo esc_attr($ex['url']); ?>" placeholder="URL (opsiyonel)">
                            <div class="nt-extra-actions">
                                <button type="button" class="button nt-extra-pick">Görsel Seç</button>
                                <button type="button" class="button nt-extra-clear" <?php echo empty($ex['image']) ? 'style="display:none;"' : ''; ?>>Görseli Kaldır</button>
                                <button type="button" class="button nt-extra-up" title="Yukarı taşı">↑</button>
                                <button type="button" class="button nt-extra-down" title="Aşağı taşı">↓</button>
                                <button type="button" class="button button-link-delete nt-extra-remove">Satırı Sil</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p style="margin-top:10px;"><button type="button" class="button button-primary" id="nt-extras-add">+ Yeni Ekle</button></p>

            <template id="nt-extra-row-template">
                <div class="nt-extra-row" data-index="__INDEX__">
                    <div class="nt-extra-thumb"><span class="nt-extra-placeholder">Görsel yok</span></div>
                    <div class="nt-extra-fields">
                        <input type="hidden" class="nt-extra-image" name="novel_extras[__INDEX__][image]" value="">
                        <input type="hidden" class="nt-extra-image-id" name="novel_extras[__INDEX__][image_id]" value="">
                        <input type="text" class="nt-extra-title" name="novel_extras[__INDEX__][title]" value="" placeholder="Başlık (örn: Cilt 1 - Eski Okul Binası)">
                        <input type="url" class="nt-extra-url" name="novel_extras[__INDEX__][url]" value="" placeholder="URL (opsiyonel)">
                        <div class="nt-extra-actions">
                            <button type="button" class="button nt-extra-pick">Görsel Seç</button>
                            <button type="button" class="button nt-extra-clear" style="display:none;">Görseli Kaldır</button>
                            <button type="button" class="button nt-extra-up" title="Yukarı taşı">↑</button>
                            <button type="button" class="button nt-extra-down" title="Aşağı taşı">↓</button>
                            <button type="button" class="button button-link-delete nt-extra-remove">Satırı Sil</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <style>
        .nt-extras-list { display:flex; flex-direction:column; gap:10px; }
        .nt-extra-row { display:flex; gap:12px; padding:10px; background:#f6f7f7; border:1px solid #dcdcde; border-radius:6px; align-items:flex-start; }
        .nt-extra-thumb { flex:0 0 90px; width:90px; aspect-ratio:2/3; background:#e0e0e0; border-radius:4px; overflow:hidden; display:flex; align-items:center; justify-content:center; }
        .nt-extra-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .nt-extra-placeholder { font-size:11px; color:#787c82; padding:4px; text-align:center; }
        .nt-extra-fields { flex:1; display:flex; flex-direction:column; gap:6px; min-width:0; }
        .nt-extra-fields input[type="text"], .nt-extra-fields input[type="url"] { width:100%; }
        .nt-extra-actions { display:flex; gap:6px; flex-wrap:wrap; margin-top:2px; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var $list = $('#nt-extras-list');
        var $tpl  = $('#nt-extra-row-template');
        var nextIndex = $list.find('.nt-extra-row').length;

        function reindex() {
            $list.find('.nt-extra-row').each(function(i, row) {
                var $r = $(row);
                $r.attr('data-index', i);
                $r.find('input').each(function() {
                    var name = $(this).attr('name') || '';
                    $(this).attr('name', name.replace(/novel_extras\[\d+\]/, 'novel_extras[' + i + ']'));
                });
            });
            nextIndex = $list.find('.nt-extra-row').length;
        }

        $('#nt-extras-add').on('click', function() {
            var html = $tpl.html().replace(/__INDEX__/g, nextIndex);
            $list.append(html);
            nextIndex++;
        });

        $list.on('click', '.nt-extra-pick', function(e) {
            e.preventDefault();
            var $row = $(this).closest('.nt-extra-row');
            var frame = wp.media({
                title: 'Görsel Seç',
                button: { text: 'Bu Görseli Kullan' },
                library: { type: 'image' },
                multiple: false
            });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $row.find('.nt-extra-image').val(att.url);
                $row.find('.nt-extra-image-id').val(att.id);
                var $thumb = $row.find('.nt-extra-thumb');
                $thumb.empty().append($('<img>').attr('src', att.url));
                $row.find('.nt-extra-clear').show();
                if (!$row.find('.nt-extra-title').val()) {
                    var titleGuess = (att.title || att.filename || '').toString();
                    $row.find('.nt-extra-title').val(titleGuess);
                }
            });
            frame.open();
        });

        $list.on('click', '.nt-extra-clear', function() {
            var $row = $(this).closest('.nt-extra-row');
            $row.find('.nt-extra-image').val('');
            $row.find('.nt-extra-image-id').val('');
            $row.find('.nt-extra-thumb').empty().append('<span class="nt-extra-placeholder">Görsel yok</span>');
            $(this).hide();
        });

        $list.on('click', '.nt-extra-remove', function() {
            if (!confirm('Bu satırı silmek istediğine emin misin?')) return;
            $(this).closest('.nt-extra-row').remove();
            reindex();
        });

        $list.on('click', '.nt-extra-up', function() {
            var $row = $(this).closest('.nt-extra-row');
            var $prev = $row.prev('.nt-extra-row');
            if ($prev.length) { $row.insertBefore($prev); reindex(); }
        });

        $list.on('click', '.nt-extra-down', function() {
            var $row = $(this).closest('.nt-extra-row');
            var $next = $row.next('.nt-extra-row');
            if ($next.length) { $row.insertAfter($next); reindex(); }
        });
    });
    </script>
    <?php
}

function webnovel_save_novel_meta($post_id) {
    if (!isset($_POST['webnovel_novel_nonce']) || !wp_verify_nonce($_POST['webnovel_novel_nonce'], 'webnovel_novel_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $simple_text_fields = array(
        'novel_author'          => '_novel_author',
        'novel_illustrator'     => '_novel_illustrator',
        'novel_alt_title'       => '_novel_alt_title',
        'novel_editor'          => '_novel_editor',
        'novel_translator'      => '_novel_translator',
        'novel_supporters'      => '_novel_supporters',
        'novel_youtube_label'   => '_novel_youtube_label',
    );
    foreach ($simple_text_fields as $post_key => $meta_key) {
        if (isset($_POST[$post_key])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$post_key])));
        }
    }
    if (isset($_POST['novel_youtube_url'])) {
        update_post_meta($post_id, '_novel_youtube_url', esc_url_raw(wp_unslash($_POST['novel_youtube_url'])));
    }
    if (isset($_POST['novel_related'])) {
        update_post_meta($post_id, '_novel_related', sanitize_textarea_field(wp_unslash($_POST['novel_related'])));
    }

    // Ciltler / Ekstra repeater
    if (isset($_POST['novel_extras']) && is_array($_POST['novel_extras'])) {
        $clean = array();
        foreach (wp_unslash($_POST['novel_extras']) as $row) {
            if (!is_array($row)) continue;
            $image    = isset($row['image']) ? esc_url_raw($row['image']) : '';
            $image_id = isset($row['image_id']) ? absint($row['image_id']) : 0;
            $title    = isset($row['title']) ? sanitize_text_field($row['title']) : '';
            $url      = isset($row['url']) ? esc_url_raw($row['url']) : '';
            // Skip wholly empty rows
            if ($image === '' && $title === '' && $url === '') continue;
            $clean[] = array(
                'image'    => $image,
                'image_id' => $image_id,
                'title'    => $title,
                'url'      => $url,
            );
        }
        update_post_meta($post_id, '_novel_extras', $clean);
    } else {
        delete_post_meta($post_id, '_novel_extras');
    }
}
add_action('save_post_novel', 'webnovel_save_novel_meta');

/**
 * Render a comma-separated list of names as clickable links.
 * Each name links to /novel/?{$param}={name} which archive-novel.php
 * filters via meta LIKE — shows other novels by that author/illustrator.
 *
 * @param string $csv     Comma-separated names (e.g. "Yazar 1, Yazar 2")
 * @param string $param   Query var name ('nauthor' or 'nillustrator')
 * @param string $sep     Separator HTML between links (default ", ")
 * @return string HTML
 */
function webnovel_link_authors($csv, $param, $sep = ', ') {
    if (empty($csv)) return '';

    // Split on commas, but keep commas inside parentheses intact (e.g. "Foo (1-50, 80-100), Bar")
    if (!preg_match_all('/(?:[^,(]|\([^)]*\))+/', (string) $csv, $matches) || empty($matches[0])) {
        return '';
    }
    $parts = array_filter(array_map('trim', $matches[0]));
    if (empty($parts)) return '';

    $base = get_post_type_archive_link('novel');
    $links = array();
    foreach ($parts as $part) {
        // "Name (annotation)" → link sadece "Name", parantez plain text
        if (preg_match('/^(.+?)\s*(\([^)]*\))\s*$/u', $part, $m)) {
            $link_name  = trim($m[1]);
            $annotation = $m[2];
        } else {
            $link_name  = $part;
            $annotation = '';
        }
        if ($link_name === '') continue;
        $url = add_query_arg($param, rawurlencode($link_name), $base);
        $html = '<a href="' . esc_url($url) . '" class="nt-author-link" title="' . esc_attr($link_name) . '">' . esc_html($link_name) . '</a>';
        if ($annotation !== '') {
            $html .= ' <span class="nt-author-note">' . esc_html($annotation) . '</span>';
        }
        $links[] = $html;
    }
    return implode($sep, $links);
}

/**
 * Parse "_novel_related" textarea into [{label, url}, ...]
 */
function webnovel_parse_related($raw) {
    $out = array();
    if (empty($raw)) return $out;
    foreach (preg_split("/\r\n|\n|\r/", $raw) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = explode('|', $line, 2);
        if (count($parts) !== 2) continue;
        $label = trim($parts[0]);
        $url = trim($parts[1]);
        if ($label === '' || $url === '') continue;
        $out[] = array(
            'label' => $label,
            'url'   => esc_url_raw($url),
        );
    }
    return $out;
}

/**
 * Normalize "_novel_extras" meta into [{image, image_id, title, url}, ...].
 * Accepts either the new array format or legacy empty values.
 */
function webnovel_parse_extras($raw) {
    $out = array();
    if (empty($raw) || !is_array($raw)) return $out;
    foreach ($raw as $row) {
        if (!is_array($row)) continue;
        $image    = isset($row['image']) ? (string) $row['image'] : '';
        $image_id = isset($row['image_id']) ? (int) $row['image_id'] : 0;
        $title    = isset($row['title']) ? (string) $row['title'] : '';
        $url      = isset($row['url']) ? (string) $row['url'] : '';
        if ($image === '' && $title === '' && $url === '') continue;
        $out[] = array(
            'image'    => $image,
            'image_id' => $image_id,
            'title'    => $title,
            'url'      => $url,
        );
    }
    return $out;
}

/**
 * Helper: Get novel cover URL.
 * Uses the featured image (better SEO: og:image, image sitemap, srcset).
 */
function webnovel_get_cover_url($post_id, $size = 'novel-cover') {
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, $size);
    }
    return '';
}

// ============================================
// Chapter Meta Boxes
// ============================================
function webnovel_add_chapter_meta_boxes() {
    add_meta_box(
        'chapter_details',
        'Bölüm Detayları',
        'webnovel_chapter_details_callback',
        'chapter',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'webnovel_add_chapter_meta_boxes');

/**
 * Bir bölümün rozetlerini ('son', 'revize') normalize edip dizi olarak döndür.
 * Eski string format ('son' veya 'revize') ve yeni dizi formatını birlikte destekler.
 */
function webnovel_get_chapter_labels($chapter_id) {
    $val = get_post_meta($chapter_id, '_chapter_label', true);
    $valid = array('son', 'revize');
    if (is_array($val)) {
        return array_values(array_intersect($valid, $val));
    }
    if (is_string($val) && in_array($val, $valid, true)) {
        return array($val);
    }
    return array();
}

function webnovel_chapter_details_callback($post) {
    wp_nonce_field('webnovel_chapter_details', 'webnovel_chapter_nonce');

    $novel_id       = get_post_meta($post->ID, '_chapter_novel_id', true);
    $chapter_number = get_post_meta($post->ID, '_chapter_number', true);
    $volume_number  = get_post_meta($post->ID, '_chapter_volume', true);
    $chapter_labels = webnovel_get_chapter_labels($post->ID);
    $yt_url         = get_post_meta($post->ID, '_chapter_youtube_url', true);
    $yt_label       = get_post_meta($post->ID, '_chapter_youtube_label', true);

    $novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ));
    ?>
    <h3 class="nt-meta-section-title" style="margin-top:4px;">Temel Bilgiler</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field nt-meta-field-full">
            <label for="chapter_novel_id">Roman</label>
            <select id="chapter_novel_id" name="chapter_novel_id">
                <option value="">-- Roman Seç --</option>
                <?php foreach ($novels as $novel) : ?>
                    <option value="<?php echo $novel->ID; ?>" <?php selected($novel_id, $novel->ID); ?>>
                        <?php echo esc_html($novel->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="nt-meta-field">
            <label for="chapter_volume">Cilt Numarası</label>
            <input type="number" id="chapter_volume" name="chapter_volume" value="<?php echo esc_attr($volume_number); ?>" min="0" placeholder="Opsiyonel">
            <span class="description">Boş bırakılırsa cilt olmadan kaydedilir.</span>
        </div>
        <div class="nt-meta-field">
            <label for="chapter_number">Bölüm Numarası</label>
            <input type="number" id="chapter_number" name="chapter_number" value="<?php echo esc_attr($chapter_number); ?>" min="0">
        </div>
        <div class="nt-meta-field">
            <label>Bölüm Rozetleri</label>
            <div class="nt-chapter-labels">
                <label class="nt-chapter-label-opt">
                    <input type="checkbox" name="chapter_labels[]" value="son" <?php checked(in_array('son', $chapter_labels, true)); ?>>
                    <span>Son</span>
                </label>
                <label class="nt-chapter-label-opt">
                    <input type="checkbox" name="chapter_labels[]" value="revize" <?php checked(in_array('revize', $chapter_labels, true)); ?>>
                    <span>Revize</span>
                </label>
            </div>
        </div>
        <style>
            .nt-chapter-labels { display:flex; gap:18px; padding:6px 0; }
            .nt-chapter-label-opt { display:inline-flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer; }
            .nt-chapter-label-opt input[type="checkbox"] { width:16px !important; height:16px; margin:0; flex-shrink:0; }
        </style>
    </div>

    <h3 class="nt-meta-section-title">YouTube Butonu</h3>
    <div class="nt-meta-grid">
        <div class="nt-meta-field">
            <label for="chapter_youtube_url">YouTube Linki</label>
            <input type="url" id="chapter_youtube_url" name="chapter_youtube_url" value="<?php echo esc_attr($yt_url); ?>" placeholder="https://youtube.com/watch?v=...">
            <span class="description">Bölüm sayfasında animasyonlu bir YouTube butonu gösterir. Boş bırakılırsa görünmez.</span>
        </div>
        <div class="nt-meta-field">
            <label for="chapter_youtube_label">Buton Yazısı</label>
            <input type="text" id="chapter_youtube_label" name="chapter_youtube_label" value="<?php echo esc_attr($yt_label); ?>" placeholder="YouTube'da Dinle">
        </div>
    </div>
    <?php
}

function webnovel_save_chapter_meta($post_id) {
    if (!isset($_POST['webnovel_chapter_nonce']) || !wp_verify_nonce($_POST['webnovel_chapter_nonce'], 'webnovel_chapter_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['chapter_novel_id'])) {
        update_post_meta($post_id, '_chapter_novel_id', intval($_POST['chapter_novel_id']));
    }
    if (isset($_POST['chapter_volume'])) {
        $vol = $_POST['chapter_volume'];
        if ($vol !== '' && $vol !== null) {
            update_post_meta($post_id, '_chapter_volume', intval($vol));
        } else {
            delete_post_meta($post_id, '_chapter_volume');
        }
    }
    if (isset($_POST['chapter_number'])) {
        update_post_meta($post_id, '_chapter_number', intval($_POST['chapter_number']));
    }
    // Bölüm rozetleri (çoklu): Son ve/veya Revize
    $valid_labels = array('son', 'revize');
    $submitted = isset($_POST['chapter_labels']) && is_array($_POST['chapter_labels'])
        ? array_map('sanitize_text_field', wp_unslash($_POST['chapter_labels']))
        : array();
    $clean_labels = array_values(array_intersect($valid_labels, $submitted));
    if (empty($clean_labels)) {
        delete_post_meta($post_id, '_chapter_label');
    } else {
        update_post_meta($post_id, '_chapter_label', $clean_labels);
    }
    if (isset($_POST['chapter_youtube_url'])) {
        update_post_meta($post_id, '_chapter_youtube_url', esc_url_raw(wp_unslash($_POST['chapter_youtube_url'])));
    }
    if (isset($_POST['chapter_youtube_label'])) {
        update_post_meta($post_id, '_chapter_youtube_label', sanitize_text_field(wp_unslash($_POST['chapter_youtube_label'])));
    }
}
add_action('save_post_chapter', 'webnovel_save_chapter_meta');

// ============================================
// Admin: Bulk Chapter Upload Page
// ============================================
function webnovel_add_admin_menu() {
    add_menu_page(
        'Toplu Bölüm Yükle',
        'Toplu Yükleme',
        'manage_options',
        'webnovel-bulk-upload',
        'webnovel_bulk_upload_page',
        'dashicons-upload',
        7
    );
    add_menu_page(
        'Tema Ayarları',
        'Tema Ayarları',
        'manage_options',
        'webnovel-settings',
        'webnovel_settings_page',
        'dashicons-admin-generic',
        8
    );
}
add_action('admin_menu', 'webnovel_add_admin_menu');

function webnovel_bulk_upload_page() {
    $novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ));
    ?>
    <div class="wrap">
        <h1>📚 Toplu Bölüm Yükleme</h1>
        <p>ZIP dosyası içindeki .txt dosyalarından otomatik bölüm oluşturur. Her .txt dosyasının adı bölüm başlığı, içeriği ise bölüm metni olarak kullanılır.</p>
        
        <div class="bulk-upload-wrap" style="max-width:700px; margin-top:20px;">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="bulk_novel_id">Roman Seçin</label></th>
                    <td>
                        <select id="bulk_novel_id" name="bulk_novel_id" style="min-width:300px;">
                            <option value="">-- Roman Seçin --</option>
                            <?php foreach ($novels as $novel) : ?>
                                <option value="<?php echo $novel->ID; ?>"><?php echo esc_html($novel->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Bölümlerin ekleneceği romanı seçin.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bulk_zip_file">ZIP Dosyası</label></th>
                    <td>
                        <input type="file" id="bulk_zip_file" name="bulk_zip_file" accept=".zip">
                        <p class="description">İçinde .txt dosyaları bulunan ZIP dosyası seçin.<br>
                        <strong>Dosya adı formatları:</strong><br>
                        <code>Roman Adı Bölüm 51 - Bölüm Başlığı.txt</code><br>
                        <code>Roman Adı Cilt 1 Bölüm 10 - Bölüm Başlığı.txt</code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="chapter_status">Yayın Durumu</label></th>
                    <td>
                        <select id="chapter_status" name="chapter_status">
                            <option value="publish">Yayınla</option>
                            <option value="draft">Taslak</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p>
                <button type="button" id="start-bulk-upload" class="button button-primary button-hero">
                    🚀 Yüklemeyi Başlat
                </button>
            </p>
            
            <div id="upload-progress" class="bulk-upload-progress" style="display:none;">
                <div class="progress-bar-wrap" style="width:100%;height:24px;background:#ddd;border-radius:12px;overflow:hidden;margin-bottom:10px;">
                    <div id="progress-bar" style="height:100%;background:linear-gradient(90deg,#7c5bf5,#f5527c);border-radius:12px;transition:width 0.3s;width:0%;"></div>
                </div>
                <p id="progress-text" style="margin-bottom:10px;"></p>
                <div id="upload-log" style="max-height:400px;overflow-y:auto;background:#1a1a2e;border:1px solid #2c2c3e;border-radius:8px;padding:12px;font-family:Consolas,monospace;font-size:12px;line-height:1.8;color:#a0a0b8;"></div>
            </div>
        </div>
    </div>
    <?php
}

// ============================================
// AJAX: Handle ZIP Upload
// ============================================
function webnovel_handle_zip_upload() {
    // Prevent timeout for large ZIP files
    @set_time_limit(0);
    @ini_set('max_execution_time', '0');
    
    check_ajax_referer('webnovel_bulk_upload', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Yetkiniz yok.');
    }
    
    if (!isset($_FILES['zip_file'])) {
        wp_send_json_error('ZIP dosyası bulunamadı.');
    }
    
    $novel_id = intval($_POST['novel_id'] ?? 0);
    $chapter_status = sanitize_text_field($_POST['chapter_status'] ?? 'publish');
    
    if (!$novel_id) {
        wp_send_json_error('Roman seçilmedi.');
    }
    
    $file = $_FILES['zip_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('Dosya yükleme hatası: ' . $file['error']);
    }
    
    if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
        wp_send_json_error('Sadece ZIP dosyaları kabul edilir.');
    }
    
    // Check ZipArchive availability
    if (!class_exists('ZipArchive')) {
        wp_send_json_error('PHP ZipArchive eklentisi yüklü değil.');
    }
    
    $zip = new ZipArchive();
    $result = $zip->open($file['tmp_name']);
    
    if ($result !== true) {
        wp_send_json_error('ZIP dosyası açılamadı. Hata kodu: ' . $result);
    }
    
    $temp_dir = get_temp_dir() . 'webnovel_upload_' . time() . '/';
    wp_mkdir_p($temp_dir);
    
    $zip->extractTo($temp_dir);
    $zip->close();
    
    // Find all .txt files recursively
    $txt_files = webnovel_find_txt_files($temp_dir);
    
    if (empty($txt_files)) {
        webnovel_cleanup_dir($temp_dir);
        wp_send_json_error('ZIP içinde .txt dosyası bulunamadı.');
    }
    
    // Sort files by volume (from folder or filename) then chapter number
    usort($txt_files, function($a, $b) use ($temp_dir) {
        $vol_a = webnovel_extract_volume_from_path($a, $temp_dir);
        $vol_b = webnovel_extract_volume_from_path($b, $temp_dir);
        if ($vol_a !== $vol_b) return $vol_a - $vol_b;
        $num_a = webnovel_extract_chapter_number(basename($a));
        $num_b = webnovel_extract_chapter_number(basename($b));
        return $num_a - $num_b;
    });
    
    $results = array(
        'total'   => count($txt_files),
        'success' => 0,
        'errors'  => 0,
        'log'     => array(),
    );
    
    foreach ($txt_files as $txt_file) {
        $filename = basename($txt_file, '.txt');
        $content = file_get_contents($txt_file);
        
        if ($content === false) {
            $results['errors']++;
            $results['log'][] = array('type' => 'error', 'message' => "Dosya okunamadı: $filename");
            continue;
        }
        
        // Handle BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // Detect encoding and convert to UTF-8
        $encoding = mb_detect_encoding($content, array('UTF-8', 'ISO-8859-9', 'Windows-1254', 'ISO-8859-1'), true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        
        // Parse filename
        $chapter_number = webnovel_extract_chapter_number($filename);
        // Extract volume from folder path first, then fallback to filename
        $volume_number = webnovel_extract_volume_from_path($txt_file, $temp_dir);

        // Get chapter title from first non-empty line of the file
        $lines = preg_split('/\r?\n/', $content);
        $chapter_title = '';
        $title_line_index = -1;
        foreach ($lines as $i => $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $chapter_title = $trimmed;
                $title_line_index = $i;
                break;
            }
        }
        // Fallback to filename if file has no content
        if ($chapter_title === '') {
            $chapter_title = webnovel_parse_chapter_title($filename);
        } else {
            // Remove the title line (and any following blank lines) from content
            array_splice($lines, 0, $title_line_index + 1);
            $content = implode("\n", $lines);
        }

        // Convert content to HTML paragraphs
        $content = trim($content);
        $paragraphs = preg_split('/\r?\n\s*\r?\n/', $content);
        $html_content = '';
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (!empty($para)) {
                // Handle single line breaks within paragraphs
                $para = nl2br(esc_html($para));
                $html_content .= '<p>' . $para . '</p>' . "\n";
            }
        }
        
        // If no double line breaks found, treat each line as paragraph
        if (count($paragraphs) <= 1 && !empty($content)) {
            $lines = preg_split('/\r?\n/', $content);
            $html_content = '';
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $html_content .= '<p>' . esc_html($line) . '</p>' . "\n";
                }
            }
        }
        
        // Create chapter post
        $post_data = array(
            'post_title'   => $chapter_title,
            'post_content' => $html_content,
            'post_status'  => $chapter_status,
            'post_type'    => 'chapter',
            'post_author'  => get_current_user_id(),
        );
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            $results['errors']++;
            $results['log'][] = array('type' => 'error', 'message' => "Bölüm oluşturulamadı: $chapter_title - " . $post_id->get_error_message());
        } else {
            // Save chapter meta
            update_post_meta($post_id, '_chapter_novel_id', $novel_id);
            update_post_meta($post_id, '_chapter_number', $chapter_number);
            if ($volume_number > 0) {
                update_post_meta($post_id, '_chapter_volume', $volume_number);
            }
            
            $results['success']++;
            $vol_text = $volume_number > 0 ? "Cilt $volume_number " : '';
            $results['log'][] = array('type' => 'success', 'message' => "✓ {$vol_text}Bölüm $chapter_number: $chapter_title");
        }
    }
    
    // Cleanup
    webnovel_cleanup_dir($temp_dir);
    
    wp_send_json_success($results);
}
add_action('wp_ajax_webnovel_bulk_upload', 'webnovel_handle_zip_upload');

// ============================================
// Helper Functions
// ============================================

/**
 * Parse chapter title from filename
 * Example: "I am Legendary BOSS Bölüm 51 - Bloody Crusher.txt"
 * Returns: "Bölüm 51 - Bloody Crusher"
 * Example: "I am Legendary BOSS Cilt 1 Bölüm 10 - Bloody Crusher.txt"
 * Returns: "Cilt 1 Bölüm 10 - Bloody Crusher"
 */
function webnovel_parse_chapter_title($filename) {
    // Remove .txt extension if present
    $filename = preg_replace('/\.txt$/i', '', $filename);
    
    // Try to find "Cilt XX Bölüm YY" pattern and extract from there
    if (preg_match('/(C[iİ]lt\s+\d+\s+B[öo]l[üu]m\s+\d+.*)/iu', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Try "Vol XX Chapter YY" pattern
    if (preg_match('/(Vol(?:ume)?\s+\d+\s+Chapter\s+\d+.*)/i', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Try to find "Bölüm XX" pattern and extract from there
    if (preg_match('/(B[öo]l[üu]m\s+\d+.*)/iu', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Try "Chapter XX" pattern
    if (preg_match('/(Chapter\s+\d+.*)/i', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Try "Cilt XX" without Bölüm
    if (preg_match('/(C[iİ]lt\s+\d+.*)/iu', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Try "Bölüm" without number
    if (preg_match('/(B[öo]l[üu]m\s+.*)/iu', $filename, $matches)) {
        return trim($matches[1]);
    }
    
    // Fallback: use the whole filename
    return trim($filename);
}

/**
 * Extract chapter number from filename
 */
function webnovel_extract_chapter_number($filename) {
    // Try to find "Bölüm XX" pattern
    if (preg_match('/B[öo]l[üu]m\s+(\d+)/iu', $filename, $matches)) {
        return intval($matches[1]);
    }
    
    // Try "Chapter XX" pattern
    if (preg_match('/Chapter\s+(\d+)/i', $filename, $matches)) {
        return intval($matches[1]);
    }
    
    // Try any number in the filename (skip volume numbers)
    if (preg_match('/(?<!C[iİ]lt\s)(?<!Vol\s)(?<!Volume\s)(\d+)/iu', $filename, $matches)) {
        return intval($matches[1]);
    }
    
    return 0;
}

/**
 * Extract volume (cilt) number from filename
 */
function webnovel_extract_volume_number($filename) {
    // Try to find "Cilt XX" pattern
    if (preg_match('/C[iİ]lt\s+(\d+)/iu', $filename, $matches)) {
        return intval($matches[1]);
    }
    
    // Try "Vol XX" or "Volume XX" pattern
    if (preg_match('/Vol(?:ume)?\s+(\d+)/i', $filename, $matches)) {
        return intval($matches[1]);
    }
    
    return 0;
}

/**
 * Recursively find all .txt files in a directory
 */
function webnovel_find_txt_files($dir) {
    $files = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'txt') {
            // Skip macOS resource fork files
            if (strpos($file->getFilename(), '._') === 0 || strpos($file->getFilename(), '.') === 0) {
                continue;
            }
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

/**
 * Extract volume number from file path (checks parent folder names)
 * Supports: "Cilt 1", "Cilt 2", "Vol 3", "Volume 1", or range folders like "1-100"
 * Falls back to filename-based extraction
 */
function webnovel_extract_volume_from_path($file_path, $base_dir) {
    // First check if the filename itself has volume info
    $filename_vol = webnovel_extract_volume_number(basename($file_path));
    if ($filename_vol > 0) return $filename_vol;
    
    // Get relative path from base directory
    $rel_path = str_replace($base_dir, '', $file_path);
    $rel_path = ltrim(str_replace('\\', '/', $rel_path), '/');
    $parts = explode('/', $rel_path);
    
    // Check each parent folder for volume info
    foreach ($parts as $part) {
        if ($part === basename($file_path)) continue; // skip the filename itself
        
        // Match "Cilt X", "Vol X", "Volume X", "C X"
        if (preg_match('/(?:cilt|vol(?:ume)?|c)[\s._-]*(\d+)/iu', $part, $m)) {
            return (int) $m[1];
        }
    }
    
    return 0;
}

/**
 * Recursively delete a directory
 */
function webnovel_cleanup_dir($dir) {
    if (!is_dir($dir)) return;
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    
    rmdir($dir);
}

// ============================================
// Helper: Get chapters for a novel
// ============================================
/**
 * Format a post's publish time as relative Turkish (e.g. "2 Saat", "11 Gün").
 * Used by both the homepage card and the chapter list — single source of truth.
 */
function webnovel_relative_time($post_id) {
    $diff = human_time_diff(get_the_time('U', $post_id), current_time('timestamp'));
    $replacements = array(
        '/\b(minutes|minute|mins|min|dakika|dk)\b/i' => 'Dk',
        '/\b(hours|hour|saat)\b/i'                   => 'Saat',
        '/\b(days|day|gün)\b/i'                      => 'Gün',
        '/\b(weeks|week|hafta)\b/i'                  => 'Hf',
        '/\b(months|month|ay)\b/i'                   => 'Ay',
        '/\b(years|year|yıl)\b/i'                    => 'Yıl',
        '/\b(seconds|second|saniye|sn)\b/i'          => 'Sn',
    );
    $diff = preg_replace(array_keys($replacements), array_values($replacements), $diff);
    return preg_replace('/(\d+)([a-zA-ZÇĞİÖŞÜçğıöşü])/u', '$1 $2', $diff);
}

/**
 * Get the N most recent chapters of a novel, ordered by Cilt+Bölüm DESC.
 * Optimized for homepage cards (LIMIT in SQL, single round-trip).
 */
function webnovel_get_latest_chapters($novel_id, $limit = 3) {
    global $wpdb;
    $limit = max(1, (int) $limit);
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_novel ON p.ID = pm_novel.post_id AND pm_novel.meta_key = '_chapter_novel_id'
         LEFT JOIN {$wpdb->postmeta} pm_vol ON p.ID = pm_vol.post_id AND pm_vol.meta_key = '_chapter_volume'
         LEFT JOIN {$wpdb->postmeta} pm_num ON p.ID = pm_num.post_id AND pm_num.meta_key = '_chapter_number'
         WHERE p.post_type = 'chapter'
         AND p.post_status = 'publish'
         AND pm_novel.meta_value = %s
         ORDER BY CAST(IFNULL(pm_vol.meta_value, 1) AS SIGNED) DESC,
                  CAST(IFNULL(pm_num.meta_value, 0) AS SIGNED) DESC
         LIMIT %d",
        $novel_id,
        $limit
    ));
    $posts = array();
    foreach ((array) $ids as $id) {
        $p = get_post($id);
        if ($p) $posts[] = $p;
    }
    return $posts;
}

function webnovel_get_chapters($novel_id, $order = 'ASC') {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_novel ON p.ID = pm_novel.post_id AND pm_novel.meta_key = '_chapter_novel_id'
         LEFT JOIN {$wpdb->postmeta} pm_vol ON p.ID = pm_vol.post_id AND pm_vol.meta_key = '_chapter_volume'
         LEFT JOIN {$wpdb->postmeta} pm_num ON p.ID = pm_num.post_id AND pm_num.meta_key = '_chapter_number'
         WHERE p.post_type = 'chapter' 
         AND p.post_status = 'publish' 
         AND pm_novel.meta_value = %s
         ORDER BY CAST(IFNULL(pm_vol.meta_value, 1) AS SIGNED) $order, 
                  CAST(IFNULL(pm_num.meta_value, 0) AS SIGNED) $order",
        $novel_id
    ));
    
    $posts = array();
    if ($results) {
        foreach ($results as $row) {
            $post = get_post($row->ID);
            if ($post) {
                $posts[] = $post;
            }
        }
    }
    return $posts;
}

/**
 * Get chapter count for a novel
 */
function webnovel_get_chapter_count($novel_id) {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
         WHERE p.post_type = 'chapter'
         AND p.post_status = 'publish'
         AND pm.meta_key = '_chapter_novel_id'
         AND pm.meta_value = %d",
        $novel_id
    ));
}

/**
 * Get adjacent chapter
 */
function webnovel_get_adjacent_chapter($novel_id, $current_chapter_number, $direction = 'next') {
    global $wpdb;
    
    $op = ($direction === 'next') ? '>' : '<';
    $order = ($direction === 'next') ? 'ASC' : 'DESC';
    
    // Get current chapter's volume and number safely
    $current_chapter_id = get_the_ID();
    $current_volume = (int)get_post_meta($current_chapter_id, '_chapter_volume', true) ?: 1;
    $current_number = (int)$current_chapter_number;

    $chapter_id = $wpdb->get_var($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm_novel ON p.ID = pm_novel.post_id AND pm_novel.meta_key = '_chapter_novel_id'
         LEFT JOIN {$wpdb->postmeta} pm_vol ON p.ID = pm_vol.post_id AND pm_vol.meta_key = '_chapter_volume'
         LEFT JOIN {$wpdb->postmeta} pm_num ON p.ID = pm_num.post_id AND pm_num.meta_key = '_chapter_number'
         WHERE p.post_type = 'chapter'
         AND p.post_status = 'publish'
         AND pm_novel.meta_value = %s
         AND (
            CAST(IFNULL(pm_vol.meta_value, 1) AS SIGNED) $op %d
            OR (CAST(IFNULL(pm_vol.meta_value, 1) AS SIGNED) = %d AND CAST(IFNULL(pm_num.meta_value, 0) AS SIGNED) $op %d)
         )
         ORDER BY CAST(IFNULL(pm_vol.meta_value, 1) AS SIGNED) $order, 
                  CAST(IFNULL(pm_num.meta_value, 0) AS SIGNED) $order
         LIMIT 1",
        $novel_id, $current_volume, $current_volume, $current_number
    ));
    
    return $chapter_id ? get_post($chapter_id) : null;
}

// ============================================
// Flush Rewrite Rules on Theme Activation
// ============================================
function webnovel_rewrite_flush() {
    webnovel_register_novel_cpt();
    webnovel_register_chapter_cpt();
    webnovel_register_taxonomies();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'webnovel_rewrite_flush');

// ============================================
// Add chapter count column to novel admin list
// ============================================
function webnovel_novel_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['chapter_count'] = 'Bölüm Sayısı';
        }
    }
    return $new_columns;
}
add_filter('manage_novel_posts_columns', 'webnovel_novel_columns');

function webnovel_novel_column_content($column, $post_id) {
    if ($column === 'chapter_count') {
        echo webnovel_get_chapter_count($post_id);
    }
}
add_action('manage_novel_posts_custom_column', 'webnovel_novel_column_content', 10, 2);

// ============================================
// Add novel filter column to chapter admin list
// ============================================
function webnovel_chapter_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['novel_name'] = 'Roman';
            $new_columns['chapter_vol'] = 'Cilt';
            $new_columns['chapter_num'] = 'Bölüm No';
        }
    }
    return $new_columns;
}
add_filter('manage_chapter_posts_columns', 'webnovel_chapter_columns');

function webnovel_chapter_column_content($column, $post_id) {
    if ($column === 'novel_name') {
        $novel_id = get_post_meta($post_id, '_chapter_novel_id', true);
        if ($novel_id) {
            $novel = get_post($novel_id);
            if ($novel) {
                echo '<a href="' . get_edit_post_link($novel_id) . '">' . esc_html($novel->post_title) . '</a>';
            } else {
                echo '—';
            }
        } else {
            echo '—';
        }
    }
    if ($column === 'chapter_vol') {
        $vol = get_post_meta($post_id, '_chapter_volume', true);
        echo $vol ? 'Cilt ' . $vol : '—';
    }
    if ($column === 'chapter_num') {
        echo get_post_meta($post_id, '_chapter_number', true) ?: '—';
    }
}
add_action('manage_chapter_posts_custom_column', 'webnovel_chapter_column_content', 10, 2);

// Make chapter number column sortable
function webnovel_chapter_sortable_columns($columns) {
    $columns['chapter_num'] = 'chapter_num';
    return $columns;
}
add_filter('manage_edit-chapter_sortable_columns', 'webnovel_chapter_sortable_columns');

function webnovel_chapter_orderby($query) {
    if (!is_admin()) return;
    
    $orderby = $query->get('orderby');
    if ($orderby === 'chapter_num') {
        $query->set('meta_key', '_chapter_number');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'webnovel_chapter_orderby');

// ============================================
// Filter chapters by novel in admin
// ============================================
function webnovel_chapter_filter_by_novel($post_type) {
    if ($post_type !== 'chapter') return;
    
    $novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'any',
    ));
    
    $selected = isset($_GET['filter_novel']) ? intval($_GET['filter_novel']) : 0;
    
    echo '<select name="filter_novel">';
    echo '<option value="">Tüm Romanlar</option>';
    foreach ($novels as $novel) {
        printf(
            '<option value="%d" %s>%s</option>',
            $novel->ID,
            selected($selected, $novel->ID, false),
            esc_html($novel->post_title)
        );
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'webnovel_chapter_filter_by_novel');

function webnovel_chapter_filter_query($query) {
    global $pagenow;
    
    if (!is_admin() || $pagenow !== 'edit.php') return;
    if ($query->get('post_type') !== 'chapter') return;
    if (empty($_GET['filter_novel'])) return;
    
    $query->set('meta_query', array(
        array(
            'key'   => '_chapter_novel_id',
            'value' => intval($_GET['filter_novel']),
        ),
    ));
}
add_action('pre_get_posts', 'webnovel_chapter_filter_query');

// ============================================
// Theme Settings: Register
// ============================================
function webnovel_register_settings() {
    // Ad settings
    register_setting('webnovel_settings_group', 'webnovel_ad_header');
    register_setting('webnovel_settings_group', 'webnovel_ad_before_content');
    register_setting('webnovel_settings_group', 'webnovel_ad_mid_content');
    register_setting('webnovel_settings_group', 'webnovel_ad_after_content');
    register_setting('webnovel_settings_group', 'webnovel_ad_sidebar');
    register_setting('webnovel_settings_group', 'webnovel_ad_footer');
    
    // Comment embed HTML
    register_setting('webnovel_settings_group', 'webnovel_comment_embed');
    
    // Homepage settings
    register_setting('webnovel_settings_group', 'webnovel_novels_per_page');
    register_setting('webnovel_settings_group', 'webnovel_slider_count');
    register_setting('webnovel_settings_group', 'webnovel_latest_series_count');
    
    register_setting('webnovel_settings_group', 'webnovel_donate_title');
    register_setting('webnovel_settings_group', 'webnovel_donate_description');
    register_setting('webnovel_settings_group', 'webnovel_donate_links');
    register_setting('webnovel_settings_group', 'webnovel_logo');
}
add_action('admin_init', 'webnovel_register_settings');

// ============================================
// Theme Settings: Page
// ============================================
function webnovel_settings_page() {
    ?>
    <div class="wrap">
        <h1>⚙️ WebNovel Tema Ayarları</h1>
        <form method="post" action="options.php">
            <?php settings_fields('webnovel_settings_group'); ?>
            <?php do_settings_sections('webnovel_settings_group'); ?>
            <h2 class="title">🖼️ Site Logosu</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Logo</th>
                    <td>
                        <?php 
                        $logo_id = get_option('webnovel_logo');
                        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
                        ?>
                        <div id="webnovel-logo-preview" style="margin-bottom: 10px; max-width: 250px; background: #f0f0f0; border: 1px solid #ccc; padding: 10px; display: <?php echo $logo_url ? 'block' : 'none'; ?>;">
                            <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 100%; height: auto; display: block;">
                        </div>
                        <input type="hidden" name="webnovel_logo" id="webnovel_logo_id" value="<?php echo esc_attr($logo_id); ?>">
                        <button type="button" class="button" id="webnovel-logo-select">Logo Seç / Değiştir</button>
                        <button type="button" class="button" id="webnovel-logo-remove" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">Logoyu Kaldır</button>
                        <p class="description">Header kısmında gösterilecek logoyu seçin. Seçilmezse site adı metin olarak gösterilir.</p>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(document).ready(function($) {
                var mediaFrame;
                $('#webnovel-logo-select').on('click', function(e) {
                    e.preventDefault();
                    if (mediaFrame) {
                        mediaFrame.open();
                        return;
                    }
                    mediaFrame = wp.media({
                        title: 'Logo Seç',
                        button: { text: 'Logoyu Kullan' },
                        multiple: false
                    });
                    mediaFrame.on('select', function() {
                        var attachment = mediaFrame.state().get('selection').first().toJSON();
                        $('#webnovel_logo_id').val(attachment.id);
                        $('#webnovel-logo-preview img').attr('src', attachment.url);
                        $('#webnovel-logo-preview').show();
                        $('#webnovel-logo-remove').show();
                    });
                    mediaFrame.open();
                });

                $('#webnovel-logo-remove').on('click', function() {
                    $('#webnovel_logo_id').val('');
                    $('#webnovel-logo-preview').hide();
                    $(this).hide();
                });
            });
            </script>

            <hr>
            
            <h2 class="title">🏠 Ana Sayfa Ayarları</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webnovel_novels_per_page">Sayfa Başına Seri Sayısı</label></th>
                    <td>
                        <input type="number" id="webnovel_novels_per_page" name="webnovel_novels_per_page" value="<?php echo esc_attr(get_option('webnovel_novels_per_page', 20)); ?>" min="4" max="100" style="width:80px;">
                        <p class="description">Ana sayfada kaç adet seri gösterilecek. Diğerleri sayfalama ile gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_slider_count">Slider Seri Sayısı</label></th>
                    <td>
                        <input type="number" id="webnovel_slider_count" name="webnovel_slider_count" value="<?php echo esc_attr(get_option('webnovel_slider_count', 15)); ?>" min="5" max="50" style="width:80px;">
                        <p class="description">Ana sayfa slider'ında kaç seri gösterilecek.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_latest_series_count">Son Güncellemeler Sayısı</label></th>
                    <td>
                        <input type="number" id="webnovel_latest_series_count" name="webnovel_latest_series_count" value="<?php echo esc_attr(get_option('webnovel_latest_series_count', 10)); ?>" min="1" max="50" style="width:80px;">
                        <p class="description">Ana sayfadaki "Son Güncellenen Seriler" tablosunda kaç seri gösterilecek.</p>
                    </td>
                </tr>
            </table>

            <hr>
            
            <h2 class="title">📢 Reklam Yerleşimi (AdSense)</h2>
            <p>Her alana reklam kodunuzu (HTML/JS) yapıştırın. Boş bırakırsanız o alanda reklam gösterilmez.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webnovel_ad_header">Header Reklamı</label></th>
                    <td>
                        <textarea id="webnovel_ad_header" name="webnovel_ad_header" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_header', '')); ?></textarea>
                        <p class="description">Site başlığının altında, sayfa içeriğinin üstünde gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_ad_before_content">İçerik Öncesi Reklam</label></th>
                    <td>
                        <textarea id="webnovel_ad_before_content" name="webnovel_ad_before_content" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_before_content', '')); ?></textarea>
                        <p class="description">Bölüm içeriğinin hemen üstünde gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_ad_mid_content">İçerik Ortası Reklam</label></th>
                    <td>
                        <textarea id="webnovel_ad_mid_content" name="webnovel_ad_mid_content" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_mid_content', '')); ?></textarea>
                        <p class="description">Bölüm içeriğinin ortasında gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_ad_after_content">İçerik Sonrası Reklam</label></th>
                    <td>
                        <textarea id="webnovel_ad_after_content" name="webnovel_ad_after_content" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_after_content', '')); ?></textarea>
                        <p class="description">Bölüm içeriğinin hemen altında gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_ad_sidebar">Kenar Çubuğu Reklamı</label></th>
                    <td>
                        <textarea id="webnovel_ad_sidebar" name="webnovel_ad_sidebar" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_sidebar', '')); ?></textarea>
                        <p class="description">Roman detay sayfasında yan tarafta gösterilir.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_ad_footer">Footer Reklamı</label></th>
                    <td>
                        <textarea id="webnovel_ad_footer" name="webnovel_ad_footer" rows="4" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_ad_footer', '')); ?></textarea>
                        <p class="description">Sayfa alt bilgisinin üstünde gösterilir.</p>
                    </td>
                </tr>
            </table>
            
            <hr>
            
            <h2 class="title">💬 Yorum Sistemi</h2>
            <p>Disqus, Hyvor Talk, CommentBox veya herhangi bir yorum sisteminizin HTML/JS embed kodunu yapıştırın.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webnovel_comment_embed">Yorum Embed Kodu</label></th>
                    <td>
                        <textarea id="webnovel_comment_embed" name="webnovel_comment_embed" rows="10" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_comment_embed', '')); ?></textarea>
                        <p class="description">Örnek: Disqus, Hyvor Talk, CommentBox vb. embed HTML/JS kodu.</p>
                    </td>
                </tr>
            </table>
            
            <hr>
            
            <h2 class="title">💝 Bağış Sayfası</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webnovel_donate_title">Bağış Başlığı</label></th>
                    <td>
                        <input type="text" id="webnovel_donate_title" name="webnovel_donate_title" value="<?php echo esc_attr(get_option('webnovel_donate_title', 'Bizi Destekleyin')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_donate_description">Bağış Açıklaması</label></th>
                    <td>
                        <textarea id="webnovel_donate_description" name="webnovel_donate_description" rows="5" cols="80" class="large-text"><?php echo esc_textarea(get_option('webnovel_donate_description', '')); ?></textarea>
                        <p class="description">Bağış sayfasında gösterilecek açıklama metni. HTML destekler.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webnovel_donate_links">Bağış Linkleri</label></th>
                    <td>
                        <textarea id="webnovel_donate_links" name="webnovel_donate_links" rows="6" cols="80" class="large-text code"><?php echo esc_textarea(get_option('webnovel_donate_links', '')); ?></textarea>
                        <p class="description">Her satıra bir link: <code>İkon|Başlık|URL</code> formatında. Örnek:<br>
                        <code>☕|Buy Me a Coffee|https://buymeacoffee.com/xxx</code><br>
                        <code>💳|Papara|https://papara.com/xxx</code><br>
                        <code>🎨|Patreon|https://patreon.com/xxx</code></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Ayarları Kaydet'); ?>
        </form>
    </div>
    <?php
}

// ============================================
// Ad & Comment Helper Functions
// ============================================

/**
 * Render an ad placement
 */
function webnovel_render_ad($position) {
    $ad_code = get_option('webnovel_ad_' . $position, '');
    if (!empty(trim($ad_code))) {
        echo '<div class="ad-slot ad-' . esc_attr($position) . '">' . $ad_code . '</div>';
    }
}

/**
 * Insert mid-content ad into chapter content
 */
function webnovel_insert_mid_content_ad($content) {
    if (!is_singular('chapter')) return $content;
    
    $ad_code = get_option('webnovel_ad_mid_content', '');
    if (empty(trim($ad_code))) return $content;
    
    $paragraphs = explode('</p>', $content);
    $total = count($paragraphs);
    
    if ($total < 4) return $content;
    
    $mid = (int) floor($total / 2);
    $ad_html = '<div class="ad-slot ad-mid-content">' . $ad_code . '</div>';
    
    $output = '';
    foreach ($paragraphs as $i => $para) {
        $output .= $para;
        if (!empty(trim($para))) $output .= '</p>';
        if ($i === $mid) {
            $output .= $ad_html;
        }
    }
    
    return $output;
}
add_filter('the_content', 'webnovel_insert_mid_content_ad', 20);

/**
 * Render comment embed HTML
 */
function webnovel_render_comments() {
    $embed = get_option('webnovel_comment_embed', '');
    if (!empty(trim($embed))) {
        echo '<div class="webnovel-comments">';
        echo '<div class="comments-header"><h3 style="font-size:18px; font-weight:700; color:var(--text-main); margin-bottom:16px;">💬 Yorumlar</h3></div>';
        echo '<div class="comments-embed">' . $embed . '</div>';
        echo '</div>';
    } else {
        // Fallback to Native WP comments
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
    }
}



// ============================================
// Upload Limits (try to increase from theme)
// ============================================
function webnovel_increase_upload_limits() {
    @ini_set('upload_max_filesize', '64M');
    @ini_set('post_max_size', '64M');
    @ini_set('max_execution_time', '300');
    @ini_set('max_input_time', '300');
    @ini_set('memory_limit', '256M');
}
add_action('init', 'webnovel_increase_upload_limits');

function webnovel_upload_size_limit($size) {
    return 64 * 1024 * 1024; // 64MB
}
add_filter('upload_size_limit', 'webnovel_upload_size_limit');

// ============================================
// Novel View Tracking (for Popular sidebar)
// ============================================
function webnovel_track_novel_view() {
    if (!is_singular('novel')) return;
    
    $novel_id = get_the_ID();
    
    // Don't count admin views
    if (current_user_can('manage_options')) return;
    
    // Simple view count
    $total_views = (int) get_post_meta($novel_id, '_novel_views_total', true);
    update_post_meta($novel_id, '_novel_views_total', $total_views + 1);
    
    // Weekly/Monthly/Yearly view tracking via transient-based counters
    $week_key = '_novel_views_week_' . date('Y_W');
    $month_key = '_novel_views_month_' . date('Y_m');
    $year_key = '_novel_views_year_' . date('Y');
    
    $week_views = (int) get_post_meta($novel_id, $week_key, true);
    update_post_meta($novel_id, $week_key, $week_views + 1);
    
    $month_views = (int) get_post_meta($novel_id, $month_key, true);
    update_post_meta($novel_id, $month_key, $month_views + 1);
    
    $year_views = (int) get_post_meta($novel_id, $year_key, true);
    update_post_meta($novel_id, $year_key, $year_views + 1);
}
add_action('wp_head', 'webnovel_track_novel_view');

/**
 * Get popular novels for a given period
 */
function webnovel_get_popular_novels($period = 'week', $limit = 5) {
    switch ($period) {
        case 'week':
            $meta_key = '_novel_views_week_' . date('Y_W');
            break;
        case 'month':
            $meta_key = '_novel_views_month_' . date('Y_m');
            break;
        case 'year':
            $meta_key = '_novel_views_year_' . date('Y');
            break;
        default:
            $meta_key = '_novel_views_total';
    }
    
    return get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => $limit,
        'meta_key'       => $meta_key,
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ));
}

// ============================================
// Random Novel Redirect
// ============================================
function webnovel_random_novel_rewrite() {
    add_rewrite_rule('^random-novel/?$', 'index.php?webnovel_random=1', 'top');
}
add_action('init', 'webnovel_random_novel_rewrite');

function webnovel_random_query_vars($vars) {
    $vars[] = 'webnovel_random';
    return $vars;
}
add_filter('query_vars', 'webnovel_random_query_vars');

function webnovel_random_redirect() {
    if (get_query_var('webnovel_random')) {
        $novels = get_posts(array(
            'post_type'      => 'novel',
            'posts_per_page' => 1,
            'orderby'        => 'rand',
            'post_status'    => 'publish',
        ));
        if (!empty($novels)) {
            wp_redirect(get_permalink($novels[0]->ID));
            exit;
        }
        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'webnovel_random_redirect');

// ============================================
// Author Pages
// ============================================
function webnovel_author_rewrite() {
    add_rewrite_rule('^yazar/([^/]+)/?$', 'index.php?webnovel_author=$matches[1]', 'top');
    add_rewrite_rule('^yazar/([^/]+)/page/([0-9]+)/?$', 'index.php?webnovel_author=$matches[1]&paged=$matches[2]', 'top');
}
add_action('init', 'webnovel_author_rewrite');

function webnovel_author_query_vars($vars) {
    $vars[] = 'webnovel_author';
    return $vars;
}
add_filter('query_vars', 'webnovel_author_query_vars');

function webnovel_author_template($template) {
    if (get_query_var('webnovel_author')) {
        $author_template = locate_template('page-author.php');
        if ($author_template) return $author_template;
    }
    return $template;
}

// Donation page auto-routing by slug
function webnovel_donate_template($template) {
    global $wp_query;
    if (is_page()) {
        $slug = get_queried_object()->post_name ?? '';
        if (in_array($slug, array('bagis', 'donate', 'bağış', 'donation'))) {
            $donate_template = locate_template('page-donate.php');
            if ($donate_template) return $donate_template;
        }
    }
    return $template;
}
add_filter('template_include', 'webnovel_donate_template', 5);
add_filter('template_include', 'webnovel_author_template');

// ============================================
// Bulk Delete Admin Page
// ============================================
function webnovel_add_bulk_delete_menu() {
    add_menu_page(
        'Toplu Silme',
        'Toplu Silme',
        'manage_options',
        'webnovel-bulk-delete',
        'webnovel_bulk_delete_page',
        'dashicons-trash',
        9
    );
}
add_action('admin_menu', 'webnovel_add_bulk_delete_menu');

function webnovel_bulk_delete_page() {
    $novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'any',
    ));
    ?>
    <div class="wrap">
        <h1>🗑️ Toplu Bölüm Silme</h1>
        <p>Bir roman seçin ve bölümlerini toplu olarak silin.</p>
        
        <table class="form-table" style="max-width:700px;">
            <tr>
                <th scope="row"><label for="delete_novel_id">Roman Seçin</label></th>
                <td>
                    <select id="delete_novel_id" style="min-width:300px;">
                        <option value="">-- Roman Seçin --</option>
                        <?php foreach ($novels as $novel) : ?>
                            <option value="<?php echo $novel->ID; ?>"><?php echo esc_html($novel->post_title); ?> (<?php echo webnovel_get_chapter_count($novel->ID); ?> bölüm)</option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        
        <div id="bulk-delete-chapters" style="display:none; margin-top:20px; max-width:800px;">
            <div style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                <label><input type="checkbox" id="select-all-chapters"> <strong>Tümünü Seç</strong></label>
                <button type="button" id="btn-delete-selected" class="button button-primary" style="background:#dc3545;border-color:#dc3545;">🗑️ Seçilenleri Sil</button>
                <button type="button" id="btn-delete-all" class="button" style="background:#dc3545;border-color:#dc3545;color:#fff;">⚠️ Tüm Bölümleri Sil</button>
                <span id="delete-count" style="color:#666;"></span>
            </div>
            <div id="chapters-list" style="max-height:500px;overflow-y:auto;border:1px solid #ccc;border-radius:4px;"></div>
            <div id="delete-progress" style="display:none;margin-top:15px;">
                <div style="width:100%;height:24px;background:#ddd;border-radius:12px;overflow:hidden;">
                    <div id="delete-progress-bar" style="height:100%;background:linear-gradient(90deg,#dc3545,#ff6b6b);border-radius:12px;transition:width 0.3s;width:0%;"></div>
                </div>
                <p id="delete-progress-text" style="margin-top:8px;"></p>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var deleteNonce = '<?php echo wp_create_nonce('webnovel_bulk_delete'); ?>';
        
        // Load chapters when novel selected
        $('#delete_novel_id').on('change', function() {
            var novelId = $(this).val();
            if (!novelId) {
                $('#bulk-delete-chapters').hide();
                return;
            }
            
            $.post(ajaxurl, {
                action: 'webnovel_get_chapters_list',
                nonce: deleteNonce,
                novel_id: novelId
            }, function(response) {
                if (response.success) {
                    var html = '';
                    response.data.chapters.forEach(function(ch) {
                        html += '<label style="display:flex;align-items:center;padding:8px 12px;border-bottom:1px solid #eee;cursor:pointer;gap:8px;" onmouseover="this.style.background=\'#f5f5f5\'" onmouseout="this.style.background=\'\'">';
                        html += '<input type="checkbox" class="chapter-cb" value="' + ch.id + '">';
                        html += '<span style="color:#888;min-width:50px;">#' + ch.number + '</span>';
                        html += '<span>' + ch.title + '</span>';
                        html += '<span style="color:#aaa;margin-left:auto;font-size:12px;">' + ch.date + '</span>';
                        html += '</label>';
                    });
                    $('#chapters-list').html(html);
                    $('#delete-count').text(response.data.chapters.length + ' bölüm');
                    $('#bulk-delete-chapters').show();
                    $('#select-all-chapters').prop('checked', false);
                }
            });
        });
        
        // Select all
        $('#select-all-chapters').on('change', function() {
            $('.chapter-cb').prop('checked', this.checked);
        });
        
        // Delete selected
        $('#btn-delete-selected').on('click', function() {
            var ids = [];
            $('.chapter-cb:checked').each(function() { ids.push($(this).val()); });
            if (ids.length === 0) { alert('Lütfen silinecek bölümleri seçin!'); return; }
            if (!confirm(ids.length + ' bölüm silinecek. Emin misiniz?')) return;
            doDelete(ids);
        });
        
        // Delete all
        $('#btn-delete-all').on('click', function() {
            var ids = [];
            $('.chapter-cb').each(function() { ids.push($(this).val()); });
            if (ids.length === 0) return;
            if (!confirm('⚠️ ' + ids.length + ' bölümün TAMAMI silinecek! Bu işlem geri alınamaz. Emin misiniz?')) return;
            if (!confirm('GERÇEKTEN EMİN MİSİNİZ? Tüm bölümler kalıcı olarak silinecek!')) return;
            doDelete(ids);
        });
        
        function doDelete(ids) {
            $('#delete-progress').show();
            var total = ids.length;
            var done = 0;
            var errors = 0;
            
            function deleteNext() {
                if (ids.length === 0) {
                    $('#delete-progress-bar').css('width', '100%');
                    $('#delete-progress-text').html('<strong style="color:#22c55e;">✓ Tamamlandı!</strong> ' + done + ' bölüm silindi. ' + (errors > 0 ? '<span style="color:red;">' + errors + ' hata</span>' : ''));
                    // Reload chapters list
                    $('#delete_novel_id').trigger('change');
                    return;
                }
                
                var batch = ids.splice(0, 5); // Delete 5 at a time
                $.post(ajaxurl, {
                    action: 'webnovel_bulk_delete_chapters',
                    nonce: deleteNonce,
                    chapter_ids: batch
                }, function(response) {
                    if (response.success) {
                        done += response.data.deleted;
                        errors += response.data.errors;
                    } else {
                        errors += batch.length;
                    }
                    var pct = Math.round(((total - ids.length) / total) * 100);
                    $('#delete-progress-bar').css('width', pct + '%');
                    $('#delete-progress-text').text('Siliniyor... ' + done + '/' + total);
                    deleteNext();
                }).fail(function() {
                    errors += batch.length;
                    deleteNext();
                });
            }
            
            deleteNext();
        }
    });
    </script>
    <?php
}

// AJAX: Get chapters list for bulk delete
function webnovel_ajax_get_chapters_list() {
    check_ajax_referer('webnovel_bulk_delete', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkiniz yok.');
    
    $novel_id = intval($_POST['novel_id']);
    $chapters = webnovel_get_chapters($novel_id, 'ASC');
    
    $list = array();
    foreach ($chapters as $ch) {
        $list[] = array(
            'id'     => $ch->ID,
            'title'  => $ch->post_title,
            'number' => get_post_meta($ch->ID, '_chapter_number', true),
            'date'   => get_the_date('d M Y', $ch->ID),
        );
    }
    
    wp_send_json_success(array('chapters' => $list));
}
add_action('wp_ajax_webnovel_get_chapters_list', 'webnovel_ajax_get_chapters_list');

// AJAX: Delete chapters
function webnovel_ajax_bulk_delete_chapters() {
    check_ajax_referer('webnovel_bulk_delete', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkiniz yok.');
    
    $chapter_ids = isset($_POST['chapter_ids']) ? array_map('intval', $_POST['chapter_ids']) : array();
    $deleted = 0;
    $errors = 0;
    
    foreach ($chapter_ids as $id) {
        $post = get_post($id);
        if ($post && $post->post_type === 'chapter') {
            $result = wp_delete_post($id, true); // Force delete (skip trash)
            if ($result) {
                $deleted++;
            } else {
                $errors++;
            }
        } else {
            $errors++;
        }
    }
    
    wp_send_json_success(array('deleted' => $deleted, 'errors' => $errors));
}
add_action('wp_ajax_webnovel_bulk_delete_chapters', 'webnovel_ajax_bulk_delete_chapters');

// ============================================
// Flush rewrite on theme activation/settings save
// ============================================
function webnovel_flush_rewrites_on_activation() {
    webnovel_register_novel_cpt();
    webnovel_register_chapter_cpt();
    webnovel_register_taxonomies();
    webnovel_random_novel_rewrite();
    webnovel_author_rewrite();
    flush_rewrite_rules();
}
// Update the activation hook - remove old one and add new
remove_action('after_switch_theme', 'webnovel_rewrite_flush');
add_action('after_switch_theme', 'webnovel_flush_rewrites_on_activation');

// ============================================
// Fix /page/2/ empty page on homepage
// ============================================
function webnovel_fix_homepage_pagination($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('post_type', 'novel');
        $query->set('posts_per_page', intval(get_option('webnovel_novels_per_page', 20)));
    }
}
add_action('pre_get_posts', 'webnovel_fix_homepage_pagination');

// ============================================
// Helper: Get latest updated series (by last chapter added)
// ============================================
function webnovel_get_latest_updated_series($limit = 20) {
    global $wpdb;
    
    // Get latest chapter per novel (unique novels only)
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT latest.chapter_id, latest.novel_id, latest.chapter_number, latest.chapter_title, latest.post_date,
                p_novel.post_title as novel_title, p_novel.guid as novel_link
         FROM (
             SELECT p.ID as chapter_id, pm_novel.meta_value as novel_id, 
                    pm_num.meta_value as chapter_number, p.post_title as chapter_title, p.post_date,
                    ROW_NUMBER() OVER (PARTITION BY pm_novel.meta_value ORDER BY p.post_date DESC) as rn
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm_novel ON p.ID = pm_novel.post_id AND pm_novel.meta_key = '_chapter_novel_id'
             INNER JOIN {$wpdb->postmeta} pm_num ON p.ID = pm_num.post_id AND pm_num.meta_key = '_chapter_number'
             WHERE p.post_type = 'chapter' AND p.post_status = 'publish'
         ) latest
         INNER JOIN {$wpdb->posts} p_novel ON latest.novel_id = p_novel.ID
         WHERE latest.rn = 1 AND p_novel.post_status = 'publish'
         ORDER BY latest.post_date DESC
         LIMIT %d",
        $limit
    ), ARRAY_A);
    
    $items = array();
    foreach ($results as $row) {
        $status = get_post_meta($row['novel_id'], '_novel_status', true) ?: 'ongoing';
        $total_chapters = webnovel_get_chapter_count($row['novel_id']);
        $time_diff = human_time_diff(strtotime($row['post_date']), current_time('timestamp'));
        
        $items[] = array(
            'novel_id'        => $row['novel_id'],
            'novel_title'     => $row['novel_title'],
            'novel_cover'     => webnovel_get_cover_url($row['novel_id'], 'thumbnail'),
            'novel_status'    => $status,
            'chapter_id'      => $row['chapter_id'],
            'chapter_title'   => $row['chapter_title'],
            'chapter_number'  => $row['chapter_number'],
            'total_chapters'  => $total_chapters,
            'time_ago'        => $time_diff . ' önce',
            'novel_link'      => get_permalink($row['novel_id']),
            'chapter_link'    => get_permalink($row['chapter_id']),
        );
    }
    
    return $items;
}

/**
 * Helper: Get latest chapter URL for a novel
 */
function webnovel_get_latest_chapter_url($novel_id) {
    if (!$novel_id) return '';
    $latest = get_posts(array(
        'post_type'      => 'chapter',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => '_chapter_novel_id',
                'value' => $novel_id,
            ),
        ),
        'orderby'        => 'post_date',
        'order'          => 'DESC',
    ));
    return !empty($latest) ? get_permalink($latest[0]->ID) : '';
}

/**
 * Helper: Get first chapter URL for a novel
 */
function webnovel_get_first_chapter_url($novel_id) {
    if (!$novel_id) return '';
    $first = get_posts(array(
        'post_type'      => 'chapter',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => '_chapter_novel_id',
                'value' => $novel_id,
            ),
        ),
        'orderby'        => 'post_date',
        'order'          => 'ASC',
    ));
    return !empty($first) ? get_permalink($first[0]->ID) : '';
}

// ============================================
// Helper: Get latest chapter number for a novel
// ============================================
function webnovel_get_latest_chapter_number($novel_id) {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(CAST(pm.meta_value AS SIGNED)) FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_chapter_number'
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_chapter_novel_id'
         WHERE p.post_type = 'chapter' AND p.post_status = 'publish' AND pm2.meta_value = %d",
        $novel_id
    ));
}

// ============================================
// AJAX: Get chapter content (dynamic loading for copy protection)
// ============================================
function webnovel_ajax_get_chapter_content() {
    check_ajax_referer('webnovel_reader_nonce', 'nonce');
    
    $chapter_id = intval($_POST['chapter_id']);
    $chapter = get_post($chapter_id);
    
    if (!$chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
        wp_send_json_error('İçerik bulunamadı.');
    }
    
    $content = apply_filters('the_content', $chapter->post_content);
    
    // Base64 encode for basic obfuscation
    wp_send_json_success(array(
        'content' => base64_encode($content),
    ));
}
add_action('wp_ajax_webnovel_get_chapter', 'webnovel_ajax_get_chapter_content');
add_action('wp_ajax_nopriv_webnovel_get_chapter', 'webnovel_ajax_get_chapter_content');

// Localize moved into webnovel_scripts() for reliability
// ============================================
// Global Query Filters
// ============================================
function webnovel_filter_by_status($query) {
    if (!is_admin() && $query->is_main_query() && (is_post_type_archive('novel') || is_tax('novel_genre') || is_tax('novel_tag'))) {
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        if (!empty($status)) {
            $meta_query = $query->get('meta_query') ?: array();
            $meta_query[] = array(
                'key'     => '_novel_status',
                'value'   => $status,
                'compare' => '=',
            );
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'webnovel_filter_by_status');

/**
 * AJAX: Check for updates in followed series
 */
function webnovel_check_updates_callback() {
    $novel_ids = isset($_POST['novel_ids']) ? (array) $_POST['novel_ids'] : array();
    $results = array();

    foreach ($novel_ids as $id) {
        $id = intval($id);
        if ($id <= 0) continue;

        $latest_chapter = get_posts(array(
            'post_type'      => 'chapter',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_chapter_novel_id',
                    'value' => $id,
                )
            ),
            'meta_key'       => '_chapter_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC'
        ));

        if (!empty($latest_chapter)) {
            $ch = $latest_chapter[0];
            $results[$id] = array(
                'latest_id'     => $ch->ID,
                'chapter_title' => get_the_title($ch->ID),
                'chapter_num'   => get_post_meta($ch->ID, '_chapter_number', true),
                'url'           => get_the_permalink($id),
                'update_time'   => get_the_modified_date('c', $ch->ID)
            );
        }
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_webnovel_check_updates', 'webnovel_check_updates_callback');
add_action('wp_ajax_nopriv_webnovel_check_updates', 'webnovel_check_updates_callback');

/**
 * Register Followed Page Template (Pseudo-shortcode)
 */
function webnovel_followed_shortcode() {
    return '<div id="followed-series-container" class="novels-grid"><p class="loading-msg">Yükleniyor...</p></div>';
}
add_shortcode('webnovel_followed', 'webnovel_followed_shortcode');
// ============================================
// Reading Stats & Trending System
// ============================================

/**
 * Handle AJAX view logging
 */
function webnovel_ajax_log_view() {
    check_ajax_referer('webnovel_reader_nonce', 'nonce');
    
    $chapter_id = isset($_POST['chapterId']) ? intval($_POST['chapterId']) : 0;
    if (!$chapter_id) wp_send_json_error('Invalid ID');
    
    $novel_id = get_post_meta($chapter_id, '_chapter_novel', true);
    if (!$novel_id) {
        // Fallback: try to find parent if novel is not stored as meta
        $novel_id = wp_get_post_parent_id($chapter_id);
    }
    
    // 1. Increment Chapter Total
    $chapter_views = (int)get_post_meta($chapter_id, '_chapter_views_total', true);
    update_post_meta($chapter_id, '_chapter_views_total', $chapter_views + 1);
    
    if ($novel_id) {
        webnovel_increment_novel_stats($novel_id);
    }
    
    wp_send_json_success('View logged');
}
add_action('wp_ajax_webnovel_log_view', 'webnovel_ajax_log_view');
add_action('wp_ajax_nopriv_webnovel_log_view', 'webnovel_ajax_log_view');

/**
 * Increment and Reset Novel Stats
 */
function webnovel_increment_novel_stats($novel_id) {
    $today = date('Y-m-d');
    $current_week = date('W');
    $current_month = date('m');
    $current_year = date('Y');
    
    $last_reset = get_post_meta($novel_id, '_novel_views_last_reset', true);
    
    // Check for Resets
    if ($last_reset !== $today) {
        // Reset daily
        update_post_meta($novel_id, '_novel_views_today', 0);
        
        $last_reset_week = get_post_meta($novel_id, '_novel_week_num', true);
        if ($last_reset_week !== $current_week) {
            update_post_meta($novel_id, '_novel_views_week', 0);
            update_post_meta($novel_id, '_novel_week_num', $current_week);
        }
        
        $last_reset_month = get_post_meta($novel_id, '_novel_month_num', true);
        if ($last_reset_month !== $current_month) {
            update_post_meta($novel_id, '_novel_views_month', 0);
            update_post_meta($novel_id, '_novel_month_num', $current_month);
        }
        
        $last_reset_year = get_post_meta($novel_id, '_novel_year_num', true);
        if ($last_reset_year !== $current_year) {
            update_post_meta($novel_id, '_novel_views_year', 0);
            update_post_meta($novel_id, '_novel_year_num', $current_year);
        }
        
        update_post_meta($novel_id, '_novel_views_last_reset', $today);
    }
    
    // Increment Stats
    $v_today = (int)get_post_meta($novel_id, '_novel_views_today', true);
    $v_week  = (int)get_post_meta($novel_id, '_novel_views_week', true);
    $v_month = (int)get_post_meta($novel_id, '_novel_views_month', true);
    $v_year  = (int)get_post_meta($novel_id, '_novel_views_year', true);
    $v_total = (int)get_post_meta($novel_id, '_novel_views_total', true);
    
    update_post_meta($novel_id, '_novel_views_today', $v_today + 1);
    update_post_meta($novel_id, '_novel_views_week', $v_week + 1);
    update_post_meta($novel_id, '_novel_views_month', $v_month + 1);
    update_post_meta($novel_id, '_novel_views_year', $v_year + 1);
    update_post_meta($novel_id, '_novel_views_total', $v_total + 1);
}

/**
 * Get Trending Novels
 */
function webnovel_get_trending($period = 'today', $limit = 10) {
    if ($period === 'rating') {
        $meta_key = '_novel_rating_avg';
    } else {
        $meta_key = '_novel_views_' . $period;
        if ($period === 'total') $meta_key = '_novel_views_total';
    }
    
    // Check if we need to seed any novels first
    $unseeded_novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => 20,
        'meta_query'     => array(
            array(
                'key'     => $meta_key,
                'compare' => 'NOT EXISTS',
            ),
        ),
    ));
    
    foreach ($unseeded_novels as $novel) {
        webnovel_seed_novel_stats($novel->ID);
    }
    
    $args = array(
        'post_type'      => 'novel',
        'posts_per_page' => $limit,
        'meta_key'       => $meta_key,
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC'
    );
    
    return new WP_Query($args);
}

// ============================================
// Rating System
// ============================================

/**
 * Handle AJAX Rating
 */
function webnovel_ajax_rate_novel() {
    check_ajax_referer('webnovel_reader_nonce', 'nonce');
    
    $novel_id = isset($_POST['novel_id']) ? intval($_POST['novel_id']) : 0;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
    
    if (!$novel_id || $rating < 1 || $rating > 5) wp_send_json_error('Geçersiz veri');
    
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $ratings_data = get_post_meta($novel_id, '_novel_ratings_map', true) ?: array();
    
    // Update or Add rating for this IP
    $ratings_data[$user_ip] = $rating;
    
    $total_sum = array_sum($ratings_data);
    $total_count = count($ratings_data);
    $new_avg = round($total_sum / $total_count, 1);
    
    update_post_meta($novel_id, '_novel_ratings_map', $ratings_data);
    update_post_meta($novel_id, '_novel_rating_sum', $total_sum);
    update_post_meta($novel_id, '_novel_rating_count', $total_count);
    update_post_meta($novel_id, '_novel_rating_avg', $new_avg);
    
    wp_send_json_success(array(
        'avg' => $new_avg,
        'count' => $total_count,
        'user_rating' => $rating
    ));
}
add_action('wp_ajax_webnovel_rate_novel', 'webnovel_ajax_rate_novel');
add_action('wp_ajax_nopriv_webnovel_rate_novel', 'webnovel_ajax_rate_novel');

/**
 * Build the novel synopsis HTML and extract plugin-injected social widgets
 * (Jetpack Sharing/Likes, AddToAny, etc.). Returns:
 *   ['synopsis' => HTML, 'social' => extracted social HTML]
 *
 * Synopsis source priority:
 *   1. Post Excerpt ("Özet" field) — preferred (semantically correct)
 *   2. Post Content ("İçerik" field) — fallback, with social widgets stripped
 *
 * Social widgets are extracted from the_content() output (which is where
 * Jetpack/AddToAny/etc. hook in via 'the_content' filter at priority 19/30).
 */
function webnovel_split_social_widgets() {
    $social_pattern = '/<(?:div|section|aside)[^>]*class\s*=\s*["\'][^"\']*\b(?:sharedaddy|sd-block|sd-rating|sd-sharing|jp-likes-widget(?:-wrapper)?|jp-relatedposts|addtoany_share_save_container|a2a_kit|essb_links|social-warfare|st-sharethis|heateor_sss_sharing_container)\b[^"\']*["\'][^>]*>/i';

    // Helper: split HTML at first social wrapper (everything before = clean, after = social)
    $split = function ($html) use ($social_pattern) {
        if (empty($html) || !preg_match($social_pattern, $html, $m, PREG_OFFSET_CAPTURE)) {
            return array('clean' => trim((string) $html), 'social' => '');
        }
        $pos = $m[0][1];
        return array(
            'clean'  => trim(substr($html, 0, $pos)),
            'social' => trim(substr($html, $pos)),
        );
    };

    // Capture content (Jetpack & co. hook into 'the_content' filter)
    ob_start();
    the_content();
    $content_split = $split(ob_get_clean());

    // Build synopsis: prefer Excerpt (also clean it — Jetpack hooks 'the_excerpt' too)
    if (has_excerpt()) {
        $excerpt_split = $split(apply_filters('the_excerpt', get_the_excerpt()));
        $synopsis = $excerpt_split['clean'];
        // Use whichever source actually had social widgets
        $social = !empty($content_split['social']) ? $content_split['social'] : $excerpt_split['social'];
    } else {
        $synopsis = $content_split['clean'];
        $social   = $content_split['social'];
    }

    return array(
        'synopsis' => $synopsis,
        'content'  => $synopsis, // backwards-compat alias
        'social'   => $social,
    );
}

/**
 * Get Share Links
 */
function webnovel_get_share_links($url, $title) {
    $title_enc = urlencode($title);
    $url_enc = urlencode($url);
    
    return array(
        'fb' => "https://www.facebook.com/sharer/sharer.php?u=$url_enc",
        'x'  => "https://twitter.com/intent/tweet?text=$title_enc&url=$url_enc",
        'wa' => "https://api.whatsapp.com/send?text=$title_enc%20$url_enc",
        'rd' => "https://www.reddit.com/submit?url=$url_enc&title=$title_enc"
    );
}
/**
 * Seed Novel Stats based on Chapter Count (Mandatory Minimum)
 */
function webnovel_seed_novel_stats($novel_id) {
    if (!$novel_id) return;
    
    $chapters = get_posts(array(
        'post_type'      => 'chapter',
        'meta_key'       => '_chapter_novel_id',
        'meta_value'     => $novel_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => array('publish', 'draft', 'pending', 'private', 'future'),
    ));
    
    $count = count($chapters);
    if ($count <= 0) $count = 0;
    
    // Total: Ensure it's at least the chapter count
    $current_total = (int)get_post_meta($novel_id, '_novel_views_total', true);
    if ($current_total < $count) {
        update_post_meta($novel_id, '_novel_views_total', $count);
    }
    
    // Safety Force: If still 0 and count > 0, set it
    if ($count > 0 && (int)get_post_meta($novel_id, '_novel_views_total', true) <= 0) {
        update_post_meta($novel_id, '_novel_views_total', $count);
    }
    
    // Periods: Boost if 0
    if ((int)get_post_meta($novel_id, '_novel_views_today', true) <= 0) {
        update_post_meta($novel_id, '_novel_views_today', floor($count * 0.05));
    }
    if ((int)get_post_meta($novel_id, '_novel_views_week', true) <= 0) {
        update_post_meta($novel_id, '_novel_views_week', floor($count * 0.15));
    }
    if ((int)get_post_meta($novel_id, '_novel_views_month', true) <= 0) {
        update_post_meta($novel_id, '_novel_views_month', floor($count * 0.40));
    }
    if ((int)get_post_meta($novel_id, '_novel_views_year', true) <= 0) {
        update_post_meta($novel_id, '_novel_views_year', floor($count * 0.80));
    }
}


/**
 * Safely Get Novel View Count (with Seeding)
 */
function webnovel_get_novel_views($novel_id, $period = 'total') {
    if (!$novel_id) return 0;
    
    // Ensure data is seeded
    webnovel_seed_novel_stats($novel_id);
    
    $meta_key = '_novel_views_' . $period;
    if ($period === 'total') $meta_key = '_novel_views_total';
    
    return (int)get_post_meta($novel_id, $meta_key, true);
}

/**
 * Format Number for Display (e.g. 1.2K)
 */
function webnovel_format_number($num) {
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 1000) return round($num / 1000, 1) . 'K';
    return $num;
}

/**
 * Register custom query variables
 */
function webnovel_query_vars($vars) {
    $vars[] = 'novel_author';
    return $vars;
}
add_filter('query_vars', 'webnovel_query_vars');

/**
 * Custom Search Filter for Author
 */
function webnovel_pre_get_posts($query) {
    if ($query->is_main_query() && !is_admin()) {
        $novel_author = get_query_var('novel_author');
        
        // If searching specifically for an author
        if ($novel_author) {
            $author = sanitize_text_field($novel_author);
            $query->set('post_type', 'novel');
            
            $meta_query = array(
                array(
                    'key'     => '_novel_author',
                    'value'   => $author,
                    'compare' => 'LIKE',
                )
            );
            
            $query->set('meta_query', $meta_query);
            
            // Also unset the search query if it was set so it doesn't conflict
            if ($query->is_search()) {
                $query->set('s', '');
            }
        }
    }
}
add_action('pre_get_posts', 'webnovel_pre_get_posts');

// ============================================
// WP Customizer: Homepage Settings
// ============================================
function webnovel_customize_register($wp_customize) {
    $wp_customize->add_section('webnovel_homepage_options', array(
        'title'    => __('Ana Sayfa Ayarları', 'webnovel-reader'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('latest_series_count', array(
        'default'           => 10,
        'sanitize_callback' => 'absint',
        'capability'        => 'edit_theme_options',
    ));

    $wp_customize->add_control('latest_series_count_control', array(
        'label'       => __('Son Güncellenen Seri Sayısı', 'webnovel-reader'),
        'description' => __('Ana sayfadaki son güncellenen seriler listesinde kaç adet seri gösterileceğini rakamla girin.', 'webnovel-reader'),
        'section'     => 'webnovel_homepage_options',
        'settings'    => 'latest_series_count',
        'type'        => 'text',
    ));
}
add_action('customize_register', 'webnovel_customize_register', 20);

// ============================================
// NOVELTURK THEME OPTIONS PAGE
// ============================================
function webnovel_theme_options_menu() {
    add_menu_page(
        'Tema Ayarları & Duyurular', 
        'Duyuru / Ayarlar', 
        'manage_options', 
        'webnovel-theme-options', 
        'webnovel_theme_options_page',
        'dashicons-megaphone',
        30
    );
}
add_action('admin_menu', 'webnovel_theme_options_menu');

function webnovel_theme_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['webnovel_save_options']) && check_admin_referer('webnovel_options_verify')) {
        // Save announcements
        $embed_code = isset($_POST['webnovel_comment_embed']) ? wp_unslash($_POST['webnovel_comment_embed']) : '';
        update_option('webnovel_comment_embed', $embed_code);
        
        $novel_announcement = isset($_POST['webnovel_novel_announcement']) ? wp_unslash($_POST['webnovel_novel_announcement']) : '';
        update_option('webnovel_novel_announcement', $novel_announcement);
        
        // Save banners
        $banners = array();
        if (isset($_POST['webnovel_banner_texts']) && is_array($_POST['webnovel_banner_texts'])) {
            $texts = $_POST['webnovel_banner_texts'];
            $colors = isset($_POST['webnovel_banner_colors']) ? $_POST['webnovel_banner_colors'] : array();
            for ($i = 0; $i < count($texts); $i++) {
                $text = sanitize_text_field(wp_unslash($texts[$i]));
                $color = isset($colors[$i]) ? sanitize_hex_color($colors[$i]) : '#1d4ed8';
                if (!empty($text)) {
                    $banners[] = array('text' => $text, 'color' => $color);
                }
            }
        }
        update_option('webnovel_homepage_banners', $banners);
        
        echo '<div class="notice notice-success is-dismissible"><p>Ayarlar kaydedildi.</p></div>';
    }

    $current_embed = get_option('webnovel_comment_embed', '');
    $banners = get_option('webnovel_homepage_banners', array());
    ?>
    <div class="wrap">
        <h1>Tema Ayarları</h1>
        <form method="post" action="">
            <?php wp_nonce_field('webnovel_options_verify'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="webnovel_comment_embed">Yorum Embed Kodu (Disqus vb.)</label></th>
                        <td>
                            <textarea name="webnovel_comment_embed" id="webnovel_comment_embed" rows="4" cols="50" class="large-text code" style="width:100%; border-radius:8px; padding:12px; font-family:monospace;"><?php echo esc_textarea($current_embed); ?></textarea>
                            <p class="description">Eğer bu alanı boş bırakırsanız WordPress'in kendi varsayılan yorum sistemi kullanılacaktır.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="webnovel_novel_announcement">Site Duyurusu (Header altı)</label></th>
                        <td>
                            <textarea name="webnovel_novel_announcement" id="webnovel_novel_announcement" rows="4" cols="50" class="large-text" style="width:100%; border-radius:8px; padding:12px;"><?php echo esc_textarea(get_option('webnovel_novel_announcement', '')); ?></textarea>
                            <p class="description">Seri sayfalarındaki (sidebar) duyuru alanında gözükecek metin.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row" colspan="2"><h2>Ana Sayfa Duyuru Bannerları</h2><p class="description">Ana sayfada gözükecek olan sınırsız sayıda duyuru bannerı ekleyebilirsiniz.</p></th>
                    </tr>
                    
                    <tr>
                        <td colspan="2" style="padding-left:0;">
                            <ul id="banner-repeater" style="padding:0; margin:0;">
                                <?php 
                                $banners = is_array($banners) ? $banners : array();
                                foreach($banners as $index => $banner):
                                    if(empty(trim($banner['text']))) continue;
                                    $color = esc_attr(isset($banner['color']) ? $banner['color'] : '#1d4ed8');
                                    $text = esc_attr($banner['text']);
                                ?>
                                <li style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
                                    <input type="text" name="webnovel_banner_texts[]" value="<?php echo $text; ?>" class="regular-text" style="width: 60%;" placeholder="Duyuru içeriği..." required>
                                    <input type="color" name="webnovel_banner_colors[]" value="<?php echo $color; ?>" title="Arka Plan Rengi">
                                    <button type="button" class="button remove-banner">Sil</button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" id="add-banner" class="button button-secondary" style="margin-top:8px;">+ Yeni Duyuru Ekle</button>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="webnovel_save_options" id="submit" class="button button-primary" value="Değişiklikleri Kaydet">
            </p>
        </form>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var container = document.getElementById('banner-repeater');
        document.getElementById('add-banner').addEventListener('click', function() {
            var li = document.createElement('li');
            li.style.marginBottom = '12px';
            li.style.display = 'flex';
            li.style.gap = '8px';
            li.style.alignItems = 'center';
            li.innerHTML = '<input type="text" name="webnovel_banner_texts[]" value="" class="regular-text" style="width: 60%;" placeholder="Duyuru içeriği..." required> ' +
                           '<input type="color" name="webnovel_banner_colors[]" value="#22c55e" title="Arka Plan Rengi"> ' +
                           '<button type="button" class="button remove-banner">Sil</button>';
            container.appendChild(li);
        });
        
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-banner')) {
                e.target.closest('li').remove();
            }
        });
    });
    </script>
    <?php
}

// Ensure comments are forcibly open for novels and chapters
add_filter('comments_open', function($open, $post_id) {
    if (in_array(get_post_type($post_id), array('novel', 'chapter'))) {
        return true;
    }
    return $open;
}, 10, 2);


// Handle Comment Like
add_action('wp_ajax_webnovel_like_comment', 'webnovel_like_comment_handler');
add_action('wp_ajax_nopriv_webnovel_like_comment', 'webnovel_like_comment_handler');
function webnovel_like_comment_handler() {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    if ($comment_id) {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $liked_ips = get_comment_meta($comment_id, '_liked_ips', true) ?: array();
        
        if (in_array($user_ip, $liked_ips)) {
            wp_send_json_error('Bu yorumu zaten beğendiniz.');
        }

        $likes = (int)get_comment_meta($comment_id, '_comment_likes', true);
        $likes++;
        
        $liked_ips[] = $user_ip;
        update_comment_meta($comment_id, '_liked_ips', $liked_ips);
        update_comment_meta($comment_id, '_comment_likes', $likes);
        
        wp_send_json_success(array('likes' => $likes));
    }
    wp_send_json_error('Comment ID missing');
}

function webnovel_custom_comment_layout($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    $is_admin = user_can($comment->user_id, 'manage_options');
    $likes = (int)get_comment_meta($comment->comment_ID, '_comment_likes', true);
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?> style="margin-bottom:16px;">
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body" style="display:flex; flex-direction:column; gap:12px; background:var(--bg-card); padding:12px; border-radius:8px; border:1px solid var(--border);">
            <div style="display:flex; gap:12px; align-items:flex-start;">
                <div class="comment-author vcard" style="flex-shrink:0;">
                    <?php if ($args['avatar_size'] != 0) echo get_avatar($comment, $args['avatar_size'], '', '', array('style'=>'border-radius:50%;')); ?>
                </div>
                <div class="comment-content" style="flex:1; min-width:0;">
                    <div class="comment-meta" style="display:flex; flex-direction:column; gap:4px; margin-bottom:8px;">
                        <div style="font-weight:700; color:var(--text-main); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <?php printf(__('%s', 'novelturk'), get_comment_author_link()); ?>
                            <?php if($is_admin): ?>
                                <span style="background:#2563eb; color:#fff; font-size:11px; padding:2px 6px; border-radius:4px;">Yönetici</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11px; color:var(--text-dim);">
                            <?php printf(__('%1$s at %2$s', 'novelturk'), get_comment_date(),  get_comment_time()); ?>
                        </div>
                    </div>
                    <?php if ($comment->comment_approved == '0') : ?>
                        <p style="color:#ef4444; font-size:13px; font-style:italic;">Yorumunuz onay bekliyor.</p>
                    <?php endif; ?>
                    <div style="font-size:14px; line-height:1.6; color:var(--text-main); margin-bottom:12px;">
                        <?php comment_text(); ?>
                    </div>
                </div>
            </div>

            <div class="reply" style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <!-- Like Button -->
                    <button class="comment-like-btn" data-id="<?php comment_ID(); ?>" style="background:none; border:none; color:var(--text-dim); cursor:pointer; font-size:13px; font-weight:600; display:flex; gap:6px; align-items:center; transition: all 0.2s;">
                        <span class="heart-icon">❤️</span> <span class="like-count"><?php echo (int)$likes; ?></span>
                    </button>
                    <!-- Native Reply -->
                    <?php
                    comment_reply_link(array_merge($args, array(
                        'add_below' => 'div-comment',
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '',
                        'after'     => '',
                        'reply_text'=> 'Yanıtla',
                        'class'     => 'comment-reply-link nt-text-dim'
                    )));
                    ?>
                </div>
            </div>
        </article>
    <?php
}

// Close comments on non-homepage pages (except novels and chapters)
add_filter('comments_open', function($open) {
    if (!is_home() && !is_front_page() && !in_array(get_post_type(), array('novel', 'chapter'))) {
        return false;
    }
    return $open;
}, 10, 1);

// Register status field in REST API for novel posts
add_action('rest_api_init', function() {
    register_rest_field('novel', 'status_label', array(
        'get_callback' => function($post) {
            $status = get_post_meta($post['id'], '_novel_status', true) ?: 'ongoing';
            return webnovel_get_status_label($status);
        },
        'schema' => array('type' => 'string')
    ));
});

// Enable REST support for featured images
add_filter('rest_prepare_novel', function($response, $post) {
    if ($post->ID) {
        $image_id = get_post_thumbnail_id($post->ID);
        if ($image_id) {
            $image_url = wp_get_attachment_image_src($image_id, 'full');
            $response->data['featured_media_src'] = $image_url[0];
        }
    }
    return $response;
}, 10, 2);

// AJAX handler for filtering novels
add_action('wp_ajax_webnovel_filter_novels', 'webnovel_ajax_filter_novels');
add_action('wp_ajax_nopriv_webnovel_filter_novels', 'webnovel_ajax_filter_novels');

function webnovel_ajax_filter_novels() {
    $categories = isset($_POST['categories']) ? json_decode(stripslashes($_POST['categories']), true) : array();

    $args = array(
        'post_type' => 'novel',
        'posts_per_page' => 15,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // Add category filter
    if (!empty($categories)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'novel_genre',
                'field' => 'slug',
                'terms' => $categories,
                'operator' => 'IN'
            )
        );
    }

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ob_start();
            get_template_part('template-parts/content', 'novel-card-novelturk');
            $html .= ob_get_clean();
        }
    } else {
        $html = '<p style="color:var(--text-dim); grid-column: 1/-1; text-align: center; padding: 40px 20px;">Roman bulunamadı.</p>';
    }

    wp_reset_postdata();
    wp_send_json_success(array('html' => $html));
}

// AJAX handler for refreshing sidebar novels
add_action('wp_ajax_refresh_sidebar_novels', 'webnovel_ajax_refresh_sidebar_novels');
add_action('wp_ajax_nopriv_refresh_sidebar_novels', 'webnovel_ajax_refresh_sidebar_novels');

function webnovel_ajax_refresh_sidebar_novels() {
    $genre_slug = isset($_POST['genre']) ? sanitize_text_field($_POST['genre']) : '';
    if (empty($genre_slug)) {
        wp_die();
    }

    $args = array(
        'post_type' => 'novel',
        'posts_per_page' => 5,
        'orderby' => 'rand',
        'cache_results' => false,
        'tax_query' => array(
            array(
                'taxonomy' => 'novel_genre',
                'field' => 'slug',
                'terms' => $genre_slug
            )
        )
    );

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $thumb = webnovel_get_cover_url(get_the_ID(), 'medium');
            $html .= '<a href="' . get_permalink() . '" class="sidebar-novel-card" style="display:flex; background-color:var(--bg-card); border-radius:8px; overflow:hidden; text-decoration:none; color:var(--text-main); height:110px; border:1px solid var(--border); box-shadow:var(--shadow-sm); transition:transform 0.2s;">';
            $html .= '<div style="width:75px; flex-shrink:0; position:relative;">';
            if ($thumb) {
                $html .= '<img src="' . esc_url($thumb) . '" style="width:100%; height:100%; object-fit:cover;" alt="' . esc_attr(get_the_title()) . '">';
            } else {
                $html .= '<div style="width:100%; height:100%; background:var(--bg-surface);"></div>';
            }
            $html .= '</div>';
            $html .= '<div style="padding:12px; display:flex; align-items:center; flex:1;">';
            $html .= '<h4 style="font-size:14px; font-weight:700; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin:0; opacity:0.95; text-align:center; width:100%;">' . get_the_title() . '</h4>';
            $html .= '</div></a>';
        }
    } else {
        $html = '<div style="padding:20px; text-align:center; color:var(--text-dim); background:#1e293b; border-radius:8px; font-size:14px;">Bu türde henüz seri eklenmemiş.</div>';
    }

    wp_reset_postdata();
    echo $html;
    wp_die();
}

// AJAX handler for refreshing sidebar categories
add_action('wp_ajax_refresh_sidebar_categories', 'webnovel_ajax_refresh_sidebar_categories');
add_action('wp_ajax_nopriv_refresh_sidebar_categories', 'webnovel_ajax_refresh_sidebar_categories');

function webnovel_ajax_refresh_sidebar_categories() {
    $genres = get_terms(array('taxonomy' => 'novel_genre', 'hide_empty' => false));

    if (is_wp_error($genres) || empty($genres)) {
        wp_die();
    }

    // Get 3 random genres
    $sidebar_tab_genres = array();
    if (count($genres) >= 3) {
        $r_keys = array_rand($genres, 3);
        foreach ((array)$r_keys as $k) {
            $sidebar_tab_genres[] = $genres[$k];
        }
    } else {
        $sidebar_tab_genres = $genres;
    }

    $html = '<div class="sidebar-tabs" style="display:flex; gap:8px; flex-wrap:wrap;">';

    // Generate tab buttons
    foreach ($sidebar_tab_genres as $index => $g) {
        $isActive = ($index === 0);
        $bg = $isActive ? 'background:#3b82f6; color:#fff;' : 'background:transparent; color:var(--text-dim);';
        $html .= '<button type="button" class="sidebar-tab-btn" data-target="sidebar-tab-' . esc_attr($g->slug) . '" style="' . $bg . ' padding:6px 14px; font-size:14px; border:none; border-radius:20px; font-weight:700; cursor:pointer; transition:all 0.2s;">' . esc_html($g->name) . '</button>';
    }

    $html .= '</div>';
    $html .= '<div class="sidebar-tab-contents">';

    // Generate tab contents
    foreach ($sidebar_tab_genres as $index => $g) {
        $isActive = ($index === 0);
        $display = $isActive ? 'flex' : 'none';
        $html .= '<div id="sidebar-tab-' . esc_attr($g->slug) . '" class="sidebar-novel-list" style="display:' . $display . '; flex-direction:column; gap:12px;">';

        $args = array(
            'post_type' => 'novel',
            'posts_per_page' => 5,
            'orderby' => 'rand',
            'cache_results' => false,
            'tax_query' => array(
                array(
                    'taxonomy' => 'novel_genre',
                    'field' => 'slug',
                    'terms' => $g->slug
                )
            )
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $thumb = webnovel_get_cover_url(get_the_ID(), 'medium');
                $html .= '<a href="' . get_permalink() . '" class="sidebar-novel-card" style="display:flex; background-color:var(--bg-card); border-radius:8px; overflow:hidden; text-decoration:none; color:var(--text-main); height:110px; border:1px solid var(--border); box-shadow:var(--shadow-sm); transition:transform 0.2s;">';
                $html .= '<div style="width:75px; flex-shrink:0; position:relative;">';
                if ($thumb) {
                    $html .= '<img src="' . esc_url($thumb) . '" style="width:100%; height:100%; object-fit:cover;" alt="' . esc_attr(get_the_title()) . '">';
                } else {
                    $html .= '<div style="width:100%; height:100%; background:var(--bg-surface);"></div>';
                }
                $html .= '</div>';
                $html .= '<div style="padding:12px; display:flex; align-items:center; flex:1;">';
                $html .= '<h4 style="font-size:14px; font-weight:700; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin:0; opacity:0.95; text-align:center; width:100%;">' . get_the_title() . '</h4>';
                $html .= '</div></a>';
            }
        } else {
            $html .= '<div style="padding:20px; text-align:center; color:var(--text-dim); background:#1e293b; border-radius:8px; font-size:14px;">Bu türde henüz seri eklenmemiş.</div>';
        }

        wp_reset_postdata();
        $html .= '</div>';
    }

    $html .= '</div>';

    echo $html;
    wp_die();
}

// AJAX handler for fetching popular novels
add_action('wp_ajax_fetch_popular_novels', 'webnovel_ajax_fetch_popular_novels');
add_action('wp_ajax_nopriv_fetch_popular_novels', 'webnovel_ajax_fetch_popular_novels');

function webnovel_ajax_fetch_popular_novels() {
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'week';

    $date_query = array();
    switch ($period) {
        case 'week':
            $date_query = array(
                array(
                    'after' => '7 days ago',
                    'inclusive' => true
                )
            );
            break;
        case 'month':
            $date_query = array(
                array(
                    'after' => '3 months ago',
                    'inclusive' => true
                )
            );
            break;
        case 'all':
            $date_query = array();
            break;
    }

    $args = array(
        'post_type' => 'novel',
        'posts_per_page' => 7,
        'orderby' => 'comment_count',
        'order' => 'DESC',
        'cache_results' => false
    );

    if (!empty($date_query)) {
        $args['date_query'] = $date_query;
    }

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $thumb = webnovel_get_cover_url(get_the_ID(), 'medium');
            $html .= '<a href="' . get_permalink() . '" style="display:flex; background-color:var(--bg-card); border-radius:8px; overflow:hidden; text-decoration:none; color:var(--text-main); height:100px; border:1px solid var(--border); box-shadow:var(--shadow-sm); transition:transform 0.2s;">';
            $html .= '<div style="width:70px; flex-shrink:0; position:relative;">';
            if ($thumb) {
                $html .= '<img src="' . esc_url($thumb) . '" style="width:100%; height:100%; object-fit:cover;" alt="' . esc_attr(get_the_title()) . '">';
            } else {
                $html .= '<div style="width:100%; height:100%; background:var(--bg-surface);"></div>';
            }
            $html .= '</div>';
            $html .= '<div style="padding:12px; display:flex; align-items:center; flex:1;">';
            $html .= '<div style="display:flex; flex-direction:column; gap:4px; width:100%;">';
            $html .= '<h4 style="font-size:12px; font-weight:700; margin:0; color:var(--text-main); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">' . get_the_title() . '</h4>';
            $rating_avg = get_post_meta(get_the_ID(), '_novel_rating_avg', true);
            $rating_count = get_post_meta(get_the_ID(), '_novel_rating_count', true);
            $html .= '<div style="font-size:11px; color:var(--text-dim); display:flex; gap:8px;">';
            if ($rating_avg) {
                $html .= '<span>⭐ ' . number_format((float)$rating_avg, 1) . ' (' . (int)$rating_count . ')</span>';
            }
            $html .= '<span>💭 ' . get_comments_number() . '</span>';
            $html .= '</div>';
            $html .= '</div></div></a>';
        }
    } else {
        $html = '<div style="padding:20px; text-align:center; color:var(--text-dim); background:#1e293b; border-radius:8px; font-size:14px;">Bu dönemde popüler novel bulunamadı.</div>';
    }

    wp_reset_postdata();
    echo $html;
    wp_die();
}
