<?php

require_once __DIR__ . '/includes/featured-events.php';
require_once __DIR__ . '/includes/page-curation.php';
require_once __DIR__ . '/includes/page-curation-customizer.php';
require_once __DIR__ . '/includes/archives.php';
require_once __DIR__ . '/includes/calendar.php';
require_once __DIR__ . '/includes/search.php';
require_once __DIR__ . '/includes/organizations-shortcode.php';
require_once __DIR__ . '/includes/event-contributor.php';
require_once __DIR__ . '/includes/ics.php';
require_once __DIR__ . '/includes/dashboard-widget.php';

add_filter( 'spine_child_theme_version', 'events_theme_version' );
add_action( 'wp_enqueue_scripts', 'events_enqueue_scripts' );
add_action( 'wp_footer', 'events_social_media_icons' );
add_filter( 'pre_get_posts', 'events_filter_today_query', 11 );
add_action( 'admin_init', 'events_remove_featured_image_position' );

/**
 * Provides a theme version for use in cache busting.
 *
 * @since 0.0.1
 *
 * @return string
 */
function events_theme_version() {
	return '0.4.5';
}

/**
 * Enqueues custom styles and scripts.
 *
 * @since 0.0.1
 */
function events_enqueue_scripts() {
	wp_enqueue_style( 'source-sans-pro', '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,900,900i', array(), spine_get_child_version() );
	wp_enqueue_script( 'today', esc_url( get_stylesheet_directory_uri() . '/js/today.js' ), array( 'jquery' ), spine_get_child_version(), true );
	wp_enqueue_script( 'calendar', esc_url( get_stylesheet_directory_uri() . '/js/calendar.js' ), array( 'jquery' ), spine_get_child_version(), true );
	wp_localize_script( 'calendar', 'calendar_navigation', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'calendar-navigation' ),
	) );

	if ( is_front_page() ) {
		wp_enqueue_script( 'browse-by', esc_url( get_stylesheet_directory_uri() . '/js/browse-by.js' ), array( 'jquery' ), spine_get_child_version(), true );
	}
}

/**
 * Provides social media sharing icons.
 *
 * @since 0.1.0
 */
function events_social_media_icons() {
	if ( ! is_singular( 'event' ) ) {
		return;
	}
	?>
	<svg class="social-media-icons" xmlns="http://www.w3.org/2000/svg">
		<symbol id="social-media-icon_linkedin" viewbox="0 0 20 20">
			<path d="M20 20h-4v-6.999c0-1.92-.847-2.991-2.366-2.991-1.653 0-2.634 1.116-2.634 2.991V20H7V7h4v1.462s1.255-2.202 4.083-2.202C17.912 6.26 20 7.986 20 11.558V20zM2.442 4.921A2.451 2.451 0 0 1 0 2.46 2.451 2.451 0 0 1 2.442 0a2.451 2.451 0 0 1 2.441 2.46 2.45 2.45 0 0 1-2.441 2.461zM0 20h5V7H0v13z"></path>
		</symbol>
		<symbol id="social-media-icon_twitter" viewbox="0 0 20 16">
			<path d="M6.29 16c7.547 0 11.675-6.156 11.675-11.495 0-.175 0-.35-.012-.522A8.265 8.265 0 0 0 20 1.89a8.273 8.273 0 0 1-2.356.637A4.07 4.07 0 0 0 19.448.293a8.303 8.303 0 0 1-2.606.98 4.153 4.153 0 0 0-5.806-.175 4.006 4.006 0 0 0-1.187 3.86A11.717 11.717 0 0 1 1.392.738 4.005 4.005 0 0 0 2.663 6.13 4.122 4.122 0 0 1 .8 5.625v.051C.801 7.6 2.178 9.255 4.092 9.636a4.144 4.144 0 0 1-1.852.069c.537 1.646 2.078 2.773 3.833 2.806A8.315 8.315 0 0 1 0 14.185a11.754 11.754 0 0 0 6.29 1.812"></path>
		</symbol>
		<symbol id="social-media-icon_facebook" viewbox="0 0 10 20">
			<path d="M6.821 20v-9h2.733L10 7H6.821V5.052C6.821 4.022 6.848 3 8.287 3h1.458V.14c0-.043-1.253-.14-2.52-.14C4.58 0 2.924 1.657 2.924 4.7V7H0v4h2.923v9h3.898z"></path>
		</symbol>
	</svg>
	<?php
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
	$all_day = get_post_meta( $post_id, 'wp_event_calendar_all_day', true );

	if ( is_single() ) {
		// Build out a more robust data set for individual event views
		$end_date = strtotime( get_post_meta( $post_id, 'wp_event_calendar_end_date_time', true ) );
		$start_parts = explode( ' ', date_i18n( 'l, F j, Y g:i a', $start_date ) );
		$end_parts = explode( ' ', date_i18n( 'l, F j, Y g:i a', $end_date ) );

		// Build the date output.
		// Pobably overbuilt, as most events don't span multiple days.
		$same_year = ( $start_parts[3] === $end_parts[3] );
		$same_month = ( $end_parts[1] === $start_parts[1] );
		$same_day = ( $end_parts[2] === $start_parts[2] );

		if ( $same_year && $same_month && $same_day ) {
			$date = date_i18n( 'l, F j, Y', $start_date );
		} else {
			$date = $start_parts[1] . ' ';

			if ( $same_year ) {
				$date .= str_replace( ',', '', $start_parts[2] );
				$date .= ( $same_month ) ? '-' : ' - ' . $end_parts[1] . ' ';
			} else {
				$date .= $start_parts[2] . ' ' . $start_parts[3] . ' - ';
				$date .= $end_parts[1] . ' ';
			}

			$date .= $end_parts[2] . ' ' . $end_parts[3];
		}

		// Build the time output.
		if ( ! empty( $all_day ) ) {
			$time = 'All day';
		} else {
			$time = $start_parts[4];
			$time .= ( $end_parts[5] !== $start_parts[5] ) ? ' ' . $start_parts[5] . ' to ' : '-';
			$time .= $end_parts[4] . ' ' . $end_parts[5];
			$time = str_replace( ':00', '', $time );
		}

		$data = array(
			'date_time' => date_i18n( 'Y-m-d H:i', $start_date ),
			'date' => $date,
			'time' => $time,
			'location_notes' => get_post_meta( $post_id, '_wsuwp_event_location_notes', true ),
			'contact' => array(
				'name' => get_post_meta( $post_id, '_wsuwp_event_contact_name', true ),
				'email' => get_post_meta( $post_id, '_wsuwp_event_contact_email', true ),
				'phone' => get_post_meta( $post_id, '_wsuwp_event_contact_phone', true ),
			),
			'action' => array(
				'text' => get_post_meta( $post_id, '_wsuwp_event_action_text', true ),
				'url' => get_post_meta( $post_id, '_wsuwp_event_action_url', true ),
			),
			'cost' => get_post_meta( $post_id, '_wsuwp_event_cost', true ),
		);

	} else {
		$event_time = ( ! empty( $all_day ) ) ? 'All day' : str_replace( ':00', '', date_i18n( 'g:i a', $start_date ) );

		if ( is_post_type_archive( 'event' ) && ! is_month() ) {
			$event_date = '';
		} else {
			$event_date = date_i18n( 'l, F j', $start_date );
			$event_date .= ( empty( $all_day ) ) ? ' @' : ' ';
		}

		$data = array(
			'date' => $event_date,
			'time' => $event_time,
		);
	}

	return $data;
}

