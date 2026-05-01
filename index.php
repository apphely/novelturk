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

        <!-- Hızlı Erişim Grid -->
        <div class="nt-quick-access" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin-bottom: 24px;">
            <a href="#" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">Destek/Bağış</a>
            <a href="#" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">En Popülerler</a>
            <a href="/#Fİltreleme" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">Filtreleme</a>
            <a href="/#SiteYorumlari" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">Site Yorumları</a>
            <a href="/#SonYorumlar" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">Son Yorumlar</a>
            <a href="#" style="background: var(--accent); color: #fff; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: 700; text-align: center; font-size: 12px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; min-height: 32px;">Tema Ayarları</a>
        </div>

        <style>
            .nt-quick-access a:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
            @media (max-width: 1200px) {
                .nt-quick-access { grid-template-columns: repeat(4, 1fr) !important; gap: 8px; }
            }
            @media (max-width: 768px) {
                .nt-quick-access { grid-template-columns: repeat(3, 1fr) !important; gap: 6px; margin-bottom: 20px; }
                .nt-quick-access a { font-size: 10px; padding: 6px; min-height: 28px; }
            }
            @media (max-width: 480px) {
                .nt-quick-access { grid-template-columns: repeat(3, 1fr) !important; gap: 4px; }
                .nt-quick-access a { font-size: 9px; padding: 4px; min-height: 24px; }
            }
        </style>
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

        <!-- Comments Section -->
        <section class="nt-comments-section" style="padding: 16px; background: var(--bg-card); border-radius: 8px;">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 24px;">Yorumlar</h2>
            <?php
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </section>
    </main>

    <!-- Sidebar Widget Area (NovelTurk specific Filtreleme) -->
    <aside class="nt-sidebar">
        <div id="Filtreleme" class="nt-card" style="background-color: var(--bg-surface); border:none; overflow:visible;">
            <div class="nt-card-body" style="overflow:visible;">
                <h3 class="nt-text-xl nt-font-bold nt-mb-4" style="color:var(--text-main);">Novel Filtreleme</h3>

                <div class="nt-filters" style="display:flex; flex-wrap:wrap; justify-content:center; gap:12px;">
                    <!-- Search -->
                    <div style="position:relative; flex:1 1 100%;">
                        <input type="text" id="nt-filter-search" placeholder="Novel/Bölüm ARA" value="<?php echo get_search_query(); ?>" style="width:100%; padding:10px 16px 10px 40px; background:var(--bg-card); border:1px solid var(--border); color:var(--text-main); border-radius:24px; outline:none; font-size:14px;">
                        <svg style="position:absolute; left:14px; top:12px; color:var(--text-dim);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>

                    <!-- Durum -->
                    <dl style="display:grid; align-items:center; flex:1; position:relative;">
                        <dt class="filter-toggle" data-target="dd-durum" style="background:var(--bg-card); font-weight:500; border-radius:24px; font-size:14px; padding:8px 20px; text-align:center; display:inline-flex; align-items:center; gap:6px; justify-content:center; cursor:pointer; border:1px solid var(--border); color:var(--text-main);">
                            Durum
                            <svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M4 16V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a1 1 0 0 1-1 1H5a1 1 0 0 0 1 1h9.5a.5.5 0 0 1 0 1H6a2 2 0 0 1-2-2M15 4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v11h10zM9.455 6.293a.5.5 0 0 0-.902-.017L7.19 9H6.5a.5.5 0 0 0 0 1h1a.5.5 0 0 0 .447-.276L8.98 7.66l2.066 4.546a.5.5 0 0 0 .884.05L13.283 10h.217a.5.5 0 0 0 0-1H13a.5.5 0 0 0-.429.243l-1.01 1.683z" fill="currentColor"/></svg>
                        </dt>
                        <dd id="dd-durum" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:10; margin-top:4px; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            <div style="padding:12px;">
                                <?php
                                $statuses = array('Tamamlandı' => 'completed', 'Güncel' => 'ongoing', 'Devam Ediyor' => 'ongoing', 'Çok Yakında' => 'upcoming', 'Terk Edildi' => 'discontinued', 'Süresizlik' => 'hiatus');
                                foreach ($statuses as $label => $val) :
                                ?>
                                <div style="display:flex; align-items:center; margin-bottom:4px;">
                                    <input class="genre" name="filter_durum" id="s-<?php echo esc_attr($val); ?>-<?php echo esc_attr(sanitize_title($label)); ?>" type="radio" value="<?php echo esc_attr($val); ?>" style="width:16px; height:16px; cursor:pointer; accent-color:#3b82f6;">
                                    <label for="s-<?php echo esc_attr($val); ?>-<?php echo esc_attr(sanitize_title($label)); ?>" style="font-size:14px; font-weight:500; flex:1; padding:6px; border-radius:24px; cursor:pointer; color:var(--text-main);"><?php echo esc_html($label); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </dd>
                    </dl>

                    <!-- Tür -->
                    <dl style="display:grid; align-items:center; flex:1; position:relative;">
                        <dt class="filter-toggle" data-target="dd-tur" style="background:var(--bg-card); font-weight:500; border-radius:24px; font-size:14px; padding:8px 20px; text-align:center; display:inline-flex; align-items:center; gap:6px; justify-content:center; cursor:pointer; border:1px solid var(--border); color:var(--text-main);">
                            Tür
                            <svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M4 16V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a1 1 0 0 1-1 1H5a1 1 0 0 0 1 1h9.5a.5.5 0 0 1 0 1H6a2 2 0 0 1-2-2M15 4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v11h10zm-8 7.25q.001-.114.031-.218q.218.165.453.3A5.1 5.1 0 0 0 10 12c.982 0 1.863-.293 2.516-.669q.235-.135.453-.299q.03.105.031.218c0 .3-.182.55-.33.71a2.8 2.8 0 0 1-.653.505A4.1 4.1 0 0 1 10 13a4.1 4.1 0 0 1-2.017-.535a2.8 2.8 0 0 1-.654-.504C7.182 11.8 7 11.55 7 11.25m.031-2.218A.8.8 0 0 0 7 9.25c0 .3.182.551.33.71a2.8 2.8 0 0 0 .653.505A4.1 4.1 0 0 0 10 11c.788 0 1.498-.236 2.017-.535c.26-.15.485-.322.654-.504c.147-.16.329-.41.329-.71a.8.8 0 0 0-.031-.219q-.218.165-.453.3A5.1 5.1 0 0 1 10 10a5.1 5.1 0 0 1-2.516-.669a4 4 0 0 1-.453-.299M8 7c0-.213.126-.448.483-.655C8.841 6.137 9.374 6 10 6s1.159.137 1.517.345S12 6.787 12 7s-.126.448-.483.655C11.159 7.863 10.626 8 10 8s-1.159-.137-1.517-.345S8 7.213 8 7m2-2c-.755 0-1.472.163-2.019.48C7.434 5.798 7 6.313 7 7s.434 1.202.981 1.52C8.528 8.837 9.245 9 10 9s1.472-.163 2.019-.48C12.566 8.202 13 7.687 13 7s-.434-1.202-.981-1.52C11.472 5.163 10.755 5 10 5" fill="currentColor"/></svg>
                        </dt>
                        <dd id="dd-tur" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:10; margin-top:4px; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            <div style="padding:12px;">
                                <?php
                                $types = get_terms(array('taxonomy' => 'novel_type', 'hide_empty' => false));
                                if (!is_wp_error($types)) :
                                    foreach ($types as $t) :
                                ?>
                                <div style="display:flex; align-items:center; margin-bottom:4px;">
                                    <input class="genre" name="filter_tur" id="t-<?php echo esc_attr($t->slug); ?>" type="radio" value="<?php echo esc_attr($t->slug); ?>" style="width:16px; height:16px; cursor:pointer; accent-color:#3b82f6;">
                                    <label for="t-<?php echo esc_attr($t->slug); ?>" style="font-size:14px; font-weight:500; flex:1; padding:6px; border-radius:24px; cursor:pointer; color:var(--text-main);"><?php echo esc_html($t->name); ?></label>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </dd>
                    </dl>

                    <!-- Ülke -->
                    <dl style="display:grid; align-items:center; flex:1; position:relative;">
                        <dt class="filter-toggle" data-target="dd-ulke" style="background:var(--bg-card); font-weight:500; border-radius:24px; font-size:14px; padding:8px 20px; text-align:center; display:inline-flex; align-items:center; gap:6px; justify-content:center; cursor:pointer; border:1px solid var(--border); color:var(--text-main);">
                            Ülke
                            <svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M4 16V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a1 1 0 0 1-1 1H5a1 1 0 0 0 1 1h9.5a.5.5 0 0 1 0 1H6a2 2 0 0 1-2-2M15 4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v11h10zM7.041 8h.973c.045-.773.192-1.485.42-2.059A3.002 3.002 0 0 0 7.04 8M6 8.5a4 4 0 1 1 8 0a4 4 0 0 1-8 0m6.959-.5a3.002 3.002 0 0 0-1.392-2.059c.227.574.374 1.286.419 2.059zm-.973 1c-.045.773-.192 1.486-.42 2.059A3.002 3.002 0 0 0 12.96 9zm-1.002-1c-.046-.707-.189-1.324-.383-1.778c-.12-.28-.25-.474-.368-.591c-.117-.115-.195-.131-.233-.131c-.038 0-.116.016-.233.13c-.118.118-.248.312-.368.592c-.194.454-.337 1.07-.383 1.778zM9.016 9c.046.707.189 1.324.383 1.778c.12.28.25.474.368.591c.117.115.195.131.233.131c.038 0 .116-.016.233-.13c.118-.118.248-.312.368-.592c.194-.454.336-1.07.383-1.778zM8.014 9h-.973a3.01 3.01 0 0 0 1.392 2.059c-.227-.573-.374-1.286-.419-2.059" fill="currentColor"/></svg>
                        </dt>
                        <dd id="dd-ulke" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:10; margin-top:4px; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            <div style="padding:12px;">
                                <?php
                                $origins = get_terms(array('taxonomy' => 'novel_origin', 'hide_empty' => false));
                                if (!is_wp_error($origins)) :
                                    foreach ($origins as $o) :
                                ?>
                                <div style="display:flex; align-items:center; margin-bottom:4px;">
                                    <input class="genre" name="filter_ulke" id="o-<?php echo esc_attr($o->slug); ?>" type="radio" value="<?php echo esc_attr($o->slug); ?>" style="width:16px; height:16px; cursor:pointer; accent-color:#3b82f6;">
                                    <label for="o-<?php echo esc_attr($o->slug); ?>" style="font-size:14px; font-weight:500; flex:1; padding:6px; border-radius:24px; cursor:pointer; color:var(--text-main);"><?php echo esc_html($o->name); ?></label>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </dd>
                    </dl>

                    <!-- Kategori -->
                    <dl style="display:grid; align-items:center; flex:1; position:relative;">
                        <dt class="filter-toggle" data-target="dd-kategori" style="background:var(--bg-card); font-weight:500; border-radius:24px; font-size:14px; padding:8px 20px; text-align:center; display:inline-flex; align-items:center; gap:6px; justify-content:center; cursor:pointer; border:1px solid var(--border); color:var(--text-main);">
                            Kategori
                            <svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 7.505a.5.5 0 0 1 .5-.5h1.29l.221-1.102a.5.5 0 0 1 .98.197l-.18.905h.98l.22-1.101a.5.5 0 0 1 .98.195l-.18.906h.94a.5.5 0 0 1 0 1h-1.14l-.2 1h1.09a.5.5 0 1 1 0 1h-1.289l-.218 1.093a.5.5 0 0 1-.98-.196l.178-.897H9.21l-.219 1.093a.5.5 0 1 1-.98-.196l.18-.897h-.938a.5.5 0 0 1 0-1H8.39l.2-1H7.5a.5.5 0 0 1-.5-.5m3.392 1.5l.2-1H9.61l-.2 1zM6 2h8.004a2 2 0 0 1 2 2v11.501a.5.5 0 0 1-.5.5H5A1 1 0 0 0 6 17h9.504a.5.5 0 0 1 0 1H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2M5 15.001h10.004V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1z" fill="currentColor"/></svg>
                        </dt>
                        <dd id="dd-kategori" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:100; margin-top:4px; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:200px;">
                            <div style="padding:12px; max-height:288px; overflow-y:auto;">
                                <?php
                                $genres = get_terms(array('taxonomy' => 'novel_genre', 'hide_empty' => false));
                                if (!is_wp_error($genres)) :
                                    foreach ($genres as $g) :
                                ?>
                                <div style="display:flex; align-items:center; margin-bottom:4px;">
                                    <input class="genre" id="g-<?php echo esc_attr($g->slug); ?>" type="checkbox" value="<?php echo esc_attr($g->slug); ?>" style="width:16px; height:16px; cursor:pointer; accent-color:#3b82f6;">
                                    <label for="g-<?php echo esc_attr($g->slug); ?>" style="font-size:14px; font-weight:500; flex:1; padding:6px; border-radius:24px; cursor:pointer; color:var(--text-main);"><?php echo esc_html($g->name); ?> (<?php echo $g->count; ?>)</label>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </dd>
                    </dl>

                    <!-- Filtrele Button -->
                    <button type="button" id="nt-filtrele-btn" style="flex:1 1 100%; padding:12px; border-radius:24px; background:linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%); color:#fff; border:none; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9.5a.5.5 0 0 0 0-1H6a1 1 0 0 1-1-1h10a1 1 0 0 0 1-1V4a2 2 0 0 0-2-2zm5.586 7.879l1.268 1.267a.5.5 0 0 1-.708.708l-1.267-1.268a2.5 2.5 0 1 1 .707-.707M8 8.5a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0" fill="currentColor"/></svg>
                        Filtrele
                    </button>
                </div>

            </div>
        </div>

        <!-- Sidebar Category Novels Card -->
        <div class="nt-card" style="background-color: var(--bg-surface); border:none; overflow:visible; margin-bottom:24px;">
            <div class="nt-card-body" style="overflow:visible;">

                <!-- Sidebar Genre Tabs / Pills -->
                <div id="sidebar-tabs-wrapper" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div class="sidebar-tabs" style="display:flex; gap:8px; flex-wrap:wrap;">
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
                    <button type="button" id="refresh-sidebar-novels" style="background:var(--bg-card); color:var(--text-dim); border:1px solid var(--border); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="Yenile">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                </div>

                <!-- Recent Novels Mini Cards Array (Tab Contents) -->
                <div id="sidebar-tab-contents-wrapper">
                <div class="sidebar-tab-contents">
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

                <!-- Popüler Novellar Section -->
                <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--border);">
                    <h3 style="color:var(--text-main); margin:0 0 16px 0; font-size:16px; font-weight:700;">Popüler Novellar</h3>
                    <div id="popular-novels-tabs" style="display:flex; gap:8px; margin-bottom:16px;">
                        <button type="button" class="popular-tab-btn" data-period="week" style="background:#3b82f6; color:#fff; padding:6px 12px; font-size:12px; border:none; border-radius:20px; font-weight:600; cursor:pointer;">Bu Hafta</button>
                        <button type="button" class="popular-tab-btn" data-period="month" style="background:transparent; color:var(--text-dim); padding:6px 12px; font-size:12px; border:none; border-radius:20px; font-weight:600; cursor:pointer;">3 Ay</button>
                        <button type="button" class="popular-tab-btn" data-period="all" style="background:transparent; color:var(--text-dim); padding:6px 12px; font-size:12px; border:none; border-radius:20px; font-weight:600; cursor:pointer;">Tüm Zamanlar</button>
                    </div>
                    <div id="popular-novels-content" style="display:flex; flex-direction:column; gap:12px;">
                        <?php
                        $popular_args = array(
                            'post_type' => 'novel',
                            'posts_per_page' => 7,
                            'orderby' => 'comment_count',
                            'order' => 'DESC',
                            'date_query' => array(
                                array(
                                    'after' => '7 days ago',
                                    'inclusive' => true
                                )
                            )
                        );
                        $popular_query = new WP_Query($popular_args);
                        if ($popular_query->have_posts()) :
                            while ($popular_query->have_posts()) : $popular_query->the_post();
                                $thumb = webnovel_get_cover_url(get_the_ID(), 'medium');
                        ?>
                        <a href="<?php the_permalink(); ?>" style="display:flex; background-color:var(--bg-card); border-radius:8px; overflow:hidden; text-decoration:none; color:var(--text-main); height:100px; border:1px solid var(--border); box-shadow:var(--shadow-sm); transition:transform 0.2s;">
                            <div style="width:70px; flex-shrink:0; position:relative;">
                                <?php if($thumb): ?>
                                    <img src="<?php echo esc_url($thumb); ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php the_title_attribute(); ?>">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:var(--bg-surface);"></div>
                                <?php endif; ?>
                            </div>
                            <div style="padding:12px; display:flex; align-items:center; flex:1;">
                                <div style="display:flex; flex-direction:column; gap:4px; width:100%;">
                                    <h4 style="font-size:12px; font-weight:700; margin:0; color:var(--text-main); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?php the_title(); ?></h4>
                                    <div style="font-size:11px; color:var(--text-dim); display:flex; gap:8px;">
                                        <?php
                                        $rating_avg = get_post_meta(get_the_ID(), '_novel_rating_avg', true);
                                        $rating_count = get_post_meta(get_the_ID(), '_novel_rating_count', true);
                                        if ($rating_avg) :
                                        ?>
                                        <span>⭐ <?php echo number_format((float)$rating_avg, 1); ?> (<?php echo (int)$rating_count; ?>)</span>
                                        <?php endif; ?>
                                        <span>💭 <?php echo get_comments_number(); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php
                            endwhile;
                        endif;
                        wp_reset_postdata();
                        ?>
                    </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.sidebar-tab-btn');
    const tabLists = document.querySelectorAll('.sidebar-novel-list');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');

            // Hide all
            tabLists.forEach(list => list.style.display = 'none');
            // Deactivate all buttons
            tabBtns.forEach(b => {
                b.style.background = 'transparent';
                b.style.color = 'var(--text-dim)';
            });

            // Activate current
            const target = document.getElementById(targetId);
            if(target) target.style.display = 'flex';
            this.style.background = '#3b82f6'; // Match Romantizm active state style
            this.style.color = '#fff';
        });
    });
});

