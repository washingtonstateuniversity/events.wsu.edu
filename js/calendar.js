jQuery( document ).ready( function( $ ) {

	"use strict";

	let calendar = $( ".calendar" );
	let calendar_header = calendar.find( "header" );
	let calendar_body = calendar.find( ".month" );

	// Adds buttons for navigation to the previous or next month.
	calendar_header.after(
		"<button class='previous'>Previous month</button>" +
		"<button class='next'>Next month</button>"
	);

	// Handles month navigation.
	calendar.on( "click", "button", function() {
		let data = {
			action: "load_month",
			nonce: window.calendar_navigation.nonce,
			current_month: calendar_header.text(),
			direction: $( this ).attr( "class" )
		};

		$.post( window.calendar_navigation.ajax_url, data, function( response ) {
			let new_month = $.parseJSON( response );

			calendar_header.text( new_month.heading );
			calendar_body.html( new_month.body );
		} );
	} );
} );
