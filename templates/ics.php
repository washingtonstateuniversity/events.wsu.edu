<?php

$start_date = get_post_meta( get_the_ID(), 'wp_event_calendar_date_time', true );
$start_date = date( 'Ymd\THis', strtotime( $start_date ) );
$end_date = get_post_meta( get_the_ID(), 'wp_event_calendar_end_date_time', true );
$end_date = date( 'Ymd\THis', strtotime( $end_date ) );
$event_venue = \WSU\Events\Venues\get_venue();

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
DESCRIPTION:<?php echo wp_kses_post( the_content() ) . "\r\n"; ?>
END:VEVENT
END:VCALENDAR
