<?php

namespace WSU\Events\Page_Curation\Customizer;

add_action( 'rest_api_init', 'WSU\Events\Page_Curation\Customizer\register_rest_route' );
add_filter( 'pre_get_posts', 'WSU\Events\Page_Curation\Customizer\filter_query' );
add_filter( 'customize_register', 'WSU\Events\Page_Curation\Customizer\register_featured_events' );
add_action( 'customize_controls_print_footer_scripts', 'WSU\Events\Page_Curation\Customizer\enqueue_scripts' );
add_action( 'customize_controls_enqueue_scripts', 'WSU\Events\Page_Curation\Customizer\enqueue_styles' );

/**
 * Register a custom endpoint to handle lookups for featured events from the Customizer.
 *
 * @since 0.1.0
 */
function register_rest_route() {
	\register_rest_route( 'events/v1', '/featured', array(
		'methods'  => 'GET',
		'callback' => 'WSU\Events\Page_Curation\Customizer\rest_search_featured',
	) );
}

/**
 * Return search results for featured events.
 *
 * @since 0.1.0
 *
 * @param \WP_REST_Request $request
 *
 * @return array
 */
function rest_search_featured( $request ) {
	if ( empty( $request['term'] ) ) {
		return array();
	}

	$results = new \WP_Query( array(
		'post_type' => 'event',
		'posts_per_page' => 20,
		's' => sanitize_text_field( $request['term'] ),
		'wsuwp_events_curator_search' => true,
	) );

	$posts = array();

	foreach ( $results->posts as $post ) {
		if ( ! $post->ID ) {
			continue;
		}

		$event_data = get_event_data( $post->ID );
		$event_title = trim( esc_html( wp_strip_all_tags( get_the_title( $post ) ) ) );
		$event_title .= ' - ' . esc_html( $event_data['start']['date'] );

		$posts[] = array(
			'value' => $post->ID,
			'label' => $event_title,
		);
	}

	return $posts;
}

/**
 * Filter the query for the featured events search query to
 * return only those events which have not already passed.
 *
 * @since 0.2.4
 *
 * @param \WP_Query $wp_query
 */
function filter_query( $wp_query ) {
	if ( empty( $wp_query->query['wsuwp_events_curator_search'] ) ) {
		return;
	}

	$wp_query->set( 'meta_query', array(
		'wsuwp_event_end_date' => array(
			'key' => 'wp_event_calendar_end_date_time',
			'value' => current_time( 'mysql' ),
			'compare' => '>',
			'type' => 'DATETIME',
		),
	) );

	$wp_query->set( 'meta_key', 'wp_event_calendar_date_time' );
	$wp_query->set( 'meta_type', 'DATETIME' );
	$wp_query->set( 'orderby', 'meta_value' );
	$wp_query->set( 'order', 'ASC' );
}

/**
 * Register the section, setting, and control used for curating featured events
 * at the top of the front page.
 *
 * @since 0.1.0
 *
 * @param \WP_Customize_Manager $wp_customize
 */
function register_featured_events( $wp_customize ) {

	$wp_customize->add_section( 'featured_events', array(
		'title' => 'Featured Events',
		'priority' => 9,
		'capability' => 'publish_pages',
		'active_callback' => 'is_front_page',
	) );

	$wp_customize->add_setting( 'featured_events', array(
		'default' => \WSU\Events\Page_Curation\get_featured_events(),
		'type' => 'option',
		'capability' => 'publish_pages',
	) );

	include_once __DIR__ . '/class-featured-events-customizer-control.php';

	$wp_customize->add_control( new \WSU\Events\Page_Curation\Customizer\Featured_Events_Control( $wp_customize, 'featured_events', array(
		'description' => 'Curate featured events displayed on the front page.',
		'section' => 'featured_events',
		'settings' => 'featured_events',
		'input_attrs' => \WSU\Events\Page_Curation\get_featured_events(),
		'priority' => 10,
		'type' => 'hidden',
		'sanitize_callback' => 'WSU\Events\Page_Curation\Customizer\sanitize_sections',
	) ) );
}

/**
 * Sanitize the new saved input of curated sections from the Customizer.
 *
 * @since 0.1.0
 *
 * @param $input
 *
 * @return mixed
 */
function sanitize_sections( $input ) {
	return $input;
}

/**
 * Enqueue the additional scripts required for front page curation in
 * the Customizer.
 *
 * @since 0.1.0
 */
function enqueue_scripts() {
	wp_enqueue_script( 'page-curation-customizer', esc_url( get_stylesheet_directory_uri() . '/includes/js/page-curation-customizer.js' ), array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-core', 'jquery-ui-autocomplete' ), spine_get_child_version(), true );
	wp_localize_script( 'page-curation-customizer', 'wsu_page_curation', array(
		'featured_events_endpoint' => esc_js( get_rest_url( get_current_blog_id(), '/events/v1/featured' ) ),
	) );
}

/**
 * Enqueue the additional styles required for front page curation in
 * the Customizer.
 *
 * @since 0.1.0
 */
function enqueue_styles() {
	wp_enqueue_style( 'page-curation-customizer', esc_url( get_stylesheet_directory_uri() . '/includes/css/page-curation-customizer.css' ), '', spine_get_child_version() );
}
