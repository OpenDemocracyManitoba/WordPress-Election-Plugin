jQuery(document).ready( function($) {
	$('span:contains("Title")').each(function (i) {
		$(this).text('Name');
	});-
	$('span:contains("Password")').each(function (i) {
		$(this).parent().parent().hide();
	});
	$('span:contains("Date")').each(function (i) {
		$(this).parent().hide();
	});
	$('.inline-edit-date').each(function (i) {
		$(this).hide();
	});
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
				$edit_row.find( 'input[name="' + ed_quick_edit[$field] + '"]' ).val( $value );
			}
		}
	};
})(jQuery);