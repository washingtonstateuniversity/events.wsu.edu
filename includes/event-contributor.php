<?php

namespace WSU\Events\Event_Contributor;

remove_filter( 'map_meta_cap', 'wp_event_calendar_meta_caps', 10, 4 );

add_filter( 'register_post_type_args', 'WSU\Events\Event_Contributor\filter_event_post_type_args', 10, 2 );
add_action( 'admin_init', 'WSU\Events\Event_Contributor\add_role' );
add_action( 'switch_theme', 'WSU\Events\Event_Contributor\remove_role' );

/**
 * Unsets the capability related arguments from the event post type.
 *
 * @since 0.2.4
 *
 * @param array  Arguments for registering a post type.
 * @param string Post type key.
 *
 * @return array
 */
function filter_event_post_type_args( $args, $post_type ) {
	if ( 'event' === $post_type ) {
		unset( $args['capabilities'] );
		unset( $args['capability_type'] );
	}

	return $args;
}

/**
 * Adds the Event Contributor role on theme activation.
 *
 * @since 0.2.4
 */
function add_role() {
	\add_role(
		'wsuwp_event_contributor',
		'Event Contributor',
		array(
			'create_events' => true,
			'delete_events' => true,
			'edit_events' => true,
			'read' => true,
			'upload_files' => true,
		)
	);
}

/**
 * Removes the Event Contributor role on theme deactivation.
 *
 * @since 0.2.4
 */
function remove_role() {
	\remove_role( 'wsuwp_event_contributor' );
}
