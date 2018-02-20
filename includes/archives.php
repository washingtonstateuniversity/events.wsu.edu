<?php

namespace WSU\Events\Archives;

add_filter( 'pre_get_posts', 'WSU\Events\Archives\filter_query', 11 );
add_filter( 'register_taxonomy_args', 'WSU\Events\Archives\taxonomy_rewrites', 10, 2 );
add_action( 'generate_rewrite_rules', 'WSU\Events\Archives\generate_date_archive_rewrite_rules', 10, 1 );
add_filter( 'spine_get_title', 'WSU\Events\Archives\filter_page_title', 11, 3 );

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

	if ( $wp_query->is_post_type_archive( 'event' ) || $wp_query->is_date() ) {
		if ( $wp_query->is_date() ) {
			$year = get_query_var( 'year' );
			$month = $wp_query->query['monthnum'];
			$day = get_query_var( 'day' );
			$current_day = $year . '-' . $month . '-' . $day . ' 00:00:00';

			// Set a custom query var with date information for later use.
			set_query_var( 'wsuwp_event_date', $year . '-' . $month . '-' . $day );

			// Prevent the default publish date query.
			$wp_query->set( 'year', 0 );
			$wp_query->set( 'monthnum', 0 );
			$wp_query->set( 'day', 0 );
		} else {
			$current_day = $today;
		}

		$next_day = date( 'Y-m-d 00:00:00', strtotime( $current_day . ' +1 day' ) );

		$wp_query->set( 'meta_query', array(
			'relation' => 'AND',
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $current_day,
				'compare' => '>=',
				'type' => 'DATETIME',
			),
			'wsuwp_next_day_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $next_day,
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
 * Generate day based archive rewrite rules.
 *
 * @since 0.1.1
 *
 * @param \WP_Rewrite $wp_rewrite
 *
 * @return \WP_Rewrite
 */
function generate_date_archive_rewrite_rules( $wp_rewrite ) {
	$rules = array();

	$post_type = get_post_type_object( 'event' );

	if ( false === $post_type->has_archive ) {
		return $wp_rewrite;
	}

	$rule = 'event/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})';
	$query = 'index.php?post_type=event';
	$query .= '&year=' . $wp_rewrite->preg_index( 1 );
	$query .= '&monthnum=' . $wp_rewrite->preg_index( 2 );
	$query .= '&day=' . $wp_rewrite->preg_index( 3 );

	$rules[ $rule . '/?$' ] = $query;

	$taxonomies = get_object_taxonomies( 'event', 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		$rule = $taxonomy->rewrite['slug'] . '/([^/]+)/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})';
		$query = 'index.php?' . $taxonomy->query_var . '=' . $wp_rewrite->preg_index( 1 );
		$query .= '&year=' . $wp_rewrite->preg_index( 2 );
		$query .= '&monthnum=' . $wp_rewrite->preg_index( 3 );
		$query .= '&day=' . $wp_rewrite->preg_index( 4 );

		$rules[ $rule . '/?$' ] = $query;
	}

	$wp_rewrite->rules = $rules + $wp_rewrite->rules;

	return $wp_rewrite;
}

/**
 * Filter the document title used for daily archives.
 *
 * @since 0.1.1
 *
 * @param string $title
 * @param string $site_part
 * @param string $global_part
 *
 * @return string
 */
function filter_page_title( $title, $site_part, $global_part ) {
	if ( ! is_archive() ) {
		return $title;
	}

	$title = '';

	if ( is_tax() ) {
		$title = single_term_title( '', false );
	}

	if ( is_day() ) {
		$date = date( 'F j, Y', strtotime( get_query_var( 'wsuwp_event_date' ) ) );

		if ( date( 'F j, Y' ) !== $date ) {
			$title .= ' ' . $date;
		}
	}

	if ( is_post_type_archive( 'event' ) && ( ! is_day() || date( 'F j, Y' ) === $date ) ) {
		$title = 'What’s happening today';
	}

	$title .= ' | ' . $site_part . $global_part;

	return $title;
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
		$date = get_query_var( 'wsuwp_event_date' ) . ' 00:00:00';
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
		// Taxonomy archives for the current day already show all upcoming events.
		if ( is_tax() && ! is_day() ) {
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
		if ( date( 'Y-m-d 00:00:00' ) === $next_date ) {
			$next_url = $base_url;
			$next_label = ( is_tax() ) ? 'Upcoming events' : 'Today’s events';
		} else {
			$next_url = $base_url . $next_year . '/' . $next_month . '/' . $next_day . '/';
			$next_label = ( $next_date > date( 'Y-m-d 00:00:00' ) ) ? 'Upcoming events' : 'Next events';
		}
	} else {
		$next_url = false;
		$next_label = false;
	}

	return array(
		'previous' => $previous_url,
		'next' => $next_url,
		'next_label' => $next_label,
	);
}
