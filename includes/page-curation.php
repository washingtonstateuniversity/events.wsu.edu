<?php

namespace WSU\Events\Page_Curation;

/**
 * Retrieve the current featured events displayed on the front page.
 *
 * @since 0.0.1
 *
 * @return array|\WP_Query
 */
function get_featured_events( $output = 'ids' ) {
	$args = array(
		'post_type' => 'event',
	);

	$featured_event_ids = get_option( 'featured_events', false );

	if ( false === $featured_event_ids || empty( $featured_event_ids ) || is_object( $featured_event_ids ) ) {
		$args['posts_per_page'] = 8;
		$args['meta_query'] = array(
			array(
				'key' => '_wsuwp_event_featured',
				'value' => 'yes',
			),
		);
	} else {
		$featured_event_ids = explode( ',', $featured_event_ids );
		$args['posts_per_page'] = count( $featured_event_ids );
		$args['post__in'] = $featured_event_ids;
	}

	if ( 'ids' === $output ) {
		$args['fields'] = 'ids';
	}

	$featured_query = new \WP_Query( $args );

	if ( 'ids' === $output ) {
		wp_reset_postdata();
	}

	return $featured_query;
}
