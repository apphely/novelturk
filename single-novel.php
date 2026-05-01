<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post();
    $novel_id = get_the_ID();

    // Ensure stats are seeded
    webnovel_seed_novel_stats($novel_id);

    $status         = get_post_meta($novel_id, '_novel_status', true) ?: 'ongoing';
    $author_name    = get_post_meta($novel_id, '_novel_author', true);
    $alt_title      = get_post_meta($novel_id, '_novel_alt_title', true);
    $editor         = get_post_meta($novel_id, '_novel_editor', true);
    $translator     = get_post_meta($novel_id, '_novel_translator', true);
    $illustrator    = get_post_meta($novel_id, '_novel_illustrator', true);
    $supporters     = get_post_meta($novel_id, '_novel_supporters', true);
    $note           = get_post_meta($novel_id, '_novel_note', true);
    $yt_url         = get_post_meta($novel_id, '_novel_youtube_url', true);
    $yt_label       = get_post_meta($novel_id, '_novel_youtube_label', true) ?: "YouTube'da Dinle";
    $related_items  = webnovel_parse_related(get_post_meta($novel_id, '_novel_related', true));
    $extras_items   = webnovel_parse_extras(get_post_meta($novel_id, '_novel_extras', true));

    // Önerilen Noveller — aynı türde (novel_genre) olan diğer romanlardan rastgele 6 tane
    $recommended_novels = array();
    $current_genre_ids = wp_get_post_terms($novel_id, 'novel_genre', array('fields' => 'ids'));
    if (!empty($current_genre_ids) && !is_wp_error($current_genre_ids)) {
        $rec_q = new WP_Query(array(
            'post_type'      => 'novel',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'post__not_in'   => array($novel_id),
            'orderby'        => 'rand',
            'tax_query'      => array(array(
                'taxonomy' => 'novel_genre',
                'field'    => 'term_id',
                'terms'    => $current_genre_ids,
            )),
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
        ));
        $recommended_novels = $rec_q->posts;
        wp_reset_postdata();
    }

    // Build synopsis (prefers the Excerpt/Özet field, falls back to content)
    $nt_split       = webnovel_split_social_widgets();
    $synopsis_html  = $nt_split['synopsis'];

    $chapters       = webnovel_get_chapters($novel_id, 'ASC');
    $chapter_count  = webnovel_get_chapter_count($novel_id);
    $genres         = get_the_terms($novel_id, 'novel_genre');
    $tags           = get_the_terms($novel_id, 'novel_tag');

    $status_label  = webnovel_get_status_label($status);

    $first_chapter = !empty($chapters) ? $chapters[0] : null;
    $last_chapter  = !empty($chapters) ? end($chapters) : null;

    // Rating + comment counters (used in both sidebar widget and mobile hero rating)
    $rating        = floatval(get_post_meta($novel_id, '_novel_rating_avg', true) ?: '0');
    $rating_count  = (int) (get_post_meta($novel_id, '_novel_rating_count', true) ?: 0);
    $comment_count = (int) get_comments_number($novel_id);

    // Group chapters by volume (Cilt) — falls back to 100-item chunks
    $has_volumes = false;
    $grouped_chapters = array();
    foreach ($chapters as $ch) {
        $vol = get_post_meta($ch->ID, '_chapter_volume', true);
        if ($vol) {
            $has_volumes = true;
            $key = 'Cilt ' . $vol;
            if (!isset($grouped_chapters[$key])) $grouped_chapters[$key] = array();
            $grouped_chapters[$key][] = $ch;
        } else {
            if (!isset($grouped_chapters['Diğer Bölümler'])) $grouped_chapters['Diğer Bölümler'] = array();
            $grouped_chapters['Diğer Bölümler'][] = $ch;
        }
    }
    if (!$has_volumes && !empty($chapters)) {
        $grouped_chapters = array();
        // Group by chapter number ranges: 0-100.x, 101-200.x, 201-300.x ...
        // Rule: integer part 0-100 → bucket 0, 101-200 → bucket 1, etc.
        // Formula: floor(max(intval(num) - 1, 0) / 100)
        $temp_groups = array();
        foreach ($chapters as $ch) {
            $num    = (float) get_post_meta($ch->ID, '_chapter_number', true);
            $iv     = (int) $num;
            $bucket = (int) floor(max($iv - 1, 0) / 100);
            $temp_groups[$bucket][] = $ch;
        }
        ksort($temp_groups, SORT_NUMERIC);
        $grouped_chapters_meta = array();
        foreach ($temp_groups as $bucket => $group) {
            $nums = array_map(function($ch) {
                return (float) get_post_meta($ch->ID, '_chapter_number', true);
            }, $group);
            $min_num = min($nums);
            $max_num = max($nums);
            $fmt = function($n) {
                return $n == (int)$n ? (string)(int)$n : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
            };
            $label_desc = $min_num === $max_num ? $fmt($min_num) : ($fmt($max_num) . '-' . $fmt($min_num));
            $label_asc  = $min_num === $max_num ? $fmt($min_num) : ($fmt($min_num) . '-' . $fmt($max_num));
            $grouped_chapters[$label_desc] = array_reverse($group);
            $grouped_chapters_meta[$label_desc] = array('asc' => $label_asc, 'desc' => $label_desc);
        }
        // Tabs ordered descending (200-101 first), chapters within each group newest-first
        $grouped_chapters = array_reverse($grouped_chapters, true);
        $grouped_chapters_meta = array_reverse($grouped_chapters_meta, true);
    }
    // For volumes case, also reverse each volume's chapters for display (newest first)
    if ($has_volumes) {
        foreach ($grouped_chapters as $k => $vc) {
            $grouped_chapters[$k] = array_reverse($vc);
        }
    }
