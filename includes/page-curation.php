<?php

namespace WSU\Events\Page_Curation;

add_filter( 'pre_get_posts', __NAMESPACE__ . '\\filter_front_page_featured_events_query', 11 );

/**
 * Filter the query for the front page featured events.
 *
 * @since 0.1.0
 *
 * @param \WP_Query $wp_query
 */
function filter_front_page_featured_events_query( $wp_query ) {

	// Bail if the `wsuwp_events_featured` argument is not set.
	if ( empty( $wp_query->query['wsuwp_events_featured'] ) ) {
		return;
	}

	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
	$wp_query->set( 'meta_query', array(
		'wsuwp_event_start_date' => array(
			'key' => 'wp_event_calendar_date_time',
			'compare' => 'EXISTS',
			'type' => 'DATETIME',
		),
		'wsuwp_event_end_date' => array(
			'key' => 'wp_event_calendar_end_date_time',
			'value' => current_time( 'mysql' ),
			'compare' => '>',
			'type' => 'DATETIME',
		),
	) );
}

/**
 * Retrieve the current featured events.
 *
 * @since 0.1.0
 *
 * @return array|\WP_Query
 */
function get_featured_events( $output = 'ids' ) {
	$args = array(
		'post_type' => 'event',
		'wsuwp_events_featured' => true,
	);

	$featured_event_ids = get_option( 'featured_events', false );

	if ( false !== $featured_event_ids && ! empty( $featured_event_ids ) && ! is_object( $featured_event_ids ) ) {
		$featured_event_ids = explode( ',', $featured_event_ids );
		$args['posts_per_page'] = count( $featured_event_ids );
		$args['post__in'] = $featured_event_ids;
	} else {
		// Force a return of zero results if no featured events are set.
		$args['post__in'] = array( 0 );
	}

	if ( 'ids' === $output ) {
		$args['fields'] = 'ids';
	}

	$featured_query = new \WP_Query( $args );

	// Stop here if this function is being called from the Customizer.
	if ( 'ids' === $output ) {
		wp_reset_postdata();

		return $featured_query;
	}

	// Fall back to the next eight upcoming events for display on the home page.
	if ( 0 === $featured_query->found_posts ) {
		$args['post__in'] = array();
		$args['posts_per_page'] = 8;
	}

	$featured_query = new \WP_Query( $args );

	wp_reset_postdata();

	return $featured_query;
}
