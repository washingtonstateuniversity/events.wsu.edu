<?php
$event_data = get_event_data( get_the_ID() );
$types = wp_get_post_terms( get_the_ID(), 'event-type' );
$type = ( ! empty( $types[0] ) ) ? $types[0]->name : false;
$locations = wp_get_post_terms( get_the_ID(), 'wsuwp_university_location' );
$location = ( ! empty( $locations[0] ) ) ? $locations[0]->name : false;
?>
<article id="event-<?php the_ID(); ?>" class="card card--event">

	<div class="card-date"><?php echo esc_html( $event_data['date'] . $event_data['time'] ); ?></div>

	<header class="card-title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</header>

	<?php if ( $type && ! is_tax( 'event-type' ) ) { ?>
	<div class="card-taxonomy card-type"><?php echo esc_html( $type ); ?></div>
	<?php } ?>

	<?php if ( $location && ! is_tax( 'wsuwp_university_location' ) ) { ?>
	<div class="card-taxonomy card-location"><?php
		echo esc_html( $location );

		if ( ! is_front_page() ) {
			$venue = WSU\Events\Venues\get_venue();

			if ( $venue ) {
				echo ' - ' . esc_html( $venue['raw']['name'] );
			}
		}
	?></div>
	<?php } ?>

	<div class="card-excerpt">
		<?php
		$featured_excerpt = get_post_meta( get_the_ID(), '_wsuwp_event_featured_excerpt', true );

		if ( is_front_page() && $featured_excerpt ) {
			echo wp_kses_post( apply_filters( 'the_content', $featured_excerpt ) );
		} else {
			the_excerpt();
		}
		?>
	</div>

</article>
