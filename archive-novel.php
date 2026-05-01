<?php get_header(); ?>

<div style="max-width: 1200px; margin: 0 auto; padding: 24px 16px;">
    
    <?php
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    
    // Get filtering parameters
    $status_filter      = isset($_GET['nstatus'])      ? sanitize_text_field($_GET['nstatus'])      : '';
    $type_filter        = isset($_GET['novel_type'])   ? sanitize_text_field($_GET['novel_type'])   : '';
    $origin_filter      = isset($_GET['novel_origin']) ? sanitize_text_field($_GET['novel_origin']) : '';
    $genre_filter       = isset($_GET['novel_genre'])  ? sanitize_text_field($_GET['novel_genre'])  : '';
    $author_filter      = isset($_GET['nauthor'])      ? sanitize_text_field(wp_unslash($_GET['nauthor']))      : '';
    $illustrator_filter = isset($_GET['nillustrator']) ? sanitize_text_field(wp_unslash($_GET['nillustrator'])) : '';
    $editor_filter      = isset($_GET['neditor'])      ? sanitize_text_field(wp_unslash($_GET['neditor']))      : '';
    $translator_filter  = isset($_GET['ntranslator'])  ? sanitize_text_field(wp_unslash($_GET['ntranslator']))  : '';
    $supporter_filter   = isset($_GET['nsupporter'])   ? sanitize_text_field(wp_unslash($_GET['nsupporter']))   : '';
    
    // If we are on a taxonomy page, override the corresponding filter
    $current_genre_id = is_tax('novel_genre') ? get_queried_object_id() : 0;
    if ($current_genre_id) {
        $genre_filter = get_term($current_genre_id)->slug;
    }

    $args = array(
        'post_type'      => 'novel',
        'posts_per_page' => 24,
        'paged'          => $paged,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );

    // Meta Query (Status / Author / Illustrator)
    if ($status_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_status',
            'value'   => $status_filter,
            'compare' => '=',
        );
    }
    if ($author_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_author',
            'value'   => $author_filter,
            'compare' => 'LIKE',
        );
    }
    if ($illustrator_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_illustrator',
            'value'   => $illustrator_filter,
            'compare' => 'LIKE',
        );
    }
    if ($editor_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_editor',
            'value'   => $editor_filter,
            'compare' => 'LIKE',
        );
    }
    if ($translator_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_translator',
            'value'   => $translator_filter,
            'compare' => 'LIKE',
        );
    }
    if ($supporter_filter) {
        $args['meta_query'][] = array(
            'key'     => '_novel_supporters',
            'value'   => $supporter_filter,
            'compare' => 'LIKE',
        );
    }

    // Tax Query (Type, Origin, Genre)
    if ($type_filter || $origin_filter || $genre_filter) {
        $args['tax_query'] = array('relation' => 'AND');
        
        if ($type_filter) {
            $args['tax_query'][] = array(
                'taxonomy' => 'novel_type',
                'field'    => 'slug',
                'terms'    => $type_filter,
            );
        }
        if ($origin_filter) {
            $args['tax_query'][] = array(
                'taxonomy' => 'novel_origin',
                'field'    => 'slug',
                'terms'    => $origin_filter,
            );
        }
        if ($genre_filter) {
            $args['tax_query'][] = array(
                'taxonomy' => 'novel_genre',
                'field'    => 'slug',
                'terms'    => $genre_filter,
            );
        }
    }

    $novels_query = new WP_Query($args);
    ?>

    <?php if ($author_filter || $illustrator_filter || $editor_filter || $translator_filter || $supporter_filter) : ?>
        <div style="text-align:center; margin-bottom:16px; padding:12px 16px; background:var(--bg-card); border:1px solid var(--border); border-radius:8px;">
            <span style="color:var(--text-dim); font-size:14px;">
                <?php if ($author_filter) : ?>
                    <strong style="color:var(--text-main);"><?php echo esc_html($author_filter); ?></strong> tarafından yazılan romanlar
                <?php elseif ($illustrator_filter) : ?>
                    <strong style="color:var(--text-main);"><?php echo esc_html($illustrator_filter); ?></strong> tarafından çizilen romanlar
                <?php elseif ($editor_filter) : ?>
                    <strong style="color:var(--text-main);"><?php echo esc_html($editor_filter); ?></strong> tarafından düzenlenen romanlar
                <?php elseif ($translator_filter) : ?>
                    <strong style="color:var(--text-main);"><?php echo esc_html($translator_filter); ?></strong> tarafından çevrilen romanlar
                <?php elseif ($supporter_filter) : ?>
                    <strong style="color:var(--text-main);"><?php echo esc_html($supporter_filter); ?></strong> tarafından desteklenen romanlar
                <?php endif; ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('novel')); ?>" style="margin-left:12px; color:var(--accent); text-decoration:none; font-weight:600;">✕ Filtreyi temizle</a>
            </span>
        </div>
    <?php endif; ?>

    <!-- Status Filters (Screenshot 3 Style) -->
    <div style="display:flex; justify-content:center; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
        <?php
        $status_options = array_merge(array('' => 'Tümü'), webnovel_get_novel_statuses());
        $base_url = is_tax('novel_genre') ? get_term_link($current_genre_id) : get_post_type_archive_link('novel');
        
        foreach($status_options as $key => $label) {
            $is_active = ($status_filter === $key);
            $bg = $is_active ? 'var(--accent)' : 'var(--bg-card)'; 
            $color = $is_active ? '#fff' : 'var(--text-dim)';
            $url = $key ? add_query_arg('nstatus', $key, $base_url) : remove_query_arg('nstatus', $base_url);
            echo '<a href="'.esc_url($url).'" style="background:'.$bg.'; color:'.$color.'; border-radius:24px; padding:6px 16px; font-size:13px; font-weight:700; text-decoration:none; transition:all 0.2s; border:1px solid '.($is_active ? 'var(--accent)' : 'var(--border)').';">'.esc_html($label).'</a>';
        }
        ?>
    </div>

    <!-- Genre Filters -->
    <div style="display:flex; justify-content:center; gap:8px; flex-wrap:wrap; margin-bottom:32px;">
        <?php
        $genres = get_terms(array('taxonomy' => 'novel_genre', 'hide_empty' => true));
        if(!is_wp_error($genres)) {
            foreach($genres as $g) {
                $is_active = ($current_genre_id === $g->term_id);
                $bg = $is_active ? 'var(--accent)' : 'var(--bg-card)';
                $color = $is_active ? '#fff' : 'var(--text-dim)';
                $url = get_term_link($g);
                if($status_filter) {
                    $url = add_query_arg('nstatus', $status_filter, $url);
                }
                echo '<a href="'.esc_url($url).'" style="background:'.$bg.'; color:'.$color.'; border-radius:24px; padding:4px 14px; font-size:12px; font-weight:600; text-decoration:none; transition:all 0.2s; border:1px solid '.($is_active ? 'var(--accent)' : 'var(--border)').';">'.esc_html($g->name).'</a>';
            }
        }
        ?>
    </div>

    <!-- Novels Grid -->
    <?php if ($novels_query->have_posts()) : ?>
        <div class="nt-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:16px;">
            <?php 
            while ($novels_query->have_posts()) : $novels_query->the_post();
                get_template_part('template-parts/content', 'novel-card-novelturk');
            endwhile; 
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($novels_query->max_num_pages > 1) : ?>
        <div class="pagination" style="display:flex; justify-content:center; gap:8px; margin-top:32px; margin-bottom:24px;">
            <?php
            $pages = paginate_links(array(
                'total'     => $novels_query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '← Önceki',
                'next_text' => 'Sonraki →',
                'type'      => 'array'
            ));
            if(is_array($pages)) {
                foreach($pages as $page_link) {
                    // Inject styling to pagination links
                    $page_link = str_replace('page-numbers', 'page-numbers nt-btn-pagination', $page_link);
                    echo $page_link;
                }
            }
            ?>
        </div>
        <style>
            .nt-btn-pagination { display:inline-block; padding:8px 16px; background:var(--bg-card); color:var(--text-dim); border-radius:8px; text-decoration:none; font-weight:600; transition:background 0.2s; border:1px solid var(--border);}
            .nt-btn-pagination.current { background:var(--accent); color:#fff; border-color:var(--accent);}
            .nt-btn-pagination:hover:not(.current) { background:var(--bg-card-hover); color:var(--text-main);}
        </style>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    <?php else: ?>
        <div style="text-align:center; padding:4rem 0;">
            <p style="color:var(--text-dim); font-size:1.1rem;">Kriterlere uygun roman bulunamadı.</p>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
