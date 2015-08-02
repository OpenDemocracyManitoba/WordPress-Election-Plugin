<?php

/**
 * Post Meta Data handler.
 *
 * @package		Election_Data
 * @since		1.0
 * @author 		Robert Burton <RobertBurton@gmail.com>
 *
 */

class Post_Meta {
	
	/**
	 * Holds meta box parameters.
	 *
	 * @var array
	 * @access protected
	 *
	 */
	protected $meta_box;
	
	/**
	 * Holds meta data fields.
	 *
	 * @var array
	 * @access protected
	 *
	 */
	protected $fields;
	
	/**
	 * Identifies the fields to display in the admin column.
	 *
	 * @var array
	 * @access protected
	 *
	 */
	protected $admin_columns;
	
	/**
	 * The post type for the custom fields.
	 *
	 * @var string
	 * @access protected
	 *
	 */
	protected $post_type;
	
	/**
	 * The prefix used for the id and name of the custom fields.
	 *
	 * @var string
	 * @access protected
	 *
	 */
	protected $prefix;
	
	/**
	 * A list of field types that can be used ad admin_columns.
	 *
	 * @var array
	 * @access protected 
	 *
	 */
	static protected $allowed_admin_column_types;
	/**
	 * Constructer
	 *
	 * @since 1.0
	 * @access public
	 * @param array $fields
	 * @param array $meta_box
	 * @param array $admin_columns
	 *
	 */
	public function __construct( $meta_box, $fields, $admin_columns=array() ) {
		if ( !is_admin() ) {
			return;
		}
		
		$default_meta_box = array( 
			'id' => '',
			'title' => '',
			'post_type' => 'post',
			'context' => 'normal',
			'priority' => 'high',
		);
		
		$meta_box += $default_meta_box;
		
		$this->meta_box = $meta_box;
		$this->post_type = $meta_box['post_type'];
		$this->fields = $fields;
		$this->admin_columns = array();
		foreach ( $admin_columns as $field )
		{
			if ( isset( self::$allowed_admin_column_types[$this->fields[$field]['type']] ) ) {
				$this->admin_columns[$field] = true;
			}
		}
		
		$this->prefix = "meta_{$this->post_type}_";
		// Setup required actions and filters.
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( "save_post_{$this->post_type}", array( $this, 'save_post_fields' ) );
		add_action( 'wp_ajax_save_post_meta_data', array( $this, 'save_post_fields_ajax' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'populate_columns' ) );
		add_action( 'bulk_edit_custom_box', array( $this, 'bulk_quick_edit_custom_box' ) );
		add_action( 'quick_edit_custom_box', array( $this, 'bulk_quick_edit_custom_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'define_columns' ) );
		add_filter( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sort_columns' ) );
		add_filter( 'request', array( $this, 'column_orderby' ) );
	}
	
	/**
	 * Initializes the static variables.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	static public function init()
	{
		self::$allowed_admin_column_types = array( 'text' => '', 'url' => '', 'email' => '', 'checkbox' => '' );
	}
	
	/**
	 * Initializations required for the administrative interface.
	 * 
	 * @sine 1.0
	 * @access public
	 *
	 */
	public function admin_init()
	{
		add_meta_box( 
			$this->meta_box['id'],
			$this->meta_box['title'],
			array( $this, 'render_custom_meta_box' ),
			$this->meta_box['post_type'], 
			$this->meta_box['context'],
			$this->meta_box['priority']
		);
	}
	
	/**
	 * Callback that creates the custom meta box.
	 *
	 * @since 1.0
	 * @access public
	 * @param object $post
	 *
	 */
	public function render_custom_meta_box( $post ) {
		$fields = $this->fields;
		echo '<table class="form-table">';
		foreach ( $this->fields as $field ) {
			echo '<tr>';
			$value = get_post_meta( $post->ID, $field['id'], true );
			call_user_func( array( $this, "show_{$field['type']}" ), $field, 'edit', $value );
			echo '</tr>';
		}
		echo "</table>";
	}
	
	/** 
	 * Ajax callback that handles bulk editting of fields.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function save_post_fields_ajax( ) {
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			wp_die();
		}
		
		$post_ids = empty( $_POST['post_ids'] ) ? '' : $_POST['post_ids'];
		if ( !empty( $_POST['post_id'] ) ) {
			$this->save_post_fields( $_POST['post_id'] );
		} elseif ( is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$post_type = get_post_type( $post_id );
								
				// Check if the post type is the correct type.
				if ( $this->post_type != $post_type ) {
					continue;
				}
				
				$this->save_post_fields( $post_id, true );
			}
		}
		
		wp_die();
	}

	/*
	 * Stores the posted meta data for the post.
	 *
	 * @since 1.0
	 * @access public
	 * @param int $post_id
	 *
	 */
	public function save_post_fields( $post_id, $skip_empty = false ) {
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// check permissions
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		
		// Update the meta data using the posted values.
		foreach ( $this->fields as $field ) {
			// Skip the field if it has not been posted.
			// Also skip the field if it is empty (false) and skip_empty is true.
			if ( isset( $_POST[$this->prefix . $field['id']] ) && ( !$skip_empty || $_POST[$this->prefix . $field['id']] ) ) {
				$new = $_POST[$this->prefix . $field['id']];
				update_post_meta( $post_id, $field['id'], $new );
			}
		}
	}

	/*
	 * Adds columns to the post type's administration interface.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $columns
	 *
	 */
	public function define_columns( $columns ) {
		foreach ( $this->admin_columns as $field => $value) {
			$columns[$field] = $this->fields[$field]['label'];
		}
		
		return $columns;
	}
	
	/*
	 * Fills the columns data in the post type's administration interface.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $columns
	 *
	 */
	public function populate_columns( $column ) {
		if ( isset( $this->admin_columns[$column] ) ) {
			echo "<div id='$column-" . get_the_ID() . "'>";
			$field = $this->fields[$column];
			$value = get_post_meta( get_the_ID(), $field['id'], true );
			call_user_func( array( $this, "show_{$field['type']}" ), $field, 'column', $value );
			echo '</div>';
		}
	}

	/*
	 * Adds meta data to the custom box used when quick or bulk editting the custom post.
	 *
	 * @since 1.0
	 * @access public
	 * @param string $column_name
	 *
	 */
	public function bulk_quick_edit_custom_box( $column_name ) {
		if ( isset( $this->admin_columns[$column_name] ) ) {
			$field = $this->fields[$column_name];
			echo '<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><div class="inline-edit-group">';
			call_user_func( array( $this, "show_{$field['type']}" ), $field, 'quick', '' );
			echo '</div></div></fieldset>';
		}
	}
	
	/*
	 * Identifies the sortable columns in the administration interface.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $columns
	 *
	 */
	public function sort_columns( $columns ) {
		foreach ( $this->admin_columns as $field => $value ) {
			$columns[$field] = $field;
		}
		
		return $columns;
	}

	/*
	 * Allows meta data columns in the administration interface to be sorted.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $vars
	 *
	 */
	public function column_orderby( $vars ) {
		if ( !is_admin() )
			return $vars;
		if ( isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] && isset( $vars['orderby'] ) ) {
			foreach ( $this->admin_columns as $field => $value ) {
				if ( $this->fields[$field]['id'] == $vars['orderby'] ) {
					$vars = array_merge( $vars, array( 'meta_key' => $vars['orderby'], 'orderby' => 'meta_value' ) );
				}
			}
		}
		
		return $vars;
	}
	/*
	 * Enqueus the scripts and styles required to edit the custom data.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function enqueue_scripts() {
		global $current_screen;
		
		if ( $current_screen->id == "edit-{$this->post_type}" && !empty( $this->admin_columns ) ) {
			$script_id = "post-meta-{$this->post_type}";
			wp_register_script( $script_id, plugin_dir_url( __FILE__ )  . 'js/post-meta.js', array( 'jquery', 'inline-edit-post' ), '', true );
			$translation_array = array();
			foreach ( $this->admin_columns as $field => $value ) {
				$translation_array[$field] = $this->prefix . $this->fields[$field]['id'];
			}
			
			wp_localize_script( $script_id, 'pm_post_meta', $translation_array );
			
			wp_enqueue_script( $script_id );
		}

	}

	/**
	 * Generates the HTML for a field label in the Edit screen.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 *
	 */
	protected function show_edit_label ( $field )
	{
		$label = esc_html( $field['label'] );
		echo "<th style='width: 20%'><label for='{$this->prefix}{$field['id']}'>$label</label></th>";
	}
	
	/**
	 * Generates the HTML for a field label in the Quick and Bulk Edit screens.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 *
	 */
	protected function show_quick_label ( $field ) {
		$label = esc_html( $field['label'] );
		echo "<label class='alignleft'><span class='title'>$label</span></label>";
	}
	
	/**
	 * Generates the HTML for a text style field.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 * @param string $mode
	 * @param string $value
	 * @param string $type
	 *
	 */	
	protected function show_text( $field, $mode, $value, $type='text' ) {
		switch ( $mode ) {
			case 'edit':
				$this->show_edit_label ( $field, $mode );
				esc_attr( $value = $value ? $value : $field['std'] );
				echo "<td><input type='$type' name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' value='$value' size='30' style='width:97%' />";
				echo "<br />{$field['desc']}</td>";
				break;
			case 'quick':
				$value = esc_attr( $field['std'] );
				$this->show_quick_label ( $field, $mode );
				echo "<input type='$type' name='{$this->prefix}{$field['id']}' value='' />";
				break;
			case 'column':
				if ( $type == 'url' ) {
					$url = esc_url( $value );
					$value = esc_html( $value );
					echo "<a href='$url'>$value</a>";
				} else {
					echo esc_html( $value );
				}
				break;
		}
	}
	
	/**
	 * Generates the HTML for a URL field.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 * @param string $mode
	 * @param string $value
	 *
	 */	
	protected function show_url ( $field, $mode, $value ) {
		$this->show_text ( $field, $mode, $value, 'url' );
	}
	
	/**
	 * Generates the HTML for an email field.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 * @param string $mode
	 * @param string $value
	 *
	 */	
	protected function show_email ( $field, $mode, $value ) {
		$this->show_text ( $field, $mode, $value, 'email' );
	}
	
	/**
	 * Generates the HTML for a single checkbox.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $field
	 * @param string $mode
	 * @param string $value
	 *
	 */	
	protected function show_checkbox ( $field, $mode, $value ) {
		switch ( $mode ) {
			case 'edit':
				$checked = $value ? 'checked' : '';
				$this->show_edit_label ( $field );
				echo "<td><input type='checkbox' name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' value='true' $checked size='30' />";
				echo "<br />{$field['desc']}</td>";
				break;
			case 'quick':
				$this->show_quick_label ( $field, $mode );
				echo "<input type='checkbox' name='{$this->prefix}{$field['id']}' value='true' />";
				break;
			case 'column':
				echo $value ? 'X' : '';
				break;
		}
	}
}

Post_Meta::init();