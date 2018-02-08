jQuery( document ).ready( function( $ ) {
	"use strict";

	let featured_events = $( ".selected-featured-events" );

	featured_events.sortable( {
		start: function( e, ui ) {
			ui.placeholder.height( ui.item.height() );
		}
	} );

	let process_featured_events = function( ) {
		let post_ids = [];
		featured_events.find( ".featured-event-single" ).each( function() {
			post_ids.push( $( this ).data( "featured-event-id" ) );
		} );

		$( "input[data-customize-setting-link='featured_events']" ).attr( "value", post_ids ).trigger( "change" );
	};

	featured_events.bind( "sortstop", process_featured_events );

	$( featured_events ).on( "click", ".remove-featured", function( e ) {
		e.preventDefault();
		$( this ).parent().remove();
		process_featured_events();
		featured_events.append( "<div class='featured-event-empty'>No featured event selected for this area.</div>" );
	} );

	var featured_event_input = $( "#featured-event-title" );

	featured_event_input.autocomplete( {
		source: window.wsu_page_curation.featured_events_endpoint,
		minLength: 2,

		// Add the selected item label to the input field rather than the value.
		select: function( event, ui ) {
			event.preventDefault();
			var next_slot = $( ".featured-event-empty" ).first();

			next_slot.removeClass( "featured-event-empty" ).addClass( "featured-event-single" );
			next_slot.data( "featured-event-id", ui.item.value );
			next_slot.html( "<p>" + ui.item.label + "</p>" + "<button class='remove-featured'>Remove</button>" );

			$( this ).val( "" );

			var post_ids = [];

			$( ".featured-event-single" ).each( function() {
				post_ids.push( $( this ).data( "featured-event-id" ) );
			} );

			$( "input[data-customize-setting-link='featured_events']" ).attr( "value", post_ids ).trigger( "change" );
		},

		// Don't show selected titles in the input before selection.
		focus: function( event ) {
			event.preventDefault();
		}
	} );
} );
