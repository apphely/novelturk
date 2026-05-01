<?php
/**
 * Template for Author Pages
 * URL: /yazar/{author-slug}/
 */
get_header();

$author_slug = get_query_var('webnovel_author');
$author_name = urldecode($author_slug);
// Convert slug back to name (replace hyphens with spaces)
$author_display = str_replace('-', ' ', $author_name);

// Find all novels by this author
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = array(
    'post_type'      => 'novel',
    'posts_per_page' => 24,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'     => '_novel_author',
            'value'   => $author_display,
            'compare' => 'LIKE',
        ),
    ),
    'orderby' => 'modified',
    'order'   => 'DESC',
);

$author_query = new WP_Query($args);

// Try to get exact author name from first result
if ($author_query->have_posts()) {
    $first_novel = $author_query->posts[0];
    $exact_name = get_post_meta($first_novel->ID, '_novel_author', true);
    if ($exact_name) $author_display = $exact_name;
}
?>

<section class="hero-section" style="padding: 2rem 0;">
    <div class="container">
        <h1 class="hero-title">✍ Yazar: <span><?php echo esc_html($author_display); ?></span></h1>
        <p class="hero-subtitle">Bu yazarın tüm serileri</p>
    </div>
</section>

<section class="container">
    <?php if ($author_query->have_posts()) : ?>
    <div class="novels-grid">
        <?php while ($author_query->have_posts()) : $author_query->the_post();
            $novel_id = get_the_ID();
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
    
    <!-- Pagination -->
    <?php if ($author_query->max_num_pages > 1) : ?>
    <div class="pagination">
        <?php
        echo paginate_links(array(
            'total'     => $author_query->max_num_pages,
            'current'   => $paged,
            'prev_text' => '←',
            'next_text' => '→',
        ));
        ?>
    </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
    
    <?php else : ?>
    <div style="text-align:center;padding:4rem 0;">
        <p style="color:var(--text-muted);font-size:1.1rem;">Bu yazara ait roman bulunamadı.</p>
    </div>
    <?php endif; ?>
</section>

<?php get_footer(); ?>
