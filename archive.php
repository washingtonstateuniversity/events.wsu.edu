<?php

get_header();

global $is_today;

$is_today = false;
?>
<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<header class="page-header">
		<h1><?php
		if ( is_tax() ) {
			single_term_title();
		} elseif ( is_post_type_archive( 'event' ) ) {
			echo 'What’s happening today';
		}
		?></h1>
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
