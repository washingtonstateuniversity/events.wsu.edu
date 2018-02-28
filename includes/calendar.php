<?php

namespace WSU\Events\Calendar;

$dates_with_events = array();

/**
 * Build an array of dates which have events.
 *
 * @since 0.1.1
 *
 * @param string $start
 * @param string $end
 */
function days_with_events( $start, $end ) {
	global $dates_with_events;

	$query_args = array(
		'post_type' => 'event',
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
 * @since 0.1.1
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
 * @since 0.1.1
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
		$label = 'View events for ' . date( 'l, F j, Y', $timestamp );
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
 * @since 0.1.1
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
		$label = 'View events for ' . date( 'l, F j, Y', $timestamp );
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
 * @since 0.1.1
 *
 * @param string $month
 * @param string $year
 */
function get_calendar( $month, $year ) {
	date_default_timezone_set( 'America/Los_Angeles' );

	$timestamp = mktime( 0, 0, 0, $month, 1, $year );
	$total_days = date_i18n( 't', $timestamp );
	$this_month = getdate( $timestamp );
	$start_day = $this_month['wday'];
	$month_heading = date( 'F Y', $timestamp );
	$week_headings = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );

	// Set up variables for padding the beginning of the calendar.
	$prev_timestamp = strtotime( '-' . $start_day . ' days', $timestamp );
	$prev_year = date( 'Y', $prev_timestamp );
	$prev_month = date( 'm', $prev_timestamp );
	$prev_day = date( 'd', $prev_timestamp );
	$start = date( 'Y-m-d 00:00:00', $prev_timestamp );

	// Set up variables for padding the end of the calendar.
	$next_timestamp = strtotime( $total_days . ' ' . $month_heading . ' +6 days' );
	$next_year = date( 'Y', $next_timestamp );
	$next_month = date( 'm', $next_timestamp );
	$end = date( 'Y-m-d 00:00:00', $next_timestamp );

	$days_in_this_week = 0;

	days_with_events( $start, $end );
	?>
	<div class="calendar">

		<header><?php echo esc_html( $month_heading ); ?></header>

		<div class="month">

			<?php foreach ( $week_headings as $week_heading ) { ?>
			<div class="day-heading"><?php echo esc_html( $week_heading ); ?></div>
			<?php } ?>

			<?php
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

			for ( $i = 0; $i < ( 7 - $days_in_this_week ); $i++ ) {
				get_pad_days( $i, 1, $next_month, $next_year );
			}
			?>

		</div>

	</div>
	<?php
}
