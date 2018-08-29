<?php
$event_data = get_event_data( get_the_ID() );
$types = wp_get_post_terms( get_the_ID(), 'event-type' );
$type = ( ! empty( $types[0] ) ) ? $types[0] : false;
$ics_link = add_query_arg( 'wsuwp_events_ics', '1', get_the_permalink() );
?>
<article id="event-<?php the_ID(); ?>" class="card card--event">

	<header class="card-header">

		<?php if ( $type ) { ?>
		<p class="card-taxonomy card-type">
			<a href="<?php echo esc_url( get_term_link( $type->term_id ) ); ?>"><?php echo esc_html( $type->name ); ?></a>
		</p>
		<?php } ?>

		<h1 class="card-title"><?php the_title(); ?></h1>

	</header>

	<section class="card-event-details row halves">

		<div class="column one">

			<time class="card-event-datetime" datetime="<?php echo esc_attr( $event_data['start']['date_time'] ); ?>">
				<span class="card-date"><?php echo esc_html( $event_data['full_date'] ); ?> -
					<a href="<?php echo esc_url( $ics_link ); ?>">add to calendar</a>
				</span>
				<span class="card-time"><?php echo esc_html( $event_data['full_time'] ); ?></span>
			</time>

			<?php $event_venue = WSU\Events\Venues\get_venue(); ?>

			<?php if ( ! empty( $event_venue ) || ! empty( $event_data['location_notes'] ) ) { ?>
			<div class="card-location">

				<?php if ( ! empty( $event_venue['address'] ) ) { ?>
				<span class="card-address"><?php echo esc_html( $event_venue['address'] ); ?></span>
				<?php } ?>

				<?php if ( ! empty( $event_data['location_notes'] ) ) { ?>
				<div class="card-location-notes">
					<?php echo wp_kses_post( $event_data['location_notes'] ); ?>
				</div>
				<?php } ?>

				<?php if ( ! empty( $event_venue['link'] ) ) { ?>
				<span class="card-directions">
					<a href="<?php echo esc_url( $event_venue['link'] ); ?>">View location in Google Maps</a>
				</span>
				<?php } ?>

			</div>
			<?php } ?>

			<?php if ( ! empty( $event_data['cost'] ) && strcasecmp( 'free', $event_data['cost'] ) !== 0 ) { ?>
			<span class="card-cost"><?php echo esc_html( $event_data['cost'] ); ?></span>
			<?php } ?>

			<?php if ( ! empty( $event_data['action']['text'] ) && ! empty( $event_data['action']['url'] ) ) { ?>
			<span>
				<a class="card-cta button" href="<?php echo esc_url( $event_data['action']['url'] ); ?>"><?php echo esc_html( $event_data['action']['text'] ); ?></a>
			</span>
			<?php } ?>

		</div>

		<div class="column two">

			<?php if ( has_post_thumbnail() ) { ?>
			<figure class="card-image">

				<?php the_post_thumbnail( 'large' ); ?>

				<?php $featured_image_caption = get_post( get_post_thumbnail_id() )->post_excerpt; ?>

				<?php if ( $featured_image_caption ) { ?>
				<figcaption class="wp-caption-text">
					<?php echo esc_html( $featured_image_caption ); ?>
				</figcaption>
				<?php } ?>

			</figure>
			<?php } ?>

		</div>

	</section>

	<section class="row side-right card-event-details card-about">

		<div class="column one">

			<?php if ( ! empty( get_the_content() ) ) { ?>
			<h2>About the event</h2>
			<?php the_content(); ?>
			<?php } ?>

		</div>

		<div class="column two">

			<?php if ( ! empty( $event_data['contact']['name'] ) || ! empty( $event_data['contact']['email'] ) || ! empty( $event_data['contact']['phone'] ) ) { ?>
			<h2>Contact</h2>

			<div class="card-contact-details">

				<?php if ( ! empty( $event_data['contact']['name'] ) ) { ?>
				<span class="card-contact-name"><?php echo esc_html( $event_data['contact']['name'] ); ?></span>
				<?php } ?>

				<?php if ( ! empty( $event_data['contact']['email'] ) ) { ?>
				<a href="mailto:<?php echo esc_attr( $event_data['contact']['email'] ); ?>" class="card-contact-email"><?php echo esc_html( $event_data['contact']['email'] ); ?></a><br />
				<?php } ?>

				<?php if ( ! empty( $event_data['contact']['phone'] ) ) { ?>
				<a href="tel:<?php echo esc_attr( $event_data['contact']['phone'] ); ?>" class="card-contact-tel"><?php echo esc_html( $event_data['contact']['phone'] ); ?></a>
				<?php } ?>

			</div>
			<?php } ?>

		</div>

	</section>

	<footer class="row side-right card-footer">

		<?php get_template_part( 'parts/share-tools' ); ?>

		<div class="column one card-taxonomy">

			<?php if ( has_term( '', 'wsuwp_university_category' ) ) { ?>
			<div class="card-university-categories">
				<h2>Categorized</h2>
				<?php the_terms( get_the_ID(), 'wsuwp_university_category' ); ?>
			</div>
			<?php } ?>

			<?php if ( has_term( '', 'wsuwp_university_org' ) ) { ?>
			<div class="card-university-organizations">
				<h2>Organization</h2>
				<?php the_terms( get_the_ID(), 'wsuwp_university_org' ); ?>
			</div>
			<?php } ?>

			<?php if ( taxonomy_exists( 'wsuwp_university_location' ) && has_term( '', 'wsuwp_university_location' ) ) { ?>
			<div class="card-university-locations">
				<h2>Location</h2>
				<?php the_terms( get_the_ID(), 'wsuwp_university_location' ); ?>
			</div>
			<?php } ?>

		</div>

		<div class="column two"></div>

	</footer>

</article>
