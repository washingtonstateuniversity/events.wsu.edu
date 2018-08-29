<?php

namespace WSU\Events\Search;

add_filter( 'query_vars', __NAMESPACE__ . '\\filter_query_variable' );
add_action( 'template_redirect', __NAMESPACE__ . '\\redirect_wp_default_search' );
add_filter( 'wsuwp_search_post_types', __NAMESPACE__ . '\\filter_post_types' );
add_filter( 'wsuwp_search_post_data', __NAMESPACE__ . '\\search_data', 10, 2 );

/**
 * Redirect requests to the default WordPress search to our new URL.
 *
 * @since 0.2.2
 */
function redirect_wp_default_search() {
	if ( is_search() ) {
		wp_safe_redirect( home_url( '/search/?q=' . get_Query_var( 's' ) ) );
		exit;
	}
}

/**
 * Adds `q` as our search query variable.
 *
 * @since 0.2.2
 *
 * @param $vars
 *
 * @return array
 */
function filter_query_variable( $vars ) {
	$vars[] = 'q';
	return $vars;
}

/**
 * Processes a search request by passing to the WSU ES server.
 *
 * @since 0.2.2
 *
 * @param string $var
 *
 * @return array
 */
function get_elastic_response( $var ) {
	if ( '' === trim( $var ) ) {
		return array();
	}

	$search_key = md5( 'search' . $var );
	$results = wp_cache_get( $search_key, 'search' );

	if ( $results ) {
		return $results;
	}

	$host = home_url();
	$host = wp_parse_url( $host, PHP_URL_HOST );

	$request_url = 'https://elastic.wsu.edu/wsu-web/_search?q=%2bhostname:' . esc_attr( $host ) . '%20%2b' . rawurlencode( $var );

	$response = wp_remote_get( $request_url );

	if ( is_wp_error( $response ) ) {
		return array();
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return array();
	}

	$response = wp_remote_retrieve_body( $response );
	$response = json_decode( $response );

	if ( isset( $response->hits ) && isset( $response->hits->total ) && 0 === $response->hits->total ) {
		return array(); // no results found.
	}

	$search_results = $response->hits->hits;

	wp_cache_set( $search_key, $search_results, 'search', 3600 );

	return $search_results;
}

/**
 * Limit the post types supported by the WSUWP search plugin to events.
 *
 * @since 0.2.2
 *
 * @param array $post_types
 *
 * @return array
 */
function filter_post_types( $post_types ) {
	$post_types = array( 'event' );

	return $post_types;
}

/**
 * Filter the data sent to Elasticsearch for an event record.
 *
 * @since 0.2.6
 *
 * @param array    $data The data being sent to Elasticsearch.
 * @param \WP_Post $post The full post object.
 *
 * @return array Modified list of data to send to Elasticsearch.
 */
function search_data( $data, $post ) {
	if ( 'event' !== $post->post_type ) {
		return $data;
	}

	$start_date = strtotime( get_post_meta( $post->ID, 'wp_event_calendar_date_time', true ) );
	$start_date = date( 'l, M. j Y @g:i a', $start_date );

	$types = wp_get_post_terms( $post->ID, 'event-type' );
	$type = ( ! empty( $types[0] ) ) ? esc_html( $types[0]->name ) : '';

	$locations = wp_get_post_terms( $post->ID, 'wsuwp_university_location' );
	$location = ( ! empty( $locations[0] ) ) ? esc_html( $locations[0]->name ) : '';

	$data['event_start_date'] = $start_date;
	$data['event_type'] = $type;
	$data['event_location'] = $location;
	$data['event_excerpt'] = wpautop( get_the_excerpt( $post->ID ) );

	return $data;
}
