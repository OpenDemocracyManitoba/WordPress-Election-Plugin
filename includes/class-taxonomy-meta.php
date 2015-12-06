<?php

/**
 * Post Meta Data handler.
 *
 * @package		Election_Data
 * @since		1.0
 * @author 		Robert Burton <RobertBurton@gmail.com>
 *
 */

require_once plugin_dir_path( __FILE__ ) . 'class-post-import.php';
 
class Tax_Meta {
	
	/*
	 * The name of the taxonomy.
	 *
	 * @var string
	 * @access protected
	 *
	 */
	protected $taxonomy;
	
	/**
	 * The prefix used for the id and name of the custom fields.
	 *
	 * @var string
	 * @access protected
	 *
	 */
	protected $prefix;
	
	/**
	 * Holds the definition of the meta data fields.
	 *
	 * @var array
	 * @access protected
	 *
	 */
	protected $fields;
	
	/**
	 * Constructor
	 *
	 * @since 1.0
	 * @access protected
	 * @param string $taxonomy
	 * @param array $fields
	 *
	 */
	public function __construct( $args, $define_hooks = true ) {
		$this->fields = $args['fields'];
		$taxonomy = $args['taxonomy'];
		$this->taxonomy = $taxonomy;
		$this->prefix = "tm_{$taxonomy}_";
		$this->hidden = isset( $args['hidden'] ) ? $args['hidden'] : array();
		$this->renamed = isset( $args['renamed'] ) ? $args['renamed'] : array();
		
		if ( $define_hooks ) {
			add_action( 'delete_term', array( $this, 'delete_meta'), 10, 3 );
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_form_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_form_fields' ) );
			add_action( "edited_$taxonomy", array( $this, 'save_meta' ) );
			add_action( "created_$taxonomy", array( $this, 'save_meta' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}
	
	public function enqueue_scripts()
	{
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( $taxonomy == $this->taxonomy ) {
			$script_id = "tax-meta-$taxonomy";
			wp_register_script( $script_id, plugin_dir_url( __FILE__ ) . 'js/tax-meta.js', array( 'jquery' ), '', true );
			$translation_array = array(
				'mode' => isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ? 'edit' : 'add',
			);
			wp_localize_script( $script_id, 'tm_data', $translation_array );
			$translation_array = array();
			foreach ( $this->hidden as $field ) {
				$translation_array[$field] = $field;
			}
			wp_localize_script( $script_id, 'tm_remove_fields', $translation_array );
			$translation_array = array();
			foreach ( $this->renamed as $old => $new ) {
				$translation_array[$old] = $new;
			}
			wp_localize_script( $script_id, 'tm_rename_fields', $translation_array );
			wp_enqueue_script( $script_id );
			if ( $this->has_type( 'image' ) ) {
				$script_id = "tax-meta-image-$taxonomy";
				wp_register_script( $script_id, plugin_dir_url( __FILE__ ) . 'js/tax-meta-image.js', array( 'jquery', 'media-upload', 'thickbox' ), '', true );
				$translation_array = array();
				foreach ( $this->fields as $field ) {
					if ( $field['type'] == 'image' ) {
						$translation_array[$field['id']] = $this->prefix . $field['id'];
					}
				}
				
				wp_localize_script( $script_id, 'tm_image_data', $translation_array );
				wp_enqueue_script( $script_id );
				wp_enqueue_media();
			}
			if ( $this->has_type( 'text_with_load_value_button' ) ) {
				$script_id = "tax-meta-load_button-$taxonomy";
				wp_register_script( $script_id, plugin_dir_url( __FILE__ ) . 'js/tax-meta-text-load.js', array( 'jquery', ), '', true );
				$translation_array = array();
				foreach ( $this->fields as $field ) {
					if ( $field['type'] == 'text_with_load_value_button' ) {
						$translation_array["{$this->prefix}{$field['id']}"] = $field['ajax_callback'];
					}
				}
				wp_localize_script( $script_id, 'tm_load_button_ajax', $translation_array );
				wp_enqueue_script( $script_id );
			}
		}
	}
	
	protected function has_type( $type )
	{
		foreach ( $this->fields as $field ) {
			if ( $field['type'] == $type ) {
				return true;
			}
		}
		
		return false;
	}
	
	public function add_form_fields( $term_name )
	{
		$this->form_fields( '', 'add' );
	}
	
	public function edit_form_fields( $term )
	{
		$this->form_fields( $term->term_id, 'edit' );
	}
	
	protected function display_field_label( $field, $mode ) {
		if ( $mode == 'edit' ) {
			$header = '<th scope="row">';
			$footer = '</th>';
		} else {
			$header = '';
			$footer = '';
		}
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$label = $field['label'];
		echo "$header<label for='$id'>$label</label>$footer";
	}
	
	protected function show_wysiwyg( $field, $mode, $value ) {
		if ( $mode == 'edit' ) {
			$header = '<td>';
			$footer = '</td></tr>';
			$value = $value ? $value : $field['std'];
			echo '<tr class="form-field">';
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$value = $field['std'];
		}
		$this->display_field_label( $field, $mode );
		echo $header;
		wp_editor( $value, esc_attr( "{$this->prefix}{$field['id']}" ), array( 'wpautop' => false ) );
		$desc = $field['desc'];
		echo "<p>$desc</p>$footer";
	}
	
	protected function show_image( $field, $mode, $value )
	{
		if ($mode == 'edit' )
		{
			$header = '<td>';
			$footer = '</td></tr>';
			if ( !empty( $value ) ) {
				$image_id = esc_attr($value);
				$image_url = esc_url(wp_get_attachment_url( $image_id ));
				$add_class = 'class="hidden"';
				$del_class = '';
			} else {
				$image_id = '';
				$image_url = '';
				$add_class = '';
				$del_class = 'class="hidden"';
			}
			echo '<tr class="form-field">';
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$image_id = '';
			$image_url = '';
			$add_class = '';
			$del_class = 'class="hidden"';
		}
		$this->display_field_label( $field, $mode );
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$desc = $field['desc'];
		echo "$header<img id='{$id}_img' src='$image_url' style='max-width:100%'/>";
		echo "<input type='text' name='$id' id='$id' value='$image_id' />";
		echo "<br><input type='button' id='{$id}_add' name='{$id}_add' value='Select Image' $add_class/>";
		echo "<br><input type='button' id='{$id}_del' name='{$id}_del' value='Remove Image' $del_class/>";
		echo "<p>$desc</p>$footer";
	}

	
	protected function show_text_with_load_value_button( $field, $mode, $value ) {
		if ( $mode == 'edit' ) {
			$header = '<td>';
			$footer = '</td></tr>';
			echo '<tr class="form-field">';
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$value = $field['std'];
		}
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$value = esc_attr( $value );
		$this->display_field_label( $field, $mode );
		$desc = $field['desc'];
		echo "$header<input type='text' id='$id' name='$id' value='$value'/>";
		echo "<button id='{$id}_button' type='button'>{$field['button_label']}</button>";
		echo "<p>$desc</p>$footer";
	}	
	protected function show_checkbox( $field, $mode, $value ) {
		if ( $mode == 'edit' ) {
			$header = '<td>';
			$footer = '</td></tr>';
			$checked = $value ? ' checked' : '';
			echo '<tr class="form-field">';
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$checked = $field['std'] ? ' checked' : '';
		}
		$this->display_field_label( $field, $mode );
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$desc = $field['desc'];
		echo "$header<input type='checkbox' id='$id' name='$id'$checked /><p>$desc</p>$footer";			
	}
	
	protected function show_text( $field, $mode, $value, $type='text' ) {
		if ( $mode == 'edit' ) {
			$header = '<td>';
			$footer = '</td></tr>';
			$value = esc_attr( $value ? $value : $field['std'] );
			echo '<tr class="form-field">';
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$value = esc_attr( $field['std'] );
		}
		$this->display_field_label( $field, $mode );
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$desc = $field['desc'];
		echo "$header<input type='$type' id='$id' name='$id' value='$value'/><p>$desc</p>$footer";
	}
	
	protected function show_hidden_input( $field, $mode, $value ) {
		if (! $value ) {
			if ( $mode == 'edit' ) {
				$header = '<td class="hidden">';
				$footer = '</td></tr>';
				echo '<tr class="form-field">';
			} elseif ( $mode == 'add' ) {
				$header = '<div class="form-field">';
				$footer = '</div>';
			}
			$id = esc_attr( "{$this->prefix}{$field['id']}" );
			$value = esc_attr( $field['std'] );
			echo "$header<input type='hidden' id='$id' name='$id' value='$value'/>$footer";
		}
	}
	
	protected function show_hidden( $field, $mode, $value ) {
	}
	
	protected function show_url( $field, $mode, $value ) {
		$this->show_text( $field, $mode, $value, 'url' );
	}
	
	protected function show_email( $field, $mode, $value ) {
		$this->show_text( $field, $mode, $value, 'email' );
	}
	
	protected function show_color( $field, $mode, $value ) {
		$this->show_text( $field, $mode, $value, 'color' );
	}
	
	protected function form_fields( $term_id, $mode ) {
		if ( $mode == 'edit' ) {
			$values = get_tax_meta_all( $term_id );
		} else {
			$values = array();
		}
		foreach ( $this->fields as $field ) {
			if ( ! empty( $field['std_callback'] ) ) {
				$field['std'] = call_user_func( $field['std_callback'] );
			}
			call_user_func( array( $this, "show_{$field['type']}" ), $field, $mode, isset( $values[$field['id']] ) ? $values[$field['id']] : $field['std'] );
		}
	}
	
	private function get_posted_data( $field_type, $field_id, $current_value ) {
		switch ( $field_type ) {
			case 'text':
			case 'url':
			case 'email':
			case 'color':
			case 'hidden':
			case 'image':
			case 'wysiwyg':
			case 'text_with_load_value_button':
			case 'hidden_input':
				if ( isset( $_POST[$field_id] ) ) {
					return stripslashes( $_POST[$field_id] );
				} else {
					return $current_value;
				}
				break;
			case 'checkbox':
				return isset( $_POST[$field_id] );
				break;
			case 'hidden':
				return $current_Value;
				break;
		}
	}
	
	public function save_meta( $term_id ) {
		if ( isset($_POST['action'] ) && ( 'editedtag' == $_POST['action'] || 'add-tag' == $_POST['action'] ) ) {
			$term_meta = get_tax_meta_all( $term_id );
			foreach ( $this->fields as $field ) {
				$field_id = "{$this->prefix}{$field['id']}";
				$term_meta[$field['id']] = $this->get_posted_data( $field['type'], $field_id, empty($term_meta[$field['id']]) ? $field['std'] : $term_meta[$field['id']] );
			}

			update_tax_meta_all( $term_id, $term_meta );
		}
	}
	
	public function delete_meta( $term_id, $tt_id, $taxonomy ) {
		if ( $taxonomy == $this->taxonomy ) {
			delete_tax_meta_all( $term_id );
		}
	}
	
	public function get_field_names($mode = 'all') {
		$names = array();
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] ) {
				if ( 'image' == $field['type'] && 'non_image' != $mode ) {
					$names[] = array( 
						'url' => "{$field['id']}_url",
						'base64' => "{$field['id']}_base64", 
						'filename' => "{$field['id']}_filename",
						'' => $field['id'],
					);
				} elseif ( 'image' != $field['type'] && 'image' != $mode ) {
					$names[] = $field['id'];
				}
			}
		}
		
		return $names;
	}
	
