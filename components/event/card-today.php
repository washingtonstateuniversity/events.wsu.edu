<?php
$event_data = get_event_data( get_the_ID() );
$types = wp_get_post_terms( get_the_ID(), 'event-type' );
$type = ( ! empty( $types[0] ) ) ? $types[0]->name : false;
?>
<article id="event-<?php the_ID(); ?>" class="card card--event">

	<?php if ( $type ) { ?><div class="card-taxonomy card-type"><?php echo esc_html( $type ); ?></div><?php } ?>

	<header class="card-title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</header>

	<div class="card-date"><?php echo esc_html( $event_data['start']['time'] ); ?></div>

</article>
