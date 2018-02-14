<?php
/**
 * The custom Customizer Control used to manage the selection of
 * featured events for display on the front page.
 */

namespace WSU\Events\Page_Curation\Customizer;

class Featured_Events_Control extends \WP_Customize_Control {

	/**
	 * Output the elements used to select featured events for display on
	 * the front page.
	 *
	 * @since 0.1.0
	 */
	public function render_content() {
		$post_ids = $this->value();

		if ( $post_ids && is_string( $post_ids ) ) {
			$post_ids = explode( ',', $post_ids );
		} else {
			$post_ids = array();
		}

		if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif;
		?>

		<div class="featured-event-selection">
			<label for="featured-event-title">Find featured event</label>
			<input id="featured-event-title" type="text" value="" />
		</div>

		<div class="selected-featured-events">
			<?php
			for ( $i = 0; $i <= 3; $i++ ) {
				if ( isset( $post_ids[ $i ] ) ) {
					?>
					<div class="featured-event-single" data-featured-event-id="<?php echo esc_attr( $post_ids[ $i ] ); ?>">
						<p><?php echo esc_html( get_the_title( $post_ids[ $i ] ) ); ?></p>
						<button class="remove-featured">Remove</button>
					</div>
					<?php
				} else {
					?>
					<div class="featured-event-empty">No featured event selected for this area.</div>
					<?php
				}
			}
			?>
		</div>

		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $post_ids ) ); ?>"/>
		<?php
	}
}
