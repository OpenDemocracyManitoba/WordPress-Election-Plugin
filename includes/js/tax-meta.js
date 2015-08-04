jQuery(document).ready( function($) {
	var x = $( '#addtag' );
	
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

})