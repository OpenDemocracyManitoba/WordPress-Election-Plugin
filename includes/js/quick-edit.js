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