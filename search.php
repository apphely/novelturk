<?php get_header(); ?>

<section class="hero-section" style="padding: 2rem 0;">
    <div class="container">
        <h1 class="hero-title">Arama: <span>"<?php echo esc_html(get_search_query()); ?>"</span></h1>
        <p class="hero-subtitle">
            <?php
            global $wp_query;
            printf('%d sonuç bulundu', $wp_query->found_posts);
            ?>
        </p>
    </div>
</section>

<section class="container">
    <?php if (have_posts()) : ?>
    <div class="novels-grid">
        <?php while (have_posts()) : the_post();
            $novel_id = get_the_ID();
            
            // Skip if not a novel
            if (get_post_type() !== 'novel') continue;
            
            $status = get_post_meta($novel_id, '_novel_status', true) ?: 'ongoing';
            $chapter_count = webnovel_get_chapter_count($novel_id);
            $genres = get_the_terms($novel_id, 'novel_genre');
        ?>
        <a href="<?php the_permalink(); ?>" class="novel-card" id="novel-card-<?php echo $novel_id; ?>">
            <div class="novel-card-cover">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('novel-cover'); ?>
                <?php else : ?>
                    <div class="no-cover">📚</div>
                <?php endif; ?>
                <div class="cover-overlay"></div>
                <?php $status_label_local = webnovel_get_status_label($status); ?>
                <span class="novel-card-status nt-card-badge nt-card-badge-status" data-status="<?php echo esc_attr($status_label_local); ?>">
                    <?php echo esc_html($status_label_local); ?>
                </span>
                <?php if ($chapter_count > 0) : ?>
                <span class="novel-card-chapter-count"><?php echo $chapter_count; ?> Bölüm</span>
                <?php endif; ?>
            </div>
            <div class="novel-card-body">
                <h3 class="novel-card-title"><?php the_title(); ?></h3>
                <?php if ($genres && !is_wp_error($genres)) : ?>
                <div class="novel-card-tags">
                    <?php foreach (array_slice($genres, 0, 3) as $genre) : ?>
                        <span class="tag"><?php echo esc_html($genre->name); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    
    <div class="pagination">
        <?php echo paginate_links(array('prev_text' => '←', 'next_text' => '→')); ?>
    </div>
    
    <?php else : ?>
    <div style="text-align:center;padding:4rem 0;">
        <p style="color:var(--text-muted);font-size:1.2rem;">😕</p>
        <p style="color:var(--text-muted);font-size:1.1rem;">Aramanızla eşleşen roman bulunamadı.</p>
        <a href="<?php echo get_post_type_archive_link('novel'); ?>" class="btn btn-primary" style="margin-top:1rem;">Tüm Romanları Gör</a>
    </div>
    <?php endif; ?>
</section>

<?php get_footer(); ?>
