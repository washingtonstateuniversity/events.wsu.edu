<?php

get_header();

global $is_today;

$is_today = false;

if ( is_date() ) {
	$year = get_query_var( 'year' );
	$month = get_query_var( 'monthnum' );
	$day = get_query_var( 'day' );
	$date = strtotime( $year . '-' . $month . '-' . $day );
	$current_view = date( 'F j, Y', $date );
	$todays_date = date( 'F j, Y' );
	$subtitle = date( 'l, F j, Y', $date );
}
?>
<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<header class="page-header">
		<h1><?php
		if ( is_tax() ) {
			single_term_title();
		} elseif ( is_post_type_archive( 'event' ) ) {
			echo 'What’s happening';

			if ( ! is_date() || $current_view === $todays_date ) {
				echo ' today';
			}
		}
		?></h1>

		<?php if ( is_date() && $current_view !== $todays_date ) { ?>
		<p><?php echo esc_html( $subtitle ); ?></p>
		<?php } ?>
	</header>

	<section class="row single divider-bottom">

		<div class="column one deck deck-river">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'components/event/content' ) ?>

			<?php endwhile; ?>

		</div>

	</section>

	<?php $pagination = WSU\Events\Archives\get_pagination_urls(); ?>

	<footer class="main-footer archive-footer">

		<div class="pagination previous">
			<?php if ( $pagination['previous'] ) { ?><a href="<?php echo esc_url( $pagination['previous'] ); ?>">Previous events</a><?php } ?>
		</div>

		<div class="pagination next">
			<?php if ( $pagination['next'] ) { ?><a href="<?php echo esc_url( $pagination['next'] ); ?>">Next events</a><?php } ?>
		</div>

	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
