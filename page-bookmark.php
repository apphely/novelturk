<?php
/**
 * Template Name: Bookmark
 */
get_header(); ?>

<section class="hero-section" style="padding: 3rem 0; background: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
            <div>
                <h1 class="hero-title">Bölüm <span>Bookmark</span></h1>
                <p class="hero-subtitle">Favori serileriniz ve son güncellemeler</p>
            </div>
            <div style="display:flex; gap:12px;">
                <button id="export-follows" class="btn-secondary" style="padding:10px 20px; font-size:0.9rem;">📥 Dışa Aktar</button>
                <button id="import-follows-btn" class="btn-secondary" style="padding:10px 20px; font-size:0.9rem;">📤 İçe Aktar</button>
                <button id="clear-all-follows" class="btn-secondary" style="padding:10px 20px; font-size:0.9rem; background: rgba(245, 44, 111, 0.1); color: var(--accent-vibrant); border: 1px solid var(--accent-vibrant);">🗑️ Tümünü Temizle</button>
                <input type="file" id="import-follows-file" style="display:none;" accept=".json">
            </div>
        </div>
    </div>
</section>

<section class="container" style="padding: 3rem 0;">
    <div id="followed-series-grid" class="novels-grid">
        <!-- JS will populate this -->
        <div class="loading-msg" style="grid-column: 1/-1; text-align:center; padding: 50px;">
            <p>Favori serileriniz yükleniyor...</p>
        </div>
    </div>
    
    <div id="no-follows-msg" style="display:none; text-align:center; padding: 100px 20px;">
        <div style="font-size: 4rem; margin-bottom: 20px;">📚</div>
        <h2 style="margin-bottom: 10px;">Henüz takip ettiğiniz seri yok</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Seri sayfalarındaki "Takip Et" butonunu kullanarak buraya ekleyebilirsiniz.</p>
        <a href="<?php echo get_post_type_archive_link('novel'); ?>" class="btn-primary">Serileri Keşfet</a>
    </div>
</section>

<div id="toast-container" class="toast-container"></div>

<?php get_footer(); ?>
