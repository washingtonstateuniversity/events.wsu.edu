<?php get_header(); ?>

<main>

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row single hero">

		<header class="tagline">Everything<br />
			that’s<br />
			happening<span>.</span>
		</header>

		<div class="column one today">

			<?php get_template_part( 'parts/today' ); ?>

		</div>

	</section>

	<section class="row single filter">
		<div class="column one">

			<p>Browse by<span>&hellip;</span></p>

			<ul>
				<li><?php display_event_filter( 'Event type', 'event-type' ); ?></li>
				<li><?php display_event_filter( 'Campus', 'wsuwp_university_location' ); ?></li>
			</ul>

			<?php get_template_part( 'parts/search-form' ); ?>

		</div>
	</section>

	<section class="row single features">

		<div class="column one">

			<header>
				<h2>Featured events</h2>
			</header>

			<div class="deck deck-features">

				<?php
				$featured_posts = WSU\Events\Page_Curation\get_featured_posts( 'query' );
				if ( $featured_posts->have_posts() ) {
					while ( $featured_posts->have_posts() ) {
						$featured_posts->the_post();
						get_template_part( 'components/event/content' );
					}
				}
				wp_reset_postdata();
				?>

			</div>

		</div>

	</section>

	<footer class="row quarters gutter site-footer">

		<div class="column one">&nbsp;</div>

		<div class="column two">
			<a href="#" class="button gray">Submit an event</a>
		</div>

		<div class="column three">
			<a href="#" class="button gray">Contact us</a>
		</div>

		<div class="column four">
			<a href="#" class="site-title gray">WSU Events</a>
		</div>

	</footer>

</main>

<?php
get_footer();
