<?php

/**
 * Post Meta Data handler.
 *
 * @package		Election_Data
 * @since		1.0
 * @author 		Robert Burton <RobertBurton@gmail.com>
 *
 */

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
	public function __construct( $taxonomy, $fields ) {
		$this->fields = $fields;
		$this->taxonomy = $taxonomy;
		$this->prefix = "tm_{$taxonomy}_";
		
		add_action( 'delete_term', array( $this, 'delete_meta'), 10, 3 );
		add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_form_fields' ) );
		add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_form_fields' ) );
		add_action( "edited_$taxonomy", array( $this, 'save_meta' ) );
		add_action( "created_$taxonomy", array( $this, 'save_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}
	
	public function enqueue_scripts()
	{
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( $taxonomy == $this->taxonomy ) {
			$script_id = "tax-meta-$taxonomy";
			wp_register_script( $script_id, plugin_dir_url( __FILE__ ) . 'js/tax-meta.js', array( 'jquery' ), '', true );
			$translation_array = array( 'prefix', $this->prefix );
			wp_localize_script( $script_id, 'tm_data', $translation_array );
			wp_enqueue_script( $script_id );
			if ( $this->has_type( 'image' ) )
			{
				$script_id = "tax-meta-image-$taxonomy";
				wp_register_script( $script_id, plugin_dir_url( __FILE__ ) . 'js/tax-meta-image.js', array( 'jquery', 'media-upload', 'thickbox' ), '', true );
				$translation_array = array();
				
				foreach ( $this->fields as $field ) {
					if ( $field['type'] == 'image' )
					{
						$translation_array[$field['id']] = $this->prefix . $field['id'];
					}
				}
				
				wp_localize_script( $script_id, 'tm_image_data', $translation_array );
				wp_enqueue_script( $script_id );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_media();
			}
		}
	}
	
	protected function has_type( $type )
	{
		foreach ( $this->fields as $field )
		{
			if ( $field['type'] == $type )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function add_form_fields( $term_name )
	{
		$this->show_form_fields( '', 'add' );
	}
	
	public function edit_form_fields( $term )
	{
		$this->show_form_fields( $term->term_id, 'edit' );
	}
	
	protected function show_field_label( $field, $mode ) {
		if ( $mode == 'edit' ) {
			$header = '<th scope="row">';
			$footer = '</th>';
		} else {
			$header = '';
			$footer = '';
		}
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$label = esc_html( $field['label'] );
		echo "$header<label for='$id'>$label</label>$footer";
	}
	
	protected function show_image( $field, $mode, $value )
	{
		if ($mode == 'edit' )
		{
			$header = '<td>';
			$footer = '</td></tr>';
			if ( isset( $value['id'] ) ) {
				$image_id = esc_attr($value['id']);
				$image_url = esc_url(wp_get_attachment_url( $image_id ));
				$add_class = 'class="hidden"';
				$del_class = '';
			} else {
				$image_id = '';
				$image_url = '';
				$add_class = '';
				$del_class = 'class="hidden"';
			}
			echo '<tr>';
		} else {
			$header = '';
			$footer = '';
			$image_id = '';
			$image_url = '';
			$add_class = '';
			$del_class = 'class="hidden"';
		}
		$this->show_field_label( $field, $mode );
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$desc = $field['desc'];
		echo "$header<img id='{$id}_img' src='$image_url' style='max-width:100%'/>";
		echo "<input type='hidden' name='$id' id='$id' value='$image_id' />";
		echo "<br><input type='button' id='{$id}_add' name='{$id}_add' value='Select Image' $add_class/>";
		echo "<br><input type='button' id='{$id}_del' name='{$id}_del' value='Remove Image' $del_class/>";
		echo "<p>$desc</p>$footer";
	}
	
	protected function show_text( $field, $mode, $value, $type='text' ) {
		if ( $mode == 'edit' )
		{
			$header = '<td>';
			$footer = '</td></tr>';
			$value = esc_attr( $value ? $value : $field['std'] );
			echo '<tr class="form-field">';
		}
		else
		{
			$header = '';
			$footer = '';
			$value = esc_attr( $field['std'] );
		}
		$this->show_field_label( $field, $mode );
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		$desc = $field['desc'];
		echo "$header<input type='$type' id='$id' name='$id' value='$value'/><p>$desc</p>$footer";
	}
	
	protected function show_hidden( $field, $mode, $value ) {
		if ( $mode == 'edit' )
		{
			$header = '<td class="hidden">';
			$footer = '</td></tr>';
			$value = esc_attr( $value ? $value : $field['std'] );
			echo '<tr class="form-field">';
		}
		else
		{
			$header = '';
			$footer = '';
			$value = esc_attr( $field['std'] );
		}
		$id = esc_attr( "{$this->prefix}{$field['id']}" );
		echo "$header<input type='hidden' id='$id' name='$id' value='$value'/>$footer";
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
	
	protected function show_form_fields( $term_id, $mode ) {
		if ( $mode == 'edit' ) {
			$header = '';
			$footer = '';
			$values = get_tax_meta_all( $term_id );
		} else {
			$header = '<div class="form-field">';
			$footer = '</div>';
			$values = array();
		}
		echo $header;
		foreach ( $this->fields as $field ) {
			call_user_func( array( $this, "show_{$field['type']}" ), $field, $mode, isset( $values[$field['id']] ) ? $values[$field['id']] : '' );
		}
		echo $footer;
	}
	
	protected function get_posted_text( $field_id ) {
		return $_POST[$field_id];
	}
	
	protected function get_posted_url( $field_id ) {
		return $this->get_posted_text( $field_id );
	}
	
	protected function get_posted_email( $field_id ) {
		return $this->get_posted_text( $field_id );
	}
	
	protected function get_posted_color( $field_id ) {
		return $this->get_posted_text( $field_id );
	}
	
	protected function get_posted_hidden( $field_id ) {
		return $this->get_posted_text( $field_id );
	}
	
	protected function get_posted_image( $field_id ) {
		$id = $_POST[$field_id];
		return array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) );
	}
	
	public function save_meta( $term_id ) {
		if ( isset($_POST['action'] ) && ( 'editedtag' == $_POST['action'] || 'add-tag' == $_POST['action'] ) ) {
			$term_meta = get_tax_meta_all( $term_id );
			foreach ( $this->fields as $field ) {
				$term_meta[$field['id']] = call_user_func( array( $this, "get_posted_{$field['type']}" ), "{$this->prefix}{$field['id']}" );
			}
			if ( isset( $term_meta['ur'] ) ) {
				unset( $term_meta['ur'] );
			}
			update_tax_meta_all( $term_id, $term_meta );
		}
	}
	
	public function delete_meta( $term_id, $tt_id, $taxonomy ) {
		if ( $taxonomy == $this->taxonomy )
		{
			delete_tax_meta_all( $term_id );
		}
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