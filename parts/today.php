<header class="tagline">What’s<br />
happening<br />
<a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">today<span>.</span></a>
</header>

<div class="deck deck-today">

	<?php
	global $is_today;

	$args = array(
		'post_type' => 'event',
		'posts_per_page' => 10,
		'wsuwp_events_today' => true,
	);

	$events_today = new WP_Query( $args );

	if ( $events_today->have_posts() ) {
		while ( $events_today->have_posts() ) {
			$events_today->the_post();
			$is_today = true;
			get_template_part( 'components/event/content' );
		}
	}

	wp_reset_postdata();

	$is_today = false;
	?>

</div>
