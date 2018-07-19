<?php

namespace WSU\Events\Featured;

add_action( 'add_meta_boxes_event', 'WSU\Events\Featured\add_meta_boxes', 10 );
add_action( 'save_post_event', 'WSU\Events\Featured\save_post', 10, 2 );
add_filter( 'manage_event_posts_columns', 'WSU\Events\Featured\manage_columns', 10, 1 );
add_action( 'manage_event_posts_custom_column', 'WSU\Events\Featured\manage_custom_column', 10, 2 );
add_action( 'rest_event_query', 'WSU\Events\Featured\filter_rest_query' );

/**
 * Adds a meta box for capturing an excerpt to display on the home page.
 * This is only available to users who can manage options.
 *
 * @since 0.3.0
 *
 * @param string $post_type
 */
function add_meta_boxes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_meta_box(
		'events_featured_excerpt',
		'Featured Excerpt',
		'WSU\Events\Featured\display_featured_excerpt_meta_box',
		'event',
		'normal',
		'high'
	);
}

/**
 * Displays a meta box used to capture an excerpt for display on the home page.
 *
 * @since 0.3.0
 *
 * @param \WP_Post $post
 */
function display_featured_excerpt_meta_box( $post ) {
	$featured_excerpt = get_post_meta( $post->ID, '_wsuwp_event_featured_excerpt', true );

	$editor_args = array(
		'media_buttons' => false,
		'textarea_rows' => 5,
		'teeny' => true,
	);

	wp_nonce_field( 'wsuwp_events_featured_excerpt', 'wsuwp_events_featured_excerpt_nonce' );

	wp_editor( $featured_excerpt, '_wsuwp_event_featured_excerpt', $editor_args );
}

/**
 * Saves the featured excerpt of an event.
 *
 * @since 0.3.0
 *
 * @param int      $post_id
 * @param \WP_Post $post
 */
function save_post( $post_id, $post ) {
	if ( ! isset( $_POST['wsuwp_events_featured_excerpt_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwp_events_featured_excerpt_nonce'], 'wsuwp_events_featured_excerpt' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	if ( isset( $_POST['_wsuwp_event_featured_excerpt'] ) ) {
		update_post_meta( $post_id, '_wsuwp_event_featured_excerpt', wp_kses_post( $_POST['_wsuwp_event_featured_excerpt'] ) );
	} else {
		delete_post_meta( $post_id, '_wsuwp_event_featured_excerpt' );
	}
}

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
 * Forces a return of zero results if no featured events are set.
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
			$args['post__in'] = array( 0 );
		}
	}

	return $args;
}
