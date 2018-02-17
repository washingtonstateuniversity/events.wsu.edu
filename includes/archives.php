<?php

namespace WSU\Events\Archives;

add_filter( 'pre_get_posts', 'WSU\Events\Archives\filter_query', 11 );
add_filter( 'register_taxonomy_args', 'WSU\Events\Archives\taxonomy_rewrites', 10, 2 );

/**
 * Filter the query for all archive views.
 *
 * @since 0.1.0
 *
 * @param \WP_Query $wp_query
 */
function filter_query( $wp_query ) {

	if ( is_admin() || ! $wp_query->is_main_query() || ! is_archive() ) {
		return;
	}

	date_default_timezone_set( 'America/Los_Angeles' );

	$today = date( 'Y-m-d 00:00:00' );

	if ( $wp_query->is_post_type_archive( 'event' ) ) {
		$tomorrow = date( 'Y-m-d 00:00:00', strtotime( $today . ' +1 day' ) );

		$wp_query->set( 'meta_query', array(
			'relation' => 'AND',
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $today,
				'compare' => '>=',
				'type' => 'DATETIME',
			),
			'wsuwp_tomorrow_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $tomorrow,
				'compare' => '<',
				'type' => 'DATETIME',
			),
		) );
	} else {
		$wp_query->set( 'meta_query', array(
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $today,
				'compare' => '>=',
				'type' => 'DATETIME',
			),
		) );
	}

	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
	$wp_query->set( 'posts_per_page', '100' );
}

/**
 * (Re)sets the `rewrite` argument for taxonomies registered for events.
 *
 * @since 0.1.0
 *
 * @param array  $args     Arguments for registering a taxonomy.
 * @param string $taxonomy Taxonomy key.
 *
 * @return array
 */
function taxonomy_rewrites( $args, $taxonomy ) {
	if ( 'event-type' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'type',
			'with_front' => false,
		);
	}

	if ( 'event-category' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'category',
			'with_front' => false,
		);
	}

	if ( 'event-tag' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'tag',
			'with_front' => false,
		);
	}

	if ( 'wsuwp_university_category' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'university-category',
			'with_front' => false,
		);
	}

	if ( 'wsuwp_university_location' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'location',
			'with_front' => false,
		);
	}

	if ( 'wsuwp_university_org' === $taxonomy ) {
		$args['rewrite'] = array(
			'slug' => 'organization',
			'with_front' => false,
		);
	}

	return $args;
}
