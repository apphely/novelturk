<?php
/**
 * The template for displaying Comments
 */
if ( post_password_required() ) {
    return;
}

// Force comment_status open for novel and chapter posts
global $post;
if ( $post && in_array( $post->post_type, array( 'novel', 'chapter' ) ) ) {
    $post->comment_status = 'open';
}
?>

<div id="comments" class="comments-area" style="margin-top: 24px;">

    <?php if ( have_comments() ) : ?>
        <h3 class="comments-title" style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: var(--text-main);">
            <?php
            $comments_number = get_comments_number();
            printf(
                _n( '%s Yorum', '%s Yorum', $comments_number, 'novelturk' ),
                number_format_i18n( $comments_number )
            );
            ?>
        </h3>

        <ul class="comment-list" style="list-style: none; padding: 0; margin: 0;">
            <?php
            wp_list_comments( array(
                'style'       => 'ul',
                'short_ping'  => true,
                'avatar_size' => 48,
            ) );
            ?>
        </ul>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php comment_form(); ?>

</div>
