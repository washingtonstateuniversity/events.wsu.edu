<?php

namespace WSU\Events\Organizations_Shortcode;

add_shortcode( 'wsu_events_organizations', 'WSU\Events\Organizations_Shortcode\display', 11 );

/**
 * Display an index of organization terms.
 *
 * @since 0.1.0
 *
 * @param array $atts
 */
function display( $atts ) {
	$defaults = array(
		'hide_empty' => 'true',
	);

	$atts = shortcode_atts( $defaults, $atts );

	$hide_empty = ( 'false' === $atts['hide_empty'] ) ? false : true;

	$taxonomy = 'wsuwp_university_org';

	ob_start();

	$terms = get_terms( array(
		'taxonomy' => $taxonomy,
		'hide_empty' => $hide_empty,
		'parent' => '0',
	) );

	echo '<ul class="organization-index">';

	foreach ( $terms as $term ) {

		echo '<li><h2 class="level-one">' . esc_html( $term->name ) . 's</h2>';

		$child_terms = get_terms( $taxonomy, array(
			'hide_empty' => $hide_empty,
			'parent' => $term->term_id,
		) );

		echo '<ul class="level-two">';

		foreach ( $child_terms as $child ) {

			$child_link = get_term_link( $child->term_id, $taxonomy );

			echo '<li><a href="' . esc_url( $child_link ) . '">' . esc_html( $child->name ) . '</a>';

			$grandchild_terms = get_terms( $taxonomy, array(
				'hide_empty' => $hide_empty,
				'parent' => $child->term_id,
			) );

			if ( empty( $grandchild_terms ) ) {
				continue;
			}

			echo '<ul class="level-three">';

			foreach ( $grandchild_terms as $grandchild ) {
				$grandchild_link = get_term_link( $grandchild->term_id, $taxonomy );

				echo '<li><a href="' . esc_url( $grandchild_link ) . '">' . esc_html( $grandchild->name ) . '</a></li>';
			}

			echo '</ul>';
		}

		echo '</ul>';

		echo '</li>';
	}

	echo '</ul>';

	$html = ob_get_clean();

	return $html;
}
