<?php get_header(); ?>

<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row single hero">

		<header class="tagline">Everything<br />
			that’s<br />
			happening<span>.</span>
		</header>

		<?php get_template_part( 'parts/today' ); ?>

	</section>

	<section class="row single filter divider-bottom">
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
				$featured_posts = WSU\Events\Page_Curation\get_featured_events( 'query' );
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

	<?php get_template_part( 'parts/footers' ); ?>

</main>

<?php
get_footer();
