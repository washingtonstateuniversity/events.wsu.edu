<?php

$post_share_placement = spine_get_option( 'post_social_placement' );
$event_data = get_event_data( get_the_ID() );

?>
<article id="event-<?php the_ID(); ?>" class="card card--event">

	<header class="card-header">
		<?php if ( is_single() ) { ?>
			<?php // Display the event type.
			$types = wp_get_post_terms( get_the_ID(), 'event-type' );
			if ( $types ) {
				foreach ( $types as $type ) {
					?><p class="card-taxonomy card-event-type"><a href="#"><?php echo esc_html( $type->name ); ?></a></p><?php
				}
			}
			?>
			<h1 class="card-title"><?php the_title(); ?></h1>
		<?php } else { ?>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php } ?>
	</header>

	<?php if ( is_single() ) { ?>
	<section class="card-event-details row halves">

		<div class="column one">

			<time class="card-event-datetime" datetime="<?php echo esc_attr( $event_data['start']['date_time'] ); ?>">
				<span class="card-date"><?php echo esc_html( $event_data['start']['date'] ); ?> -
					<a href="#">add to calendar</a>
				</span>
				<span class="card-time"><?php echo esc_html( $event_data['start']['time'] ); ?></span>
			</time>

			<div class="card-location">

				<?php if ( ! empty( $event_data['location']['name'] ) ) { ?>
				<span class="card-address"><?php echo esc_html( $event_data['location']['name'] ); ?></span>
				<?php } ?>

				<?php if ( ! empty( $event_data['location']['notes'] ) ) { ?>
				<span class="card-location-notes"></span>
				<?php } ?>

				<span class="card-directions">
					<a href="#">View directions</a>
				</span>
			</div>

			<?php if ( ! empty( $event_data['cost'] ) ) { ?>
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

			<h2>About the event</h2>

			<?php the_content(); ?>

			<?php if ( ! empty( $event_data['related'] ) ) { ?>
			<ul class="card-related-links">
				<li>
					<a href="<?php echo esc_url( $event_data['related'] ); ?>"><?php echo esc_url( $event_data['related'] ); ?></a>
				</li>
			</ul>
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

			<?php if ( has_term( '', 'event-category' ) ) { ?>
			<div class="card-categories">
				<h2>Categorized</h2>
				<?php the_terms( get_the_ID(), 'event-category' ); ?>
			</div>
			<?php } ?>

			<?php if ( has_term( '', 'event-tag' ) ) { ?>
			<div class="card-tags">
				<h2>Tagged</h2>
				<?php the_terms( get_the_ID(), 'event-tag' ); ?>
			</div>
			<?php } ?>

			<?php if ( taxonomy_exists( 'wsuwp_university_location' ) && has_term( '', 'wsuwp_university_location' ) ) { ?>
			<div class="card-organization-location">
				<h2>Location</h2>
				<?php the_terms( get_the_ID(), 'wsuwp_university_location' ); ?>
			</div>
			<?php } ?>

		</div>

		<div class="column two"></div>

	</footer>

	<?php } else { ?>

	<!-- Not yet determined -->

	<?php } ?>
</article>
