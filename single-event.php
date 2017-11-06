<?php get_header(); ?>

<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row side-right gutter pad-ends">
		<div class="column one">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'articles/post', get_post_type() );
			endwhile;
			?>
		</div>
		<div class="column two">
			<?php get_sidebar(); ?>
		</div>
	</section>

	<footer class="main-footer">
		<section class="row halves pager prevnext gutter pad-ends">
			<div class="column one">
				<?php previous_post_link(); ?>
			</div>
			<div class="column two">
				<?php next_post_link(); ?>
			</div>
		</section>
	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>

<?php get_footer();
