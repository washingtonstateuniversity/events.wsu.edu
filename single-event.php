<?php

get_header();

?>
<main id="wsuwp-main">
	<?php get_template_part( 'parts/headers' ); ?>
	<section class="row side-right gutter pad-ends">

		<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'components/event/content' ) ?>

			<?php endwhile; ?>

		</div><!--/column-->

		<div class="column two"></div><!--/column two-->

	</section>

	<footer class="main-footer">
		<section class="row halves gutter pad-ends pagination">
			<div class="column one nav-previous">
				<?php previous_post_link(); ?>
			</div>
			<div class="column two nav-next">
				<?php next_post_link(); ?>
			</div>
		</section><!--pager-->
	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
