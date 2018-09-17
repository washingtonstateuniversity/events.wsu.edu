<div class="calendar">

	<header><?php echo esc_html( date_i18n( 'F Y' ) ); ?></header>

	<div class="weekday-heading">

		<?php
		$week_headings = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );

		foreach ( $week_headings as $week_heading ) {
			?>
			<div class="day-heading"><?php echo esc_html( $week_heading ); ?></div>
			<?php
		}
		?>

	</div>

	<div class="month">

		<?php WSU\Events\Calendar\get_calendar( date_i18n( 'm' ), date_i18n( 'Y' ) ); ?>

	</div>

</div>
