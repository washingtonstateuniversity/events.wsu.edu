<?php

namespace WSU\Events\Search;

add_filter( 'query_vars', 'WSU\Events\Search\filter_query_variable' );
add_action( 'template_redirect', 'WSU\Events\Search\redirect_wp_default_search' );
add_filter( 'wsuwp_search_post_types', 'WSU\Events\Search\filter_post_types' );

/**
 * Redirect requests to the default WordPress search to our new URL.
 *
 * @since 0.2.2
 */
function redirect_wp_default_search() {
	if ( is_search() ) {
		wp_redirect( home_url( '/search/?q=' . get_Query_var( 's' ) ) );
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
 * Filters the content returned by Elastic Search for display in a search
 * results page.
 *
 * @since 0.2.2
 *
 * @param string $visible_content
 *
 * @return mixed|string
 */
function filter_elastic_content( $visible_content ) {
	$visible_content = preg_replace( '/[\r\n]+/', "\n", $visible_content );
	$visible_content = preg_replace( '/[ \t]+/', ' ', $visible_content );
	$visible_content = strip_tags( $visible_content, '<p><strong><em>' );
	$visible_content = trim( $visible_content );
	$visible_content = substr( $visible_content, 0, 260 );
	$visible_content = force_balance_tags( $visible_content . '....' );
	$visible_content = wpautop( $visible_content, false );

	return $visible_content;
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
 * Add the WSU event post type to those supported by the WSUWP search plugin.
 *
 * @since 0.2.2
 *
 * @param array $post_types
 *
 * @return array
 */
function filter_post_types( $post_types ) {
	if ( ! in_array( 'event', $post_types, true ) ) {
		$post_types[] = 'event';
	}

	return $post_types;
}
