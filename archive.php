<?php

get_header();

global $is_today;

$is_today = false;
?>
<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<header class="page-header">
		<h1><?php single_term_title(); ?></h1>
	</header>

	<section class="row single">

		<div class="column one deck deck-river">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'components/event/content' ) ?>

			<?php endwhile; ?>

		</div>

	</section>

	<footer class="main-footer">

	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
