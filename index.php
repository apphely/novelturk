<?php get_header(); ?>

<!-- NovelTurk Swiper CSS/JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<div class="nt-container" style="padding-top:24px;">
    <!-- Main Content Area -->
    <main class="nt-main">
        
        <!-- Featured Slider -->
        <div class="nt-featured-slider" style="margin-bottom: 24px; position: relative; group: true;">
            <div class="slider-track" style="display: flex; overflow-x: auto; scroll-behavior: smooth; gap: 16px; scroll-snap-type: x mandatory;">
                <?php
                $slider_args = array(
                    'post_type' => 'novel',
                    'posts_per_page' => 12,
                    'orderby' => 'rand'
                );
                $slider_query = new WP_Query($slider_args);
                if ($slider_query->have_posts()) :
                    while ($slider_query->have_posts()) : $slider_query->the_post();
                        $novel_id = get_the_ID();
                        $cover_url = webnovel_get_cover_url($novel_id, 'large');
                        $status = get_post_meta($novel_id, '_novel_status', true) ?: 'ongoing';
                        $status_name = webnovel_get_status_label($status);
                ?>
                <article class="post-card" style="height: 244px; position: relative; border-radius: 8px; background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); border-left: 3px solid var(--accent); overflow: hidden; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="position: absolute; inset: 0; display: flex;">
                        <div style="width: 60%; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; z-index: 20; position: relative;">
                            <div style="height: 100%; display: flex; flex-direction: column;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                    <span style="font-size: 10px; font-weight: 900; color: var(--text-main); background: var(--bg-surface); padding: 3px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.8px;">
                                        <?php echo esc_html($status_name); ?>
                                    </span>
                                </div>
                                <h3 style="font-size: 14px; font-weight: 900; line-height: 1.3; color: var(--text-main); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 8px; transition: color 0.3s;">
                                    <a href="<?php echo get_permalink(); ?>" style="color: inherit; text-decoration: none; display: block;">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <p style="font-size: 12px; color: var(--text-dim); margin-top: auto; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; opacity: 0.8;">
                                    <?php echo wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...'); ?>
                                </p>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                                <a href="<?php echo get_permalink(); ?>" style="font-size: 10px; font-weight: 900; color: #fff; background: var(--accent); padding: 8px 20px; border-radius: 6px; text-decoration: none; text-transform: uppercase; transition: filter 0.3s; display: inline-block;">
                                    Oku
                                </a>
                            </div>
                        </div>
                        <div style="width: 40%; position: absolute; right: 0; top: 0; height: 100%; overflow: hidden; z-index: 10; transition: transform 0.7s;">
                            <img src="<?php echo esc_url($cover_url); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover; clip-path: polygon(15% 0%, 100% 0%, 100% 100%, 0% 100%); transition: transform 0.7s;" loading="lazy">
                        </div>
                    </div>
                </article>
                <?php
                    endwhile;
                endif;
                wp_reset_postdata();
                ?>
            </div>
            <button class="slider-btn slider-prev" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 50%; color: var(--text-main); cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.3s; z-index: 20; font-size: 20px;">❮</button>
            <button class="slider-btn slider-next" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 50%; color: var(--text-main); cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.3s; z-index: 20; font-size: 20px;">❯</button>
        </div>

        <!-- Novel & Chapter Statistics -->
        <?php
        $novel_count = wp_count_posts('novel')->publish;
        $chapter_count = wp_count_posts('chapter')->publish;
        ?>
        <div style="background: linear-gradient(135deg, var(--accent) 0%, #0891b2 100%); color: #fff; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; margin-bottom: 24px; text-align: center;">
            Şu anda Novel Türk'te <strong><?php echo number_format($novel_count); ?></strong> novel ve <strong><?php echo number_format($chapter_count); ?></strong> bölüm bulunmaktadır.
        </div>

        <style>
            @keyframes nt-spin { to { transform: rotate(360deg); } }
            .nt-featured-slider { margin-bottom: 24px; }
            .slider-track { overflow-x: auto; scroll-behavior: smooth; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: var(--accent) var(--bg-surface); }
            .slider-track::-webkit-scrollbar { height: 8px; }
            .slider-track::-webkit-scrollbar-track { background: var(--bg-surface); border-radius: 4px; }
            .slider-track::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; transition: background 0.3s; }
            .slider-track::-webkit-scrollbar-thumb:hover { background: var(--text-main); }
            .post-card { cursor: pointer; flex: 0 0 calc(50% - 8px); height: 244px; scroll-snap-align: start; }
            .post-card:hover { border-left-color: var(--text-main); transition: all 0.3s; }
            .post-card:hover img { transform: scale(1.05); }
            .post-card:hover h3 a { color: var(--accent); }
            .post-card a[href] { transition: color 0.3s; }
            .nt-featured-slider:hover .slider-btn { opacity: 1; }
            .slider-btn { transition: all 0.3s; }
            .slider-btn:hover { background: var(--accent); color: #000; }
            @media (max-width: 768px) {
                .slider-track { gap: 0; }
                .post-card { flex: 0 0 100%; height: 240px; }
                .post-card > div { display: flex !important; }
                .post-card > div > div:first-child { width: 58% !important; }
                .post-card > div > div:last-child { width: 42% !important; position: relative !important; }
                .post-card img { clip-path: polygon(12% 0%, 100% 0%, 100% 100%, 0% 100%) !important; border-radius: 0 0 8px 8px; }
            }
        </style>
        

        <!-- Dynamic Theme Banners -->
        <?php
        $banners = get_option('webnovel_homepage_banners', array());
        if(!empty($banners) && is_array($banners)){
            echo '<div style="margin-bottom:24px; display:flex; flex-direction:column; gap:12px; width:100%;">';
            foreach($banners as $i => $banner) {
                if(!is_array($banner) || !isset($banner['text']) || empty(trim($banner['text']))) continue;
                $color = esc_attr(isset($banner['color']) ? $banner['color'] : '#22c55e'); // Default to green per user example
                $text = esc_html($banner['text']);
                echo '<div style="background-color:'.$color.'; color:#fff; padding:14px 20px; border-radius:6px; font-size:15px; font-weight:500; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 6px rgba(0,0,0,0.1);">';
                echo '<div style="display:flex; align-items:center; gap:8px;">';
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                echo '<span>' . $text . '</span></div>';
                echo '<button onclick="this.parentElement.style.display=\'none\'" style="background:none; border:none; color:#fff; cursor:pointer; font-size:18px; font-weight:bold; opacity:0.8;">&times;</button>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>

        <!-- Hızlı Erişim Pills -->
        <?php
        $quick_access_buttons = get_option('webnovel_homepage_quick_access', array());
        if (empty($quick_access_buttons)) {
            $quick_access_buttons = array(
                array('label' => 'Destek/Bağış',   'url' => '#'),
                array('label' => 'En Popülerler',  'url' => '#'),
                array('label' => 'Filtreleme',     'url' => '/#Filtreleme'),
                array('label' => 'Site Yorumları', 'url' => '/#SiteYorumlari'),
                array('label' => 'Son Yorumlar',   'url' => '/#SonYorumlar'),
            );
            update_option('webnovel_homepage_quick_access', $quick_access_buttons);
        }
        if (!empty($quick_access_buttons) && is_array($quick_access_buttons)) : ?>
        <div class="nt-flex nt-flex-wrap nt-gap-2 nt-mb-6" style="justify-content:center;">
            <?php foreach ($quick_access_buttons as $btn) :
                if (empty(trim($btn['label'])) || empty(trim($btn['url']))) continue;
            ?>
            <a href="<?php echo esc_attr($btn['url']); ?>" class="nt-quick-btn"><?php echo esc_html($btn['label']); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tabbed Novels Grid -->
        <section class="tabbed-novels">
            <div class="titleBox nt-mb-4" style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="font-size: 20px; font-weight:700;">Noveller ve Bölümleri</h2>
                <a href="<?php echo home_url('/novel/'); ?>" style="background:#2563eb; color:#fff; padding:6px 16px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:bold;">Tüm Noveller</a>
            </div>
            
            <div class="tabs nt-flex nt-gap-2 nt-mb-4" style="border:none;">
                <button class="custom-btn active" style="flex:1; border: 1px solid var(--border); background:var(--accent); color:#fff; font-weight:bold; font-style:italic;" onclick="showTab('devam', this)"><span>Devam Eden</span></button>
                <button class="custom-btn" style="flex:1; border: 1px solid var(--border); background:var(--bg-card); color:var(--text-main); font-weight:bold; font-style:italic;" onclick="showTab('tamamlandi', this)"><span>Tamamlanan</span></button>
                <button class="custom-btn" style="flex:1; border: 1px solid var(--border); background:var(--bg-card); color:var(--text-main); font-weight:bold; font-style:italic;" onclick="showTab('diger', this)"><span>Diğer</span></button>
            </div>

            <!-- Devam Edenler Tab -->
            <div id="tab-devam" class="novels-tab-content nt-novels-grid is-visible">
                <?php
                $args_devam = array('post_type' => 'novel', 'posts_per_page' => 15, 'meta_query' => array(array('key' => '_novel_status', 'value' => 'ongoing')));
                $query_devam = new WP_Query($args_devam);
                if ($query_devam->have_posts()) :
                    while ($query_devam->have_posts()) : $query_devam->the_post();
                        get_template_part('template-parts/content', 'novel-card-novelturk');
                    endwhile;
                else: echo '<p style="color:var(--text-dim);">Roman bulunamadı.</p>'; endif; wp_reset_postdata();
                ?>
            </div>

            <!-- Tamamlananlar -->
            <div id="tab-tamamlandi" class="novels-tab-content nt-novels-grid">
                <?php
                $args_tamam = array('post_type' => 'novel', 'posts_per_page' => 15, 'meta_query' => array(array('key' => '_novel_status', 'value' => 'completed')));
                $query_tamam = new WP_Query($args_tamam);
                if ($query_tamam->have_posts()) :
                    while ($query_tamam->have_posts()) : $query_tamam->the_post();
                        get_template_part('template-parts/content', 'novel-card-novelturk');
                    endwhile;
                else: echo '<p style="color:var(--text-dim);">Roman bulunamadı.</p>'; endif; wp_reset_postdata();
                ?>
            </div>

            <!-- Diğer (Türkiye & Genel/Global) -->
            <div id="tab-diger" class="novels-tab-content nt-novels-grid">
                <?php
                $args_all = array(
                    'post_type' => 'novel', 
                    'posts_per_page' => 15,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'novel_origin',
                            'field' => 'slug',
                            'terms' => array('turkiye', 'genel', 'global', 'turkey'),
                            'operator' => 'IN'
                        )
                    )
                );
                $query_all = new WP_Query($args_all);
                if ($query_all->have_posts()) :
                    while ($query_all->have_posts()) : $query_all->the_post();
                        get_template_part('template-parts/content', 'novel-card-novelturk');
                    endwhile;
                else: echo '<p style="color:var(--text-dim);">Roman bulunamadı.</p>'; endif; wp_reset_postdata();
                ?>
            </div>
        </section>

    </main>

    <!-- Sidebar Widget Area (NovelTurk specific Filtreleme) -->
    <aside class="nt-sidebar">
        <div id="Filtreleme" class="nt-card" style="background-color:var(--bg-surface); border:none; border-top:3px solid var(--accent); border-radius:8px; overflow:hidden;">
            <div class="nt-card-body">

                <!-- Başlık -->
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:15px; font-weight:800; color:var(--text-main); margin:0; line-height:1.2;">Novel Filtreleme</h3>
                        <span style="font-size:11px; color:var(--text-dim);">Aradığını bul</span>
                    </div>
                </div>

                <form action="<?php echo home_url(); ?>" method="get" style="display:flex; flex-direction:column; gap:10px;">
                    <input type="hidden" name="post_type" value="novel">

                    <!-- Metin Arama -->
                    <div style="position:relative;">
                        <input type="text" name="s" placeholder="Novel veya bölüm ara..." value="<?php echo get_search_query(); ?>"
                               style="width:100%; padding:9px 14px 9px 38px; background:var(--bg-card); border:1.5px solid var(--border); color:var(--text-main); border-radius:8px; outline:none; font-size:13px; box-sizing:border-box; transition:border-color 0.2s;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
                        <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-dim); pointer-events:none;" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>

                    <!-- Durum + Tür -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <div>
                            <label style="display:block; font-size:10px; font-weight:700; color:#4b5563; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.6px;">📋 Durum</label>
                            <div style="position:relative;">
                                <?php $current_nstatus = $_GET['nstatus'] ?? ''; ?>
                                <select name="nstatus" onchange="this.style.color=this.value?'var(--text-main)':'#9ca3af'" style="width:100%; background:var(--bg-card); padding:8px 28px 8px 10px; border-radius:8px; color:<?php echo $current_nstatus ? 'var(--text-main)' : '#9ca3af'; ?>; font-size:13px; border:1.5px solid var(--border); cursor:pointer; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">Tümü</option>
                                    <?php foreach (webnovel_get_novel_statuses() as $skey => $slabel) : ?>
                                        <option value="<?php echo esc_attr($skey); ?>" <?php selected($current_nstatus, $skey); ?>><?php echo esc_html($slabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <svg style="position:absolute; right:9px; top:50%; transform:translateY(-50%); pointer-events:none; color:#9ca3af;" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                        <div>
                            <label style="display:block; font-size:10px; font-weight:700; color:#4b5563; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.6px;">🏷️ Tür</label>
                            <div style="position:relative;">
                                <?php $current_novel_type = $_GET['novel_type'] ?? ''; ?>
                                <select name="novel_type" onchange="this.style.color=this.value?'var(--text-main)':'#9ca3af'" style="width:100%; background:var(--bg-card); padding:8px 28px 8px 10px; border-radius:8px; color:<?php echo $current_novel_type ? 'var(--text-main)' : '#9ca3af'; ?>; font-size:13px; border:1.5px solid var(--border); cursor:pointer; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">Tümü</option>
                                    <?php
                                    $types = get_terms(array('taxonomy' => 'novel_type', 'hide_empty' => false));
                                    if (!is_wp_error($types)) {
                                        foreach($types as $t) {
                                            $selected = ($current_novel_type == $t->slug) ? 'selected' : '';
                                            echo '<option value="'.esc_attr($t->slug).'" '.$selected.'>'.esc_html($t->name).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <svg style="position:absolute; right:9px; top:50%; transform:translateY(-50%); pointer-events:none; color:#9ca3af;" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Ülke + Kategori -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <div>
                            <label style="display:block; font-size:10px; font-weight:700; color:#4b5563; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.6px;">🌐 Ülke</label>
                            <div style="position:relative;">
                                <?php $current_novel_origin = $_GET['novel_origin'] ?? ''; ?>
                                <select name="novel_origin" onchange="this.style.color=this.value?'var(--text-main)':'#9ca3af'" style="width:100%; background:var(--bg-card); padding:8px 28px 8px 10px; border-radius:8px; color:<?php echo $current_novel_origin ? 'var(--text-main)' : '#9ca3af'; ?>; font-size:13px; border:1.5px solid var(--border); cursor:pointer; outline:none; appearance:none; -webkit-appearance:none;">
                                    <option value="">Tümü</option>
                                    <?php
                                    $origins = get_terms(array('taxonomy' => 'novel_origin', 'hide_empty' => false));
                                    if (!is_wp_error($origins)) {
                                        foreach($origins as $o) {
                                            $selected = ($current_novel_origin == $o->slug) ? 'selected' : '';
                                            echo '<option value="'.esc_attr($o->slug).'" '.$selected.'>'.esc_html($o->name).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <svg style="position:absolute; right:9px; top:50%; transform:translateY(-50%); pointer-events:none; color:#9ca3af;" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                        <div style="position:relative;">
                            <label style="display:block; font-size:10px; font-weight:700; color:#4b5563; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.6px;">📁 Kategori</label>
                            <?php
                            $genres = get_terms(array('taxonomy' => 'novel_genre', 'hide_empty' => false));
                            $selected_genres = (array)($_GET['novel_genre'] ?? []);
                            $sel_count = count($selected_genres);
                            if ($sel_count === 0) {
                                $genre_btn_label = 'Tümü';
                            } elseif ($sel_count === 1 && !is_wp_error($genres)) {
                                $matched = array_values(array_filter((array)$genres, function($g) use ($selected_genres) {
                                    return $g->slug === $selected_genres[0];
                                }));
                                $genre_btn_label = !empty($matched) ? esc_html($matched[0]->name) : '1 seçildi';
                            } else {
                                $genre_btn_label = $sel_count . ' kategori seçildi';
                            }
                            ?>
                            <button type="button" id="nt-genre-btn" onclick="ntToggleGenreDropdown()" style="width:100%; background:var(--bg-card); padding:8px 10px; border-radius:8px; color:<?php echo $sel_count ? 'var(--text-main)' : '#9ca3af'; ?>; font-size:13px; border:1.5px solid var(--border); cursor:pointer; outline:none; display:flex; justify-content:space-between; align-items:center; text-align:left;">
                                <span id="nt-genre-label"><?php echo $genre_btn_label; ?></span>
                                <svg id="nt-genre-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0; transition:transform 0.2s;"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div id="nt-genre-dropdown" style="display:none; position:absolute; top:calc(100% + 2px); left:0; right:0; z-index:200; background:var(--bg-card); border:1.5px solid var(--border); border-radius:8px; max-height:200px; overflow-y:auto; box-shadow:var(--shadow-sm);">
                                <?php if (!is_wp_error($genres) && !empty($genres)) : foreach($genres as $g) : $is_on = in_array($g->slug, $selected_genres); ?>
                                <label style="display:flex; align-items:center; gap:8px; padding:6px 10px; cursor:pointer; font-size:13px; color:var(--text-main);" onmouseover="this.style.background='var(--bg-base)'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="novel_genre[]" value="<?php echo esc_attr($g->slug); ?>" <?php checked($is_on); ?> style="width:13px; height:13px; accent-color:var(--accent); cursor:pointer; flex-shrink:0;" onchange="ntUpdateGenreLabel()">
                                    <?php echo esc_html($g->name); ?>
                                </label>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Filtrele + Temizle -->
                    <div style="display:flex; gap:8px; margin-top:4px; align-items:stretch;">
                        <button type="submit" style="flex:1; padding:10px 14px; border-radius:8px; background:var(--accent); color:#fff; border:none; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            Filtrele
                        </button>
                        <a href="<?php echo home_url('/'); ?>" title="Filtreleri Temizle" style="padding:10px 12px; border-radius:8px; background:var(--bg-card); border:1.5px solid var(--border); color:var(--text-dim); display:flex; align-items:center; justify-content:center; text-decoration:none; flex-shrink:0;" onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-dim)'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </a>
                    </div>
                </form>

                <!-- Sidebar Genre Tabs / Pills -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; margin-bottom:16px;">
                    <div class="sidebar-tabs" id="sidebar-genre-tabs" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <?php
                        // Pull 3 random genres as tabs
                        $sidebar_tab_genres = array();
                        if (!is_wp_error($genres) && is_array($genres) && count($genres) > 0) {
                            $r_keys = (count($genres) >= 3) ? array_rand($genres, 3) : array_keys((array)$genres);
                            foreach((array)$r_keys as $index => $k) {
                                $sidebar_tab_genres[] = $genres[$k];
                            }
                        }

                        foreach($sidebar_tab_genres as $index => $g) {
                            $isActive = ($index === 0);
                            $bg = $isActive ? 'background:#3b82f6; color:#fff;' : 'background:transparent; color:var(--text-dim);';
                            echo '<button type="button" class="sidebar-tab-btn" data-target="sidebar-tab-'.esc_attr($g->slug).'" style="'.$bg.' padding:6px 14px; font-size:14px; border:none; border-radius:20px; font-weight:700; cursor:pointer; transition:all 0.2s;">'.esc_html($g->name).'</button>';
                        }
                        ?>
                    </div>
                    <button type="button" id="btn-refresh-genres" onclick="ntRefreshGenres(this)" style="background:var(--bg-card); color:var(--text-dim); border:1px solid var(--border); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="Yenile">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                </div>

                <!-- Recent Novels Mini Cards Array (Tab Contents) -->
                <div class="sidebar-tab-contents" id="sidebar-genre-contents">
                    <?php
                    foreach($sidebar_tab_genres as $index => $g):
                        $isActive = ($index === 0);
                        $display = $isActive ? 'flex' : 'none';
                    ?>
                    <div id="sidebar-tab-<?php echo esc_attr($g->slug); ?>" class="sidebar-novel-list" style="display:<?php echo $display; ?>; flex-direction:column; gap:12px;">
                        <?php
                        $sidebar_args = array(
                            'post_type' => 'novel', 
                            'posts_per_page' => 5, 
                            'orderby' => 'rand',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'novel_genre',
                                    'field' => 'slug',
                                    'terms' => $g->slug
                                )
                            )
                        );
                        $sidebar_query = new WP_Query($sidebar_args);
                        if ($sidebar_query->have_posts()) :
                            while ($sidebar_query->have_posts()) : $sidebar_query->the_post();
                                $thumb = webnovel_get_cover_url(get_the_ID(), 'medium');
                        ?>
                        <a href="<?php the_permalink(); ?>" class="sidebar-novel-card" style="display:flex; background-color:var(--bg-card); border-radius:8px; overflow:hidden; text-decoration:none; color:var(--text-main); height:110px; border:1px solid var(--border); box-shadow:var(--shadow-sm); transition:transform 0.2s;">
                            <div style="width:75px; flex-shrink:0; position:relative;">
                                <?php if($thumb): ?>
                                    <img src="<?php echo esc_url($thumb); ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php the_title_attribute(); ?>">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:var(--bg-surface);"></div>
                                <?php endif; ?>
                            </div>
                            <div style="padding:12px; display:flex; align-items:center; flex:1;">
                                <h4 style="font-size:14px; font-weight:700; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin:0; opacity:0.95; text-align:center; width:100%;"><?php the_title(); ?></h4>
                            </div>
                        </a>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div style="padding:20px; text-align:center; color:var(--text-dim); background:#1e293b; border-radius:8px; font-size:14px;">Bu türde henüz seri eklenmemiş.</div>
                        <?php
                        endif;
                        wp_reset_postdata();
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
            <?php dynamic_sidebar( 'sidebar-1' ); ?>
        <?php endif; ?>
    </aside>

    <!-- Recent Comments Section -->
    <section class="nt-recent-comments" style="margin-top:40px; margin-bottom:24px; width:100%; clear:both;">
        <div class="titleBox nt-mb-4" id="SonYorumlar" style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid var(--border); padding-bottom:8px;">
            <h2 style="font-size: 20px; font-weight:700; display:flex; align-items:center; gap:8px; color:var(--text-main);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Son Yorumlar (! Spoiler İçerebilir !)
            </h2>
        </div>
        
        <div class="nt-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
            <?php
            $recent_comments = get_comments(array(
                'number' => 8,
                'status' => 'approve',
                'post_type' => array('novel', 'chapter')
            ));

            if ($recent_comments) :
                foreach ($recent_comments as $comment) :
                    $post = get_post($comment->comment_post_ID);
                    if (!$post) continue;
                    
                    $author_name = get_comment_author($comment);
                    $comment_snippet = wp_trim_words($comment->comment_content, 15);
                    $avatar = get_avatar_url($comment->user_id, array('size' => 40));
                    $post_title = get_the_title($post->ID);
                    
                    // If it's a chapter, find the novel title too
                    if ($post->post_type === 'chapter') {
                        $novel_id = get_post_meta($post->ID, '_chapter_novel_id', true);
                        if ($novel_id) {
                            $novel_title = get_the_title($novel_id);
                            $display_title = $novel_title . ' - ' . $post_title;
                        } else {
                            $display_title = $post_title;
                        }
                    } else {
                        $display_title = $post_title;
                    }
            ?>
                <div class="nt-card recent-comment-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:10px; transition:all 0.3s ease; position:relative; overflow:hidden; box-shadow:var(--shadow-sm);">
                    <?php if (user_can($comment->user_id, 'manage_options')) : ?>
                        <div style="position:absolute; top:12px; right:-28px; background:linear-gradient(135deg, #2563eb, #1d4ed8); color:#fff; font-size:10px; font-weight:800; padding:4px 30px; transform: rotate(45deg); box-shadow:0 2px 4px rgba(0,0,0,0.2); z-index:10; border:1px solid rgba(255,255,255,0.1); letter-spacing:0.5px;">YÖNETİCİ</div>
                    <?php endif; ?>
                    
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img src="<?php echo esc_url($avatar); ?>" style="width:36px; height:36px; border-radius:50%;" alt="<?php echo esc_attr($author_name); ?>">
                        <div style="display:flex; flex-direction:column;">
                            <span style="font-weight:700; color:var(--text-main); font-size:14px;"><?php echo esc_html($author_name); ?></span>
                            <span style="font-size:11px; color:var(--text-dim);"><?php echo human_time_diff(get_comment_date('U', $comment), current_time('timestamp')) . ' önce'; ?></span>
                        </div>
                    </div>
                    <div style="font-size:13px; color:var(--text-main); font-style:italic; line-height:1.5; min-height:40px;">
                        "<?php echo esc_html($comment_snippet); ?>"
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:8px; margin-top:4px;">
                        <a href="<?php echo get_comment_link($comment); ?>" style="font-size:12px; font-weight:600; color:var(--accent); text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            <?php echo esc_html($display_title); ?>
                        </a>
                    </div>
                </div>
            <?php
                endforeach;
            else :
                echo '<p style="color:var(--text-dim);">Henüz yorum yapılmamış.</p>';
            endif;
            ?>
        </div>
    </section>
</div>

<script>
(function() {
    'use strict';

    class NTFeaturedSlider {
        constructor(el) {
            this.el = el;
            this.currentPage = 0;
            this.totalPosts = [];
            this.isTransitioning = false;
            this.shuffle = el.dataset.shuffle === 'true';
            this.duration = parseInt(el.dataset.duration) || 5000;
            this.autoPlayTimer = null;
            this.max = this.determineMax();

            this.init();
            window.addEventListener('resize', () => {
                const newMax = this.determineMax();
                if (newMax !== this.max) {
                    this.max = newMax;
                    this.currentPage = 0;
                    this.render();
                }
            });
        }

        determineMax() {
            if (window.innerWidth < 768) return 1;
            if (window.innerWidth < 1024) return 2;
            return 3;
        }

        async init() {
            await this.fetchPosts();
            if (this.totalPosts.length > 0) {
                if (this.shuffle) this.shufflePosts();
                this.render();
                this.startAutoPlay();
            }
        }

        async fetchPosts() {
            try {
                const baseUrl = <?php echo wp_json_encode(rest_url('wp/v2/novel')); ?>;
                const apiUrl = baseUrl + '?per_page=12';
                console.log('Fetching from:', apiUrl);

                const response = await fetch(apiUrl);
                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error('HTTP error, status=' + response.status);
                }

                const data = await response.json();
                console.log('Fetched posts:', data);

                if (!Array.isArray(data)) {
                    throw new Error('Response is not an array');
                }

                this.totalPosts = data.slice(0, 12).map(post => ({
                    ...post,
                    featured_media_src: post.featured_media_src || null,
                    status_label: post.status_label || 'Ongoing'
                }));

                console.log('Total posts processed:', this.totalPosts.length);
            } catch (err) {
                console.error('Error fetching posts:', err);
                this.totalPosts = [];
            }
        }

        shufflePosts() {
            for (let i = this.totalPosts.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [this.totalPosts[i], this.totalPosts[j]] = [this.totalPosts[j], this.totalPosts[i]];
            }
        }

        render() {
            if (!this.totalPosts.length) {
                const track = this.el.querySelector('.slider-track');
                track.innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-dim);">Roman bulunamadı</div>';
                return;
            }

            const current = this.totalPosts.slice(this.currentPage * this.max, (this.currentPage + 1) * this.max);
            const postsHTML = current.map(post => {
                const title = post.title.rendered || 'No Title';
                const excerpt = (post.excerpt.rendered || '').replace(/<[^>]*>?/gm, '').substring(0, 80) || 'No description';
                const cover = post.featured_media_src || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23333%22 width=%22400%22 height=%22300%22/%3E%3C/svg%3E';
                const link = post.link || '#';
                const status = post.status_label || 'Ongoing';

                return `
                    <article style="height: 244px; position: relative; border-radius: 8px; background: var(--bg-card); border: 1px solid var(--border); overflow: hidden; transition: all 300ms; display: flex;">
                        <div style="position: absolute; inset: 0; display: flex;">
                            <div style="width: 100%; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; z-index: 20; position: relative;" class="group">
                                <div style="height: 100%; display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; font-weight: bold; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em; width: fit-content; margin-bottom: 8px;">${status}</span>
                                    <h3 style="font-size: 0.875rem; font-weight: bold; color: var(--text-main); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 8px;">${title}</h3>
                                    <p style="font-size: 0.7rem; color: var(--text-dim); margin-top: auto; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; opacity: 0.8;">${excerpt}...</p>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                                    <a href="${link}" style="font-size: 9px; font-weight: bold; color: #000; background: #06b6d4; padding: 6px 12px; border-radius: 4px; text-decoration: none; text-transform: uppercase; transition: all 300ms;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">Oku</a>
                                </div>
                            </div>
                            <div style="position: absolute; right: 0; top: 0; width: 40%; height: 100%; overflow: hidden; z-index: 10;">
                                <img src="${cover}" alt="${title}" style="width: 100%; height: 100%; object-fit: cover; clip-path: polygon(15% 0%, 100% 0%, 100% 100%, 0% 100%); transition: transform 700ms;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" />
                            </div>
                        </div>
                    </article>
                `;
            }).join('');

            const track = this.el.querySelector('.slider-track');
            track.style.opacity = '1';
            track.innerHTML = postsHTML;

            this.setupEventListeners();
        }

        setupEventListeners() {
            const prevBtn = this.el.querySelector('.slider-prev');
            const nextBtn = this.el.querySelector('.slider-next');

            prevBtn.onclick = () => this.goToPrev();
            nextBtn.onclick = () => this.goToNext();
        }

        async goToPrev() {
            const totalPages = Math.ceil(this.totalPosts.length / this.max);
            this.currentPage = (this.currentPage - 1 + totalPages) % totalPages;
            this.render();
        }

        async goToNext() {
            const totalPages = Math.ceil(this.totalPosts.length / this.max);
            this.currentPage = (this.currentPage + 1) % totalPages;
            this.render();
        }

        startAutoPlay() {
            this.autoPlayTimer = setInterval(() => this.goToNext(), this.duration);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('nt-featured-slider');
        if (slider) new NTFeaturedSlider(slider);
    });
})();

