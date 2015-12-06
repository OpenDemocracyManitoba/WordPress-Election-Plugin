jQuery(document).ready( function($) {
	// Hides the fields in the edit screen.
	for ( var field in tm_remove_fields ) {
		if ( !tm_remove_fields.hasOwnProperty( field ) ) {
			continue;
		}

		query = ' .term-' + field + '-wrap, .column-' + field;
		var x = $( query );
		$( query ).hide();
	}
	
	for ( var field in tm_rename_fields ) {
		if ( !tm_rename_fields.hasOwnProperty( field ) ) {
			continue;
		}
		
		var label = tm_rename_fields[field];
		query = ' .term-' + field + '-wrap label, .column-' + field + ' a span';
		var x = $( query );
		$( query ).text(label);
	}
	
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