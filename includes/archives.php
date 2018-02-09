<?php

namespace WSU\Events\Archives;

add_filter( 'pre_get_posts', 'WSU\Events\Archives\filter_query', 11 );

/**
 * Filter the query for all archive views.
 *
 * @since 0.0.1
 *
 * @param \WP_Query $wp_query
 */
function filter_query( $wp_query ) {

	if ( is_admin() || ! $wp_query->is_main_query() || ! is_archive() ) {
		return;
	}

	date_default_timezone_set( 'America/Los_Angeles' );

	$wp_query->set( 'meta_query', array(
		'wsuwp_event_start_date' => array(
			'key' => 'wp_event_calendar_date_time',
			'value' => date( 'Y-m-d 00:00:00' ),
			'compare' => '>=',
			'type' => 'DATETIME',
		),
	) );

	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
}
