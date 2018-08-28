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
	// Bail if this isn't the query we're looking for.
	if ( is_admin() || ! $wp_query->is_main_query() || ! is_archive() ) {
		return;
	}

	// Ensure that tag archives display the event post type.
	if ( is_tag() ) {
		$wp_query->set( 'post_type', 'event' );
	}

	// Get the start date for the `BETWEEN` query.
	$current_date = date_i18n( 'Y-m-d' );

	// Override the start date to match the current view if applicable.
	if ( $wp_query->is_date() ) {
		$year = get_query_var( 'year' );
		$month = $wp_query->query['monthnum'];
		$day = ( $wp_query->is_day() ) ? get_query_var( 'day' ) : 01;
		$current_date = $year . '-' . $month . '-' . $day;
	} elseif ( $wp_query->is_tax() || $wp_query->is_tag() ) {
		$current_date = date_i18n( 'Y-m' ) . '-01';
	}

	// Set a custom query var with date information for later use.
	set_query_var( 'wsuwp_event_date', $current_date );

	// Get the end date for the `BETWEEN` query.
	if ( ( is_post_type_archive( 'event' ) && ! is_month() ) || is_day() ) {
		$next_date = date_i18n( 'Y-m-d', strtotime( $current_date . ' +1 day' ) );
	} else {
		$next_date = date_i18n( 'Y-m', strtotime( $current_date . ' +1 month' ) ) . '-01';
	}

	// Append a time to the start and end date.
	$current_date_time = $current_date . ' 00:00:00';
	$next_date_time = $next_date . ' 00:00:00';

	// Set the query args to find events between the start and end dates.
	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
	$wp_query->set( 'posts_per_page', '100' );
	$wp_query->set( 'meta_query', array(
		'wsuwp_event_start_date' => array(
			'key' => 'wp_event_calendar_date_time',
			'value' => array( $current_date_time, $next_date_time ),
			'compare' => 'BETWEEN',
			'type' => 'DATETIME',
		),
	) );

	// Prevent the default publish date query.
	$wp_query->set( 'year', 0 );
	$wp_query->set( 'monthnum', 0 );
	$wp_query->set( 'day', 0 );
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

	if ( is_tax() || is_tag() ) {
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
 * Gets paging URLs and labels for the previous and next month with events.
 * This is meant to be used for all views except day archives and `/event/`.
 *
 * @since 0.4.3
 *
 * @param string $view_date  The date of the current archive view.
 * @param string $base_url   The base URI to add paths to.
 * @param array  $query_args Default query args for finding adjacent events.
 * @param array  $link_data  Default link data to populate based on adjacent events.
 *
 * @return array
 */
function get_pagination_link_data( $view_date, $base_url, $query_args, $link_data ) {
	$current_view_date = date_i18n( 'Y-m-01 00:00:00', strtotime( $view_date ) );

	// Set up base query arguments.
	$query_args['meta_query']['wsuwp_event_start_date']['value'] = $current_view_date;

	// Set up additional query arguments for tag and taxonomy archives.
	// Override the base url, while we're at it.
	if ( is_tag() ) {
		$term = get_query_var( 'tag' );
		$base_url = get_term_link( $term, 'post_tag' );
		$query_args['tag'] = $term;
	} elseif ( is_tax() ) {
		$term = get_query_var( 'term' );
		$taxonomy = get_query_var( 'taxonomy' );
		$base_url = get_term_link( $term, $taxonomy );
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $term,
			),
		);
	}

	// Query for a previous adjacent event.
	$previous_event = get_posts( $query_args );

	// Set up the previous link label and URL if a previous adjacent event was found.
	if ( 0 !== count( $previous_event ) ) {
		$start_date = get_post_meta( $previous_event[0], 'wp_event_calendar_date_time', true );
		$path = date_i18n( 'Y/m/', strtotime( $start_date ) );
		$this_month = date_i18n( 'Y/m/' ) === $path;
		$link_data['previous'] = ( $this_month && ( is_tag() || is_tax() ) ) ? $base_url : $base_url . $path;
		$link_data['previous_label'] = date_i18n( 'F Y', strtotime( $start_date ) );
	}

	// Adjust query arguments to find the next adjacent event.
	$next_date = date_i18n( 'Y-m-01 00:00:00', strtotime( $current_view_date . ' + 1 month' ) );
	$query_args['order'] = 'ASC';
	$query_args['meta_query']['wsuwp_event_start_date']['compare'] = '>=';
	$query_args['meta_query']['wsuwp_event_start_date']['value'] = $next_date;

	// Query for the next upcoming adjacent event.
	$next_event = get_posts( $query_args );

	// Set up the next link label and URL if an upcoming adjacent event was found.
	if ( 0 !== count( $next_event ) ) {
		$start_date = get_post_meta( $next_event[0], 'wp_event_calendar_date_time' );

		foreach ( $start_date as $start ) {
			if ( $start < $next_date ) {
				continue;
			}

			$next_start_date = strtotime( $start );

			break;
		}

		$path = date_i18n( 'Y/m/', $next_start_date );
		$this_month = date_i18n( 'Y/m/' ) === $path;
		$link_data['next'] = ( $this_month && ( is_tag() || is_tax() ) ) ? $base_url : $base_url . $path;
		$link_data['next_label'] = date_i18n( 'F Y', $next_start_date );
	}

	return $link_data;
}

