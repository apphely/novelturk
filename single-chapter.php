<?php
/**
 * Template for reading a single chapter
 */

$chapter_id = get_the_ID();
$novel_id = get_post_meta($chapter_id, '_chapter_novel_id', true);
$chapter_number = get_post_meta($chapter_id, '_chapter_number', true);
$volume_number = get_post_meta($chapter_id, '_chapter_volume', true);
$novel = $novel_id ? get_post($novel_id) : null;

$prev_chapter = $novel_id ? webnovel_get_adjacent_chapter($novel_id, $chapter_number, 'prev') : null;
$next_chapter = $novel_id ? webnovel_get_adjacent_chapter($novel_id, $chapter_number, 'next') : null;
$all_chapters = $novel_id ? webnovel_get_chapters($novel_id, 'ASC') : array();

$chapter_yt_url   = get_post_meta($chapter_id, '_chapter_youtube_url', true);
$chapter_yt_label = get_post_meta($chapter_id, '_chapter_youtube_label', true) ?: "YouTube'da Dinle";

get_header();
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<!-- Reader Nav Styles -->
<style>
.slideUp, .slideDown {
    max-width: 100%;
    width: 100%;
    position: sticky;
    top: 0;
    z-index: 9999;
    transition: transform 0.3s ease;
}
.slideUp {
    transform: translateY(-100%);
}
.slideDown {
    transform: translateY(0);
}
.navi-bar {
    display: flex;
    align-items: center;
    background-color: var(--bg-surface);
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    padding: 8px 12px;
    gap: 12px;
    font-size: 14px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.navi-btn {
    color: var(--text-main);
    background-color: var(--bg-card);
    border: 1px solid var(--border);
    font-weight: 500;
    padding: 4px 16px;
    text-align: center;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
}
.navi-btn:hover {
    color: var(--accent);
    border-color: var(--accent);
}
.navi-btn-primary {
    color: #fff;
    background-color: var(--accent);
    font-weight: 500;
    padding: 4px 16px;
    text-align: center;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid transparent;
}
.navi-btn-primary:hover {
    opacity: 0.9;
}
.flex-gap-3 { display: flex; gap: 12px; justify-content: center; align-items: center; }
.ms-auto { margin-left: auto; justify-content: flex-end; display: flex; gap: 12px; }
@media (max-width: 768px) {
    .hidden-md { display: none; }
    .navi-btn { padding: 4px 10px; }
    .navi-bar { padding: 8px 6px; gap: 6px; }
}
</style>

<div id="reader-container" style="max-width: 900px; margin: 0 auto; padding: 0 16px 24px; display: flex; flex-direction: column; gap: 24px;">

    <!-- Strict implementation of User's sticky navigation bar -->
    <div class="slideDown navi-bar" id="navi">
        <nav aria-label="Novel Sayfası" role="navigation">
            <div class="flex-gap-3">
                <!-- Önceki -->
                <?php if ($prev_chapter) : ?>
                <a class="navi-btn" rel="prev" href="<?php echo get_permalink($prev_chapter->ID); ?>">
                    <svg aria-hidden="true" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path></svg>
                    <span class="hidden-md">Önceki</span>
                </a>
                <?php else: ?>
                <span class="navi-btn" style="opacity:0.5; cursor:not-allowed;">
                    <svg aria-hidden="true" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path></svg>
                </span>
                <?php endif; ?>
                
                <!-- Tüm Bölümler / Novel -->
                <a class="navi-btn-primary" rel="home" href="<?php echo $novel ? get_permalink($novel->ID) : home_url(); ?>">
                    <svg width="20" height="20" aria-hidden="true" viewBox="0 0 512 512"><path fill="currentColor" d="m102.5 26.03l90.03 345.75l289.22 23.25l-90.063-345.75zm-18.906 1.564c-30.466 11.873-55.68 53.098-49.75 75.312l3.25 11.78c.667-1.76 1.36-3.522 2.093-5.28C49.097 85.7 65.748 62.64 89.564 50.5zm10.844 41.593c-16.657 10.012-29.92 28.077-38 47.407c-5.247 12.55-8.038 25.63-8.75 36.53L112.5 388.407c.294-.55.572-1.106.875-1.656c10.603-19.252 27.823-37.695 51.125-48.47L94.437 69.19zm74.874 287.594c-17.677 9.078-31.145 23.717-39.562 39c-4.464 8.107-7.27 16.364-8.688 23.75l11.688 42.408l1.625.125c-3.84-27.548 11.352-60.504 41.25-81.094zm26.344 34c-32.567 17.27-46.51 52.44-41.844 72.94l289.844 24.5c-5.34-7.79-8.673-17.947-8.594-28.5l-22.406-9L459 443.436l-13.5-12.875c5.604-6.917 13.707-13.05 24.813-17.687L195.656 390.78z"></path></svg>
                    <span class="hidden-md">Novel Sayfası</span>
                </a>
                
                <!-- Sonraki -->
                <?php if ($next_chapter) : ?>
                <a class="navi-btn" rel="next" href="<?php echo get_permalink($next_chapter->ID); ?>">
                    <span class="hidden-md">Sonraki</span>
                    <svg aria-hidden="true" width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="transform:rotate(180deg);"><path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path></svg>
                </a>
                <?php else: ?>
                <span class="navi-btn" style="opacity:0.5; cursor:not-allowed;">
                    <svg aria-hidden="true" width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="transform:rotate(180deg);"><path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path></svg>
                </span>
                <?php endif; ?>
            </div>
        </nav>
        
        <button class="navi-btn" id="btn-chapter-list" type="button">
            <svg aria-hidden="true" width="20" height="20" fill="none" viewBox="0 0 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 1h10M6 5h10M6 9h10M1.49 1h.01m-.01 4h.01m-.01 4h.01"></path></svg>
            <span class="hidden-md">Bölüm Listesi</span>
        </button>
        
        <div class="ms-auto">
            <button class="navi-btn" style="padding-left:10px; padding-right:10px;" id="btn-save"
                data-novel-id="<?php echo $novel_id; ?>"
                data-novel-title="<?php echo esc_attr(get_the_title($novel_id)); ?>"
                data-novel-thumb="<?php echo get_the_post_thumbnail_url($novel_id, 'medium'); ?>"
                data-last-ch="<?php echo $chapter_id; ?>">
                <svg aria-hidden="true" width="16" height="16" fill="currentColor" viewBox="0 0 384 512"><path d="M336 0H48C21.49 0 0 21.49 0 48v464l192-112 192 112V48c0-26.51-21.49-48-48-48zm0 428.43l-144-84-144 84V54a6 6 0 0 1 6-6h276c3.314 0 6 2.683 6 5.996V428.43z"></path></svg>
                <span class="hidden-md">Kaydet</span>
            </button>
            <button class="navi-btn" style="padding-left:10px; padding-right:10px;" id="btn-settings" type="button">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 1024 1024"><path d="m924.8 625.7l-65.5-56c3.1-19 4.7-38.4 4.7-57.8s-1.6-38.8-4.7-57.8l65.5-56a32.03 32.03 0 0 0 9.3-35.2l-.9-2.6a443.74 443.74 0 0 0-79.7-137.9l-1.8-2.1a32.12 32.12 0 0 0-35.1-9.5l-81.3 28.9c-30-24.6-63.5-44-99.7-57.6l-15.7-85a32.05 32.05 0 0 0-25.8-25.7l-2.7-.5c-52.1-9.4-106.9-9.4-159 0l-2.7.5a32.05 32.05 0 0 0-25.8 25.7l-15.8 85.4a351.86 351.86 0 0 0-99 57.4l-81.9-29.1a32 32 0 0 0-35.1 9.5l-1.8 2.1a446.02 446.02 0 0 0-79.7 137.9l-.9 2.6c-4.5 12.5-.8 26.5 9.3 35.2l66.3 56.6c-3.1 18.8-4.6 38-4.6 57.1c0 19.2 1.5 38.4 4.6 57.1L99 625.5a32.03 32.03 0 0 0-9.3 35.2l.9 2.6c18.1 50.4 44.9 96.9 79.7 137.9l1.8 2.1a32.12 32.12 0 0 0 35.1 9.5l81.9-29.1c29.8 24.5 63.1 43.9 99 57.4l15.8 85.4a32.05 32.05 0 0 0 25.8 25.7l2.7.5a449.4 449.4 0 0 0 159 0l2.7-.5a32.05 32.05 0 0 0 25.8-25.7l15.7-85a350 350 0 0 0 99.7-57.6l81.3 28.9a32 32 0 0 0 35.1-9.5l1.8-2.1c34.8-41.1 61.6-87.5 79.7-137.9l.9-2.6c4.5-12.3.8-26.3-9.3-35zM788.3 465.9c2.5 15.1 3.8 30.6 3.8 46.1s-1.3 31-3.8 46.1l-6.6 40.1l74.7 63.9a370.03 370.03 0 0 1-42.6 73.6L721 702.8l-31.4 25.8c-23.9 19.6-50.5 35-79.3 45.8l-38.1 14.3l-17.9 97a377.5 377.5 0 0 1-85 0l-17.9-97.2l-37.8-14.5c-28.5-10.8-55-26.2-78.7-45.7l-31.4-25.9l-93.4 33.2c-17-22.9-31.2-47.6-42.6-73.6l75.5-64.5l-6.5-40c-2.4-14.9-3.7-30.3-3.7-45.5c0-15.3 1.2-30.6 3.7-45.5l6.5-40l-75.5-64.5c11.3-26.1 25.6-50.7 42.6-73.6l93.4 33.2l31.4-25.9c23.7-19.5 50.2-34.9 78.7-45.7l37.9-14.3l17.9-97.2c28.1-3.2 56.8-3.2 85 0l17.9 97l38.1 14.3c28.7 10.8 55.4 26.2 79.3 45.8l31.4 25.8l92.8-32.9c17 22.9 31.2 47.6 42.6 73.6L781.8 426l6.5 39.9zM512 326c-97.2 0-176 78.8-176 176s78.8 176 176 176s176-78.8 176-176s-78.8-176-176-176zm79.2 255.2A111.6 111.6 0 0 1 512 614c-29.9 0-58-11.7-79.2-32.8A111.6 111.6 0 0 1 400 502c0-29.9 11.7-58 32.8-79.2C454 401.6 482.1 390 512 390c29.9 0 58 11.6 79.2 32.8A111.6 111.6 0 0 1 624 502c0 29.9-11.7 58-32.8 79.2z"></path></svg>
                <span class="hidden-md">Ayarlar</span>
            </button>
        </div>
    </div>

    <div class="nt-card" style="overflow:hidden; margin-top:24px;">
        <!-- Off-canvas Chapter Drawer -->
        <div id="chapter-drawer" style="position:fixed; top:0; left:-350px; width:350px; height:100vh; background-color:#1e293b; color:#cbd5e1; z-index:9999; transition:left 0.3s ease; display:flex; flex-direction:column; box-shadow: 2px 0 10px rgba(0,0,0,0.5);">
            <div style="padding:16px; background-color:#334155; display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <input type="text" id="chapter-search" placeholder="Bölüm ara..." style="flex:1; min-width:200px; padding:8px 16px; background-color:#1e293b; border:1px solid #475569; color:#f8fafc; border-radius:24px; outline:none; font-size:14px;">
                    <button id="chapter-drawer-close" style="background:none; border:none; color:#f8fafc; font-size:24px; cursor:pointer; flex-shrink:0;">&times;</button>
                </div>
                <div style="font-size:12px; color:#94a3b8; text-align:center; font-weight:700;"><?php echo esc_html(get_the_title($novel_id)); ?></div>
            </div>
            <div id="drawer-chapter-list" style="flex:1; overflow-y:auto; padding:0;">
                <?php
                // Render list
                foreach (array_reverse($all_chapters) as $ch) :
                    $isActive = ($ch->ID == $chapter_id);
                    $n = get_post_meta($ch->ID, '_chapter_number', true);
                    $v = get_post_meta($ch->ID, '_chapter_volume', true);
                    $ch_labels = webnovel_get_chapter_labels($ch->ID);

                    $display_name = ($v ? 'Cilt ' . $v . ' ' : '') . 'Bölüm ' . $n;

                    $itemBg = $isActive ? '#2563eb' : 'transparent';
                    $itemColor = $isActive ? '#ffffff' : '#cbd5e1';
                ?>
                <a href="<?php echo get_permalink($ch->ID); ?>" class="drawer-ch-item<?php echo $isActive ? ' is-active' : ''; ?>" data-title="<?php echo esc_attr(strtolower($display_name)); ?>" style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:16px 20px; text-decoration:none; color:<?php echo $itemColor; ?>; background-color:<?php echo $itemBg; ?>; border-bottom:1px solid #334155; font-size:14px; transition:background 0.2s;">
                    <span style="font-weight:600; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo esc_html($display_name); ?></span>
                    <?php if (!empty($ch_labels)) : ?>
                        <span class="drawer-ch-badges" style="flex-shrink:0; display:inline-flex; gap:4px;">
                            <?php if (in_array('revize', $ch_labels, true)) : ?>
                                <span class="drawer-badge drawer-badge--fix">Revize</span>
                            <?php endif; ?>
                            <?php if (in_array('son', $ch_labels, true)) : ?>
                                <span class="drawer-badge drawer-badge--end">Son</span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <div style="padding:24px 16px; text-align:center; color:#475569;">
                    <svg width="80" height="20" viewBox="0 0 100 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M 5 10 Q 20 5 35 10 T 65 10 T 95 10" stroke-linecap="round"/>
                        <circle cx="50" cy="10" r="4" fill="currentColor"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <script>
        // Sticky Header Auto-hide (Headroom effect for navi)
        let lastScrollY = window.scrollY;
        const navi = document.getElementById('navi');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                if (window.scrollY > lastScrollY) {
                    navi.classList.replace('slideDown', 'slideUp');
                } else {
                    navi.classList.replace('slideUp', 'slideDown');
                }
            } else {
                navi.classList.replace('slideUp', 'slideDown');
            }
            lastScrollY = window.scrollY;
        });

        // Drawer Logic
        document.getElementById('btn-chapter-list').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('chapter-drawer').style.left = '0';
        });
        document.getElementById('chapter-drawer-close').addEventListener('click', function() {
            document.getElementById('chapter-drawer').style.left = '-350px';
        });
        document.addEventListener('click', function(e) {
            const drawer = document.getElementById('chapter-drawer');
            if (drawer.style.left === '0px' && !drawer.contains(e.target)) {
                drawer.style.left = '-350px';
            }
        });
        document.getElementById('chapter-search').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.drawer-ch-item').forEach(item => {
                const title = item.getAttribute('data-title');
                if(title.includes(val)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        </script>

        <div class="nt-card-body" style="padding: 32px 24px;">
            <div class="nt-text-center nt-mb-6 nt-border-b nt-pb-2">
                <?php if ($volume_number) : ?>
                <p class="nt-text-sm nt-font-bold nt-text-accent" style="margin-bottom:8px;">Cilt <?php echo intval($volume_number); ?></p>
                <?php endif; ?>
                <h1 class="nt-text-2xl nt-font-bold nt-text-main" style="margin:0;"><?php the_title(); ?></h1>
            </div>

            <?php webnovel_render_ad('before_content'); ?>
            <?php if (!empty($chapter_yt_url)) : ?>
                <youtube-button href="<?php echo esc_url($chapter_yt_url); ?>" label="<?php echo esc_attr($chapter_yt_label); ?>"></youtube-button>
            <?php endif; ?>
            <!-- Chapter Text (Dynamic loading for protection) -->
            <div class="reader-text-wrap" style="min-height: 400px; padding: 20px 0;">
                <div class="reader-text" id="reader-text" data-chapter-id="<?php echo $chapter_id; ?>" style="font-size:1.125rem; line-height:1.8; color:var(--text-main);">
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 0; color:var(--text-dim);">
                        <div style="width:48px; height:48px; border:3px solid var(--border); border-top-color:var(--accent); border-radius:50%; animation:spin 1s linear infinite; margin-bottom:16px;"></div>
                        <p>Bölüm içeriği güvenli bir şekilde yükleniyor...</p>
                    </div>
                </div>
            </div>
            
            <?php webnovel_render_ad('after_content'); ?>

            <!-- Bottom Navigation -->
            <div class="nt-flex-row nt-items-center nt-justify-center nt-border-b" style="margin-top: 48px; padding-top: 32px; border-bottom:none; border-top: 1px solid var(--border); gap:16px; flex-wrap:wrap;">
                
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="nt-card">
        <div class="nt-card-body" style="padding: 32px 24px;">
            <h3 class="nt-text-xl nt-font-bold nt-mb-4 nt-border-b nt-pb-2 nt-text-accent">Bölüm Yorumları</h3>
            <?php webnovel_render_comments(); ?>
        </div>
    </div>
</div>

<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
/* Remove scrollbar for the drawer */
#drawer-chapter-list::-webkit-scrollbar { width: 6px; }
#drawer-chapter-list::-webkit-scrollbar-thumb { background: #475569; border-radius:3px; }

/* Bölüm listesi rozetleri (Son / Revize) */
.drawer-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 0.5em;
    line-height: 1.2;
    letter-spacing: 0.02em;
    white-space: nowrap;
}
.drawer-badge--end { background: #4F8C12; color: #fff; }
.drawer-badge--fix { background: #FFD580; color: #000; }
</style>

<!-- Advanced Settings Modal (Matches Screenshot) -->
<div id="settings-panel" class="settings-panel" style="position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:10000; background:#3a4557; border-radius:12px; border:1px solid #4a5568; width:90%; max-width:500px; color:#cbd5e1; font-family:inherit; box-shadow:0 10px 40px rgba(0,0,0,0.6); display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #334155;">
        <h3 style="margin:0; font-size:18px; font-weight:700; color:#f8fafc;">Okuma Ayarları</h3>
        <button id="settings-close" class="settings-close" style="background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer;">&times;</button>
    </div>
    
    <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Yazı Boyutu:</span>
            <select id="set-font-size" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="14px">14px</option>
                <option value="16px">16px</option>
                <option value="18px" selected>Varsayılan(18px)</option>
                <option value="20px">20px</option>
                <option value="24px">24px</option>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Yazı Tipi:</span>
            <select id="set-font-family" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="inherit" selected>Varsayılan</option>
                <option value="'Roboto', sans-serif">Roboto</option>
                <option value="'Sriracha', cursive">Sriracha</option>
                <option value="'Source Sans Pro', sans-serif">Source Sans Pro</option>
                <option value="'Courier New', monospace">Courier New</option>
                <option value="'Shantell Sans', cursive">Shantell Sans</option>
                <option value="'Nunito', sans-serif">Nunito</option>
                <option value="'Merienda', cursive">Merienda</option>
                <option value="'Chakra Petch', sans-serif">Chakra Petch</option>
                <option value="'Quicksand', sans-serif">Quicksand</option>
                <option value="'Lobster', cursive">Lobster</option>
                <option value="'Amatic SC', cursive">AMATIC SC</option>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Yazı Hizalama:</span>
            <select id="set-text-align" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="left" selected>Solda</option>
                <option value="center">Ortada</option>
                <option value="justify">İki Yana Yasla</option>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Yazı Kalınlığı:</span>
            <select id="set-font-weight" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="normal" selected>Normal</option>
                <option value="bold">Kalın (Bold)</option>
                <option value="300">İnce (Light)</option>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Yazı İtalikliği:</span>
            <select id="set-font-style" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="normal" selected>Normal</option>
                <option value="italic">İtalik</option>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <span style="font-size:14px; font-weight:600; width:45%;">Satır İçi Yükseklik:</span>
            <select id="set-line-height" style="background:#1e293b; color:#cbd5e1; border:1px solid #334155; border-radius:6px; padding:6px 12px; width:55%; outline:none; font-size:13px; appearance:none;">
                <option value="1.2">Dar (1.2)</option>
                <option value="1.5">Orta (1.5)</option>
                <option value="1.8" selected>Varsayılan (1.8)</option>
                <option value="2.2">Geniş (2.2)</option>
            </select>
        </div>

        <button id="settings-save" style="margin-top:12px; background:#1d4ed8; color:#fff; border:none; border-radius:6px; padding:10px; font-size:14px; font-weight:600; cursor:pointer; width:100%; transition:background 0.2s;">
            Ayarları Uygula ve Kaydet
        </button>

        <div style="margin-top:8px;">
            <span style="font-size:16px; font-weight:700; color:#f8fafc; display:block; margin-bottom:12px;">Genişliği Sınırla</span>
            <input type="range" id="set-max-width" min="40" max="100" value="100" style="width:100%; accent-color:#3b82f6;">
            <div style="font-size:11px; color:#64748b; margin-top:12px; line-height:1.4;">
                * Seçenek önizlemeleri mobil cihazlarda gözükmez.<br>
                * Genişlik sınırlaması mobil cihazlarda uygulanmaz.
            </div>
        </div>

    </div>
</div>
<style>
/* Reset basic modal styles */
.settings-panel {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10000;
    display: none;
}
.settings-panel.active {
    display: block;
}
.settings-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: none;
    backdrop-filter: blur(2px);
}
.settings-overlay.active {
    display: block;
}
select {
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23cbd5e1%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 0.7rem top 50%;
    background-size: 0.65rem auto;
}
</style>
<div id="reader-controls" class="reader-controls">
    <button id="btn-jump-comments" class="reader-control-btn" title="Yorumlara Git">
        <svg height="20" viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12c0 2.02.6 3.9 1.63 5.48L2 22l4.52-1.63C7.1 21.4 8.98 22 11 22h1c5.53 0 10-4.47 10-10S17.53 2 12 2zm0 18c-1.74 0-3.37-.5-4.75-1.37l-.25-.13l-3 1.08l1.08-3l-.13-.25C4.33 14.87 3.84 13.48 3.84 12c0-4.5 3.66-8.16 8.16-8.16s8.16 3.66 8.16 8.16c0 4.51-3.66 8.16-8.16 8.16zM13 11V9l-1 2-1-2v2H9v1.5h1.5V14h1.5v-1.5h1.5V11h-1.5z"/></svg>
    </button>
    <div id="reader-progress-wrap" class="reader-progress-wrap">
        <svg class="progress-ring" width="50" height="50">
            <circle class="progress-ring-bg" stroke="#334155" stroke-width="3" fill="transparent" r="22" cx="25" cy="25"/>
            <circle id="reader-progress-ring" class="progress-ring-circle" stroke="#3b82f6" stroke-width="3" fill="transparent" r="22" cx="25" cy="25"/>
        </svg>
        <span id="reader-progress-text" class="progress-text">0%</span>
    </div>
</div>

<div id="settings-overlay" class="settings-overlay"></div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
