<?php
/**
 * Template Name: Bağış Sayfası
 * Description: Donation page with customizable content
 */
get_header();

$donate_title = get_option('webnovel_donate_title', 'Bizi Destekleyin');
$donate_desc = get_option('webnovel_donate_description', '');
$donate_links_raw = get_option('webnovel_donate_links', '');

// Parse donation links
$donate_links = array();
if (!empty($donate_links_raw)) {
    $lines = explode("\n", trim($donate_links_raw));
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 3) {
            $donate_links[] = array(
                'icon'  => trim($parts[0]),
                'title' => trim($parts[1]),
                'url'   => trim($parts[2]),
            );
        }
    }
}
?>

<section class="hero-section" style="padding: 2rem 0;">
    <div class="container">
        <h1 class="hero-title">💝 <span><?php echo esc_html($donate_title); ?></span></h1>
    </div>
</section>

<section class="container" style="padding-bottom: 5rem;">
    <div class="donate-page-v2">
        <div class="donate-header-premium">
            <div class="donate-icon-main">💎</div>
            <p class="donate-intro">Bize destek vererek serilerin devam etmesini ve daha hızlı çevrilmesini sağlayabilirsiniz. Desteğiniz bizim için çok değerli!</p>
        </div>

        <div class="donate-grid-premium">
            <!-- WordPress content if any -->
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php if (get_the_content()) : ?>
                <div class="donate-card-premium content-card">
                    <div class="card-inner"><?php the_content(); ?></div>
                </div>
                <?php endif; ?>
            <?php endwhile; endif; ?>
            
            <!-- Dynamic Links -->
            <?php if (!empty($donate_links)) : foreach ($donate_links as $link) : ?>
                <a href="<?php echo esc_url($link['url']); ?>" class="donate-method-card" target="_blank" rel="noopener noreferrer">
                    <div class="method-icon"><?php echo $link['icon']; ?></div>
                    <div class="method-info">
                        <span class="method-name"><?php echo esc_html($link['title']); ?></span>
                        <span class="method-action">Destek Ol →</span>
                    </div>
                </a>
            <?php endforeach; endif; ?>
        </div>

        <?php if (empty($donate_desc) && empty($donate_links) && !get_the_content()) : ?>
            <div class="donate-empty-state">
                <p>Bağış bilgileri henüz eklenmemiş. Lütfen ayarlardan ekleyin.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
