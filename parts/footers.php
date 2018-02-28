<?php if ( ! is_front_page() ) { ?>
<section class="row single today-footer">

	<div class="column one today">

		<?php get_template_part( 'parts/today' ); ?>

	</div>

</section>
<?php } ?>

<footer class="row quarters gutter site-footer">

	<div class="column one">
		<?php WSU\Events\Calendar\get_calendar( date( 'm' ), date( 'Y' ) ); ?>
	</div>

	<div class="column two">
		<a href="#" class="button gray">Submit an event</a>
	</div>

	<div class="column three">
		<a href="#" class="button gray">Contact us</a>
	</div>

	<div class="column four">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title gray"><?php bloginfo( 'name' ); ?></a>
	</div>

</footer>
