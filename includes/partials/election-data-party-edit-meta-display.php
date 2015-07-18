<?php

$term_meta = get_option( 'taxonomy_' . $term->term_id );
error_log( $term->term_id );
error_log( print_r( $term_meta, true ) );
?>
<?php foreach ($fields as $field) : ?>
	<tr class='form-field'>
		<th scope="row">
			<label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label>
		</th>
		<td>
			<?php switch($field['type']) :
				case 'image': ?>
					<img id='<?php echo $field['id'], '_img'; ?>' src='' style='max-width:100%' />
					<input type='hidden' name='<?php echo $field['id']; ?>' id=<?php echo $field['id']; ?>' value='' />
					<input type='button' id='<?php echo $field['id'], '_button'; ?>' name='<?php echo $field['id'], '_button'; ?>' value='<?php echo $field['std']; ?>' />
					<?php break;
				default: ?>
					<input type='<?php echo $field['type']; ?>' id='<?php echo $field['id']; ?>' name='<?php echo $field['id']; ?>' value='<?php echo esc_attr( isset( $term_meta[$field['meta_id']] ) ? $term_meta[$field['meta_id']] : ''); ?>' />
					<?php break;
			endswitch ?>
			<br /><?php echo $field['desc']; ?>
		</td>
	</tr>
<?php endforeach ?>
