jQuery( document ).ready( function( $ ) {

	let button = $( ".filter button" );
	let menu = button.next( "ul" );

	button.attr( "aria-haspopup", "true" );

	menu.attr( "role", "menu" )
		.find( "li" ).attr( "role", "none" )
		.find( "a" ).attr( {
			"role": "menuitem",
			"tabindex": "-1"
		} );

	// Closes any currently open menus when a button is focused.
	button.focus( function() {
		button.removeAttr( "aria-expanded" );
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
			case "Tab":
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
		if ( $( e.target ).is( button ) ) {
			$( e.target ).attr( "aria-expanded", "true" );
		} else {
			button.removeAttr( "aria-expanded" );
		}
	} );

	let menu_container = $( ".filter .column > ul > li" );

	menu_container.blur( function() {
		$( this ).find( button ).removeAttr( "aria-expanded" );
	} );

	menu_container.mouseover( function() {
		$( this ).find( button ).attr( "aria-expanded", "true" );
	} );

	menu_container.mouseout( function() {
		$( this ).find( button ).removeAttr( "aria-expanded" );
	} );
} );
