<?php

namespace WSU\Events\Calendar;

add_filter( 'wp_ajax_nopriv_load_month', __NAMESPACE__ . '\\ajax_callback' );
add_action( 'wp_ajax_load_month', __NAMESPACE__ . '\\ajax_callback' );

$dates_with_events = array();

/**
 * Build an array of dates which have events.
 *
 * @since 0.2.0
 *
 * @param string $start
 * @param string $end
 */
function days_with_events( $start, $end ) {
	global $dates_with_events;

	$query_args = array(
		'post_type' => 'event',
		'post_status' => array( 'publish', 'passed' ),
		'posts_per_page' => -1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $start,
				'compare' => '>',
				'type' => 'DATETIME',
			),
			array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $end,
				'compare' => '<',
				'type' => 'DATETIME',
			),
		),
	);

	$events = new \WP_Query( $query_args );

	if ( $events->have_posts() ) {
		while ( $events->have_posts() ) {
			$events->the_post();
			$event_start = get_post_meta( get_the_ID(), 'wp_event_calendar_date_time', true );
			$event_start_date = strtok( $event_start, ' ' );

			if ( ! in_array( $event_start_date, $dates_with_events, true ) ) {
				$dates_with_events[] = $event_start_date;
			}
		}
	}

	wp_reset_postdata();
}

/**
 * Return the URL of an archive page for a given day.
 *
 * @since 0.2.0
 *
 * @param string $year
 * @param string $month
 * @param string $day
 *
 * @return string
 */
function get_day_link( $year, $month, $day ) {
	global $dates_with_events;

	$day = sprintf( '%02d', $day );
	$date = $year . '-' . $month . '-' . $day;
	$url = false;

	if ( in_array( $date, $dates_with_events, true ) ) {
		$url = get_post_type_archive_link( 'event' );
		$url .= $year . '/' . $month . '/' . $day . '/';
	}

	return $url;
}

/**
 * Pad the beginning and end of the calendar
 * with days from the previous and next month.
 *
 * @since 0.2.0
 *
 * @param int    $iterator
 * @param mixed  $start_day
 * @param string $month
 * @param string $year
 */
function get_pad_days( $iterator = 1, $start_day, $month, $year ) {
	$day = $iterator + $start_day;
	$link = get_day_link( $year, $month, $day );

	if ( $link ) {
		$timestamp = mktime( 0, 0, 0, $month, $day, $year );
		$label = 'View events for ' . date_i18n( 'l, F j, Y', $timestamp );
		?>
		<div class="pad-day">
			<a href="<?php echo esc_url( $link ); ?>"
			   aria-label="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $day ); ?></a>
		</div>
		<?php
	} else {
		?>
		<div class="pad-day"><?php echo esc_html( $day ); ?></div>
		<?php
	}
}

/**
 * Build the days for the current month.
 *
 * @since 0.2.0
 *
 * @param int    $iterator
 * @param int    $start_day
 * @param string $month
 * @param string $year
 */
function get_month_days( $iterator = 1, $start_day = 1, $month, $year ) {
	$day = $iterator - $start_day + 1;
	$link = get_day_link( $year, $month, $day );

	if ( $link ) {
		$timestamp = mktime( 0, 0, 0, $month, $day, $year );
		$label = 'View events for ' . date_i18n( 'l, F j, Y', $timestamp );
		?>
		<div>
			<a href="<?php echo esc_url( $link ); ?>"
			   aria-label="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $day ); ?></a>
		</div>
		<?php
	} else {
		?>
		<div><?php echo esc_html( $day ); ?></div>
		<?php
	}
}

/**
 * Display a calendar for the given month and year.
 *
 * @since 0.2.0
 *
 * @param string $month
 * @param string $year
 */
function get_calendar( $month, $year ) {
	$timestamp = mktime( 0, 0, 0, $month, 1, $year );
	$total_days = date_i18n( 't', $timestamp );
	$this_month = getdate( $timestamp );
	$start_day = $this_month['wday'];

	// Set up variables for padding the beginning of the calendar.
	$prev_timestamp = strtotime( '-' . $start_day . ' days', $timestamp );
	$prev_year = date_i18n( 'Y', $prev_timestamp );
	$prev_month = date_i18n( 'm', $prev_timestamp );
	$prev_day = date_i18n( 'd', $prev_timestamp );
	$start = date_i18n( 'Y-m-d 00:00:00', $prev_timestamp );

	// Set up variables for padding the end of the calendar.
	$next_timestamp = strtotime( $total_days . ' ' . date_i18n( 'F Y', $timestamp ) . ' +6 days' );
	$next_year = date_i18n( 'Y', $next_timestamp );
	$next_month = date_i18n( 'm', $next_timestamp );
	$end = date_i18n( 'Y-m-d 00:00:00', $next_timestamp );

	$days_in_this_week = 0;

	days_with_events( $start, $end );

	for ( $i = 0; $i < ( $total_days + $start_day ); $i++ ) {

		if ( $i < $start_day ) {
			get_pad_days( $i, $prev_day, $prev_month, $prev_year );
		} else {
			get_month_days( $i, $start_day, $month, $year );
		}

		$days_in_this_week++;

		if ( ( $i % 7 ) === 6 ) {
			$days_in_this_week = 0;
		}
	}

	if ( 0 < $days_in_this_week ) {
		for ( $i = 0; $i < ( 7 - $days_in_this_week ); $i++ ) {
			get_pad_days( $i, 1, $next_month, $next_year );
		}
	}
}

/**
 * Handles AJAX calendar navigation.
 *
 * @since 0.2.0
 */
function ajax_callback() {
	check_ajax_referer( 'calendar-navigation', 'nonce' );

	$current_month = $_POST['current_month'];
	$direction = $_POST['direction'];
	$math = ( 'next' === $direction ) ? ' +1 month' : ' -1 month';
	$timestamp = strtotime( $current_month . $math );
	$load_month = date_i18n( 'm', $timestamp );
	$load_year = date_i18n( 'Y', $timestamp );

	ob_start();
	get_calendar( $load_month, $load_year );
	$html = ob_get_clean();

	$calendar = array(
		'heading' => date_i18n( 'F Y', $timestamp ),
		'body' => $html,
	);

	echo wp_json_encode( $calendar );

	exit;
}
