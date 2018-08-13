<?php

namespace WSU\Events\Event_Contributor;

remove_filter( 'map_meta_cap', 'wp_event_calendar_meta_caps', 10, 4 );

add_filter( 'register_post_type_args', 'WSU\Events\Event_Contributor\filter_event_post_type_args', 10, 2 );
add_action( 'admin_init', 'WSU\Events\Event_Contributor\add_role' );
add_action( 'switch_theme', 'WSU\Events\Event_Contributor\remove_role' );
add_action( 'init', 'WSU\Events\Event_Contributor\map_capabilities', 12 );

// If a user authenticates with WSU AD, and they don't exist as a user, add them as a user.
add_filter( 'wsuwp_sso_create_new_user', '__return_true' );
add_action( 'wsuwp_sso_user_created', 'WSU\Events\Event_Contributor\new_user', 10, 1 );
add_action( 'admin_menu', 'WSU\Events\Event_Contributor\user_auto_role' );

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

/**
 * Maps the Event Contributor capabilities to the event post type.
 *
 * @since 0.2.4
 */
function map_capabilities() {
	$user = wp_get_current_user();

	if ( ! in_array( 'wsuwp_event_contributor', (array) $user->roles, true ) ) {
		return;
	}

	$event = get_post_type_object( 'event' );

	if ( $event ) {
		$event->cap->create_posts = 'create_events';
		$event->cap->delete_posts = 'delete_events';
		$event->cap->edit_posts = 'edit_events';
		$event->cap->edit_published_posts = 'edit_events';
	}

	$taxonomies = get_taxonomies( array(), 'objects' );

	if ( $taxonomies ) {
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy->cap->assign_terms = 'edit_events';
		}
	}
}

/**
 * Add new users created through the SSO plugin to the site as Event Contributors.
 *
 * @since 0.2.4
 *
 * @param int $user_id
 */
function new_user( $user_id ) {
	add_user_to_blog( get_current_blog_id(), $user_id, 'wsuwp_event_contributor' );
}

/**
 * Add all logged in users in the admin screen to the Events site.
 *
 * @since 0.2.4
 */
function user_auto_role() {
	if ( is_user_logged_in() && ! is_user_member_of_blog() ) {
		add_user_to_blog( get_current_blog_id(), get_current_user_id(), 'wsuwp_event_contributor' );
		wp_safe_redirect( admin_url( '/post-new.php?post_type=event' ) );
		exit;

	}
}
