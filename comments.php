<?php
/**
 * The template for displaying Comments
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php
	if ( have_comments() ) :
		?>
		<h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( 1 === $comments_number ) {
				echo esc_html__( '1 thought on &ldquo;', 'novelturk' );
			} else {
				echo esc_html(
					sprintf(
						/* translators: %d: number of comments */
						_nx(
							'%d thought on &ldquo;',
							'%d thoughts on &ldquo;',
							$comments_number,
							'comments title',
							'novelturk'
						),
						$comments_number
					)
				);
			}
			echo wp_kses_post( get_the_title() ) . '&rdquo;';
			?>
		</h2>

		<?php
		$comments_args = array(
			'walker'            => new Walker_Comment(),
			'max_depth'         => '2',
			'style'             => 'div',
			'callback'          => null,
			'end-callback'      => null,
			'short_ping'        => false,
			'avatar_size'       => 32,
			'reverse_top_level' => null,
			'reverse_children'  => '',
			'format'            => 'xhtml',
			'type'              => 'all',
		);
		wp_list_comments( $comments_args );
		?>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Older Comments', 'novelturk' ),
				'next_text' => esc_html__( 'Newer Comments', 'novelturk' ),
			)
		);
		?>

	<?php
	endif;

	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'novelturk' ); ?></p>
	<?php
	endif;

	comment_form();
	?>

</div><!-- #comments -->
