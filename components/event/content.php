<?php

$post_share_placement = spine_get_option( 'post_social_placement' );

?>
<article id="event-<?php the_ID(); ?>" class="card card--event">

	<header class="card-title">
		<?php

		if ( is_single() ) {
			?><h1><?php the_title(); ?></h1><?php
		} else {
			?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php
		}
		?>
	</header>

	<div class="card-dates">
		<span class="start-date"></span>
		<span class="end-date"></span>
	</div>

	<div class="card-action">
		<a href="">Text</a>
	</div>

	<div class="card-content">
		<?php the_content(); ?>
	</div>

	<div class="card-location">

		<div class="card-location-notes">

		</div>
	</div>

	<div class="card-organizer">
		<div class="organizer-name">

		</div>

		<div class="organizer-email">

		</div>

		<div class="organizer-phone">

		</div>
	</div>

	<div class="card-cost">

	</div>

	<?php
	if ( spine_has_featured_image() ) {
		?>
		<figure class="card-image">
			<?php the_post_thumbnail( 'spine-small_size' ); ?>
		</figure>
		<?php
	}
	?>
</article>
