<?php $data = wsuwp_event_get_data( get_the_ID() ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="card-header">
		<hgroup>
			<?php if ( is_singular() ) { ?>
			<h1 class="card-title"><?php the_title(); ?></h1>
			<?php } else { ?>
			<h2 class="card-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>
			<?php } ?>
		</hgroup>
	</header>

	<div class="card-body">

		<?php if ( is_singular() ) { ?>
		<div class="card-description">
			<?php the_content(); ?>
		</div>
		<?php } ?>

	</div>

	<footer class="card-footer">
	</footer>

</article>
