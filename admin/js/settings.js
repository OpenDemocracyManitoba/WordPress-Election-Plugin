jQuery(document).ready( function($) {
	$( '#button_scrape_news' ).click( function( event ) {
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
	} );
});