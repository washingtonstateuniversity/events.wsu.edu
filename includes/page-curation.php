<?php

namespace WSU\Events\Page_Curation;

add_filter( 'pre_get_posts', 'WSU\Events\Page_Curation\filter_front_page_featured_events_query', 11 );

/**
 * Filter the query for the front page featured events.
 *
 * @since 0.0.1
 *
 * @param \WP_Query $wp_query
 */
function filter_front_page_featured_events_query( $wp_query ) {

	// Bail if the `wsuwp_events_featured` argument is not set.
	if ( empty( $wp_query->query['wsuwp_events_featured'] ) ) {
		return;
	}

	date_default_timezone_set( 'America/Los_Angeles' );

	// Set `orderby` and `meta_query` arguments for events curated through Customizer.
	if ( ! empty( $wp_query->query['post__in'] ) ) {
		$wp_query->set( 'orderby', 'post__in' );
		$wp_query->set( 'meta_query', array(
			array(
				'key' => 'wp_event_calendar_end_date_time',
				'value' => date( 'Y-m-d H:i:s' ),
				'compare' => '>',
				'type' => 'DATETIME',
			),
		) );

		return;
	}

	// Set `meta_query` and `orderby` arguments for featured status events fallback.
	if ( true === $wp_query->query['wsuwp_events_featured_status_fallback'] ) {
		$wp_query->set( 'meta_query', array(
			'relation' => 'AND',
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'compare' => 'EXISTS',
				'type' => 'DATETIME',
			),
			'wsuwp_event_end_date' => array(
				'key' => 'wp_event_calendar_end_date_time',
				'value' => date( 'Y-m-d H:i:s' ),
				'compare' => '>',
				'type' => 'DATETIME',
			),
			'wsuwp_event_featured' => array(
				'key' => '_wsuwp_event_featured',
				'value' => 'yes',
			),
		) );

		$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
		$wp_query->set( 'order', 'ASC' );

		return;
	}

	// Set `meta_query` and `orderby` arguments for upcoming events fallback.
	$wp_query->set( 'meta_query', array(
		'relation' => 'AND',
		'wsuwp_event_start_date' => array(
			'key' => 'wp_event_calendar_date_time',
			'compare' => 'EXISTS',
			'type' => 'DATETIME',
		),
		'wsuwp_event_end_date' => array(
			'key' => 'wp_event_calendar_end_date_time',
			'value' => date( 'Y-m-d H:i:s' ),
			'compare' => '>',
			'type' => 'DATETIME',
		),
	) );

	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
}

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
		'wsuwp_events_featured' => true,
	);

	$featured_event_ids = get_option( 'featured_events', false );

	if ( false === $featured_event_ids || empty( $featured_event_ids ) || is_object( $featured_event_ids ) ) {
		$args['posts_per_page'] = 8;
		$args['wsuwp_events_featured_status_fallback'] = true;
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
		return $featured_query;

	}

	// If the events curated through Customizer have all passed,
	// query for events with featured status.
	if ( false !== $featured_event_ids && ! empty( $featured_event_ids ) && 0 === $featured_query->found_posts ) {
		$args['post__in'] = false;
		$args['posts_per_page'] = 8;
		$args['wsuwp_events_featured_status_fallback'] = true;
		$featured_query = new \WP_Query( $args );
	}

	// If all events with featured status have also passed,
	// query for upcoming events.
	if ( 0 === $featured_query->found_posts ) {
		$args['wsuwp_events_featured_status_fallback'] = false;
		$featured_query = new \WP_Query( $args );
	}

	return $featured_query;
}
