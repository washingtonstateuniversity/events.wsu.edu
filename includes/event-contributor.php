<?php

namespace WSU\Events\Event_Contributor;

add_action( 'admin_init', 'WSU\Events\Event_Contributor\add_role' );
add_action( 'switch_theme', 'WSU\Events\Event_Contributor\remove_role' );

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