	public function get_field_values( $term_id ) {
		$values = array();
		$meta_values = get_tax_meta_all( $term_id );
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] ) {
				if ( 'image' == $field['type'] ) {
					$image_id = isset( $meta_values[$field['id']] ) ? $meta_values[$field['id']] : 0;
					if ( $image_id ){
						$image_meta = wp_get_attachment_metadata( $image_id );
						$upload_dir = wp_upload_dir();
						$image_filename = "{$upload_dir['basedir']}/{$image_meta['file']}";
						$values["{$field['id']}_filename"] = basename( $image_filename );
						$values["{$field['id']}_base64"] = base64_encode( file_get_contents( $image_filename ) );
					} else {
						$values["{$field['id']}_base64"] = '';
						$values["{$field['id']}_filename"] = '';
					}
				} elseif ( isset( $meta_values[$field['id']] ) ) {
					$value = $meta_values[$field['id']];
					if ( isset( $field['pre_serialize'] ) ) {
						$value = $field['pre_serialize']( $value );
					}
					$values[$field['id']] = maybe_serialize( $value );
				} else {
					$value = $field['std'];
					if ( isset( $field['pre_serialize'] ) ) {
						$value = $field['pre_serialize']( $value );
					}
					$values[$field['id']] = maybe_serialize( $value );
				}
			}
		}
		
		return $values;
	}
	
	public function update_field_values( $term_id, $data, $mode ) {
		$meta_values = get_tax_meta_all( $term_id );
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] && ( 'overwrite' == $mode || empty( $meta_values[$field['id']] ) ) ) {
				if ( 'image' == $field['type'] ) {
					$meta_values[$field['id']] = Post_Import::add_image_data( $data, $field['id'] );
				} elseif ( ! empty( $data[$field['id']] ) ) {
					$meta_values[$field['id']] = maybe_unserialize( $data[$field['id']] );
					if ( isset( $field['post_unserialize'] ) ) {
						$meta_values[$field['id']] = $field['post_unserialize']( $meta_values[$field['id']] );
					}
				} elseif ( empty( $meta_values[$field['id']] ) ) {
					$meta_values[$field['id']] = $field['std'];
				}
			}
		}
		
		update_tax_meta_all( $term_id, $meta_values );
	}
}