/**
 * Outputs markup for the "Browse by..." taxonomy filters on the home page.
 *
 * @since 0.1.0
 *
 * @param string $button_text The button/top select option text.
 * @param string $taxonomy    The taxonomy from which to retrieve terms.
 */
function display_event_filter( $button_text, $taxonomy ) {
	?>
	<button><?php echo esc_html( $button_text ); ?></button>
	<?php

	$args = array(
		'taxonomy' => esc_html( $taxonomy ),
	);

	// Exclude the "Academic Calendar" term from the Event Type output.
	// (It will be represented in the site menu.)
	if ( 'event-type' === $taxonomy ) {
		$academic_calendar = get_term_by( 'name', 'Academic Calendar', 'event-type' );
		$args['exclude'] = $academic_calendar->term_id;
	}

	// Output only the Campuses for the location filter.
	if ( 'wsuwp_university_location' === $taxonomy ) {
		$args['name'] = array(
			'WSU Global Campus',
			'WSU North Puget Sound at Everett',
			'WSU Pullman',
			'WSU Spokane',
			'WSU Tri-Cities',
			'WSU Vancouver',
			'WSU Everett',
		);
	}

	$terms = get_terms( $args );

	?>

	<ul>
		<?php foreach ( $terms as $term ) { ?>
		<?php $term_link = get_term_link( $term->term_id ); ?>
		<li>
			<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $term->name ); ?></a>
		</li>
		<?php } ?>
	</ul>

	<form title="Select <?php echo esc_attr( $button_text ); ?>">
		<label><span><?php echo esc_html( $button_text ); ?></span>
		<select>
			<?php foreach ( $terms as $term ) { ?>
			<?php $term_link = get_term_link( $term->term_id ); ?>
			<option value="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php } ?>
		</select>
		</label>
		<button>Browse events by <?php echo esc_attr( strtolower( $button_text ) ); ?></button>
	</form>
	<?php
}

/**
 * Filter the query for the "What's happening today" component.
 *
 * @since 0.1.0
 *
 * @param WP_Query $wp_query
 */
function events_filter_today_query( $wp_query ) {

	// Bail if the `wsuwp_events_today` argument is not set.
	if ( empty( $wp_query->query['wsuwp_events_today'] ) ) {
		return;
	}

	$todays_date = date_i18n( 'Y-m-d' );

	$today = $todays_date . ' 00:00:00';
	$end_of_day = $todays_date . ' 23:59:59';

	$wp_query->set( 'meta_query', array(
		'wsuwp_event_start_date' => array(
			'key' => 'wp_event_calendar_date_time',
			'value' => array( $today, $end_of_day ),
			'compare' => 'BETWEEN',
			'type' => 'DATETIME',
		),
		array(
			'relation' => 'OR',
			array(
				'key' => 'wp_event_calendar_end_date_time',
				'value' => current_time( 'mysql' ),
				'compare' => '>',
				'type' => 'DATETIME',
			),
			array(
				'key' => 'wp_event_calendar_all_day',
				'value' => '1',
				'compare' => '=',
			),
		),
	) );

	$wp_query->set( 'orderby', 'wsuwp_event_start_date' );
	$wp_query->set( 'order', 'ASC' );
}

/**
 * Removes the background position input added by the Spine parent theme.
 *
 * @since 0.4.0
 */
function events_remove_featured_image_position() {
	if ( ! class_exists( 'Spine_Theme_Images' ) ) {
		return;
	}

	remove_filter( 'admin_post_thumbnail_html', array( Spine_Theme_Images::get_instance(), 'meta_featured_image_position' ), 10 );
}
