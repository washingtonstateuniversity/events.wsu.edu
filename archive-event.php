<?php

get_header();

?>
<main>

	<?php

	get_template_part( 'parts/headers' );

	?>
	<section class="row side-right gutter pad-ends">

		<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'components/event/content' ); ?>

			<?php endwhile; ?>

		</div><!--/column-->

		<div class="column two">
		</div><!--/column two-->

	</section>
	<?php

	/* @type WP_Query $wp_query */
	global $wp_query;

	$big = 99164;
	$args = array(
		'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format'       => 'page/%#%',
		'total'        => $wp_query->max_num_pages, // Provide the number of pages this query expects to fill.
		'current'      => max( 1, get_query_var( 'paged' ) ), // Provide either 1 or the page number we're on.
	);
	?>
	<footer class="main-footer archive-footer">
		<section class="row side-right pager prevnext gutter">
			<div class="column one">
				<?php echo paginate_links( $args ); ?>
			</div>
			<div class="column two">
				<!-- intentionally empty -->
			</div>
		</section><!--pager-->
	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
