<?php

get_header();

?>
<main id="wsuwp-main">

	<?php get_template_part( 'parts/headers' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'components/event/card', 'single' ) ?>

	<?php endwhile; ?>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();
