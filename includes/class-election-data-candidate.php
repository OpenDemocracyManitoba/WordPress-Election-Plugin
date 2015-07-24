<?php

/**
 * The file that defines the candidate custom post type.
 *
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */

/**
 * The candidate custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'Tax-meta-class/Tax-meta-class.php';

class Election_Data_Candidate {
	// Definition of the custom post type.
	protected $custom_post;
	
	// Definition of the custom post meta fields.
	protected $custom_post_meta;
	
	// Definition of the Party and Constituency taxonomies.
	protected $taxonomies;
	
	// Definition of the Party and Constituency meta fields.
	protected $taxonomy_meta;
	
	function __construct() {
		$this->custom_post = array(
			'name' => 'ed_candidates',
			'args' => array(
				'labels' => array(
					'name' => __( 'Candidates' ),
					'singular_name' => __( 'Candidate' ),
					'add_new_item' => __( 'Add New Candidate' ),
					'edit_item' => __( 'Edit Candidate' ),
					'new_item' => __( 'New Candidate' ),
					'view_item' => __( 'View Candidate' ),
					'search_items' => __( 'Search Candidates' ),
					'not_found' => __( 'No Candidates found' ),
					'not_found_in_trash', __( 'No Candidates found in Trash' ),
				),
				'description' => __( 'A candidate for the election.' ),
				'public' => true,
				'menu_position' => 5,
				//'menu_icon' => plugins_url( 'images/candidate.png', dirname( __FILE__ ) ), //TODO: Create a candidate image,
				'supports' => array( 'title', 'thumbnail' ),
				'taxonomies' => array( '' ),
				'has_archive' => false,
				'query_var' => __( 'candidate' ),
				'rewrite' => array( 'slug' => __( 'candidates' ), 'with_front' => false ),
				'enter_title_here' =>  __( 'Enter Candidate Name' ),
				'admin_column_names' => array( 'title' => __( 'Candidate Name' ) ),
				'remove_admin_columns' => array( 'date' ),
				'remove_admin_column_date_filter' => true,
				'hide_quick_edit_fields' => array( 'password', 'date' ),
				'quick_edit_column_names' => array( 'title' => __( 'Name' ) ),
			)
		);
		
		$this->custom_post_meta = array(
			'id' => 'election_data_candidate_meta_box',
			'title' => __( 'Candidate Details' ),
			'post_type' => $this->custom_post['name'],
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				'phone' => array(
					'label' => 'Phone Number',
					'meta_id' => 'phone',
					'desc' => __( "The candidate's phone number." ),
					'id' => 'candidate_phone',
					'type' => 'text',
					'std' => '',
				),
				'website' => array(
					'label' => 'Website',
					'meta_id' => 'website',
					'desc' => __( "The candidate's website." ),
					'id' => 'candidate_website',
					'type' => 'url',
					'std' => '',
				),
				'email' => array(
					'label' => 'Email Address',
					'meta_id' => 'email',
					'desc' => __( "The candidate's email address." ),
					'id' => 'candidate_email',
					'type' => 'email',
					'std' => '',
				),
				'facebook' => array(
					'label' => 'Facbook Page',
					'meta_id' => 'facebook',
					'desc' => __( "The url to the canidate's facebook page." ),
					'id' => 'candidate_facebook',
					'type' => 'url',
					'std' => '',
				),
				'youtube' => array(
					'label' => 'Youtube Channel or Video',
					'meta_id' => 'youtube',
					'desc' => __( "A link to the candidate's youtube channel or video" ),
					'id' => 'candidate_youtube',
					'type' => 'url',
					'std' => '',
				),
				'twitter' => array(
					'label' => 'Twitter Feed',
					'meta_id' => 'twitter',
					'desc' => __( "A link to the candidate's twitter feed." ),
					'id' => 'candidate_twitter',
					'type' => 'url',
					'std' => '',
				),
				'incumbent_year' => array(
					'label' => 'Year Previously Elected',
					'meta_id' => 'incumbent',
					'desc' => __( 'If the candidate is the incumbent, the year he/she was elected.' ),
					'id' => 'candidate_incumbent',
					'type' => 'text',
					'std' => '',
				),
				'party_leader' => array(
					'label' => 'Party Leader',
					'meta_id' => 'party_leader',
					'desc' => __( 'Indicate if the candidate is the party leader.' ),
					'id' => 'candidate_party_leader',
					'type' => 'checkbox',
					'std' => '',
				),
			),
			'admin_column_fields' => array( 'phone' => '', 'email' => '', 'website' => '', 'party_leader' => '' ),
		);

		$this->taxonomies = array(
			'party' => array(
				'name' => $this->custom_post['name'] . '_party',
				'post_type' => $this->custom_post['name'],
				'args' => array(
					'labels' => array(
						'name' => _x( 'Parties', 'taxonomy general name' ),
						'singular_name' => _x( 'Party', 'taxonomy general name' ),
						'all_items' => __( 'All Parties' ),
						'edit_item' => __( 'Edit Party' ),
						'view_item' => __( 'View Party' ),
						'update_item' => __( 'Update Party' ),
						'add_new_item' => __( 'Add New Party' ),
						'new_item_name' => __( 'New Party Name' ),
						'search_items' => __( 'Search Parties' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => true,
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'hierarchical' => true,
					'query_var' => 'party',
					'rewrite' => array( 'slug' => 'parties', 'with_front' => false )
				),
				'use_radio_button' => false,
			),
			'constituency' => array(
				'name' => $this->custom_post['name'] . '_constituency',
				'post_type' => $this->custom_post['name'],
				'args' => array(
					'labels' => array(
						'name' => _x( 'Constituencies', 'taxonomy general name' ),
						'singular_name' => _x( 'Constituency', 'taxonomy general name' ),
						'all_items' => __( 'All Constituencies' ),
						'edit_item' => __( 'Edit Constituency' ),
						'view_item' => __( 'View Constituency' ),
						'update_item' => __( 'Update Constituency' ),
						'add_new_item' => __( 'Add New Constituency' ),
						'new_item_name' => __( 'New Constituency Name' ),
						'search_items' => __( 'Search Constituencies' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => true,
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'hierarchical' => true,
					'query_var' => 'constituency',
					'rewrite' => array( 'slug' => 'constituencies', 'with_front' => false )
				),
			),
		);
		
		$this->taxonomy_meta = array(
			'party' => array(
				'id' => $this->taxonomies['party']['name'] . '_meta_box',
				'title' => 'Party Details',
				'pages' => array( $this->taxonomies['party']['name'] ),
				'context' => 'normal',
				'fields' => array(
					array(
						'type' => 'color',
						'id' => 'party_colour',
						'std' => '#000000',
						'desc' => __( 'Select a colour to identify the party.' ),
						'name' => __( 'Party Colour' ),
					),
					array(
						'type' => 'image',
						'id' => 'party_logo',
						'desc' => __( 'Select a logo for the party.' ),
						'name' => __( 'Party Logo' ),
					),
				),
				'local_images' => true,
			),
		);
	}
	
	function taxonomy_category_radio_meta_box ($post, $box) {
		echo "Needs to be written."; // See post_categories_meta_box in wordpress/admin/includes/meta_boxes.php, wp_terms_checklist in wordpress/admin/includes/template.php and Walker_Category_Checklist in wordpress/admin/includes/template.php for ideas on how to implement.
	}
	
	// Sets up the custom post type and the taxonomies.
	function initialize() {
		register_post_type( $this->custom_post['name'], $this->custom_post['args'] );
		
		foreach ( $this->taxonomies as $taxonomy ) {
			if ( isset( $taxonomy['use_radio_button'] ) && $taxonomy['use_radio_button'] ) {
				if ( $taxonomy['args']['hierarchical'] ) {
					$taxonomy['args']['meta_box_cb'] = array( $this, 'taxonomy_category_radio_meta_box' );
				}
			}
			
			register_taxonomy( $taxonomy['name'], $taxonomy['post_type'], $taxonomy['args'] );
		}
		
		foreach ( $this->taxonomy_meta as $meta_config ) {
			$meta = new Tax_Meta_Class( $meta_config );
			$meta->Finish();
		}
	}
	
	// Changes the title text to identify it as the candidate name.
	function update_title( $label )
	{
		global $post_type;
	
		if ( is_admin() && $this->custom_post['name'] == $post_type )
		{
			return $this->custom_post['args']['enter_title_here'];
		}
		
		return $label;
	}
	
	// Initialize the administrative interface.
	function admin()
	{
		add_meta_box( 
			$this->custom_post_meta['id'],
			$this->custom_post_meta['title'],
			array( $this, 'render_custom_post_meta_box' ),
			$this->custom_post_meta['post_type'], 
			$this->custom_post_meta['context'],
			$this->custom_post_meta['priority']
		);
	}
	
	// The call back to create the custom post meta box.
	function render_custom_post_meta_box( $post ) {
		$fields = $this->custom_post_meta['fields'];
		require plugin_dir_path( __FILE__ ) . 'partials/election-data-meta-box-display.php';
	}	
	
	function save_post_bulk_edit( ) {
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			wp_die();
		}
		
		$post_ids = empty( $_POST['post_ids'] ) ? '' : $_POST['post_ids'];
		if ( is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$post_type = get_post_type( $post_id );
								
				// check if the Custom Post post type
				if ( !$this->custom_post['name'] == $post_type ) {
					continue;
				}
				
				// check permissions
				if ( !current_user_can( 'edit_post', $post_id ) ) {
					continue;
				}
					
				foreach ( $this->custom_post_meta['fields'] as $field => $value ) {
					if ( !empty( $_POST['field_' . $field] ) ) {
						$new = $_POST['field_' . $field];
						update_post_meta( $post_id, $value['meta_id'], $new );
					}
				}
			}
		}
		
		wp_die();
	}
	
	// Stores the meta data from the meta box for the custom post.
	function save_custom_post_fields( $post_id, $post ) {		
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// check permissions
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
			
		foreach ( $this->custom_post_meta['fields'] as $field ) {
			if ( isset( $_POST[$field['id']] ) ) {
				$new = $_POST[$field['id']];
				update_post_meta( $post_id, $field['meta_id'], $new );
			}
		}
	}

	// Identifies the columns to display in the administrative interface.
	function define_columns( $columns ) {
		foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $this->custom_post_meta['fields'][$field]['label'];
		}
		
		if ( isset( $this->custom_post['args']['admin_column_names'] ) ) {
			foreach ( $this->custom_post['args']['admin_column_names'] as $column_name => $title ) {
				$columns[$column_name] = $title;
			}
		}
		
		if ( isset( $this->custom_post['args']['remove_admin_columns'] ) ) {
			foreach ( $this->custom_post['args']['remove_admin_columns'] as $column_name ) {
				unset( $columns[$column_name] );
			}
		}
		
		return $columns;
	}
	
	// Fills the columns data.
	function populate_columns( $column ) {
		if ( isset( $this->custom_post_meta['admin_column_fields'][$column] ) ) {
			echo "<div id='$column-" . get_the_ID() . "'>";
			$field = $this->custom_post_meta['fields'][$column];
			$value = get_post_meta( get_the_ID(), $field['meta_id'], true );
			switch ( $field['type'] ) {
				case 'checkbox':
					echo $value ? 'X' : '';
					break;
				case 'url':
					echo "<a href='$value'>$value</a>";
					break;
				default:
					echo esc_html( $value );
			}
			echo '</div>';
		}
	}

	// Adds meta data to the custom box used when quick or bulk editting the custom post.
	function bulk_quick_edit_custom_box( $column_name ) {
		if ( isset( $this->custom_post_meta['admin_column_fields'][$column_name] ) ) {
			$field = $this->custom_post_meta['fields'][$column_name];-
			require plugin_dir_path( __FILE__ ) . 'partials/election-data-quick-edit-display.php' ;
		}
	}
	
	// Identifies the sortable columns in the administrative interface.
	function sort_columns( $columns ) {
		foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $field;
		}
		foreach ( $this->taxonomies as $taxonomy ) {
			$columns['taxonomy-' . $taxonomy['name']] = 'taxonomy-' . $taxonomy['name'];
		}
		
		return $columns;
	}

	// Changes search query for taxonomies so that custom posts without the taxonomy are included in the results when sorting by a taxonomy.
	function taxonomy_clauses( $clauses, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['orderby'] ) ) {
			foreach ( $this->taxonomies as $taxonomy ) {
				if ( $taxonomy['name'] == $wp_query->query['orderby'] ) {
					$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;

					$clauses['where'] .= " AND (taxonomy = '" . substr( $wp_query->query['orderby'], 9 ) . "' OR taxonomy IS NULL)";
					$clauses['groupby'] = "object_id";
					$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
					$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
				}
			}
		}
		
		return $clauses;
	}
	
	// allows meta data columns to be sorted.
	function column_orderby( $vars ) {
		if ( !is_admin() )
			return $vars;
		if ( isset( $vars['post_type'] ) && $this->custom_post['name'] == $vars['post_type'] && isset( $vars['orderby'] ) ) {
			foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
				if ( $this->custom_post_meta['fields'][$field]['meta_id'] == $vars['orderby'] ) {
					$vars = array_merge( $vars, array( 'meta_key' => $vars['orderby'], 'orderby' => 'meta_value' ) );
				}
			}
		}
		
		return $vars;
	}
	
	// Removes the date filter from the admin column.
	function remove_dates( $vars )
	{
		if ( $this->custom_post['name'] == get_post_type() ) {
			return array();
		}
		
		return $vars;
	}
	
	// Adds filters for the taxonomies.
	function filter_lists() {
		$screen = get_current_screen();
		global $wp_query;
		
		if ( $this->custom_post['name'] == $screen->post_type ) {
			foreach ( $this->taxonomies as $taxonomy ) {
				$query = $taxonomy['args']['query_var'];
				$name = $taxonomy['name'];
				$selected = '';
				if ( isset( $wp_query->query[$query] ) ) {
					$term = get_term_by( 'slug', $wp_query->query[$query], $name );
					if ( $term )
					{
						$selected = (int)$term->term_id;
					}
				}
				
				$args = array(
					'show_option_all' => 'All ' . $taxonomy['args']['labels']['name'],
					'taxonomy' => $name,
					'name' => $query,
					'orderby' => 'name',
					'selected' => $selected,
					'hierarchical' => true,
					'depth' => 3,
					'show_count' => false,
					'hide_empty' => true,
					'value_field' => 'slug'
				);
				
				wp_dropdown_categories( $args );
			}
		}
	}
	
	// Allow for default template files to be a part of the plugin.
	function include_template_function( $template_path ) {
		$template_file = '';
		switch ( get_query_var( 'post_type' ) ) {
			case $this->custom_post['name']:
				if ( is_single() )
				{
					$template_file = 'single-' . $this->custom_post['name'] . '.php';
				}
				else
				{
					$template_file = 'archive-' . $this->custom_post['name'] . '.php';
				}
				break;
			case '':
				$taxonomy = get_query_var( 'taxonomy' );
				foreach ( $this->taxonomies as $taxonomy_def )
				{
					if ( $taxonomy == $taxonomy_def['name'] ) {
						$template_file = "single-$taxonomy.php";
						break;
					}
				}
				break;
		}
		
		$plugin_path = plugin_dir_path( dirname( __FILE__ ) ) . "template/$template_file";
		
		if ( $template_file && is_file( $plugin_path ) ) {
			if ( $theme_file = locate_template( array( $template_file ) ) ) {
				$template_path = $theme_file;
			} else {
				$template_path = $plugin_path;
			}
		}
		
		return $template_path;
	}
	
	function setup_admin_scripts() {
		global $current_screen;
		
		if ( $current_screen->id == 'edit-' . $this->custom_post['name'] ) {
			wp_register_script( 'quick-edit-' . $this->custom_post['name'], plugin_dir_url( __FILE__ )  . 'js/quick-edit.js', array( 'jquery', 'inline-edit-post' ), '', true  );
			$translation_array = array();
			foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
				$translation_array[$field] = $this->custom_post_meta['fields'][$field]['id'];
			}
			
			wp_localize_script( 'quick-edit-' . $this->custom_post['name'], 'ed_quick_edit', $translation_array );
			
			$translation_array = array();
			if ( isset( $this->custom_post['args']['hide_quick_edit_fields'] ) ) {
				foreach ( $this->custom_post['args']['hide_quick_edit_fields'] as $column ) {
					$translation_array[ucfirst($column)] = '';
				}
			}

			wp_localize_script( 'quick-edit-' . $this->custom_post['name'], 'ed_remove_columns', $translation_array );
			
			$translation_array = array();
			if ( isset( $this->custom_post['args']['quick_edit_column_names'] ) ) {
				foreach ( $this->custom_post['args']['quick_edit_column_names'] as $column => $name ) {
					$translation_array[ucfirst($column)	] = $name;
				}
			}
			
			wp_localize_script( 'quick-edit-' . $this->custom_post['name'], 'ed_rename_columns', $translation_array );
			
			wp_enqueue_script( 'quick-edit-' . $this->custom_post['name'] );
		}
	}
	
	function setup_public_scripts() {
		wp_enqueue_style( 'ed_' . $this->custom_post['name'] . '_style', plugin_dir_url( __FILE__ ) . 'css/application.css' );
	}
	
	function admin_init( $loader )
	{
		$loader->add_action( 'admin_init', $this, 'admin' );
		$loader->add_action( 'save_post_' . $this->custom_post['name'], $this, 'save_custom_post_fields', 10, 2 );
		$loader->add_action( 'wp_ajax_save_post_bulk_edit', $this, 'save_post_bulk_edit' );
		$loader->add_filter( 'manage_edit-' . $this->custom_post['name'] . '_columns', $this, 'define_columns' );
		$loader->add_action( 'manage_posts_custom_column', $this, 'populate_columns' );
		$loader->add_action( 'bulk_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_action( 'quick_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_filter( 'manage_edit-' . $this->custom_post['name'] . '_sortable_columns', $this, 'sort_columns' );
		$loader->add_filter( 'posts_clauses', $this, 'taxonomy_clauses', 10, 2 );
		$loader->add_filter( 'request', $this, 'column_orderby' );
	    $loader->add_action( 'restrict_manage_posts', $this, 'filter_lists' );
		$loader->add_action( 'admin_enqueue_scripts', $this, 'setup_admin_scripts' );
		if ( isset( $this->custom_post['args']['remove_admin_column_date_filter'] ) ) {
			$loader->add_filter( 'months_dropdown_results', $this, 'remove_dates' );
		}
		
		if ( isset( $this->custom_post['args']['enter_title_here'] ) ) {
			$loader->add_filter( 'enter_title_here', $this, 'update_title' );
		}
	}
	
	function init( $loader )
	{
		$loader->add_action( 'init', $this, 'initialize' );
		$loader->add_filter( 'template_include', $this, 'include_template_function', 1 );
		$loader->add_filter( 'wp_enqueue_scripts', $this, 'setup_public_scripts' );
	}	
 }