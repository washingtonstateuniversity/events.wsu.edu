<?php

get_header();

global $is_today;

$is_today = false;

$date = strtotime( get_query_var( 'wsuwp_event_date' ) );
$day_view = is_post_type_archive( 'event' ) && ! is_month();
$subheader = ( $day_view ) ? date_i18n( 'l, F j, Y', $date ) : date_i18n( 'F Y', $date );
?>
<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<header class="page-header">
		<h1><?php
		if ( is_tax() || is_tag() ) {
			single_term_title();
		} elseif ( is_post_type_archive( 'event' ) ) {
			echo 'What’s happening';

			if ( ! is_date() || date_i18n( 'F j, Y', $date ) === date_i18n( 'F j, Y' ) ) {
				echo ' today';
			}
		}
		?></h1>

		<p><?php echo esc_html( $subheader ); ?></p>
	</header>

	<section class="row single divider-bottom">

		<div class="column one deck deck-river">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					get_template_part( 'components/event/content' );
				endwhile;
			else :
				?>
				<article class="card card--event no-events">
					<?php if ( $day_view ) { ?>
						<p>No events listed today.</p>
					<?php } else { ?>
						<p>No <?php if ( is_tax() || is_tag() ) { echo '<span class="term-name">' . esc_html( single_term_title( '', false ) ) . '</span> '; } ?>events listed this month.</p>
					<?php } ?>
					<p>Know of something happening? <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=event' ) ); ?>">Submit an event.</a></p>
				</article>
				<?php
			endif;
			?>

		</div>

	</section>

	<?php $pagination = WSU\Events\Archives\get_pagination_links(); ?>

	<footer class="main-footer archive-footer">

		<div class="pagination previous">
			<?php if ( $pagination['previous'] ) { ?><a href="<?php echo esc_url( $pagination['previous'] ); ?>"><?php echo esc_html( $pagination['previous_label'] ); ?></a><?php } ?>
		</div>

		<div class="pagination next">
			<?php if ( $pagination['next'] ) { ?><a href="<?php echo esc_url( $pagination['next'] ); ?>"><?php echo esc_html( $pagination['next_label'] ); ?></a><?php } ?>
		</div>

	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
