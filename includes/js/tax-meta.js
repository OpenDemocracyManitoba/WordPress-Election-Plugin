(function($) {
	var x = $( '#addtag' );
	$( '#addtag' ).submit(function( event ) {
		$( '[id^="' + tm_data['prefix'] + '"]' ).each( function(item) {
			console.log(item);
		});
	});
	
	$( '#submit' ).click( function( event ) {
		$( '[id^="' + tm_data['prefix'] + '"]' ).each( function(item) {
			console.log(item);
		});
		
		$.each($("#submit").data("events"), function(i, event) {
			console.log(i);
			$.each(event, function(j, h) {
				console.log(h.handler);
			});
		});
		$.each($("#addtag").data("events"), function(i, event) {
			console.log(i);
			$.each(event, function(j, h) {
				console.log(h.handler);
			});
		});
	});
	
	$( document ).ajaxComplete(function( event, xhr, settings ) {
      try{
        respo = $.parseXML(xhr.responseText);
      
        //exit on error
        if ($(respo).find('wp_error').length) return;
        
        $(respo).find('response').each(function(i,e){
          if ($(e).attr('action').indexOf("add-tag") > -1){
            var tid = $(e).find('term_id');
            if (tid){
              $('#addtag')[0].reset();
			  }
          }
        });
      }catch(err) {}
    });

})(jQuery);