/**
 * Helper function to update the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 * @param string $key
 * @param mixed $value
 *
 */
function update_tax_meta( $term_id, $key, $value )
{
	$meta = get_option( "tax_meta_$term_id" );
	$meta[$key] = $value;
	update_option( "tax_meta_$term_id", $meta );
}

/**
 * Helper function to replace all of the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 * @param string $key
 * @param array $value
 *
 */
function update_tax_meta_all( $term_id, $value )
{
	if ( is_array( $value ) ) {
		update_option( "tax_meta_$term_id", $value );
	}
}

/**
 * Helper function to retrieve the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 * @param string $key
 *
 */
function get_tax_meta( $term_id, $key )
{
	$meta = get_option( "tax_meta_$term_id" );
	return isset( $meta[$key] ) ? $meta[$key] : '';
}

/**
 * Helper function to retrieve all of the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 *
 */
function get_tax_meta_all( $term_id )
{
	return get_option( "tax_meta_$term_id");
}

/**
 * Helper function to delete the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 * @param string $key
 *
 */
function delete_tax_meta( $term_id, $key )
{
	$meta = get_option( "tax_meta_$term_id" );
	if ( isset( $meta[$key] ) ) {
		unset( $meta[$key] );
		update_option( "tax_meta_$term_id" );
	}
}

/**
 * Helper function to delete all of the taxonomy meta data.
 *
 * @since 1.0
 * @param int $term_id
 *
 */
function delete_tax_meta_all( $term_id )
{
	delete_option( "tax_meta_$term_id" );
}