jQuery(document).ready( function($) {
	$.fn.exists = function () {
		return this.length !== 0;
	}
	for ( var id in ed_settings_button_actions ) {
		if ( !ed_settings_button_actions.hasOwnProperty( id ) ) {
			continue;
		}
		var action = ed_settings_button_actions[id];
		var confirm_message = id in ed_settings_button_messages ? ed_settings_button_messages[id] : '';
		
		$( '#' + id ).click( ( function( action, confirm_message ) {
			return function( event ) {
				if ( !confirm_message || confirm( confirm_message ) ) {
					$.ajax( {
						url: ajaxurl,
						type: 'POST',
						async: true,
						cache: false,
						data: {
							action: action
						}
					} );
				}
			};
		} )( action, confirm_message ) );
	}
	
	/*$( '#button_scrape_news' ).click( function( event ) {
		// Perform AJAX call to run the news scraping.
		$.ajax( {
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: true,
			cache: false,
			data: {
				action: 'election_data_scrape_news'
			}
		} );
	} );
	
	$( '#button_erase_site' ).click( function( event ) {
		if ( confirm( 'Are you sure? This will remove all election related data from the site.' ) ) {
			$.ajax( {
				url: ajaxurl, // this is a variable that WordPress has already defined for us
				type: 'POST',
				async: true,
				cache: false,
				data: {
					action: 'election_data_erase_site'
				}
			} );
		}
	} ); */
	
	var media_frames = {};
	for ( var image in ed_settings_image_data ) {
		var label = ed_settings_image_data[image]
		if ( !ed_settings_image_data.hasOwnProperty( image ) ) {
			continue;
		}

		var tmp = $( '#' + label + '_add' );
		
		$( document.getElementById( label + '_add' ) ).click( ( function( label, media_frames, image ) {
			return function( event ) {
				if ( image in media_frames ) {
					media_frames[image].open();
					return;
				}
				
				media_frames[image] = wp.media({
					title: '',
					button: {
						text: 'Select'
					},
					multiple: false  // Set to true to allow multiple files to be selected
				} );
				
				media_frames[image].on( 'select', function() {
					// Get media attachment details from the frame state
					var attachment = media_frames[image].state().get( 'selection' ).first().toJSON();
					$( document.getElementById( label + '_img' ) ).attr( 'src', attachment.url );
					$( document.getElementById( label ) ).val( attachment.id );
					$( document.getElementById( label + '_add' ) ).addClass( 'hidden' );
					$( document.getElementById( label + '_del' ) ).removeClass( 'hidden' );
				} );
				
				media_frames[image].open();
			};
		} )( label, media_frames, image ) );
		$( document.getElementById( label + '_del' ) ).click( ( function( label ) {
			return function( event ) {
				event.preventDefault();
				$( document.getElementById( label + '_img' ) ).attr( 'src', '' );
				$( document.getElementById( label ) ).val( '' );
				$( document.getElementById( label + '_add' ) ).removeClass( 'hidden' );
				$( document.getElementById( label + '_del' ) ).addClass( 'hidden' );
			}
		} )( label ) );
	}
});
