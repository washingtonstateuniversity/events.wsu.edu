jQuery( document ).ready( function( $ ) {

	"use strict";

	let deck = $( ".deck-today" );
	let cards = deck.find( "article" );
	let card_count = cards.length;
	let max_up_cards = ( $( window ).width() > 693 ) ? 3 : 2;

	// Adds buttons for navigating the deck of today's event cards.
	deck.prepend(
		"<button aria-label='previous' disabled>Previous events</button>" +
		"<button aria-label='next'>Next events</button>"
	);

	// Disables the next button if it isn't needed.
	if ( card_count <= max_up_cards ) {
		$( "button[aria-label='next']" ).prop( "disabled", true );
	}

	// Adds the `up` class to initially visible cards.
	cards.slice( 0, max_up_cards ).addClass( "up" );

	// Adjusts card visibility during browser resizing.
	$( window ).resize( function() {
		let up_cards = deck.find( ".up" );
		let show_from = cards.index( up_cards.first() );

		if ( $( window ).width() > 693 ) {
			max_up_cards = 3;

			if ( cards.last().hasClass( "up" ) &&
				2 === up_cards.length &&
				3 <= card_count ) {
				show_from = show_from - 1;
			}
		} else {
			max_up_cards = 2;
		}

		cards.removeClass( "up" ).slice( show_from, show_from + max_up_cards ).addClass( "up" );

		update_buttons();
	} );

	// Handles card navigation via buttons.
	deck.on( "click", "button", function() {
		navigate_cards( $( this ).attr( "aria-label" ) );
	} );

	// Toggles the `up` class of the cards.
	let navigate_cards = function( direction ) {
		let up_cards = deck.find( ".up" );
		let hide_card = ( "next" === direction ) ? up_cards.first() : up_cards.last();
		let show_card = ( "next" === direction ) ? up_cards.last().next() : up_cards.first().prev();

		hide_card.removeClass( "up" );
		show_card.addClass( "up" );

		update_buttons();
	};

	// Toggles the `disabled` state of the control buttons.
	let update_buttons = function() {
		let prev_button = $( "button[aria-label='previous']" );
		let next_button = $( "button[aria-label='next']" );

		if ( cards.first().hasClass( "up" ) ) {
			prev_button.prop( "disabled", true );
		} else {
			prev_button.prop( "disabled", false );
		}

		if ( cards.last().hasClass( "up" ) ) {
			next_button.prop( "disabled", true );
		} else {
			next_button.prop( "disabled", false );
		}
	};
} );
