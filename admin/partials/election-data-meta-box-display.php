<?php
/**
 * Provide a meta box view for the settings page
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

<form action="options.php" method="POST" enctype="multipart/form-data">
	<?php settings_fields( 'election_data_settings' ); ?>
	<?php do_settings_sections( 'election_data_settings_' . $active_tab ); ?>
	<?php submit_button(); ?>
</form>
<br class="clear" />
