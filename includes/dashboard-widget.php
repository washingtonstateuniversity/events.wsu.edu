<?php

namespace WSU\Events\Dashboard_Widget;

add_action( 'admin_init', __NAMESPACE__ . '\\settings_init' );
add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\\widget_setup' );

/**
 * Register settings for the Q&A Dashboard widget on the Reading page.
 *
 * @since 0.4.1
 */
function settings_init() {
	// Register the "events_q_a_dashboard_widget" setting.
	register_setting( 'reading', 'events_q_a_dashboard_widget' );

	// Register the section.
	add_settings_section(
		'events_q_a_dashboard_widget_section',
		'Events Q&A Dashboard Widget',
		null,
		'reading'
	);

	// Register the "Display content from" field.
	add_settings_field(
		'events_q_a_dashboard_widget_page',
		'Display content from',
		__NAMESPACE__ . '\\page_input_display',
		'reading',
		'events_q_a_dashboard_widget_section'
	);
}

/**
 * Display the input for selecting the page to pull content from for the Q&A widget.
 *
 * 0.4.1
 */
function page_input_display() {
	$pages = get_pages();
	$setting = get_option( 'events_q_a_dashboard_widget' );
	?>
	<select name="events_q_a_dashboard_widget">
		<option value="">Select a page</option>
		<?php foreach ( $pages as $page ) { ?>
			<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page->ID, $setting ); ?>><?php echo esc_html( $page->post_title ); ?></option>
		<?php } ?>
	</select>

	<?php
}

/**
 * Reconfigures the Dashboard widgets.
 *
 * @since 0.4.3
 */
function widget_setup() {
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );

	add_screen_option( 'layout_columns',
		array(
			'max' => 2,
			'default' => 1,
		)
	);

	wp_add_dashboard_widget(
		'events_q_a_dashboard_widget',
		'Events Q&A',
		__NAMESPACE__ . '\\dashboard_widget_display'
	);
}

/**
 * Displays the Q&A widget content.
 *
 * @since 0.4.1
 */
function dashboard_widget_display() {
	$page_id = get_option( 'events_q_a_dashboard_widget' );

	if ( ! $page_id ) {
		return;
	}

	$page = get_post( absint( $page_id ) );

	echo wp_kses_post( wpautop( $page->post_content ) );
}
