jQuery(function($) {
	var action = 'bulk_set_parent'
	var name = ed_tax_bulk_local['set_parent'];
	var element = $('#ed_input_set_parent');
	
	$( '.actions select' ).each(function() {
		var $option = $( this ).find( 'option:first' );
		
		$option.after( $( '<option>', { value: action, html: name }) );
	}).change(function() {
		var $select = $( this );
			
		if ( $select.val() === action ) {
			element.insertAfter( $select ).css( 'display', 'inline' ).find( ':input' ).focus();
		} else {
			element.css( 'display', 'none' );
		}
	});
});