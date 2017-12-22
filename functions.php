<?php

add_filter( 'spine_child_theme_version', 'events_theme_version' );
/**
 * Provides a theme version for use in cache busting.
 *
 * @since 0.0.1
 *
 * @return string
 */
function events_theme_version() {
	return '0.0.1';
}

/**
 * Returns a usable subset of data for displaying an event.
 *
 * @since 0.0.1
 *
 * @param int $post_id
 *
 * @return array
 */
function get_event_data( $post_id ) {
	$start_date = strtotime( get_post_meta( $post_id, 'wp_event_calendar_date_time', true ) );

	$data = array(
		'start' => array(
			'date_time' => date( 'Y-m-d H:i', $start_date ),
			'date' => date( 'l, F j, Y', $start_date ),
			'time' => date( 'g:i a', $start_date ),
		),
		'location' => array(
			'name' => get_post_meta( $post_id, '_location_name', true ),
			'latitude' => get_post_meta( $post_id, '_location_latitude', true ),
			'longitude' => get_post_meta( $post_id, '_location_longitude', true ),
			'notes' => get_post_meta( $post_id, '_location_notes', true ),
		),
		'contact' => array(
			'name' => get_post_meta( $post_id, '_contact_name', true ),
			'email' => get_post_meta( $post_id, '_contact_email', true ),
			'phone' => get_post_meta( $post_id, '_contact_phone', true ),
		),
		'action' => array(
			'text' => get_post_meta( $post_id, '_action_text', true ),
			'url' => get_post_meta( $post_id, '_action_url', true ),
		),
		'cost' => get_post_meta( $post_id, '_cost', true ),
		'related' => get_post_meta( $post_id, '_related_site', true ),
	);

	return $data;
}
