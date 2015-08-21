<?php

/**
 * Post Meta Data handler.
 * Takes care of displaying the post meta data on the add/edit screens and
 * takes care of adding/updating the post meta in the database.
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
	 * The meta fields for which to add a filter.
	 * The field name is the key to the dictionary.
	 * An array of values generates a pull down list with the given values.
	 * Anything else generates a text input field.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $post_meta_filters;
	
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
		self::$allowed_admin_column_types = array( 'text' => '', 'url' => '', 'email' => '', 'checkbox' => '', 'pulldown' => '' );
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
		echo '<table class="form-table">';
		foreach ( $this->fields as $field ) {
			echo '<tr>';
			$value = get_post_meta( $post->ID, $field['id'], true );
			call_user_func( array( $this, "show_{$field['type']}" ), $field, 'edit', maybe_serialize ( $value ) );
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
				update_post_meta( $post_id, stripslashes( $field['id'] ), $new );
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
	 * Adds a filter for to the administrative interface for the requested column.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	function filter_lists() {
		$screen = get_current_screen();
		global $wp_query;
		
		if ( $this->post_type == $screen->post_type ) {
			foreach ( $this->filter_columns as $filter_column ) {
				if ( $taxonomy['args']['show_admin_column'] )
				{
					$query = $taxonomy['args']['query_var'];
					$name = $taxonomy['name'];
					$selected = '';
					if ( isset( $wp_query->query[$query] ) ) {
						$term = get_term_by( 'slug', $wp_query->query[$query], $name );
						if ( $term ) {
							$selected = (int)$term->term_id;
						}
					}
					
					$args = array(
						'show_option_all' => "All {$taxonomy['args']['labels']['name']}",
						'taxonomy' => $name,
						'name' => $query,
						'orderby' => 'name',
						'selected' => $selected,
						'hierarchical' => true,
						'depth' => 3,
						'show_count' => false,
						'hide_empty' => false,
						'value_field' => 'slug'
					);
					
					wp_dropdown_categories( $args );
				}
			}
		}
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
		
		if ( "edit-{$this->post_type}" == $current_screen->id && !empty( $this->admin_columns ) ) {
			$script_id = "post-meta-{$this->post_type}";
			wp_register_script( $script_id, plugin_dir_url( __FILE__ )  . 'js/post-meta.js', array( 'jquery', 'inline-edit-post' ), '', true );
			$translation_array = array();
			foreach ( $this->admin_columns as $field => $value ) {
				$translation_array[$field] = $this->prefix . $this->fields[$field]['id'];
				
				if ( 'pulldown' == $this->fields[$field]['type'] ) {
					$pulldown_array = array();
					foreach ( $this->fields[$field]['options'] as $value => $label ) {
						$pulldown_array[$label] = $value;
					}
					
					wp_localize_script( $script_id, "pm_post_meta_pulldown_{$this->fields[$field]['id']}", $pulldown_array );
				}
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
	protected function display_edit_label( $field )
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
	protected function display_quick_label ( $field ) {
		$label = esc_html( $field['label'] );
		echo "<label class='alignleft'><span class='title'>$label</span></label>";
	}
	
	protected function show_pulldown( $field, $mode, $value ) {
		switch ( $mode ) {
			case 'edit':
				$this->display_edit_label( $field, $mode );
				$value = esc_attr( empty( $value ) ? $field['std'] : $value );
				echo "<td><select name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' ";
				break;
			case 'quick':
				$this->display_quick_label( $field, $mode );
				$value = esc_attr( $field['std'] );	
				echo "<select name='{$this->prefix}{$field['id']}' ";
				break;
			case 'column':
				echo esc_html( empty( $field['options'][$value] ) ? $field['options'][$field['std']] : $field ['options'][$value] );
				break;
		}
		
		if ( $mode != 'column' ) {
			echo "value='$value'>";
			foreach ( $field['options'] as $option_value => $option_display ) {
				echo "<option value='$option_value'>$option_display</option>";
			}
			echo '</select>';
			if ( $mode == 'edit' ) {
				echo "<br />{$field['desc']}</td>";
			}
		}
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
				$this->display_edit_label( $field, $mode );
				$value = esc_attr( $value = $value ? $value : $field['std'] );
				echo "<td><input type='$type' name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' value='$value' size='30' style='width:97%' />";
				echo "<br />{$field['desc']}</td>";
				break;
			case 'quick':
				$value = esc_attr( $field['std'] );
				$this->display_quick_label ( $field, $mode );
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
	
	protected function show_hidden( $field, $mode, $value ) {
		switch ( $mode ) {
			case 'edit':
				if ( ! $value ) {
					$value = esc_attr( $value = $value ? $value : $field['std'] );
					echo "<td class='hidden'><input type='hidden' name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' value='$value' /></td>";
				}
				break;
			case 'quick':
			case 'column':
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
				$this->display_edit_label ( $field );
				echo "<td><input type='checkbox' name='{$this->prefix}{$field['id']}' id='{$this->prefix}{$field['id']}' value='true' $checked size='30' />";
				echo "<br />{$field['desc']}</td>";
				break;
			case 'quick':
				$this->display_quick_label ( $field, $mode );
				echo "<input type='checkbox' name='{$this->prefix}{$field['id']}' value='true' />";
				break;
			case 'column':
				echo $value ? 'X' : '';
				break;
		}
	}
	
	
	function admin_posts_filter( $query )
	{
		global $pagenow;
		if ( is_admin() && $pagenow=='edit.php' && !empty($_GET['ADMIN_FILTER_FIELD_NAME']) ) {
			$query->query_vars['meta_key'] = $_GET['ADMIN_FILTER_FIELD_NAME'];
			if ( !empty($_GET['ADMIN_FILTER_FIELD_VALUE']))
				$query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
		}
	}
	
	function admin_posts_filter_restrict_manage_posts()
	{
/*		global $wpdb;
		$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta.' ORDER BY 1';
		$fields = $wpdb->get_results($sql, ARRAY_N);
	?>
	<select name="ADMIN_FILTER_FIELD_NAME">
	<option value=""><?php _e('Filter By Custom Fields', 'baapf'); ?></option>
	<?php
		$current = isset($_GET['ADMIN_FILTER_FIELD_NAME'])? $_GET['ADMIN_FILTER_FIELD_NAME']:'';
		$current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
		foreach ($fields as $field) {
			if (substr($field[0],0,1) != "_"){
			printf
				(
					'<option value="%s"%s>%s</option>',
					$field[0],
					$field[0] == $current? ' selected="selected"':'',
					$field[0]
				);
			}
		}
	?>
	</select> <?php _e('Value:', 'baapf'); ?><input type="TEXT" name="ADMIN_FILTER_FIELD_VALUE" value="<?php echo $current_v; ?>" />
	<?php*/
	}
	
	public function get_field_names() {
		$names = array();
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] ) {
				$names[] = $field['id'];
			}
		}
		
		return $names;
	}
	
	public function get_field_values( $post_id ) {
		$values = array();
		$meta_values = get_post_meta( $post_id );
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] ) {
				if ( isset( $meta_values[$field['id']] ) && isset( $meta_values[$field['id']][0] ) ) {
					$values[$field['id']] = maybe_serialize( $meta_values[$field['id']][0] );
				} else {
					$values[$field['id']] = maybe_serialize( $field['std'] );
				}
			}
		}
		
		return $values;
	}
	
	public function update_field_values( $post_id, $data, $mode )
	{
		$meta_values = get_post_meta( $post_id );
		foreach ( $this->fields as $field ) {
			if ( $field['imported'] && ( 'overwrite' == $mode || empty( $meta_values[$field['id']] ) ) ) {
				update_post_meta( $post_id, $field['id'], maybe_unserialize( $data[$field['id']] ) );
			} elseif ( empty( $meta_values[$field['id']] ) ) {
				update_post_meta( $post_id, $field['id'], $field['std'] );
			}
		}
	}
}

Post_Meta::init();