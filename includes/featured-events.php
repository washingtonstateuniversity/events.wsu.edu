<?php

namespace WSU\Events\Featured;

add_action( 'add_meta_boxes_event', 'WSU\Events\Featured\add_meta_boxes', 10 );
add_action( 'save_post_event', 'WSU\Events\Featured\save_post', 10, 2 );
add_filter( 'manage_event_posts_columns', 'WSU\Events\Featured\manage_columns', 10, 1 );
add_action( 'manage_event_posts_custom_column', 'WSU\Events\Featured\manage_custom_column', 10, 2 );
add_filter( 'pre_get_posts', 'WSU\Events\Featured\filter_front_page_featured_events_query', 11 );

/**
 * Adds meta boxes used to manage featured events.
 *
 * @since 0.0.1
 *
 * @param string $post_type
 */
function add_meta_boxes() {
	add_meta_box(
		'events_featured_meta',
		'Featured Status',
		'WSU\Events\Featured\display_meta_box',
		'event',
		'side',
		'high'
	);
}

/**
 * Displays the meta box used to capture an event's featured status.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_meta_box( $post ) {
	wp_nonce_field( 'wsuwp_featured_status', 'wsuwp_featured_status_nonce' );

	$featured = get_post_meta( $post->ID, '_wsuwp_event_featured', true );

	if ( 'yes' !== $featured ) {
		$featured = 'no';
	}

	?>
	<label for="featured-status-select">Featured Status:</label>
	<select id="featured-status-select" name="featured_status_select">
		<option value="no" <?php selected( 'no', $featured ); ?>>No</option>
		<option value="yes" <?php selected( 'yes', $featured ); ?>>Yes</option>
	</select>
	<?php
}

/**
 * Saves the featured status of an event.
 *
 * @since 0.0.1
 *
 * @param int      $post_id
 * @param \WP_Post $post
 */
function save_post( $post_id, $post ) {
	if ( ! isset( $_POST['wsuwp_featured_status_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwp_featured_status_nonce'], 'wsuwp_featured_status' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	if ( ! isset( $_POST['featured_status_select'] ) ) {
		return;
	}

	if ( 'yes' === $_POST['featured_status_select'] ) {
		update_post_meta( $post_id, '_wsuwp_event_featured', 'yes' );
	} else {
		delete_post_meta( $post_id, '_wsuwp_event_featured' );
	}
}

/**
 * Add a custom column to the events list table for displaying featured status.
 *
 * @since 0.0.1
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
 * @since 0.0.1
 *
 * @param string $column_name
 * @param int    $post_id
 */
function manage_custom_column( $column_name, $post_id ) {
	if ( 'item_featured' === $column_name ) {
		$featured = get_post_meta( $post_id, '_wsuwp_event_featured', true );

		if ( 'yes' !== $featured ) {
			$featured = 'no';
		}

		echo esc_html( ucwords( $featured ) );
	}
}

/**
 * Filter the query for the front page featured events.
 *
 * @since 0.0.1
 *
 * @param \WP_Query $wp_query
 */
function filter_front_page_featured_events_query( $wp_query ) {

	// Bail if the `post_type` argument is not set.
	if ( empty( $wp_query->query['post_type'] ) ) {
		return;
	}

	// Bail if the `post_type` argument is not set to `event`.
	if ( 'event' !== $wp_query->query['post_type'] ) {
		return;
	}

	// Bail if the `post__in` argument is not set.
	if ( empty( $wp_query->query['post__in'] ) ) {
		return;
	}

	$wp_query->set( 'orderby', 'post__in' );
}
