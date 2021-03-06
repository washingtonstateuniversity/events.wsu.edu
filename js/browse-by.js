jQuery( document ).ready( function( $ ) {

	let button = $( ".filter li > button" );
	let menu = button.next( "ul" );

	button.attr( "aria-expanded", "false" );

	// Closes any currently open menus when a button is focused.
	button.focus( function() {
		button.attr( "aria-expanded", "false" );
	} );

	// Opens a button's menu with the Space, Enter, or Up or Down Arrow keys.
	button.keydown( function( e ) {
		let button = $( this );
		let menu = button.next( "ul" );

		switch ( e.key ) {
			case " ":
			case "Enter":
			case "ArrowDown":
				button.attr( "aria-expanded", "true" );
				menu.find( "li:first a" ).focus();
				e.stopPropagation();
				e.preventDefault();
				break;

			case "ArrowUp":
				button.attr( "aria-expanded", "true" );
				menu.find( "li:last a" ).focus();
				e.stopPropagation();
				e.preventDefault();
				break;

			default:
				break;
		}
	} );

	// Navigates through menu items with the Up or Down arrow keys.
	// Returns focus to the menu button with Space, Escape, or Tab keys.
	menu.keydown( function( e ) {
		let current_item = $( document.activeElement ).closest( "li" );

		switch ( e.key ) {
			case "ArrowDown":
				if ( current_item.is( ":last-child" ) ) {
					current_item.siblings( ":first-child" ).find( "a" ).focus();
				} else {
					current_item.next().find( "a" ).focus();
				}
				e.stopPropagation();
				e.preventDefault();
				break;

			case "ArrowUp":
				if ( current_item.is( ":first-child" ) ) {
					current_item.siblings( ":last-child" ).find( "a" ).focus();
				} else {
					current_item.prev().find( "a" ).focus();
				}
				e.stopPropagation();
				e.preventDefault();
				break;

			case " ":
			case "Escape":
				$( this ).closest( "ul" ).prev( "button" ).focus();
				e.stopPropagation();
				e.preventDefault();
				break;

			default:
				break;
		}
	} );

	// Toggles the `aria-expanded` state of menu buttons.
	$( document ).click( function( e ) {
		if ( $( window ).width() > 791 ) {
			if ( $( e.target ).is( button ) ) {
				$( e.target ).attr( "aria-expanded", "true" );
			} else {
				button.attr( "aria-expanded", "false" );
			}
		}
	} );

	let menu_container = $( ".filter .column > ul > li" );

	menu_container.blur( function() {
		if ( $( window ).width() > 791 ) {
			$( this ).find( button ).attr( "aria-expanded", "false" );
		}
	} );

	menu_container.mouseover( function() {
		if ( $( window ).width() > 791 ) {
			$( this ).find( button ).attr( "aria-expanded", "true" );
		}
	} );

	menu_container.mouseout( function() {
		if ( $( window ).width() > 791 ) {
			$( this ).find( button ).attr( "aria-expanded", "false" );
		}
	} );

	// Handles the "Browse by" feature for mobile devices and narrower browser widths.
	let browse_form = $( ".filter li form" );

	browse_form.submit( function( e ) {
		e.preventDefault();

		window.location.href = $( this ).find( "select" ).val();
	} );
} );