?>

<?php
// Tür/Ülke terimleri — hero rozetleri için kullanılır
$bc_types   = get_the_terms($novel_id, 'novel_type');
$bc_origins = get_the_terms($novel_id, 'novel_origin');
$bc_type    = ($bc_types   && !is_wp_error($bc_types))   ? $bc_types[0]   : null;
$bc_origin  = ($bc_origins && !is_wp_error($bc_origins)) ? $bc_origins[0] : null;
?>

<div class="nt-container">
    <main class="nt-main">

        <!-- Novel Hero (Cover + Info) -->
        <article class="nt-card">
            <div class="nt-card-body nt-flex-row" style="flex-wrap: wrap;">
                <div class="novel-cover" style="width: 100%; max-width: 240px; position:relative; flex-shrink:0;">
                    <?php $novel_cover = webnovel_get_cover_url($novel_id, 'large'); ?>
                    <?php if ($novel_cover) : ?>
                        <img src="<?php echo esc_url($novel_cover); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); display:block;">
                    <?php else : ?>
                        <div style="width:100%; aspect-ratio:2/3; background:#333; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:3rem;">📚</div>
                    <?php endif; ?>

                    <?php $types = get_the_terms($novel_id, 'novel_type'); ?>
                    <?php if ($types && !is_wp_error($types)) : ?>
                        <span class="type-badge badge-<?php echo sanitize_title($types[0]->slug); ?>" style="position:absolute; top:8px; right:8px; z-index:10;"><?php echo esc_html($types[0]->name); ?></span>
                    <?php endif; ?>
                </div>

                <div class="novel-info nt-flex-col nt-flex-1" style="min-width: 280px; gap: 12px;">
                    <h1 class="nt-text-2xl nt-font-bold nt-text-main" style="margin:0; line-height:1.2;"><?php the_title(); ?></h1>

                    <?php if ($alt_title): ?>
                        <h2 class="nt-text-sm nt-text-dim" style="font-style:italic; font-weight:normal; margin:0; line-height:1.4;"><?php echo esc_html($alt_title); ?></h2>
                    <?php endif; ?>

                    <!-- Durum / Tür / Ülke rozetleri (Blogger renkleri) -->
                    <div class="nt-flex nt-flex-wrap nt-gap-2 nt-text-sm nt-font-bold">
                        <a class="nt-badge" data-status="<?php echo esc_attr($status_label); ?>" href="<?php echo esc_url(add_query_arg('nstatus', $status, get_post_type_archive_link('novel'))); ?>" title="<?php echo esc_attr($status_label); ?> durumdaki tüm romanlar">
                            <?php echo esc_html($status_label); ?>
                        </a>
                        <?php if ($bc_type) : ?>
                            <a class="nt-badge" data-type="<?php echo esc_attr($bc_type->name); ?>" href="<?php echo get_term_link($bc_type); ?>">
                                <?php echo esc_html($bc_type->name); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($bc_origin) : ?>
                            <a class="nt-badge" data-country="<?php echo esc_attr($bc_origin->name); ?>" href="<?php echo get_term_link($bc_origin); ?>">
                                <?php echo esc_html($bc_origin->name); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($genres) : ?>
                    <div class="nt-flex nt-flex-wrap nt-gap-2 novel-genre-tags">
                        <?php foreach ($genres as $genre) : ?>
                            <a class="genre-tag" href="<?php echo get_term_link($genre); ?>" title="<?php echo esc_attr($genre->name); ?> kategorisindeki tüm romanlar">
                                <?php echo esc_html($genre->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Mobil: Geniş puan widget'ı -->
                    <div class="nt-rating-wide nt-interactive-rating nt-mobile-only-rating" data-novel-id="<?php echo $novel_id; ?>">
                        <div class="nt-rating-wide-row">
                            <div class="nt-stars-container">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="nt-star-item" data-val="<?php echo $i; ?>" style="color:<?php echo $i <= $rating ? '#ffd702' : '#475569'; ?>;">★</span>
                                <?php endfor; ?>
                            </div>
                            <div class="nt-rating-score nt-rating-score--big"><?php echo $rating > 0 ? number_format($rating, 1) : '—'; ?></div>
                        </div>
                        <div class="nt-rating-meta">
                            <span class="nt-rating-count"><?php echo $rating_count; ?> Değerlendirme</span>
                            <span class="nt-rating-sep">·</span>
                            <span class="nt-comment-count"><?php echo $comment_count; ?> Yorum</span>
                        </div>
                    </div>

                    <?php if (!empty($extras_items)) : ?>
                        <div class="nt-section-toggle" aria-label="Hızlı erişim">
                            <a class="nt-section-toggle-btn" href="#bolumler">Bölümler</a>
                            <a class="nt-section-toggle-btn" href="#ciltler-ekstra">Ciltler / Ekstra</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <!-- ============================================
             Novel Sayfası Format (Blogger uyumlu)
             ============================================ -->

        <!-- Novel Özeti (Not + Synopsis) — Hero'nun hemen altında -->
        <?php if (!empty($synopsis_html) || !empty($note)) : ?>
        <article class="nt-card" id="synopsis">
            <div class="nt-card-body">

                <?php if (!empty($note)) : ?>
                    <p class="novel-not"><b style="color:red">Not:</b> <?php echo wp_kses_post($note); ?></p>
                <?php endif; ?>

                <?php
                $has_extras = ($author_name || $illustrator || $editor || $translator || $supporters || !empty($related_items));
                ?>

                <div class="nt-mobile-collapsible">

                <?php if (!empty($synopsis_html)) : ?>
                    <fieldset class="kutucuk">
                        <legend class="baslik"><b>:Novel Özeti:</b></legend>
                        <div class="novel-ozet">
                            <?php echo $synopsis_html; ?>
                        </div>
                    </fieldset>
                <?php endif; ?>

                <!-- Mobilde özet kartının içinde gösterilen ekstra bilgiler (masaüstünde sidebar'da) -->
                <?php if ($has_extras) : ?>
                <div class="nt-synopsis-extras">
                    <div class="nt-synopsis-extras-body">

                        <?php if ($author_name || $illustrator || $editor || $translator || $supporters) : ?>
                            <h3 class="nt-extras-heading">Katkıda Bulunanlar</h3>
                            <ul class="nt-credits-list nt-credits-list--blue">
                                <?php if ($author_name) : ?>
                                    <li><span class="nt-credit-label">Yazar:</span> <?php echo webnovel_link_authors($author_name, 'nauthor'); ?></li>
                                <?php endif; ?>
                                <?php if ($illustrator) : ?>
                                    <li><span class="nt-credit-label">Çizer:</span> <?php echo webnovel_link_authors($illustrator, 'nillustrator'); ?></li>
                                <?php endif; ?>
                                <?php if ($translator) : ?>
                                    <li><span class="nt-credit-label">Çevirmen:</span> <?php echo webnovel_link_authors($translator, 'ntranslator'); ?></li>
                                <?php endif; ?>
                                <?php if ($editor) : ?>
                                    <li><span class="nt-credit-label">Editör:</span> <?php echo webnovel_link_authors($editor, 'neditor'); ?></li>
                                <?php endif; ?>
                                <?php if ($supporters) : ?>
                                    <li><span class="nt-credit-label">Destekçiler:</span> <?php echo webnovel_link_authors($supporters, 'nsupporter'); ?></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($related_items)) : ?>
                            <h3 class="nt-extras-heading">İlgili Noveller</h3>
                            <ul class="nt-related-list">
                                <?php foreach ($related_items as $rel) : ?>
                                    <li><a href="<?php echo esc_url($rel['url']); ?>"><?php echo esc_html($rel['label']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($synopsis_html) || $has_extras) : ?>
                    <button type="button" class="nt-mobile-collapsible-toggle" aria-expanded="false">
                        <span class="nt-mct-text">Özet, Katkıda Bulunlar, İlgili Noveller</span>
                        <svg class="nt-mct-chev" width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7l5 5 5-5"/></svg>
                    </button>
                <?php endif; ?>

                </div><!-- /.nt-mobile-collapsible -->
            </div>
        </article>
        <?php endif; ?>

        <!-- Etiketler kartı -->
        <?php if ($tags && !is_wp_error($tags)) : ?>
        <article class="nt-card nt-etiket-card">
            <div class="nt-card-body">
                <div class="mb-2 cursor-pointer" id="novel-etiket">
                    <details>
                        <summary>Novel Etiketleri için tıklayın</summary>
                        <p>
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo get_term_link($tag); ?>">#<?php echo esc_html($tag->name); ?></a>
                            <?php endforeach; ?>
                        </p>
                    </details>
                </div>
            </div>
        </article>
        <?php endif; ?>

        <!-- ============================================
             Bölüm Listesi (CLWD - kompakt, scrollable)
             theme-layouts.xml satır 226-244, 305, 648-659 baz alındı
             ============================================ -->
        <section class="nt-card" id="bolumler">
            <div class="nt-card-body">
                <div class="clwd-title">
                    <?php the_title(); ?> Novel Oku
                </div>

                <?php if (!empty($grouped_chapters)) : ?>
                    <div class="clwd-search-row">
                        <input type="text" id="scInput" onkeyup="searchList()" placeholder="Bölüm Ara" title="Bölüm İsmi/Numarası Giriniz">
                        <button type="button" id="clwd-sort-toggle" class="clwd-sort-btn" title="Bölüm sıralamasını ters çevir" aria-label="Sıralamayı değiştir">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h13M3 12h9M3 18h5M17 4v16M17 20l4-4M17 20l-4-4"/></svg>
                        </button>
                    </div>

                    <!-- İlk / Toplam / Son hızlı erişim -->
                    <div class="clwd-quicknav">
                        <?php
                        // Helper: format "Bölüm N C{V}" if volume exists, else "Bölüm N"
                        $fmt_chap_label = function($chapter_post) {
                            $n = get_post_meta($chapter_post->ID, '_chapter_number', true);
                            $v = get_post_meta($chapter_post->ID, '_chapter_volume', true);
                            $label = 'Bölüm ' . $n;
                            if ($v !== '' && $v !== null && $v !== '0') {
                                $label .= ' C' . $v;
                            }
                            return $label;
                        };
                        ?>
                        <?php if ($first_chapter) : ?>
                            <a class="clwd-quicknav-btn" href="<?php echo get_permalink($first_chapter->ID); ?>">
                                <span class="epcur">[<?php echo esc_html($fmt_chap_label($first_chapter)); ?>]</span>
                                <span class="clwd-muted">İLK</span>
                            </a>
                        <?php else : ?><span></span><?php endif; ?>

                        <div class="clwd-count" title="Toplam Bölüm"><?php echo intval($chapter_count); ?></div>

                        <?php if ($last_chapter) : ?>
                            <a class="clwd-quicknav-btn" href="<?php echo get_permalink($last_chapter->ID); ?>">
                                <span class="epcur">[<?php echo esc_html($fmt_chap_label($last_chapter)); ?>]</span>
                                <span class="clwd-muted">SON</span>
                            </a>
                        <?php else : ?><span></span><?php endif; ?>
                    </div>

                    <?php $multi_vol = count($grouped_chapters) > 1; ?>

                    <?php if ($multi_vol) : ?>
                        <!-- Cilt sekmeleri (sadece birden fazla cilt varsa) -->
                        <div class="clwd-tabs" id="chapter-tabs">
                            <?php $i = 0; foreach ($grouped_chapters as $group_name => $chunk) :
                                $safe_id = md5($group_name);
                                $meta = isset($grouped_chapters_meta[$group_name]) ? $grouped_chapters_meta[$group_name] : array('asc' => $group_name, 'desc' => $group_name); ?>
                                <button type="button" class="clwd-tab<?php echo $i === 0 ? ' is-active' : ''; ?>" onclick="switchChapterTab('<?php echo esc_js($safe_id); ?>', this);" data-label-asc="<?php echo esc_attr($meta['asc']); ?>" data-label-desc="<?php echo esc_attr($meta['desc']); ?>">
                                    <?php echo esc_html($group_name); ?>
                                </button>
                            <?php $i++; endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="clwd-wrap" id="clwd">
                        <?php $i = 0; foreach ($grouped_chapters as $group_name => $chunk) :
                            $safe_id = md5($group_name); ?>
                            <ul id="chp-chunk-<?php echo esc_attr($safe_id); ?>" class="clwd-list <?php echo (!$multi_vol || $i === 0) ? 'is-visible' : ''; ?>">
                                <?php foreach ($chunk as $chapter) :
                                    $num = get_post_meta($chapter->ID, '_chapter_number', true);
                                    $cmt = (int) get_comments_number($chapter->ID);
                                    $ch_labels = webnovel_get_chapter_labels($chapter->ID);
                                    $rel_time = webnovel_relative_time($chapter->ID);

                                    // Bölüm başlığı: "... Bölüm N - Alt Başlık" formatından alt başlığı çıkar
                                    $raw_title   = (string) $chapter->post_title;
                                    $clean_title = '';
                                    if (preg_match('/B[öo]l[üu]m\s+[\d.]+\s*[\-–—:]\s*(.+)$/iu', $raw_title, $m)) {
                                        $clean_title = trim($m[1]);
                                    }
                                    ?>
                                    <li>
                                        <a class="eph-num" href="<?php echo get_permalink($chapter->ID); ?>">
                                            <span class="vcn" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256"><g fill="currentColor"><path d="M232 56v144a16 16 0 0 1-16 16H40a16 16 0 0 1-16-16V56a16 16 0 0 1 16-16h176a16 16 0 0 1 16 16Z" opacity=".2"/><path d="m205.66 85.66l-96 96a8 8 0 0 1-11.32 0l-40-40a8 8 0 0 1 11.32-11.32L104 164.69l90.34-90.35a8 8 0 0 1 11.32 11.32Z"/></g></svg>
                                            </span>
                                            <span class="chapternum">
                                                <span class="ch-num-pill">Bölüm <?php echo esc_html($num); ?></span>
                                                <?php if ($clean_title !== '') : ?>
                                                    <span class="ch-sub-title"><?php echo esc_html($clean_title); ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <?php if (in_array('revize', $ch_labels, true)) : ?>
                                                <span class="novel-fix">Revize</span>
                                            <?php endif; ?>
                                            <?php if (in_array('son', $ch_labels, true)) : ?>
                                                <span class="novel-end">Son</span>
                                            <?php endif; ?>
                                            <span class="clwd-meta">
                                                <?php if ($cmt > 0) : ?>
                                                    <span class="clwd-cmt" title="<?php echo $cmt; ?> yorum">
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                                        <?php echo $cmt; ?>
                                                    </span>
                                                    <span class="clwd-sep">·</span>
                                                <?php endif; ?>
                                                <span class="chapterdate"><?php echo esc_html($rel_time); ?></span>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php $i++; endforeach; ?>
                    </div>
                <?php else : ?>
                    <div style="padding:32px; text-align:center; background:var(--bg-surface); border-radius:8px;">
                        <p class="nt-text-dim">Bu seriye ait henüz bölüm eklenmemiş.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ============================================
             Ciltler / Ekstra (görsel grid — Bölümler ile sekme)
             ============================================ -->
        <?php if (!empty($extras_items)) : ?>
        <section class="nt-card" id="ciltler-ekstra">
            <div class="nt-card-body">
                <div class="clwd-title"><?php the_title(); ?> — Ciltler / Ekstra</div>
                <div class="nt-extras-grid">
                    <?php foreach ($extras_items as $ex) :
                        $has_url = !empty($ex['url']);
                        $tag = $has_url ? 'a' : 'div';
                        $img = $ex['image'];
                        $title = $ex['title'];
                    ?>
                        <<?php echo $tag; ?> class="nt-extra-card"<?php if ($has_url) : ?> href="<?php echo esc_url($ex['url']); ?>"<?php endif; ?>>
                            <div class="nt-extra-card-thumb">
                                <?php if (!empty($img)) : ?>
                                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                <?php else : ?>
                                    <span>📚</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($title)) : ?>
                                <div class="nt-extra-card-title"><?php echo esc_html($title); ?></div>
                            <?php endif; ?>
                        </<?php echo $tag; ?>>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- YouTube Button -->
        <?php if (!empty($yt_url)) : ?>
            <youtube-button href="<?php echo esc_url($yt_url); ?>" label="<?php echo esc_attr($yt_label); ?>"></youtube-button>
        <?php endif; ?>

