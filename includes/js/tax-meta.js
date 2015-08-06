jQuery(document).ready( function($) {
	// Hides the fields in the edit screen.
	for ( var field in tm_remove_fields ) {
		if ( !tm_remove_fields.hasOwnProperty( field ) ) {
			continue;
		}

		if ( tm_data['mode'] == 'add' ) {
			query = ' .form-field label:contains("' + field + '")';
		} else {
			query = ' .form-field th:contains("' + field + '")';
		}
		var x = $( query );
		switch ( field ) {
			case 'Name':
			case 'Slug':
			case 'Parent':
			case 'Description':
				if ( tm_data['mode'] == 'add' ) {
					$( query ).each( function( i ) {
						$( this ).parent().hide();
					} );
				} else {
					$( query ).each( function( i ) {
						$( this ).parent().hide();
					} );
				}
				break;
				
		}
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