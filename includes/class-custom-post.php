<?php

/**
 * The file that defines the ED_Custom_Post class.
 *
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */
 
require_once plugin_dir_path( __FILE__ ) . 'class-post-meta.php';
require_once plugin_dir_path( __FILE__ ) . 'class-taxonomy-meta.php';


/**
 * The custom post class.
 * This class takes care of everything required to define the custom post type, 
 * and display it's fields during creation and editting in the admin interface.
 *
 *
 * @since      1.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */
class ED_Custom_Post_Type {
	
	/**
	 * The name of the custom post.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 *
	 */
	 protected $post_type;
	 
	/**
	 * The definition of the custom post type.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $custom_post_args;
	
	/**
	 * The definition of the taxonomies associated with the custom post type.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $taxonomy_args;
	
	/**
	 * The Post_Meta object that takes care of the custom post type's meta data.
	 *
	 * @var object
	 * @access public
	 * @since 1.0
	 *
	 */
	public $post_meta;
	
	/**
	 * The Taxonomy_Meta object that takes care of the taxonomy meta data.
	 *
	 * @var ojbect
	 * @access public
	 * @since 1.0
	 *
	 */
	public $taxonomy_meta;
	
	/**
	 * The existing admin columns that will have their name changed.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $admin_column_names;
	 
	/**
	 * The existing admin columns that will be hidden.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $hidden_admin_columns;
	
	/*
	 * The existing filters that will be removed from the administrative interface.
	 * (Currently only the date filter is supported)
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $hidden_admin_filters;
	 
	/**
	 * The taxonomies for which to show a filter.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $taxonomy_filters;
	
	/**
	 * The taxonomies for which the column in the admin interface is sortable.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $sortable_taxonomies;
	
	/**
	 *
	 * The taxonomies that are displayed in the admin column.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $taxonomy_admin_columns;
	
	/**
	 * The existing admin quick-edit/bulk-edit/edit/add fields that will have their name changed.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $admin_field_names;
	
	/**
	 * The existing admin quick-edit/bulk-edit/edit/add fields that will be hidden.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $hidden_admin_fields;
	 
	/**
	 * Constructur
	 *
	 * @since 1.0
	 * @access public
	 * @param array $custom_post
	 * @param array $args
	 *
	 */
	public function __construct( $post_type, $args, $define_hooks = true ) {
		$this->post_type = $post_type;
		$this->custom_post_args = $args['custom_post_args'];
		$this->taxonomy_args = empty( $args['taxonomy_args'] ) ? array() : $args['taxonomy_args'];
		$taxonomy_meta = empty( $args['taxonomy_meta'] ) ? array() : $args['taxonomy_meta'];
		$this->admin_column_names = empty( $args['admin_column_names'] ) ? array() : $args['admin_column_names'];
		$this->admin_field_names = empty( $args['admin_field_names'] ) ? array() : $args['admin_field_names'];
		$this->hidden_admin_columns = empty( $args['hidden_admin_columns'] ) ? array() : $args['hidden_admin_columns'];
		$this->hidden_admin_fields = empty( $args['hidden_admin_fields'] ) ? array() : $args['hidden_admin_fields'];
		$this->hidden_admin_filters = empty( $args['hidden_admin_filters'] ) ? array() : $args['hidden_admin_filters'];
		$this->taxonomy_admin_columns = array();
		$this->taxonomy_filters = array();
		if ( $args['taxonomy_filters'] ) {
			foreach ( $args['taxonomy_filters'] as $taxonomy ) {
				$this->taxonomy_filters[$taxonomy] = '';
			}
		}
		$this->meta_filters = empty( $args['meta_filters'] ) ? array() : $args['meta_filters'];
		
		$this->sortable_taxonomies = empty( $args['sortable_taxonomies'] ) ? array() : $args['sortable_taxonomies'];
		
		if ( ! empty( $args['custom_post_meta'] ) ) {
			$custom_post_meta = $args['custom_post_meta'];
			$this->post_meta = new Post_Meta( 
				$custom_post_meta['meta_box'],
				$custom_post_meta['fields'],
				isset( $custom_post_meta['admin_columns'] ) ? $custom_post_meta['admin_columns'] : array(),
				isset( $custom_post_meta['filters'] ) ? $custom_post_meta['filters'] : array()
			);
		} else {
			$this->post_meta = null;
		}
		
		$this->taxonomy_meta = array();
		foreach ( $taxonomy_meta as $name => $tax_meta_config ) {
			$this->taxonomy_meta[$name] = new Tax_Meta( $tax_meta_config['taxonomy'], $tax_meta_config['fields'], array( 'Description', ), $define_hooks );
		}
		
		if ( $define_hooks ) {
			$this->define_hooks();
		}
	}
	