// Sidebar Filter System — Blogger-style dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.filter-toggle');
    const allDDs = document.querySelectorAll('.nt-filters dd');

    // Toggle dropdowns
    toggles.forEach(dt => {
        dt.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.dataset.target;
            const dd = document.getElementById(targetId);
            if (!dd) return;

            // Close others
            allDDs.forEach(d => { if (d.id !== targetId) d.style.display = 'none'; });

            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nt-filters dd') && !e.target.closest('.filter-toggle')) {
            allDDs.forEach(d => d.style.display = 'none');
        }
    });

    // Filtrele button
    document.getElementById('nt-filtrele-btn').addEventListener('click', function() {
        var checked = document.querySelectorAll('.nt-filters .genre:checked');
        var values = [];
        checked.forEach(function(cb) { values.push(cb.value); });

        var search = document.getElementById('nt-filter-search').value;
        var url = '<?php echo home_url(); ?>/?post_type=novel';

        if (search) url += '&s=' + encodeURIComponent(search);
        if (values.length > 0) url += '&filter_labels=' + encodeURIComponent(values.join('+'));

        window.location.href = url;
    });

    // Refresh sidebar categories and novels
    document.getElementById('refresh-sidebar-novels').addEventListener('click', function() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=refresh_sidebar_categories'
        })
        .then(r => r.text())
        .then(html => {
            if (html) {
                // Extract tabs and contents from returned HTML
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                var newTabs = tempDiv.querySelector('.sidebar-tabs');
                var newContents = tempDiv.querySelector('.sidebar-tab-contents');

                // Update tabs buttons
                var oldTabs = document.querySelector('.sidebar-tabs');
                if (oldTabs && newTabs) oldTabs.innerHTML = newTabs.innerHTML;

                // Update contents
                var oldContents = document.querySelector('.sidebar-tab-contents');
                if (oldContents && newContents) oldContents.innerHTML = newContents.innerHTML;

                // Re-attach tab click handlers
                attachTabHandlers();
            }
        })
        .catch(e => console.error('Refresh error:', e));
    });

    // Tab click handler
    function attachTabHandlers() {
        var tabBtns = document.querySelectorAll('.sidebar-tab-btn');
        tabBtns.forEach(btn => {
            btn.removeEventListener('click', tabClickHandler);
            btn.addEventListener('click', tabClickHandler);
        });
    }

    function tabClickHandler(e) {
        var targetId = this.getAttribute('data-target');
        var targetTab = document.getElementById(targetId);
        if (!targetTab) return;

        // Hide all tabs
        var allTabs = document.querySelectorAll('.sidebar-novel-list');
        allTabs.forEach(tab => tab.style.display = 'none');

        // Show clicked tab
        targetTab.style.display = 'flex';

        // Update button styles
        var allBtns = document.querySelectorAll('.sidebar-tab-btn');
        allBtns.forEach(btn => {
            if (btn === this) {
                btn.style.background = '#3b82f6';
                btn.style.color = '#fff';
            } else {
                btn.style.background = 'transparent';
                btn.style.color = 'var(--text-dim)';
            }
        });
    }

    // Initial tab handlers
    attachTabHandlers();

    // Popular novels tab handlers
    var popularTabBtns = document.querySelectorAll('.popular-tab-btn');
    popularTabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            var period = this.getAttribute('data-period');

            // Update button styles
            popularTabBtns.forEach(b => {
                if (b === this) {
                    b.style.background = '#3b82f6';
                    b.style.color = '#fff';
                } else {
                    b.style.background = 'transparent';
                    b.style.color = 'var(--text-dim)';
                }
            });

            // Fetch popular novels for this period
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=fetch_popular_novels&period=' + encodeURIComponent(period)
            })
            .then(r => r.text())
            .then(html => {
                if (html) {
                    var content = document.getElementById('popular-novels-content');
                    if (content) content.innerHTML = html;
                }
            })
            .catch(e => console.error('Popular novels error:', e));
        });
    });

    // Tab switching function
    window.showTab = function(tabName, btn) {
        var tabs = document.querySelectorAll('.novels-tab-content');
        tabs.forEach(tab => tab.classList.remove('is-visible'));
        document.getElementById('tab-' + tabName).classList.add('is-visible');

        var buttons = document.querySelectorAll('.custom-btn');
        buttons.forEach(b => {
            b.style.background = 'var(--bg-card)';
            b.style.color = 'var(--text-main)';
        });
        btn.style.background = 'var(--accent)';
        btn.style.color = '#fff';
    };
});
</script>

<?php get_footer(); ?>
