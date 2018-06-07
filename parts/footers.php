<?php if ( ! is_front_page() ) { ?>
<section class="row single today-footer">

	<?php get_template_part( 'parts/today' ); ?>

</section>
<?php } ?>

<footer class="row quarters gutter site-footer">

	<div class="column one">
		<?php get_template_part( 'parts/calendar' ); ?>
	</div>

	<div class="column two">
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=event' ) ); ?>" class="button gray">Submit an event</a>
	</div>

	<div class="column three">
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="button gray">Contact us</a>
	</div>

	<div class="column four">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title gray"><?php bloginfo( 'name' ); ?></a>
	</div>

</footer>