function showTab(tabId, el) {
    const tabs = document.querySelectorAll('.novels-tab-content');
    tabs.forEach(t => t.classList.remove('is-visible'));

    const target = document.getElementById('tab-' + tabId);
    if (target) target.classList.add('is-visible');

    const btns = document.querySelectorAll('.tabs .custom-btn');
    btns.forEach(b => {
        b.classList.remove('active');
        b.style.background = 'var(--bg-card)';
        b.style.color = 'var(--text-main)';
        b.style.border = '1px solid var(--border)';
    });

    el.classList.add('active');
    el.style.background = 'var(--accent)';
    el.style.color = '#fff';
    el.style.border = '1px solid var(--accent)';
}

// Featured Slider Auto-Scroll & Navigation
document.addEventListener('DOMContentLoaded', function() {
    const sliderTrack = document.querySelector('.nt-featured-slider .slider-track');
    const sliderPrev = document.querySelector('.slider-prev');
    const sliderNext = document.querySelector('.slider-next');
    if (!sliderTrack) return;

    let autoScrollInterval;

    function getScrollAmount() {
        const isMobile = window.innerWidth < 768;
        const cards = sliderTrack.querySelectorAll('.post-card');
        const cardWidth = cards[0]?.offsetWidth || 0;
        const gap = 16;
        return cardWidth + gap;
    }

    function startAutoScroll() {
        const isMobile = window.innerWidth < 768;
        const cards = sliderTrack.querySelectorAll('.post-card');
        let itemsPerView = 2;
        let scrollInterval = 6000;

        if (isMobile) {
            itemsPerView = 1;
            scrollInterval = 4000;
        }

        let currentIndex = 0;

        if (autoScrollInterval) clearInterval(autoScrollInterval);

        autoScrollInterval = setInterval(() => {
            currentIndex = (currentIndex + itemsPerView) % cards.length;
            const scrollAmount = getScrollAmount();
            sliderTrack.scrollLeft = currentIndex * scrollAmount;
        }, scrollInterval);
    }

    // Button click handlers
    if (sliderPrev) {
        sliderPrev.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            sliderTrack.scrollLeft -= scrollAmount;
            clearInterval(autoScrollInterval);
            startAutoScroll();
        });
    }

    if (sliderNext) {
        sliderNext.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            sliderTrack.scrollLeft += scrollAmount;
            clearInterval(autoScrollInterval);
            startAutoScroll();
        });
    }

    startAutoScroll();

    window.addEventListener('resize', startAutoScroll);
});