<!-- Önerilen Noveller — mobilde yorumların altında (masaüstünde sidebar'da) -->
        <?php if (!empty($recommended_novels)) : ?>
        <section class="nt-card nt-rec-mobile-only">
            <div class="nt-card-body">
                <h3 class="nt-text-lg nt-font-bold nt-mb-2 nt-border-b nt-pb-2 nt-text-accent">Önerilen Noveller</h3>
                <div class="nt-rec-grid">
                    <?php foreach ($recommended_novels as $rn) :
                        $rn_thumb = webnovel_get_cover_url($rn->ID, 'medium');
                        $rn_title = get_the_title($rn->ID);
                    ?>
                        <a class="nt-rec-card" href="<?php echo esc_url(get_permalink($rn->ID)); ?>" title="<?php echo esc_attr($rn_title); ?>">
                            <div class="nt-rec-card-cover">
                                <?php if ($rn_thumb) : ?>
                                    <img src="<?php echo esc_url($rn_thumb); ?>" alt="<?php echo esc_attr($rn_title); ?>" loading="lazy">
                                <?php else : ?>
                                    <span class="nt-rec-card-placeholder">📚</span>
                                <?php endif; ?>
                            </div>
                            <div class="nt-rec-card-title"><?php echo esc_html($rn_title); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Yorumlar Bölümü -->
        <section class="nt-card">
            <div class="nt-card-body">
                <?php comments_template(); ?>
            </div>
        </section>

        <!-- SEO Hidden -->
        <div style="display:none">
            <b><?php the_title(); ?> Türkçe Novel Oku, </b>
            <b><?php the_title(); ?> Novel Oku, </b>
            <b><?php the_title(); ?> Oku</b>
            <?php if ($alt_title) : ?>
                <b><?php echo esc_html($alt_title); ?> Türkçe Novel Oku, </b>
                <b><?php echo esc_html($alt_title); ?> Novel Oku</b>
            <?php endif; ?>
        </div>
    </main>

    <aside class="nt-sidebar">
        <?php if (is_active_sidebar('sidebar-1')) : ?>
            <?php dynamic_sidebar('sidebar-1'); ?>
        <?php else : ?>
            <!-- Derecelendirme + Kütüphaneye Ekle (sidebar üst) -->
            <?php
            $rating       = floatval(get_post_meta($novel_id, '_novel_rating_avg', true) ?: '0');
            $rating_count = (int) (get_post_meta($novel_id, '_novel_rating_count', true) ?: 0);
            $comment_count = (int) get_comments_number($novel_id);
            ?>
            <div class="nt-card nt-rating-follow-card">
                <div class="nt-card-body">
                    <div class="nt-interactive-rating" data-novel-id="<?php echo $novel_id; ?>">
                        <div class="nt-rating-score"><?php echo $rating > 0 ? number_format($rating, 1) : '—'; ?></div>
                        <div class="nt-stars-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="nt-star-item" data-val="<?php echo $i; ?>" style="color:<?php echo $i <= $rating ? '#ffd702' : '#475569'; ?>;">★</span>
                            <?php endfor; ?>
                        </div>
                        <div class="nt-rating-meta">
                            <span class="nt-rating-count"><?php echo $rating_count; ?> Değerlendirme</span>
                            <span class="nt-rating-sep">·</span>
                            <span class="nt-comment-count"><?php echo $comment_count; ?> Yorum</span>
                        </div>
                    </div>
                    <button class="nt-follow-btn" id="btn-follow-novel"
                        data-id="<?php echo $novel_id; ?>"
                        data-title="<?php echo esc_attr(get_the_title($novel_id)); ?>"
                        data-thumb="<?php echo esc_attr(get_the_post_thumbnail_url($novel_id, 'medium')); ?>"
                        data-last-ch="<?php echo $last_chapter ? $last_chapter->ID : '0'; ?>">
                        <svg class="heart-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span>Kütüphaneye Ekle</span>
                    </button>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const containers = document.querySelectorAll('.nt-interactive-rating');
                    if (!containers.length) return;
                    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    const nonce = '<?php echo wp_create_nonce("webnovel_reader_nonce"); ?>';

                    // Sync helpers — update ALL widgets when one is rated
                    const syncAll = (avg, count) => {
                        document.querySelectorAll('.nt-rating-score').forEach(el => el.textContent = avg.toFixed(1));
                        document.querySelectorAll('.nt-rating-count').forEach(el => el.textContent = count + ' Değerlendirme');
                    };

                    containers.forEach(container => {
                        const stars = container.querySelectorAll('.nt-star-item');
                        const novelId = container.dataset.novelId;
                        const ratedVal = localStorage.getItem('nt_rating_' + novelId);
                        let currentRating = ratedVal ? parseInt(ratedVal) : <?php echo $rating; ?>;
                        let userRating = ratedVal ? parseInt(ratedVal) : 0;
                        const updateStars = (val) => {
                            stars.forEach(st => st.style.color = parseInt(st.dataset.val) <= val ? '#ffd702' : '#475569');
                        };
                        updateStars(currentRating);
                        stars.forEach(s => {
                            s.addEventListener('mouseenter', () => updateStars(parseInt(s.dataset.val)));
                            s.addEventListener('mouseleave', () => updateStars(userRating || currentRating));
                            s.addEventListener('click', () => {
                                const val = parseInt(s.dataset.val);
                                userRating = val;
                                updateStars(val);
                                localStorage.setItem('nt_rating_' + novelId, val.toString());
                                container.style.opacity = '0.7';
                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'action=webnovel_rate_novel&novel_id='+novelId+'&rating='+val+'&nonce='+nonce
                                }).then(r => r.json()).then(res => {
                                    if (res.success) {
                                        syncAll(res.data.avg, res.data.count);
                                        container.style.opacity = '1';
                                    }
                                });
                            });
                        });
                    });
                });
                </script>
            </div>

            <!-- Katkıda Bulunanlar (Yazar / Çizer / Editör / Çevirmen / Destekçiler) -->
            <?php if ($author_name || $illustrator || $editor || $translator || $supporters) : ?>
                <div class="nt-card">
                    <div class="nt-card-body">
                        <h3 class="nt-text-lg nt-font-bold nt-mb-2 nt-border-b nt-pb-2 nt-text-accent">Katkıda Bulunanlar</h3>
                        <ul class="nt-credits-list nt-credits-list--blue">
                            <?php if ($author_name) : ?>
                                <li><span class="nt-credit-label">Yazar:</span> <?php echo webnovel_link_authors($author_name, 'nauthor'); ?></li>
                            <?php endif; ?>
                            <?php if ($illustrator) : ?>
                                <li><span class="nt-credit-label">Çizer:</span> <?php echo webnovel_link_authors($illustrator, 'nillustrator'); ?></li>
                            <?php endif; ?>
                            <?php if ($translator) : ?>
                                <li><span class="nt-credit-label">Çevirmen:</span> <?php echo webnovel_link_authors($translator, 'ntranslator'); ?></li>
                            <?php endif; ?>
                            <?php if ($editor) : ?>
                                <li><span class="nt-credit-label">Editör:</span> <?php echo webnovel_link_authors($editor, 'neditor'); ?></li>
                            <?php endif; ?>
                            <?php if ($supporters) : ?>
                                <li><span class="nt-credit-label">Destekçiler:</span> <?php echo webnovel_link_authors($supporters, 'nsupporter'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- İlgili Noveller -->
            <?php if (!empty($related_items)) : ?>
                <div class="nt-card">
                    <div class="nt-card-body">
                        <h3 class="nt-text-lg nt-font-bold nt-mb-2 nt-border-b nt-pb-2 nt-text-accent">İlgili Noveller</h3>
                        <ul class="nt-related-list">
                            <?php foreach ($related_items as $rel) : ?>
                                <li>
                                    <a href="<?php echo esc_url($rel['url']); ?>"><?php echo esc_html($rel['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Önerilen Noveller — sadece masaüstü sidebar'ı (mobilde hidden, mobil için yorumların altında ayrı blok var) -->
            <?php if (!empty($recommended_novels)) : ?>
                <div class="nt-card nt-rec-sidebar">
                    <div class="nt-card-body">
                        <h3 class="nt-text-lg nt-font-bold nt-mb-2 nt-border-b nt-pb-2 nt-text-accent">Önerilen Noveller</h3>
                        <div class="nt-rec-grid">
                            <?php foreach ($recommended_novels as $rn) :
                                $rn_thumb = webnovel_get_cover_url($rn->ID, 'medium');
                                $rn_title = get_the_title($rn->ID);
                            ?>
                                <a class="nt-rec-card" href="<?php echo esc_url(get_permalink($rn->ID)); ?>" title="<?php echo esc_attr($rn_title); ?>">
                                    <div class="nt-rec-card-cover">
                                        <?php if ($rn_thumb) : ?>
                                            <img src="<?php echo esc_url($rn_thumb); ?>" alt="<?php echo esc_attr($rn_title); ?>" loading="lazy">
                                        <?php else : ?>
                                            <span class="nt-rec-card-placeholder">📚</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="nt-rec-card-title"><?php echo esc_html($rn_title); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </aside>
</div>

<script>
// Bölüm sıralama toggle (yeni→eski / eski→yeni)
(function () {
    const NOVEL_ID = '<?php echo (int) $novel_id; ?>';
    const KEY = 'nt_clwd_reversed_' + NOVEL_ID;
    const btn = document.getElementById('clwd-sort-toggle');
    if (!btn) return;

    function reverseLists() {
        document.querySelectorAll('#clwd .clwd-list').forEach(function (ul) {
            const items = Array.from(ul.children);
            items.reverse().forEach(function (li) { ul.appendChild(li); });
        });
    }

    function updateTabLabels(isReversed) {
        document.querySelectorAll('#chapter-tabs .clwd-tab').forEach(function (tab) {
            const label = isReversed ? tab.dataset.labelAsc : tab.dataset.labelDesc;
            if (label) tab.textContent = label;
        });
    }

    // Sayfa yüklenince kaydedilmiş tercihi uygula
    if (localStorage.getItem(KEY) === '1') {
        reverseLists();
        updateTabLabels(true);
        btn.classList.add('is-reversed');
    }

    btn.addEventListener('click', function () {
        reverseLists();
        const reversed = btn.classList.toggle('is-reversed');
        localStorage.setItem(KEY, reversed ? '1' : '0');
        updateTabLabels(reversed);
    });
})();

// Mobil özet/extras dropdown — Novel Özeti içeriğinin ilk satırından sonrası gizli
(function () {
    const wrap = document.querySelector('.nt-mobile-collapsible');
    if (!wrap) return;
    const btn = wrap.querySelector('.nt-mobile-collapsible-toggle');
    if (!btn) return;
    const txt = btn.querySelector('.nt-mct-text');
    btn.addEventListener('click', function () {
        const open = wrap.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (txt) txt.textContent = open ? 'Daha Az Göster' : 'Özet, Katkıda Bulunlar, İlgili Noveller';
    });
})();

// Bölümler / Ciltler-Ekstra hızlı kaydırma butonları
(function () {
    const buttons = document.querySelectorAll('.nt-section-toggle-btn');
    if (!buttons.length) return;
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const href = btn.getAttribute('href') || '';
            const target = href.charAt(0) === '#' ? document.getElementById(href.slice(1)) : null;
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();

function switchChapterTab(index, btn) {
    document.querySelectorAll('#clwd .clwd-list').forEach(el => el.classList.remove('is-visible'));
    const target = document.getElementById('chp-chunk-' + index);
    if (target) target.classList.add('is-visible');

    document.querySelectorAll('#chapter-tabs .clwd-tab').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');

    // Re-apply search filter on tab switch
    if (typeof searchList === 'function') searchList();
}

</script>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
