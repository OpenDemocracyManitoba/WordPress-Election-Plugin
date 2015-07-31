(function($) {
	// Backup wordpress inlineEditPost.edit so it can be called from customized function. 
	var wp_inline_edit = inlineEditPost.edit;
	inlineEditPost.edit = function( id ) {
		// Call original wordpress inlineEditPost.edit.
		wp_inline_edit.apply( this, arguments );
		var post_id = 0;
		
		// Get the post id of the post being edited.
		if ( typeof( id ) == 'object' ) {
			post_id = parseInt( this.getId( id ) );
		}
		
		if ( post_id > 0 ) {
			var edit_row = $( '#edit-' + post_id );
			for (var field in pm_post_meta) {
				if (!pm_post_meta.hasOwnProperty(field)) {
					continue;
				}
				var value = $( '#' + field + '-' + post_id ).text();
				var input = edit_row.find('input[name="' + pm_post_meta[field] + '"]');
				switch(input[0].type) {
					case 'checkbox':
					case 'radio':
						if ( value ) {
							input.prop('checked', 'True');
						}
						break;
					default:
						input.val(value);
				}
			}
		}
	};
	
	function get_meta_ajaxdata(row) {
		var ajaxdata = {
			action: 'save_post_meta_data',
		}
		
		for (field in pm_post_meta) {
			if (!pm_post_meta.hasOwnProperty(field)) {
				continue;
			}
			
			input = row.find('input[name="' + pm_post_meta[field] + '"]');
			switch (input[0].type) {
				case 'radio':
				case 'checkbox':
					ajaxdata[pm_post_meta[field]] = input.prop('checked') ? 1 : 0;
					break;
				default:
					ajaxdata[pm_post_meta[field]] = input.val();
					break;
			}
		}
		
		return ajaxdata
	}
	
	var wp_inline_save = inlineEditPost.save;
	inlineEditPost.save = function( id ) {
		var post_id = 0;
		
		// Get the post id of the post being edited.
		if ( typeof( id ) == 'object' ) {0
			post_id = parseInt( this.getId( id ) );
		}
		
		if ( post_id > 0 ) {
			var edit_row = $( '#edit-' + post_id );
			var ajaxdata = get_meta_ajaxdata( edit_row );
			ajaxdata['post_id'] = post_id;
			$.ajax({
				url: ajaxurl, // this is a variable that WordPress has already defined for us
				type: 'POST',
				async: true,
				cache: false,
				data: ajaxdata
			});
		}

		// Call original wordpress inlineEditPost.edit.
		wp_inline_save.apply( this, arguments );
	};
	
	$( '#bulk_edit' ).on( 'click', null, null, function() {
		var bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var post_ids = new Array();
		bulk_row.find( '#bulk-titles' ).children().each( function() {
			post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		var ajaxdata = get_meta_ajaxdata( bulk_row );
		ajaxdata['post_ids'] = post_ids;
		// save the data
		$.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: true,
			cache: false,
			data: ajaxdata
		});
	});
})(jQuery);