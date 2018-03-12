<?php /* Template Name: Search */

$search_results = WSU\Events\Search\get_elastic_response( get_query_var( 'q' ) );

get_header();

?>

<main class="spine-blank-template">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php get_template_part( 'parts/headers' ); ?>

	<header class="page-header">

		<h1>Search Results</h1>

		<?php
		if ( '' !== get_query_var( 'q' ) ) {

			if ( empty( $search_results ) ) {
				echo '<p>No search results found</p>';
			} else {
				$count = count( $search_results );
				$plurality = ( 1 === $count ) ? '' : 's';
				echo '<p>' . esc_html( $count . 'result' . $plurality . ' found' ) . '</p>';
			}
		} else {
			echo '<p>Use the form below to search</p>';
		}
		?>
	</header>

	<section class="row single">

		<div class="column one deck deck-river">

		<?php get_template_part( 'parts/search-form' ); ?>

			<?php foreach ( $search_results as $search_result ) { ?>
			<article class="card card--event">
				<header class="card-title">
					<a href="<?php echo esc_url( $search_result->_source->url ); ?>"><?php echo esc_html( $search_result->_source->title ); ?></a>
				</header>
				<span class="visible-url"><?php echo esc_url( $search_result->_source->url ); ?></span>
				<div class="card-excerpt visible-content">
					<?php
					$visible_content = WSU\Events\Search\filter_elastic_content( $search_result->_source->content );

					echo wp_kses_post( $visible_content );
					?>
				</div>
			</article>
			<?php } ?>

		</div>

	</section>

<?php
endwhile;
endif;

get_template_part( 'parts/footers' );

?>

</main>

<?php get_footer();
