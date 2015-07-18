<?php

?>
<?php foreach ($fields as $field) : ?>
	<div class='form-field'>
		<label for='<?php echo $field['id']; ?>'><?php echo $field['label']; ?></label>
		<?php switch ($field['type'] ) :
			case 'image': ?>
				<img id='<?php echo $field['id'], '_img'; ?>' src='' style='max-width:100%' />
				<input type='hidden' name='<?php echo $field['id']; ?>' id=<?php echo $field['id']; ?>' value='' />
				<input type='button' id='<?php echo $field['id'], '_button'; ?>' name='<?php echo $field['id'], '_button'; ?>' value='<?php echo $field['std']; ?>' />
				<?php break;
			default: ?>
				<input type='<?php echo $field['type']; ?>' id='<?php echo $field['id']; ?>' name='<?php echo $field['id']; ?>' value='<?php echo $field['std']; ?>' />
			<?php endswitch ?>
		<br /><?php echo $field['desc']; ?>
	</div>
<?php endforeach ?>
