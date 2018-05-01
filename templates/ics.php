<?php

$start_date = get_post_meta( get_the_ID(), 'wp_event_calendar_date_time', true );
$start_date = date( 'Ymd\THis', strtotime( $start_date ) );
$end_date = get_post_meta( get_the_ID(), 'wp_event_calendar_end_date_time', true );
$end_date = date( 'Ymd\THis', strtotime( $end_date ) );
$event_venue = \WSU\Events\Venues\get_venue();
$content = get_post_field( 'post_content', get_the_ID() );
$content = preg_split( '/\n|\r/', $content, -1, PREG_SPLIT_NO_EMPTY );
$content = implode( '\n\n', $content );
$content = wp_strip_all_tags( $content );

header( 'HTTP/1.0 200 OK', true, 200 );
header( 'Content-Type: text/calendar; charset=UTF-8' );
header( 'Content-Disposition: attachment; filename=invite.ics' );

?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//<?php echo esc_html( get_bloginfo( 'name' ) ); ?>//NONSGML v1.0//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
BEGIN:VEVENT
DTSTART;TZID=America/Los_Angeles:<?php echo esc_html( $start_date ) . "\r\n"; ?>
DTEND;TZID=America/Los_Angeles:<?php echo esc_html( $end_date ) . "\r\n"; ?>
SUMMARY:<?php echo esc_html( get_the_title() ) . "\r\n"; ?>
LOCATION:<?php echo esc_html( $event_venue['address'] ) . "\r\n"; ?>
DESCRIPTION:<?php echo esc_html( $content ) . "\r\n"; ?>
END:VEVENT
END:VCALENDAR