/**
 * Gets paging URLs and labels for the previous and next day with events.
 *
 * @since 0.4.3
 *
 * @param string $view_date  The date of the current archive view.
 * @param string $base_url   The base URI to add paths to.
 * @param array  $query_args Default query args for finding adjacent events.
 * @param array  $link_data  Default link data to populate based on adjacent events.
 *
 * @return array
 */
function get_day_pagination_link_data( $view_date, $base_url, $query_args, $link_data ) {
	$current_view_date = date_i18n( 'Y-m-d 00:00:00', strtotime( $view_date ) );

	// Set up base query arguments.
	$query_args['meta_query']['wsuwp_event_start_date']['value'] = $current_view_date;

	// Query for a previous adjacent event.
	$previous_event = get_posts( $query_args );

	// Set up the previous link label and URL if a previous adjacent event was found.
	if ( 0 !== count( $previous_event ) ) {
		$start_date = strtotime( get_post_meta( $previous_event[0], 'wp_event_calendar_date_time', true ) );
		$path = date_i18n( 'Y/m/d/', $start_date );
		$today = date_i18n( 'Y/m/d/' ) === $path;
		$link_data['previous'] = ( $today ) ? $base_url : $base_url . $path;
		$link_data['previous_label'] = ( $today ) ? 'Today’s events' : date_i18n( 'F d', $start_date );
	}

	// Adjust query arguments to find the next adjacent event.
	$next_date = date_i18n( 'Y-m-d 00:00:00', strtotime( $current_view_date . ' + 1 day' ) );
	$query_args['order'] = 'ASC';
	$query_args['meta_query']['wsuwp_event_start_date']['compare'] = '>=';
	$query_args['meta_query']['wsuwp_event_start_date']['value'] = $next_date;

	// Query for the next upcoming adjacent event.
	$next_event = get_posts( $query_args );

	// Set up the next link label and URL if an upcoming adjacent event was found.
	if ( 0 !== count( $next_event ) ) {
		$start_date = get_post_meta( $next_event[0], 'wp_event_calendar_date_time' );

		foreach ( $start_date as $start ) {
			if ( $start < $next_date ) {
				continue;
			}

			$next_start_date = strtotime( $start );

			break;
		}

		$path = date_i18n( 'Y/m/d', $next_start_date );
		$today = date_i18n( 'Y/m/d' ) === $path;
		$link_data['next'] = ( $today ) ? $base_url : $base_url . $path;
		$link_data['next_label'] = ( $today ) ? 'Today’s events' : date_i18n( 'F j', $next_start_date );
	}

	return $link_data;
}

/**
 * Generate the URLs and labels used to view previous and next date archives.
 *
 * @since 0.2.0
 * @since 0.3.0 Refactored to skip over empty views.
 * @since 0.4.3 Refactored to separate day handling from all other handling.
 *
 * @return array
 */
function get_pagination_links() {
	$view_date = ( is_date() ) ? get_query_var( 'wsuwp_event_date' ) : date_i18n( 'Y-m-d 00:00:00' );
	$base_url = get_post_type_archive_link( 'event' );
	$base_link_data = array(
		'previous' => false,
		'previous_label' => false,
		'next' => false,
		'next_label' => false,
	);
	$base_query_args = array(
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
				'type' => 'DATETIME',
			),
		),
	);

	if ( ( is_post_type_archive( 'event' ) && ! is_month() ) || is_day() ) {
		$pagination = get_day_pagination_link_data( $view_date, $base_url, $base_query_args, $base_link_data );
	} else {
		$pagination = get_pagination_link_data( $view_date, $base_url, $base_query_args, $base_link_data );
	}

	return $pagination;
}
