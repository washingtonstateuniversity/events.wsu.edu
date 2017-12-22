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
	<footer class="main-footer archive-footer">
		<section class="row side-right pager prevnext gutter">
			<div class="column one">
				<?php

				the_posts_pagination( array(
					'mid_size' => 2,
					'prev_text' => '&laquo; Previous',
					'next_text' => 'Next &raquo;',
				) );

				?>
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
