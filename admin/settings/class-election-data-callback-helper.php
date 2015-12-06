<?php

/**
 * Election_Data Callback Helper Class
 *
 * The callback functions of the settings page
 *
 * @package    Election_Data
 * @subpackage Election_Data/admin/settings
 * @author     Your Name <email@example.com>
 */
class Election_Data_Callback_Helper {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 */
	public function __construct( $plugin_name ) {

		$this->plugin_name = $plugin_name;

	}
	
	private function get_attribute_value( $value ) {
		return "election_data_settings[$value]";
	}

	private function get_id_attribute( $id ) {
		return ' id="election_data_settings[' . $id . ']" ';
	}

	private function get_name_attribute( $name ) {
		return ' name="election_data_settings[' . $name . ']" ';
	}

	private function get_id_and_name_attrubutes( $field_key ) {
		return  $this->get_id_attribute( $field_key ) . $this->get_name_attribute( $field_key );
	}

	private function get_label_for( $id, $desc ) {
		return '<label for="election_data_settings[' . $id . ']"> '  . $desc . '</label>';
	}

	/**
	 * Missing Callback
	 *
	 * If a function is missing for settings callbacks alert the user.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function missing_callback( $args ) {
		printf( __( 'The callback function used for <strong>%s</strong> setting is missing.', $this->plugin_name ), $args['id'] );
	}

	/**
	 * Header Callback
	 *
	 * Renders the header.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function header_callback( $args ) {
		echo '<hr/>';
	}
	
	public static function localize_image( $element_settings ) {
		$translation_array = array();
		foreach ( $element_settings as $settings ) {
			$id = $settings[0];
			$options = $settings[1];
			$translation_array[$id] = "election_data_settings[$id]";
		}
		
		return array( 'ed_settings_image_data' => $translation_array );
	}
	
	public static function localize_button( $element_settings ) {
		$actions = array();
		$messages = array();
		foreach ( $element_settings as $settings ) {
			$options = $settings[1]['options'];
			$id = $options['id'];
			$actions[$id] = $options['action'];
			if ( array_key_exists( 'message', $options ) ) {
				$messages[$id] = $options['message'];
			}
		}
		
		return array(
			'ed_settings_button_actions' => $actions,
			'ed_settings_button_messages' => $messages,
		);
	}
	
	public static function js_dependancies_image() {
		wp_enqueue_media();
		
		return array( 'jquery', 'media-upload', 'thickbox' );
	}
	
	public function image_callback( $args ) {
		$value = Election_Data_Option::get_option( $args['id'], '' );
		$url = $value ? esc_url( wp_get_attachment_url( $value ) ) : '';
		$attr_base = $this->get_attribute_value( $args['id'] );
		?>
		<image id="<?php echo $attr_base; ?>_img" src='<?php echo $url; ?>'/>
		<input type='text' <?php echo $this->get_id_and_name_attrubutes( $args['id'] ); ?> value='<?php echo $value; ?>' style='display:none' >
		<br><input type='button' id="<?php echo $attr_base; ?>_add" name="<?php echo $attr_base; ?>_add" value='Select Image' <?php echo $value ? 'class="hidden"' : ''; ?>/>
		<br><input type='button' id="<?php echo $attr_base; ?>_del" name="<?php echo $attr_base; ?>_del" value='Remove Image' <?php echo $value ? '' : 'class="hidden"'; ?>/>
		<?php if ( $args['desc'] ) : ?>
			<br><label><?php echo $args['desc']; ?></label>
		<?php endif;
	}

	public function button_callback( $args ) {
		$options = $args['options']
		?>
		<input type='button' id='<?php echo $options['id']; ?>' name='<?php echo $options['id']; ?>' value='<?php echo $options['label']; ?>' />
		<?php if ( $args['desc'] ) : ?>
			<br><label><?php echo $args['desc']; ?></label>
		<?php endif;
	}
	
	protected function display_import_export( $mode, $args ) {
		$options = $args['options'];
		$form_id = "{$options['id']}_form";
		$radio_name = "election_data_settings[{$args['id']}]";
		$missing_modules = array();
		if ( $mode == 'import' ) {
			$legend = __( 'Select the file type to import', $this->plugin_name );
			$button_label = __( 'Import', $this->plugin_name );
			$overwrite_label = __( 'Overwrite existing non-empty fields', $this->plugin_name  );
			$upload_id = "{$options['id']}_file";
			$overwrite_id = "{$options['id']}_overwrite_data";
		} else {
			$legend = __( 'Select the format of the file to export', $this->plugin_name );
			$button_label = __( 'Export', $this->plugin_name );
		}
		?>
		<fieldset class="radiogroup">
		<legend><?php echo $legend; ?></legend>
			<ul class="radio">
				<?php foreach ( $options['formats'] as $type => $label ) : 
					if ( isset( $options['required_modules'][$type] ) ) :
						$loaded = true;
						foreach ( $options['required_modules'][$type] as $module ) :
							if ( !extension_loaded( $module ) ) :
								$missing_modules[$module] = true;
								$loaded = false;
							endif;
						endforeach;
						
						$disabled = $loaded ? '' : ' disabled';
					else :
						$disabled = '';
					endif;
					if ( isset( $options['skip_if_modules_loaded'][$type] ) ) :
						$loaded = false;
						foreach ( $options['skip_if_modules_loaded'][$type] as $module ) :
							$loaded |= extension_loaded( $module );
						endforeach;
						if ( $loaded ) :
							continue;
						endif;
					endif;
					$radio_id = "election_data_settings[{$args['id']}][$type]"; ?>
					<li><input type="radio" name="<?php echo $radio_name; ?>" value="<?php echo $type; ?>" id="<?php echo "$radio_id"; ?>" <?php echo checked( $type, $options['default'], false ) . $disabled ?>/>
					<label for="<?php echo $radio_id; ?>"><?php echo $label; ?></label></li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
		<?php if ( $mode == 'import' ) : ?>
			<input type="checkbox" name="<?php echo $overwrite_id; ?>" id="<?php echo $overwrite_id; ?>" value="overwrite"/><label for="<?php echo $overwrite_id; ?>"><?php echo $overwrite_label; ?></label><br>
			<input type="file" name="<?php echo $upload_id; ?>" id="<?php echo $upload_id; ?>"/><br>
		<?php endif; ?>
		<button type="submit" name="ed_import_export" value="<?php echo $mode; ?>"><?php echo $button_label; ?></button>
		<br>
		<label><?php echo $args['desc']; ?></label>
		<?php if ( $missing_modules ) : ?>
			<br><label>Some options have been disabled due to missing modules. The missing modules are:</label>
			<ul>
				<?php foreach ( $missing_modules as $module => $value ) : ?>
					<li><?php echo $module; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif;
	}
	
	public function import_callback( $args ) {
		$this->display_import_export( 'import', $args );
	}
	
	public function export_callback( $args ) {
		$this->display_import_export( 'export', $args );
	}
	
	/**
	 * Checkbox Callback
	 *
	 * Renders checkboxes.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function checkbox_callback( $args ) {

		$value = Election_Data_Option::get_option( $args['id'] );
		$checked = isset( $value ) ? checked( 1, $value, false ) : '';

		$html = '<input type="checkbox" ';
		$html .= $this->get_id_and_name_attrubutes( $args['id'] );
		$html .= 'value="1" ' . $checked . '/>';

		$html .= '<br />';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );

		echo $html;
	}

	/**
	 * Multicheck Callback
	 *
	 * Renders multiple checkboxes.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function multicheck_callback( $args ) {

		if ( empty( $args['options'] ) ) {
			printf( __( 'Options for <strong>%s</strong> multicheck is missing.', $this->plugin_name ), $args['id'] );
			return;
		}

		$old_values = Election_Data_Option::get_option( $args['id'], array() );

		$html ='';

		foreach ( $args['options'] as $field_key => $option ) {

			if ( isset( $old_values[$field_key] ) ) {
				$enabled = $option;
			} else {
				$enabled = NULL;
			}

			$checked = checked( $option, $enabled, false );

			$html .= '<input type="checkbox" ';
			$html .= $this->get_id_and_name_attrubutes( $args['id'] . '][' . $field_key );
			$html .= ' value="' . $option . '" ' . $checked . '/> ';

			$html .= $this->get_label_for( $args['id'] . '][' . $field_key, $option );
			$html .= '<br/>';
		}

		$html .= '<p class="description">' . $args['desc'] . '</p>';

		echo $html;
	}

	/**
	 * Radio Callback
	 *
	 * Renders radio boxes.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function radio_callback( $args ) {

		if ( empty( $args['options'] ) ) {
			printf( __( 'Options for <strong>%s</strong> radio is missing.', $this->plugin_name ), $args['id'] );
			return;
		}

		$old_value = Election_Data_Option::get_option( $args['id'] );

		$html = '';

		foreach ( $args['options'] as $field_key => $option ) {

			if ( !empty( $old_value ) ) {
				$checked = checked( $field_key, $old_value,false );
			} else {
				$checked = checked( $args['std'], $field_key, false );
			}

			$html .= '<input type="radio"';
			$html .= $this->get_name_attribute( $args['id'] );
			$html .= $this->get_id_attribute( $args['id'] . '][' . $field_key );
			$html .= ' value="' . $field_key . '" ' . $checked . '/> ';

			$html .= $this->get_label_for( $args['id'] . '][' . $field_key, $option );
			$html .= '<br/>';

		}

		$html .= '<p class="description">' . $args['desc'] . '</p>';
		echo $html;
	}

	/**
	 * Text Callback
	 *
	 * Renders text fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function text_callback( $args ) {

		$this->input_type_callback( 'text', $args );

	}

	/**
	 * Email Callback
	 *
	 * Renders email fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function email_callback( $args ) {

		$this->input_type_callback( 'email', $args );

	}

	/**
	 * Url Callback
	 *
	 * Renders url fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function url_callback( $args ) {

		$this->input_type_callback( 'url', $args );

	}

	/**
	 * Password Callback
	 *
	 * Renders password fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function password_callback( $args ) {

		$this->input_type_callback( 'password', $args );

	}

	/**
	 * Input Type Callback
	 *
	 * Renders input type fields.
	 *
	 * @since 	1.0.0
	 * @param 	string $type Input Type
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	private function input_type_callback( $type, $args ) {

		$value = Election_Data_Option::get_option( $args['id'], $args['std']  );

		$html = '<input type="' . $type . '" ';
		$html .= $this->get_id_and_name_attrubutes( $args['id'] );
		$html .= 'class="' . $args['size'] . '-text" ';
		$html .= 'value="' . esc_attr( stripslashes( $value ) ) . '"/>';

		$html .= '<br />';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );
		echo $html;
	}

	/**
	 * Number Callback
	 *
	 * Renders number fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function number_callback( $args ) {

		$value = Election_Data_Option::get_option( $args['id'] );


		$html = '<input type="number" ';
		$html .= $this->get_id_and_name_attrubutes( $args['id'] );
		$html .= 'class="' . $args['size'] . '-text" ';
		$html .= 'step="' . $args['step'] . '" ';
		$html .= 'max="' . $args['max'] . '" ';
		$html .= 'min="' . $args['min'] . '" ';
		$html .= 'value="' . $value . '"/>';

		$html .= '<br />';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );

		echo $html;
	}

	/**
	 * Textarea Callback
	 *
	 * Renders textarea fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function textarea_callback( $args ) {

		$value = Election_Data_Option::get_option( $args['id'], $args['std']  );


		$html = '<textarea ';
		$html .= 'class="' . $args['size'] . '-text" ';
		$html .= 'cols="50" rows="5" ';
		$html .= $this->get_id_and_name_attrubutes( $args['id'] ) . '>';
		$html .= esc_textarea( stripslashes( $value ) );
		$html .= '</textarea>';

		$html .= '<br />';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );

		echo $html;
	}

	/**
	 * Select Callback
	 *
	 * Renders select fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function select_callback( $args ) {

		$value = Election_Data_Option::get_option( $args['id'] );

		$html = '<select ' . $this->get_id_and_name_attrubutes( $args['id'] ) . '/>';

			foreach ( $args['options'] as $option => $option_name ) {
				$selected = selected( $option, $value, false );
				$html .= '<option value="' . $option . '" ' . $selected . '>' . $option_name . '</option>';
			}

		$html .= '</select>';
		$html .= '<br />';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );

		echo $html;
	}

	/**
	 * Rich Editor Callback
	 *
	 * Renders rich editor fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @global 	$wp_version WordPress Version
	 */
	public function rich_editor_callback( $args ) {
		global $wp_version;

		$value = Election_Data_Option::get_option( $args['id'] );

		if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
			ob_start();
			wp_editor( stripslashes( $value ), 'election_data_settings_' . $args['id'], array( 'textarea_name' => 'election_data_settings[' . $args['id'] . ']', 'wpautop' => false ) );
			$html = ob_get_clean();
		} else {
			$html = '<textarea' . $this->get_id_and_name_attrubutes( $args['id'] ) . 'class="' . $args['size'] . '-text" rows="10" >' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		}

		$html .= '<br/>';
		$html .= $this->get_label_for( $args['id'], $args['desc'] );
		$html .= '<br/>';

		echo $html;
	}

	/**
	 * Upload Callback
	 *
	 * Renders upload fields.
	 *
	 * @since 	1.0.0
	 * @param 	array $args Arguments passed by the setting
	 * @return 	void
	 */
	public function upload_callback( $args ) {


		$html = '<input type="text" ';
		$html .= $this->get_id_and_name_attrubutes( $args['id'] );
		$html .= 'class="' . $args['size'] . '-text ' . 'election_data_upload_field" ';
		$html .= ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';

		$html .= '<span>&nbsp;<input type="button" class="' .  'election_data_settings_upload_button button-secondary" value="' . __( 'Upload File', $this->plugin_name ) . '"/></span>';

		$html .= $this->get_label_for( $args['id'], $args['desc'] );

		echo $html;
	}
}