// Sidebar Tab Logic
function bindSidebarTabs() {
    const tabBtns = document.querySelectorAll('#sidebar-genre-tabs .sidebar-tab-btn');
    const tabLists = document.querySelectorAll('#sidebar-genre-contents .sidebar-novel-list');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');

            tabLists.forEach(list => list.style.display = 'none');
            tabBtns.forEach(b => {
                b.style.background = 'transparent';
                b.style.color = 'var(--text-dim)';
            });

            const target = document.getElementById(targetId);
            if (target) target.style.display = 'flex';
            this.style.background = '#3b82f6';
            this.style.color = '#fff';
        });
    });
}

function ntRefreshGenres(btn) {
    const svg = btn.querySelector('svg');
    btn.disabled = true;
    if (svg) svg.style.animation = 'nt-spin 0.7s linear infinite';

    fetch(<?php echo json_encode(admin_url('admin-ajax.php')); ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=refresh_sidebar_categories'
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.text();
    })
    .then(function(html) {
        if (!html || html.trim() === '0' || html.trim() === '') return;
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        const newTabs     = tmp.querySelector('.sidebar-tabs');
        const newContents = tmp.querySelector('.sidebar-tab-contents');
        if (newTabs)     document.getElementById('sidebar-genre-tabs').innerHTML     = newTabs.innerHTML;
        if (newContents) document.getElementById('sidebar-genre-contents').innerHTML = newContents.innerHTML;
        bindSidebarTabs();
    })
    .catch(function(err) { console.warn('Genre refresh failed:', err); })
    .then(function() {
        btn.disabled = false;
        if (svg) svg.style.animation = '';
    });
}

document.addEventListener('DOMContentLoaded', bindSidebarTabs);

function ntToggleGenreDropdown() {
    var dd = document.getElementById('nt-genre-dropdown');
    var chevron = document.getElementById('nt-genre-chevron');
    var open = dd.style.display !== 'none';
    dd.style.display = open ? 'none' : 'block';
    chevron.style.transform = open ? '' : 'rotate(180deg)';
}

function ntUpdateGenreLabel() {
    var checked = document.querySelectorAll('#nt-genre-dropdown input[type="checkbox"]:checked');
    var label = document.getElementById('nt-genre-label');
    if (checked.length === 0) {
        label.textContent = 'Tümü';
    } else if (checked.length === 1) {
        label.textContent = checked[0].closest('label').textContent.trim();
    } else {
        label.textContent = checked.length + ' kategori seçildi';
    }
}

document.addEventListener('click', function(e) {
    var dd = document.getElementById('nt-genre-dropdown');
    var btn = document.getElementById('nt-genre-btn');
    if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
        dd.style.display = 'none';
        document.getElementById('nt-genre-chevron').style.transform = '';
    }
});

</script>

<?php get_footer(); ?>
