<?php
/**
 * Template for taxonomy archives (novel_genre, novel_tag)
 */
get_header();

$term = get_queried_object();
$taxonomy = get_queried_object()->taxonomy ?? '';
$term_name = $term->name ?? '';
?>

<section class="hero-section" style="padding: 2rem 0;">
    <div class="container">
        <h1 class="hero-title">
            <?php if ($taxonomy === 'novel_genre') : ?>
                Kategori: <span><?php echo esc_html($term_name); ?></span>
            <?php elseif ($taxonomy === 'novel_origin') : ?>
                Ülke: <span><?php echo esc_html($term_name); ?></span>
            <?php elseif ($taxonomy === 'novel_type') : ?>
                Tür: <span><?php echo esc_html($term_name); ?></span>
            <?php elseif ($taxonomy === 'novel_tag') : ?>
                Etiket: <span><?php echo esc_html($term_name); ?></span>
            <?php else : ?>
                <span>Romanlar: <?php echo esc_html($term_name); ?></span>
            <?php endif; ?>
        </h1>
        <?php if ($term->description) : ?>
        <p class="hero-subtitle"><?php echo esc_html($term->description); ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="container">
    <?php if (have_posts()) : ?>
    <div class="novels-grid">
        <?php while (have_posts()) : the_post();
            $novel_id = get_the_ID();
            $status = get_post_meta($novel_id, '_novel_status', true) ?: 'ongoing';
            $chapter_count = webnovel_get_chapter_count($novel_id);
            $genres = get_the_terms($novel_id, 'novel_genre');
        ?>
        <a href="<?php the_permalink(); ?>" class="novel-card" id="novel-card-<?php echo $novel_id; ?>">
            <div class="novel-card-cover">
                <?php 
                $tax_cover = webnovel_get_cover_url($novel_id, 'novel-cover');
                if ($tax_cover) : ?>
                    <img src="<?php echo esc_url($tax_cover); ?>" alt="<?php the_title_attribute(); ?>">
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
    
    <!-- Pagination -->
    <div class="pagination">
        <?php
        echo paginate_links(array(
            'prev_text' => '←',
            'next_text' => '→',
        ));
        ?>
    </div>
    
    <?php else : ?>
    <div style="text-align:center;padding:4rem 0;">
        <p style="color:var(--text-muted);font-size:1.1rem;">Bu kategoride roman bulunamadı.</p>
    </div>
    <?php endif; ?>
</section>

<?php get_footer(); ?>
