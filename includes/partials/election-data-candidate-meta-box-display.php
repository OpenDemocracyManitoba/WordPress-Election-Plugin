<?php
/**
 * Provide a meta box view for the new/edit candidate pages
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/admin/partials
 */

/**
 * Meta Box
 *
 * Renders a single meta box.
 *
 * @since       1.0.0
*/

?>
<table class="form-table">
	<?php foreach( $fields as $field ) :
		$meta = get_post_meta( $candidate->ID, $field['meta_id'], true ); ?>
		<tr>
			<th style='width: 20%'>
				<label for='<?php echo $field['id']; ?>'><?php echo $field['label']; ?></label>
			</th>
			<td>
				<?php switch( $field['type'] ) :
					case 'checkbox':
					case 'radio': ?>
						<input type='<?php echo $field['type']; ?>' name='<?php echo $field['id']; ?>' id='<?php echo $field['id']; ?>' value='true' <?php echo ($meta ? 'checked' : ''); ?> size='30' />
						<?php error_log ( "meta: $meta  " . ($meta ? 'checked' : '' ) ); ?>
						<br /><?php echo $field['desc'];
						break;
					default: ?>
						<input type='<?php echo $field['type']; ?>' name='<?php echo $field['id']; ?>' id='<?php echo $field['id']; ?>' value='<?php echo $meta ? $meta : $field['std']; ?>' size='30' style='width:97%' />
						<br /><?php echo $field['desc'];
						break;
				endswitch ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>