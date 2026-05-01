<?php
/**
 * Template Name: Kategoriler
 * Description: Displays all novel genres and tags
 */
get_header();

$genres = get_terms(array(
    'taxonomy'   => 'novel_genre',
    'hide_empty' => false,
    'orderby'    => 'count',
    'order'      => 'DESC',
));

$tags = get_terms(array(
    'taxonomy'   => 'novel_tag',
    'hide_empty' => false,
    'orderby'    => 'count',
    'order'      => 'DESC',
));

// Debug helper for admin (can be removed later)
if (is_user_logged_in() && current_user_can('manage_options')) {
    // echo 'Genres count: ' . (is_array($genres) ? count($genres) : 0);
}
?>

<section class="hero-section" style="padding: 3rem 0; background: var(--bg-surface); border-bottom: 1px solid var(--border);">
    <div class="nt-container">
        <h1 class="hero-title" style="font-size: 2.5rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 12px;">📂 <span>Kategoriler</span></h1>
        <p class="hero-subtitle" style="font-size: 1.1rem; color: var(--text-dim); margin-top: 8px;">Türlere ve etiketlere göre roman keşfedin</p>
    </div>
</section>

<div class="nt-container" style="padding-top: 40px; padding-bottom: 60px; display: block;">
    <!-- Tags Cloud First -->
    <?php if ($tags && !is_wp_error($tags) && count($tags) > 0) : ?>
    <div style="margin-bottom: 48px; width: 100%; display: block;">
        <div class="section-header" style="margin-bottom: 24px; border-bottom: 2px solid var(--border); padding-bottom: 12px;">
            <h2 class="section-title" style="font-size: 20px; font-weight: 700; color: var(--accent); display: flex; align-items: center; gap: 10px;"><span class="icon">🏷️</span> Etiketler</h2>
        </div>
        <div class="tags-cloud" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach ($tags as $tag) : ?>
            <a href="<?php echo get_term_link($tag); ?>" class="tag-cloud-item" id="tag-<?php echo $tag->term_id; ?>" style="text-decoration: none; padding: 6px 14px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; color: var(--text-main); font-size: 13px; font-weight: 600; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;">
                <?php echo esc_html($tag->name); ?>
                <span class="tag-count" style="font-size: 10px; background: var(--accent); color: #fff; padding: 1px 5px; border-radius: 4px;"><?php echo $tag->count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Genres Grid Second -->
    <?php if ($genres && !is_wp_error($genres) && count($genres) > 0) : ?>
    <div style="width: 100%; display: block;">
        <div class="section-header" style="margin-bottom: 24px; border-bottom: 2px solid var(--border); padding-bottom: 12px;">
            <h2 class="section-title" style="font-size: 20px; font-weight: 700; color: var(--accent); display: flex; align-items: center; gap: 10px;"><span class="icon">📚</span> Türler</h2>
        </div>
        <div class="categories-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            <?php foreach ($genres as $genre) : 
                $sample_novels = get_posts(array(
                    'post_type'      => 'novel',
                    'posts_per_page' => 1,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'novel_genre',
                            'field'    => 'term_id',
                            'terms'    => $genre->term_id,
                        ),
                    ),
                ));
                $bg_url = '';
                if (!empty($sample_novels) && has_post_thumbnail($sample_novels[0]->ID)) {
                    $bg_url = get_the_post_thumbnail_url($sample_novels[0]->ID, 'medium');
                }
            ?>
            <a href="<?php echo get_term_link($genre); ?>" class="nt-card category-card" id="genre-<?php echo $genre->term_id; ?>" style="text-decoration: none; position: relative; overflow: hidden; height: 140px; border-radius: 12px; display: flex; align-items: flex-end; transition: transform 0.3s ease;">
                <?php if ($bg_url) : ?>
                <div class="category-card-bg" style="position: absolute; inset: 0; background-image: url('<?php echo esc_url($bg_url); ?>'); background-size: cover; background-position: center; filter: brightness(0.6); transition: transform 0.5s ease;"></div>
                <?php else: ?>
                <div style="position: absolute; inset: 0; background: var(--bg-card);"></div>
                <?php endif; ?>
                
                <div class="category-card-content" style="position: relative; z-index: 2; padding: 16px; width: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                    <h3 class="category-card-title" style="color: #fff; margin: 0; font-size: 16px; font-weight: 700;"><?php echo esc_html($genre->name); ?></h3>
                    <span class="category-card-count" style="color: rgba(255,255,255,0.8); font-size: 11px; font-weight: 600; text-transform: uppercase;"><?php echo $genre->count; ?> Roman</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else : ?>
    <div style="text-align:center;padding:2rem 0; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border);">
        <p style="color:var(--text-dim);">Henüz tür eklenmemiş.</p>
    </div>
    <?php endif; ?>
</div>

<style>
.category-card:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.3); }
.category-card:hover .category-card-bg { transform: scale(1.1); }
.tag-cloud-item:hover { background: var(--accent) !important; color: #fff !important; border-color: var(--accent) !important; transform: translateY(-2px); }

@media (max-width: 768px) {
    .categories-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 480px) {
    .categories-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php get_footer(); ?>
