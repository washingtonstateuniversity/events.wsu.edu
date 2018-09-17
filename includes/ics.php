<?php

// Ideas borrowed from https://gist.github.com/jakebellacera/635416 and
// https://github.com/moderntribe/the-events-calendar/blob/master/src/Tribe/iCal.php.

namespace WSU\Events\ICS;

add_filter( 'query_vars', __NAMESPACE__ . '\\add_query_vars' );
add_filter( 'template_include', __NAMESPACE__ . '\\include_template' );

/**
 * Adds a custom query var for `.ics` generation.
 *
 * @since 0.2.5
 *
 * @param array $vars Whitelisted query variables.
 *
 * @return array
 */
function add_query_vars( $vars ) {
	$vars[] = 'wsuwp_events_ics';

	return $vars;
}

/**
 * Generate an `.ics` file for an event.
 *
 * @since 0.2.5
 *
 * @param string $template The path of the template to include.
 *
 * @return string
 */
function include_template( $template ) {
	if ( ! get_query_var( 'wsuwp_events_ics' ) ) {
		return $template;
	}

	$ics_template = locate_template( array( 'templates/ics.php' ) );

	if ( '' !== $ics_template ) {
		return $ics_template;
	}

	return $template;
}
