<?php
/**
 * Provides editible fields for the quick and bulk edit pages
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/admin/partials
 */

?>

<fieldset class="inline-edit-col-right">
<div class="inline-edit-col">
	<div class="inline-edit-group">
		<?php switch ( $field['type'] ) :
			case 'checkbox': ?>
			
				<?php break;
			default: ?>
				<label class="alignleft">
					<span class="title"><?php echo esc_html( $field['label'] ); ?></span>
				</label>
				<input type="text" name="<?php echo $field['id']; ?>" value="" />
				<?php break;
		endswitch ?>
	</div>
</div>
</fieldset>