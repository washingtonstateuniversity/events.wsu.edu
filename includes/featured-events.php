<?php

namespace WSU\Events\Featured;

add_filter( 'manage_event_posts_columns', 'WSU\Events\Featured\manage_columns', 10, 1 );
add_action( 'manage_event_posts_custom_column', 'WSU\Events\Featured\manage_custom_column', 10, 2 );
add_action( 'rest_event_query', 'WSU\Events\Featured\filter_rest_query' );

/**
 * Add a custom column to the events list table for displaying featured status.
 *
 * @since 0.1.0
 *
 * @param array $post_columns
 *
 * @return array
 */
function manage_columns( $post_columns ) {
	$post_columns['item_featured'] = 'Featured';

	return $post_columns;
}

/**
 * Output an event's featured status in a custom list table column.
 *
 * @since 0.1.0
 * @since 0.3.0 Use the `featured_events` option to determine featured status.
 *
 * @param string $column_name
 * @param int    $post_id
 */
function manage_custom_column( $column_name, $post_id ) {
	if ( 'item_featured' === $column_name ) {
		$featured_event_ids = get_option( 'featured_events', false );
		$featured = 'No';

		if ( $featured_event_ids && ! empty( $featured_event_ids ) && ! is_object( $featured_event_ids ) ) {
			$featured_event_ids = array_map( 'intval', explode( ',', $featured_event_ids ) );

			if ( in_array( $post_id, $featured_event_ids, true ) ) {
				$featured = 'Yes';
			}
		}

		echo esc_html( $featured );
	}
}

/**
 * Filter the events REST API query before it fires.
 *
 * @since 0.1.1
 *
 * @param array $args
 *
 * @return array
 */
function filter_rest_query( $args ) {
	if ( isset( $_REQUEST['featured'] ) && 'true' === $_REQUEST['featured'] ) { // WPCS: CSRF Ok.
		$featured_event_ids = get_option( 'featured_events', false );

		if ( $featured_event_ids ) {
			$featured_event_ids = explode( ',', $featured_event_ids );
			$args['posts_per_page'] = count( $featured_event_ids );
			$args['post__in'] = $featured_event_ids;
		} else {
			$args['meta_query']['wsuwp_event_featured'] = array(
				'key' => '_wsuwp_event_featured',
				'value' => 'yes',
			);
		}
	}

	return $args;
}