	function taxonomy_radio_meta_box ($post, $box) {
		echo "Needs to be written."; // See post_categories_meta_box in wordpress/admin/includes/meta_boxes.php, wp_terms_checklist in wordpress/admin/includes/template.php and Walker_Category_Checklist in wordpress/admin/includes/template.php for ideas on how to implement.
	}
	
	/**
	 * Registers the custom post type and the taxonomies with WordPress.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function initialize() {
		register_post_type( $this->post_type, $this->custom_post_args );
		foreach ( $this->taxonomy_args as $taxonomy_name => $taxonomy ) {
			if ( isset( $taxonomy['use_radio_button'] ) && $taxonomy['use_radio_button'] ) {
				if ( $taxonomy['hierarchical'] ) {
					$taxonomy['meta_box_cb'] = array( $this, 'taxonomy_radio_meta_box' );
				}
			}
			
			register_taxonomy( $taxonomy_name, $this->post_type, $taxonomy );
			if ( isset( $this->taxonomy_filters[$taxonomy_name] ) ) {
				if ( isset( $taxonomy['query_var'] ) ){
					if ( $taxonomy['query_var'] === false ) {
						unset( $this->taxonomy_filters[$taxonomy_name] );
					} else {
						$this->taxonomy_filters[$taxonomy_name] = $taxonomy['query_var'];
					}
				} else {
					$this->taxonomy_filters[$taxonomy_name] = $taxonomy_name;
				}
			}
			if ( isset( $taxonomy['show_admin_column'] ) && $taxonomy['show_admin_column'] ) {
				$this->taxonomy_admin_columns[$taxonomy_name] = '';
			}				
		}
	}
	
	/**
	 * Changes the Enter Title Here in the add/edit screen to the requested value.
	 * If the field name 'enter_title_here' has been defined, will use it, otherwise
	 * the 'title' field is uses. If neither are available, nothing is changed.
	 * 
	 * @since 1.0
	 * @access public
	 * @param string $label
	 *
	 */
	public function update_title( $label )
	{
		global $post_type;
	
		if ( is_admin() && $this->post_type == $post_type )
		{
			if ( isset( $this->admin_field_names['enter_title_here'] ) ) {
				return $this->admin_field_names['enter_title_here'];
			} elseif ( isset( $this->admin_field_names['title'] ) ) {
				return $this->admin_field_names['title'];
			}
		}
		
		return $label;
	}
	

	/**
	 * Identifies the columns to display in the administrative interface.
	 *
	 * @since 1.0
	 * @access public
	 * @param array $columns
	 *
	 */
	public function define_columns( $columns ) {
		if ( ! empty( $this->admin_column_names ) ) {
			foreach ( $this->admin_column_names as $column_name => $title ) {
				$columns[$column_name] = $title;
			}
		}
		
		if ( ! empty( $this->hidden_admin_columns ) ) {
			foreach ( $this->hidden_admin_columns as $column_name ) {
				unset( $columns[$column_name] );
			}
		}
		
		return $columns;
	}
	
	/**
	 * Identifies the sortable columns in the administrative interface.
	 *
	 * @since 1.0
	 * @access public
	 * @param array @columns
	 *
	*/
	public function sort_columns( $columns ) {
		foreach ( $this->sortable_taxonomies as $taxonomy ) {
			if ( isset( $this->taxonomy_admin_columns[$taxonomy] ) ) {
				$columns["taxonomy-$taxonomy"] = "taxonomy-$taxonomy}";
			}
		}
		
		return $columns;
	}

