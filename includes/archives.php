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

	$today = date_i18n( 'Y-m-d 00:00:00' );

	if ( $wp_query->is_post_type_archive( 'event' ) || $wp_query->is_date() ) {
		if ( $wp_query->is_date() ) {
			$year = get_query_var( 'year' );
			$month = $wp_query->query['monthnum'];
			$day = ( is_day() ) ? get_query_var( 'day' ) : 01;
			$current_day = $year . '-' . $month . '-' . $day . ' 00:00:00';

			// Set a custom query var with date information for later use.
			set_query_var( 'wsuwp_event_date', $year . '-' . $month . '-' . $day );

			// Prevent the default publish date query.
			$wp_query->set( 'year', 0 );
			$wp_query->set( 'monthnum', 0 );
			$wp_query->set( 'day', 0 );
		} else {
			$current_day = $today;

			set_query_var( 'wsuwp_event_date', date_i18n( 'Y-m-d' ) );
		}

		$next_date = date_i18n( 'Y-m-d 00:00:00', strtotime( $current_day . ' +1 day' ) );

		if ( is_month() ) {
			$next_date = date_i18n( 'Y-m-d 00:00:00', strtotime( $current_day . ' +1 month' ) );
		}

		$wp_query->set( 'meta_query', array(
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $current_day,
				'compare' => '>=',
				'type' => 'DATETIME',
			),
			'wsuwp_next_day_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $next_date,
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
 * @since 0.2.0
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

	$day_rule = 'event/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})';
	$month_rule = 'event/([0-9]{4})/([0-9]{1,2})';
	$query_event = 'index.php?post_type=event';
	$query_year = '&year=' . $wp_rewrite->preg_index( 1 );
	$query_month = '&monthnum=' . $wp_rewrite->preg_index( 2 );
	$query_day = '&day=' . $wp_rewrite->preg_index( 3 );

	$rules[ $day_rule . '/?$' ] = $query_event . $query_year . $query_month . $query_day;
	$rules[ $month_rule . '/?$' ] = $query_event . $query_year . $query_month;

	$taxonomies = get_object_taxonomies( 'event', 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		$tax_day_rule = $taxonomy->rewrite['slug'] . '/([^/]+)/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})';
		$tax_month_rule = $taxonomy->rewrite['slug'] . '/([^/]+)/([0-9]{4})/([0-9]{1,2})';
		$query_tax = 'index.php?' . $taxonomy->query_var . '=' . $wp_rewrite->preg_index( 1 );
		$query_year = '&year=' . $wp_rewrite->preg_index( 2 );
		$query_month = '&monthnum=' . $wp_rewrite->preg_index( 3 );
		$query_day = '&day=' . $wp_rewrite->preg_index( 4 );

		$rules[ $tax_day_rule . '/?$' ] = $query_tax . $query_year . $query_month . $query_day;
		$rules[ $tax_month_rule . '/?$' ] = $query_tax . $query_year . $query_month;
	}

	$wp_rewrite->rules = $rules + $wp_rewrite->rules;

	return $wp_rewrite;
}

/**
 * Filter the document title used for daily archives.
 *
 * @since 0.2.0
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

	if ( is_date() ) {
		$date = date_i18n( 'F j, Y', strtotime( get_query_var( 'wsuwp_event_date' ) ) );

		if ( is_month() ) {
			$date = date_i18n( 'F Y', strtotime( get_query_var( 'wsuwp_event_date' ) ) );
		}

		if ( date_i18n( 'F j, Y' ) !== $date ) {
			$title .= ' ' . $date;
		}
	}

	if ( is_post_type_archive( 'event' ) && ( ! is_day() || date_i18n( 'F j, Y' ) === $date ) && ! is_month() ) {
		$title = 'What’s happening today';
	}

	$title .= ' | ' . $site_part . $global_part;

	return $title;
}

/**
 * Generate the URLs used to view previous and next date archives.
 *
 * @since 0.2.0
 * @since 0.3.0 Refactored to skip over empty views.
 *
 * @return array
 */
function get_pagination_urls() {
	$current_view_date = date_i18n( 'Y-m-d 00:00:00' );
	$base_url = get_post_type_archive_link( 'event' );
	$previous_url = false;
	$next_url = false;
	$next_label = false;

	if ( is_date() ) {
		$view_date = get_query_var( 'wsuwp_event_date' );
		$current_view_date = date_i18n( 'Y-m-d 00:00:00', strtotime( $view_date ) );
	}

	// Set up base query arguments.
	$adjacent_event_query_args = array(
		'post_type' => 'event',
		'post_status' => array( 'publish', 'passed' ),
		'posts_per_page' => 1,
		'fields' => 'ids',
		'orderby' => 'wsuwp_event_start_date',
		'order' => 'DESC',
		'meta_query' => array(
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'compare' => '<',
				'value' => $current_view_date,
				'type' => 'DATETIME',
			),
		),
	);

	// Override `$base_url` and add query arguments for taxonomy views.
	if ( is_tax() ) {
		$term = get_query_var( 'term' );
		$taxonomy = get_query_var( 'taxonomy' );
		$base_url = get_term_link( $term, $taxonomy );
		$adjacent_event_query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $term,
			),
		);
	}

	// Query for the previous adjacent event.
	$previous_event = get_posts( $adjacent_event_query_args );

	// Set up the previous link URL if a previous adjacent event was found.
	if ( 0 !== count( $previous_event ) ) {
		$start_date = get_post_meta( $previous_event[0], 'wp_event_calendar_date_time', true );
		$path = date_i18n( 'Y/m/d/', strtotime( $start_date ) );
		$previous_url = $base_url . $path;
	}

	/**
	 * Build out the next link URL and label.
	 *
	 * Paging forward is available for, in order of appearance in the condition:
	 *   1) the `event` post type archive view;
	 *   2) day archive views;
	 *   3) taxonomy views that are also day archive views.
	 *
	 * ("Normal" taxonomy archive views display all upcoming events already).
	 */
	if ( is_post_type_archive( 'event' ) || is_day() || ( ! is_tax() || ( is_tax() && is_day() ) ) ) {

		// Adjust query arguments to find the next adjacent event.
		$next_day = date_i18n( 'Y-m-d 00:00:00', strtotime( $current_view_date . ' + 1 days' ) );
		$adjacent_event_query_args['order'] = 'ASC';
		$adjacent_event_query_args['meta_query']['wsuwp_event_start_date']['compare'] = '>=';
		$adjacent_event_query_args['meta_query']['wsuwp_event_start_date']['value'] = $next_day;

		// Query for the next adjacent event.
		$next_event = get_posts( $adjacent_event_query_args );

		// Set up the next link URL if an upcoming adjacent event was found.
		if ( 0 !== count( $next_event ) ) {
			if ( date_i18n( 'Y-m-d 00:00:00' ) === $next_day ) {
				$next_url = $base_url;
				$next_label = ( is_tax() ) ? 'Upcoming events' : 'Today’s events';
			} else {
				$start_date = get_post_meta( $next_event[0], 'wp_event_calendar_date_time' );

				foreach ( $start_date as $start ) {
					if ( $start === $current_view_date ) {
						continue;
					}

					$path = date_i18n( 'Y/m/d/', strtotime( $start ) );

					break;
				}

				$next_url = $base_url . $path;
				$next_label = ( $next_day > date_i18n( 'Y-m-d 00:00:00' ) ) ? 'Upcoming events' : 'Next events';
			}
		} elseif ( is_tax() && is_day() ) {
			$next_url = $base_url;
			$next_label = 'Upcoming events';
		}
	}

	if ( is_month() ) {
		$previous_month = date_i18n( 'Y/m/', strtotime( $current_view_date . ' - 1 month' ) );
		$previous_label = ( date_i18n( 'Y/m/' ) === $previous_month ) ? 'This month' : 'Previous month';
		$next_month = date_i18n( 'Y/m/', strtotime( $current_view_date . ' + 1 month' ) );
		$next_label = ( date_i18n( 'Y/m/' ) === $next_month ) ? 'This month' : 'Next month';

		return array(
			'previous' => $base_url . $previous_month,
			'previous_label' => $previous_label,
			'next' => $base_url . $next_month,
			'next_label' => $next_label,
		);
	}

	return array(
		'previous' => $previous_url,
		'previous_label' => 'Previous events',
		'next' => $next_url,
		'next_label' => $next_label,
	);
}
