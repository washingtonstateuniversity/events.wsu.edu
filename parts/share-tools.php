<?php
$post_share_url = esc_url( get_permalink() );
$post_share_title = rawurlencode( trim( wp_title( '', false ) ) );
$spine_social_options = spine_social_options();
if ( ! empty( $spine_social_options['twitter'] ) ) {
	$twitter_array = explode( '/', $spine_social_options['twitter'] );
	$twitter_handle = esc_attr( array_pop( $twitter_array ) );
} else {
	$twitter_handle = 'wsupullman';
}
?>
<div class="card-share">
	<p>Let's Share</p>
	<a href="<?php echo esc_url( 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . $post_share_url . '&amp;summary=' . $post_share_title ); ?>">
		<span class="screen-reader-text">Share this article on Linkedin</span>
		<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="16" height="16">
			<use xlink:href="#social-media-icon_linkedin" fill="#007bb6"/>
		</svg>
	</a>
	<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?text=' . $post_share_title . '&amp;url=' . $post_share_url . '&amp;via=' . $twitter_handle ); ?>">
		<span class="screen-reader-text">Share on Twitter</span>
		<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="16" height="16" viewBox="0 -2 20 20">
			<use xlink:href="#social-media-icon_twitter" fill="#00aced"/>
		</svg>
	</a>
	<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $post_share_url ); ?>">
		<span class="screen-reader-text">Share on Facebook</span>
		<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="16" height="16">
			<use xlink:href="#social-media-icon_facebook" fill="#3b5998"/>
		</svg>
	</a>
</div>
