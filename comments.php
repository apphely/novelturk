<?php
/**
 * The template for displaying Comments
 */
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area" style="margin-top:24px;">

    <?php if ( have_comments() ) : ?>
        <h3 class="comments-title" style="font-size:18px; font-weight:700; margin-bottom:16px; color:var(--text-main);">
            <?php
            $comments_number = get_comments_number();
            if ( 1 === $comments_number ) {
                printf( _x( '1 Yorum', 'comments title', 'novelturk' ) );
            } else {
                printf(
                    _nx(
                        '%1$s Yorum',
                        '%1$s Yorum',
                        $comments_number,
                        'comments title',
                        'novelturk'
                    ),
                    number_format_i18n( $comments_number )
                );
            }
            ?>
        </h3>

        <ul class="comment-list" style="list-style:none; padding:0; margin:0;">
            <?php
            wp_list_comments( array(
                'style'       => 'ul',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => 'webnovel_custom_comment_layout'
            ) );
            ?>
        </ul>

        <?php the_comments_navigation(); ?>
    <?php endif; // Check for have_comments(). ?>

    <?php
    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
    ?>
        <p class="no-comments" style="color:var(--text-dim); text-align:center; font-style:italic;">Yorumlar kapatıldı.</p>
    <?php endif; ?>

    <?php
    comment_form( array(
        'title_reply' => '<span style="font-size:16px; font-weight:700; color:var(--text-main);">Bir Yorum Bırak</span>',
        'class_form' => 'comment-form-custom',
        'comment_notes_before' => '',
        'submit_button' => '<button type="submit" id="%2$s" class="%3$s" style="background:#2563eb; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:700; cursor:pointer; font-size:14px;">%4$s</button>',
        'submit_field' => '<p class="form-submit" style="margin-top:16px;">%1$s %2$s</p>',
        'comment_field' => '<p class="comment-form-comment" style="margin-top:12px; margin-bottom:12px;"><textarea id="comment" name="comment" cols="45" rows="4" maxlength="65525" required="required" placeholder="Ne düşünüyorsun?" style="width:100%; border-radius:8px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main); padding:12px; outline:none; resize:vertical; font-size:14px;"></textarea></p>',
    ) );
    ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var likedComments = JSON.parse(localStorage.getItem('nt_liked_comments') || '[]');
    var likeBtns = document.querySelectorAll('.comment-like-btn');
    likeBtns.forEach(function(btn) {
        var cId = btn.getAttribute('data-id');
        if (likedComments.includes(cId)) {
            btn.classList.add('liked');
            btn.style.color = '#ef4444';
        }

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if(btn.disabled || likedComments.includes(cId)) return;
            
            var formData = new FormData();
            formData.append('action', 'webnovel_like_comment');
            formData.append('comment_id', cId);
            
            btn.disabled = true;
            btn.style.opacity = '0.5';

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if(data.success) {
                    btn.querySelector('.like-count').textContent = data.data.likes;
                    btn.classList.add('liked');
                    btn.style.color = '#ef4444';
                    btn.style.opacity = '1';
                    
                    likedComments.push(cId);
                    localStorage.setItem('nt_liked_comments', JSON.stringify(likedComments));
                }
            });
        });
    });
});
</script>
