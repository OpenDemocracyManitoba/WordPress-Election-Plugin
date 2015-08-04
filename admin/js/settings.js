jQuery(document).ready( function($) {
	$( '#button_scrape_news' ).click( function( event ) {
		// Perform AJAX call to run the news scraping.
		$.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: true,
			cache: false,
			data: {
				action: 'run_news_scrape'
			}
		});
	});
});