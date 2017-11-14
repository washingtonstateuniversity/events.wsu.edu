<?php

get_header();

?>
<main id="wsuwp-main">
	<?php

	get_template_part( 'parts/headers' );

	if ( spine_has_featured_image() ) {
		$featured_image_src = spine_get_featured_image_src();
		$featured_image_position = get_post_meta( get_the_ID(), '_featured_image_position', true );

		if ( ! $featured_image_position || sanitize_html_class( $featured_image_position ) !== $featured_image_position ) {
			$featured_image_position = '';
		}
		?><figure class="featured-image <?php echo sanitize_html_class( $featured_image_position ); ?>" style="background-image: url('<?php echo esc_url( $featured_image_src ); ?>');"><?php spine_the_featured_image(); ?></figure><?php
	}

	?>
	<section class="row side-right gutter pad-ends">

		<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'articles/post', get_post_type() ) ?>

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
