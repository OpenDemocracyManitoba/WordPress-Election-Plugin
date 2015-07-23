jQuery(document).ready( function($) {
	for (var $field in ed_remove_columns) {
		if (!ed_remove_columns.hasOwnProperty($field)) {
			continue;
		}

		$query = ' .inline-edit-col span:contains("' + $field + '")';
		switch ($field) {
			case 'Title':
			case 'Slug':
				$($query).each(function (i) {
					$(this).parent().hide();
				});
				break;
			case 'Date':
				$($query).each(function (i) {
					$(this).parent().hide();
				});
				$('.inline-edit-col div.inline-edit-date').each(function (i) {
					$(this).hide();
				});
				break;
			case 'Password':
				$($query).each(function (i) {
					$(this).parent().parent().hide();
				});
				break;
		}
	}
	
	for (var $field in ed_rename_columns) {
		if (!ed_rename_columns.hasOwnProperty($field)) {
			continue;
		}

		$('.inline-edit-col span:contains("' + $field + '")').each(function (i) {
			$(this).text(ed_rename_columns[$field]);
		});
	}
});    

(function($) {
	var $wp_inline_edit = inlineEditPost.edit;
	inlineEditPost.edit = function( id ) {
		$wp_inline_edit.apply( this, arguments );
		var $post_id = 0;
		if ( typeof( id ) == 'object' )
			$post_id = parseInt( this.getId( id ) );
		
		if ( $post_id > 0 ) {
			var $edit_row = $( '#edit-' + $post_id );
			for (var $field in ed_quick_edit) {
				if (!ed_quick_edit.hasOwnProperty($field)) {
					continue;
				}
				$value = $( '#' + $field + '-' + $post_id ).text();
				$input = $edit_row.find('input[name="' + ed_quick_edit[$field] + '"]');
				switch($input.type) {
					case 'checkbox':
					case 'radio':
						$input.attr('checked', $value);
						break;
					default:
						$input.val($value);
				}
			}
		}
	};
	
	$get_bulk_edits = function() {
		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		$ajaxdata = {
			action: 'save_post_bulk_edit',
			post_ids: $post_ids,
		}
		
		for ($field in ed_quick_edit) {
			if (!ed_quick_edit.hasOwnProperty($field)) {
				continue;
			}
			
			$input = $bulk_row.find('input[name="' + ed_quick_edit[$field] + '"]');
			switch ($input.type) {
				case 'radio':
				case 'checkbox':
					$ajaxdata['field' + $field] = $input.attr('checked') ? 1 : 0;
					break;
				default:
					$ajaxdata['field_' + $field ] = $input.val();
					break;
			}
		}
		
		return $ajaxdata;
	};
	
	$( '#bulk_edit' ).on( 'click', null, null, function() {
		$ajaxdata = $get_bulk_edits();
		
		// save the data
		$.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: false,
			cache: false,
			data: $ajaxdata
		});
	});
})(jQuery);