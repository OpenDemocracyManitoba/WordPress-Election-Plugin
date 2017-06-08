jQuery(document).ready( function($) {
	for ( var id in pm_load_button_ajax ) {
		if ( !pm_load_button_ajax.hasOwnProperty( id ) ) {
			continue;
		}
		
		$( '#' + id + '_button' ).click( ( function( id, action ) {
			return function(event) {
				var ajaxdata = {
					action: action,
				};
				$.ajax({
					url: ajaxurl, // this is a variable that WordPress has already defined for us
					type: 'POST',
					cache: false,
					data: ajaxdata,
					success: function( data ) {
						$( '#' + id ).val( data );
					},
				});
			};
		} )( id, pm_load_button_ajax[id] ) );
	}
});