	/**
	 * If the orderby has been set to taxonomy-{taxonomy_name}, 
	 * update the sql query clauses so that the results are sorted using the name field of the taxonomy.
	 *
	 * @since 1.0
	 * @access protected
	 * @param array $clauses 
	 * @param object $wp_query
	 *
	 */
	function order_by_taxonomy( $clauses, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['orderby'] ) ) {
			foreach ( $this->taxonomy_args as $taxonomy_name => $taxonomy ) {
				if ( "taxonomy_name-$taxonomy_name" == $wp_query->query['orderby'] ) {
					$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} tr2 ON {$wpdb->posts}.ID=tr2.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
LEFT OUTER JOIN {$wpdb->terms} t2 on tt2.term_id = t2.term_id
SQL;

					$clauses['where'] .= " AND (tt2.taxonomy = '$taxonomy_name' OR tt2.taxonomy IS NULL)";
					$clauses['groupby'] = "tr2.object_id";
					$clauses['orderby']  = "GROUP_CONCAT(t2.name ORDER BY name ASC) ";
					$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
				}
			}
		}
		
		return $clauses;
	}
	
	/**
	 * Removes the date filter from the admin column.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function remove_dates( $vars )
	{
		if ( $this->post_type == get_post_type() ) {
			return array();
		}
		
		return $vars;
	}
	
	/**
	 * Adds taxonomy filters to the admin screen.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function add_filters() {
		$screen = get_current_screen();
		global $wp_query;
		
		if ( $this->post_type == $screen->post_type ) {
			foreach ( $this->taxonomy_filters as $taxonomy_name => $query) {
				$selected = '';
				if ( isset( $wp_query->query[$query] ) ) {
					$term = get_term_by( 'slug', $wp_query->query[$query], $taxonomy_name );
					if ( $term ) {
						$selected = (int)$term->term_id;
					}
				}
				
				$args = array(
					'show_option_all' => "All {$this->taxonomy_args[$taxonomy_name]['labels']['name']}",
					'taxonomy' => $taxonomy_name,
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
	
	/**
	 * Localizes and enqueues the quick-edit javascript file if hiding or renaming quick/bulk edit feilds.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function setup_admin_scripts() {
		global $current_screen;
		
		if ( $current_screen->id == "edit-{$this->post_type}" && ( ! empty( $this->hidden_admin_fields ) || ! empty( $this->admin_column_names ) ) ) {
			$script_name = "quick-edit-{$this->post_type}";
			wp_register_script( $script_name, plugin_dir_url( __FILE__ )  . 'js/quick-edit.js', array( 'jquery', 'inline-edit-post' ), '', true );
			$translation_array = array();
			foreach ( $this->hidden_admin_fields as $column ) {
				$translation_array[ucfirst($column)] = '';
			}

			wp_localize_script( $script_name, 'ed_remove_columns', $translation_array );
			
			$translation_array = array();
			foreach ( $this->admin_column_names as $column => $name ) {
				$translation_array[ucfirst($column)	] = $name;
			}
			
			wp_localize_script( $script_name, 'ed_rename_columns', $translation_array );
		
			wp_enqueue_script( $script_name );
		}
					
		foreach ( $this->taxonomy_args as $taxonomy_name => $taxonomy_args ) {
			if ( $current_screen->id == "edit-$taxonomy_name" && $taxonomy_args['hierarchical'] == true ) {
				$script_name = "bulk-$taxonomy_name";
				wp_register_script( $script_name, plugin_dir_url( __FILE__ ) . 'js/tax-bulk.js', array( 'jquery' ), '', true );
				
				$translation_array = array(
					'set_parent' => __( 'Set Parent' ),
				);
				
				wp_localize_script( $script_name, 'ed_tax_bulk_local', $translation_array );
				
				wp_enqueue_script( $script_name );
			}
		}
	}
	
	/**
	 * Gets all existing root taxonomy terms. If the requested root terms are not already created, creates them.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $taxonomy_name
	 * @param array $root_names
	 *
	 */
	public function get_or_create_root_taxonomy_terms( $taxonomy_name, $root_names ) {
		$args = array(
			'fields' => 'all',
			'hide_empty' => false,
			'parent' => 0,
		);
		$terms = get_terms( $taxonomy_name, $args );
		$root_ids = array();
		foreach ( $terms as $term ) {
			$root_ids[$term->name] = $term->term_id;
		}
		
		// If a required term is not present, create it.
		foreach ( $root_names as $name ) {
			if ( !isset( $root_ids[$name] ) ) {
				$ids = wp_insert_term( $name, $taxonomy_name, array( 'parent' => 0 ) ); 
				$root_ids[$name] = $ids['term_id'];
			}
		}	
		
		return $root_ids;
	}
	
	public function load_edit_tags() {
		$taxonomy_name = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( isset( $this->taxonomy_args[$taxonomy_name] ) ) {
			$taxonomy = get_taxonomy( $taxonomy_name );
			if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->manage_terms ) ) {
				return;
			}

			add_action( 'admin_footer', array( $this, 'create_parent_select' ) );
			
			if ( empty( $_REQUEST['action' ]) || empty( $_REQUEST['delete_tags'] ) || $_REQUEST['action'] != 'bulk_set_parent' ) {
				return;
			}

			$term_ids = $_REQUEST['delete_tags'];
			$referer = wp_get_referer();
			
			if ( $referer && false != strpos( $referer, 'edit-tags.php') ) {
				$location = $referer;
			} else {
				$location = add_query_arg( 'taxonomy', $taxonomy_name, 'edit-tags.php' );
			}
			
			if ( empty( $_REQUEST['parent'] ) ) {
				$result = false;
			} else {
				$parent_id = $_REQUEST['parent'];

				foreach ( $term_ids as $term_id ) {
					if ( $term_id == $parent_id ) {
						continue;
					}

					$ret = wp_update_term( $term_id, $taxonomy_name, array( 'parent' => $parent_id ) );

					if ( is_wp_error( $ret ) ) {
						$result = false;
						break;
					}
				}
				$result = true;
			}

			wp_redirect( add_query_arg( 'ed_message', $result ? 'term-updated' : 'term-error', $location ) );
			die();
			error_log( print_r( $_REQUEST, true ) );
		}
	}
	
	public function create_parent_select() {
		global $taxonomy;
		
		echo '<div id="ed_input_set_parent" style="display:none">';
			wp_dropdown_categories( array(
				'hide_empty' => false,
				'hide_if_empty' => false,
				'name' => 'parent',
				'orderby' => 'name',
				'taxonomy' => $taxonomy,
				'hierarchical' => true,
				'show_option_none' => __( 'None' )
			) );
		echo '</div>';
	}
	
	public function admin_notice() {
		if ( !isset( $_GET['ed_message'] ) )
			return;
		$taxonomy_name = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
		if ( ! isset( $this->taxonomy_args[$taxonomy_name] ) && get_post_type() != $this->post_type ) {
			return;
		}
		
		switch ( $_GET['ed_message'] ) {
		case  'term-updated':
			echo '<div id="message" class="updated"><p>' . __( 'Terms updated.', 'term-management-tools' ) . '</p></div>';
			break;

		case 'term-error':
			echo '<div id="message" class="error"><p>' . __( 'Terms not updated.', 'term-management-tools' ) . '</p></div>';
			break;
	}}
	
	/**
	 * Sets up all of the filter and action hooks required by the custom post type.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	public function define_hooks()
	{
		add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'define_columns' ) );
		add_filter( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sort_columns' ) );
		add_filter( 'posts_clauses', array( $this, 'order_by_taxonomy' ), 10, 2 );
	    add_action( 'restrict_manage_posts', array( $this, 'add_filters' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
		if ( in_array( 'date', $this->hidden_admin_filters ) ) {
			add_filter( 'months_dropdown_results', array( $this, 'remove_dates' ) );
		}
		
		if ( isset( $this->admin_field_names['enter_title_here'] ) || isset( $this->admin_field_names['title'] ) ) {
			add_filter( 'enter_title_here', array( $this, 'update_title' ) );
		}
		
		add_action( 'load-edit-tags.php', array( $this, 'load_edit_tags' ) );
		add_action( 'init',  array( $this, 'initialize' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
	}
	
	/**
	 * Erases all of the posts of the defined type and all of the taxonomy terms for the defined taxonomies.
	 *
	 * @since 1.0
	 * @access public
	 *
	 */
	function erase_data() {
		$args = array(
			'post_type' => $this->post_type,
			'nopaging' => true,
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			wp_delete_post( $query->post->ID, true );
		}
		
		foreach ( $this->taxonomy_args as $taxonomy_name => $taxonomy ) {
			$args = array(
				'hide_empty' => false,
				'fields' => 'ids',
				'get' => 'all',
			);
			$term_ids = get_terms( $taxonomy_name, $args );
			foreach ( $term_ids as $term_id ) {
				wp_delete_term( $term_id, $taxonomy_name );
			}
		}
	}
}