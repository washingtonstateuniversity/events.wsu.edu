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

/**
 * Generate the URLs used to view previous and next date archives.
 *
 * @since 0.1.1
 *
 * @return array
 */
function get_pagination_urls() {
	if ( is_date() ) {
		$year = get_query_var( 'year' );
		$month = get_query_var( 'monthnum' );
		$day = get_query_var( 'day' );
		$date = $year . '-' . $month . '-' . $day . ' 00:00:00';
	} else {
		$date = date( 'Y-m-d 00:00:00' );
	}

	$days = 1;

	while ( $days <= 9 ) {
		$previous_day = date( 'd', strtotime( $date ) - ( DAY_IN_SECONDS * $days ) );
		$previous_month = date( 'm', strtotime( $date ) - ( DAY_IN_SECONDS * $days ) );
		$previous_year = date( 'Y', strtotime( $date ) - ( DAY_IN_SECONDS * $days ) );

		$previous_date = $previous_year . '-' . $previous_month . '-' . $previous_day . ' 00:00:00';

		$previous_check = get_posts( array(
			'post_type' => 'event',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'wp_event_calendar_date_time',
					'value' => $previous_date,
					'compare' => '>=',
					'type' => 'DATETIME',
				),
			),
		) );

		if ( 0 < count( $previous_check ) ) {
			break;
		}

		$days++;
	}

	$days = 1;

	while ( $days <= 9 ) {
		if ( is_post_type_archive( 'event' ) && ! is_day() ) {
			$next_check = array();
			break;
		}

		if ( strtotime( $date ) > ( time() - DAY_IN_SECONDS ) ) {
			$next_check = array();
			break;
		}

		$next_day = date( 'd', strtotime( $date ) + ( DAY_IN_SECONDS * $days ) );
		$next_month = date( 'm', strtotime( $date ) + ( DAY_IN_SECONDS * $days ) );
		$next_year = date( 'Y', strtotime( $date ) + ( DAY_IN_SECONDS * $days ) );

		$next_date = $next_year . '-' . $next_month . '-' . $next_day . ' 00:00:00';

		$next_check = get_posts( array(
			'post_type' => 'event',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'wp_event_calendar_date_time',
					'value' => $next_date,
					'compare' => '>=',
					'type' => 'DATETIME',
				),
			),
		) );

		if ( 0 < count( $next_check ) ) {
			break;
		}

		$days++;
	}

	if ( is_tax() ) {
		$term = get_query_var( 'term' );
		$taxonomy = get_query_var( 'taxonomy' );
		$base_url = get_term_link( $term, $taxonomy );
	} else {
		$base_url = get_post_type_archive_link( 'event' );
	}

	if ( 0 !== count( $previous_check ) ) {
		$previous_url = $base_url . $previous_year . '/' . $previous_month . '/' . $previous_day . '/';
	} else {
		$previous_url = false;
	}

	if ( 0 !== count( $next_check ) ) {
		$next_url = $base_url . $next_year . '/' . $next_month . '/' . $next_day . '/';
	} else {
		$next_url = false;
	}

	return array(
		'previous' => $previous_url,
		'next' => $next_url,
	);
}
