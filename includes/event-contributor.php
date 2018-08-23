<?php

namespace WSU\Events\Event_Contributor;

remove_filter( 'map_meta_cap', 'wp_event_calendar_meta_caps', 10, 4 );

add_filter( 'register_post_type_args', __NAMESPACE__ . '\\filter_event_post_type_args', 10, 2 );
add_action( 'admin_init', __NAMESPACE__ . '\\add_role' );
add_action( 'switch_theme', __NAMESPACE__ . '\\remove_role' );
add_action( 'init', __NAMESPACE__ . '\\map_capabilities', 12 );
add_action( 'post_submitbox_start', __NAMESPACE__ . '\\update_notice' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );
add_action( 'save_post_event', __NAMESPACE__ . '\\save_event' );
add_filter( 'map_meta_cap', __NAMESPACE__ . '\\passed_event_capabilities', 10, 4 );

// If a user authenticates with WSU AD, and they don't exist as a user, add them as a user.
add_filter( 'wsuwp_sso_create_new_user', '__return_true' );
add_action( 'wsuwp_sso_user_created', __NAMESPACE__ . '\\new_user', 10, 1 );
add_action( 'admin_menu', __NAMESPACE__ . '\\user_auto_role' );

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
 * @since 0.4.0 Mapped the `edit_events` capability to `edit_published_posts`.
 */
function map_capabilities() {
	if ( ! in_array( 'wsuwp_event_contributor', (array) wp_get_current_user()->roles, true ) ) {
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
 * Adds a note for Event Contributors about editing published events.
 *
 * @since 0.4.0
 *
 * @param WP_Post $post The current post object.
 */
function update_notice( $post ) {
	if ( 'event' !== $post->post_type ) {
		return;
	}

	if ( 'publish' !== $post->post_status ) {
		return;
	}

	if ( ! in_array( 'wsuwp_event_contributor', (array) wp_get_current_user()->roles, true ) ) {
		return;
	}

	?>
	<p class="description">Updating this event will change its status to "Pending Review". It will go through the approval process again before being republished.</p>
	<?php
}

/**
 * Dequeues the autosaving script when Event Contributors edit published events.
 *
 * @since 0.4.0
 *
 * @param string $hook_suffix The current admin page.
 */
function admin_enqueue_scripts( $hook_suffix ) {
	if ( 'post.php' !== $hook_suffix ) {
		return;
	}

	if ( 'event' !== get_current_screen()->post_type ) {
		return;
	}

	if ( ! in_array( 'wsuwp_event_contributor', (array) wp_get_current_user()->roles, true ) ) {
		return;
	}

	wp_dequeue_script( 'autosave' );
}

/**
 * Changes the status to `pending` when Event Contributors save a published event.
 *
 * @since 0.4.0
 *
 * @param int $post_id The post ID.
 */
function save_event( $post_id ) {
	if ( 'publish' !== get_post_status( $post_id ) ) {
		return;
	}

	if ( ! in_array( 'wsuwp_event_contributor', (array) wp_get_current_user()->roles, true ) ) {
		return;
	}

	remove_action( 'save_post_event', __NAMESPACE__ . '\\save_event' );

	wp_update_post( array(
		'ID' => $post_id,
		'post_status' => 'pending',
	) );

	add_action( 'save_post_event', __NAMESPACE__ . '\\save_event' );
}

/**
 * Prevents Event Contributors from edit events with the `passed` status.
 *
 * @since 0.4.3
 *
 * @param array  $caps    The user's actual capabilities.
 * @param string $cap     Capability name.
 * @param int    $user_id The user ID.
 * @param array  $args    Adds the context to the cap (typically the object ID).
 *
 * @return array $caps
 */
function passed_event_capabilities( $caps, $cap, $user_id, $args ) {
	if ( 'edit_post' !== $cap && 'delete_post' !== $cap ) {
		return $caps;
	}

	$user = get_userdata( $user_id );

	if ( ! $user || ! in_array( 'wsuwp_event_contributor', (array) $user->roles, true ) || ! $args ) {
		return $caps;
	}

	$post_id = $args[0];

	if ( 'passed' === get_post_status( $post_id ) ) {
		$caps['edit_events'] = false;
		$caps['delete_events'] = false;
	}

	return $caps;
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
