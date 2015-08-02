jQuery( function( $ ) {
	var media_frames = {};
	for ( var image in tm_image_data ) {
		var label = tm_image_data[image]
		if ( !tm_image_data.hasOwnProperty( image ) ) {
			continue;
		}
		
		$( '#' + label + '_add' ).on( 'click', ( function( label, media_frames, image ) {
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
					$( '#' + label + '_img' ).attr( 'src', attachment.url );
					$( '#' + label ).val( attachment.id );
					$( '#' + label + '_add' ).addClass( 'hidden' );
					$( '#' + label + '_del' ).removeClass( 'hidden' );
				} );
				
				media_frames[image].open();
			};
		} )( label, media_frames, image ) );
		$( '#' + label + '_del' ).on( 'click', ( function( label ) {
			return function( event ) {
				event.preventDefault();
				$( '#' + label + '_img' ).attr( 'src', '' );
				$( '#' + label ).val( '' );
				$( '#' + label + '_add' ).removeClass( 'hidden' );
				$( '#' + label + '_del' ).addClass( 'hidden' );
			}
		} )( label ) );
	}
});