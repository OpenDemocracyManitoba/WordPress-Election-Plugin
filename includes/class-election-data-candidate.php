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

require_once plugin_dir_path( __FILE__ ) . 'class-post-meta.php';
require_once plugin_dir_path( __FILE__ ) . 'class-taxonomy-meta.php';

class Election_Data_Candidate {
	// Definition of the custom post type.
	protected $custom_post;
	
	// Definition of the Party and Constituency taxonomies.
	protected $taxonomies;
	
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
				'has_archive' => true,
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
		
		$custom_post_meta = array(
			'meta_box' => array(
				'id' => 'election_data_candidate_meta_box',
				'title' => __( 'Candidate Details' ),
				'post_type' => $this->custom_post['name'],
				'context' => 'normal',
				'priority' => 'high',
			),
			'fields' => array(
				'phone' => array(
					'label' => __( 'Phone Number' ),
					'id' => 'phone',
					'desc' => __( "Enter the candidate's phone number." ),
					'type' => 'text',
					'std' => '',
				),
				'website' => array(
					'label' => __( 'Website' ),
					'id' => 'website',
					'desc' => __( "Enter the URL to the candidate's website." ),
					'type' => 'url',
					'std' => '',
				),
				'email' => array(
					'label' => __( 'Email Address' ),
					'id' => 'email',
					'desc' => __( "Enter the candidate's email address." ),
					'type' => 'email',
					'std' => '',
				),
				'facebook' => array(
					'label' => __( 'Facbook Page' ),
					'id' => 'facebook',
					'desc' => __( "Enter the URL to the canidate's facebook page." ),
					'type' => 'url',
					'std' => '',
				),
				'youtube' => array(
					'label' => __( 'Youtube Channel or Video' ),
					'id' => 'youtube',
					'desc' => __( "Enter the URL to the candidate's youtube channel or video" ),
					'type' => 'url',
					'std' => '',
				),
				'twitter' => array(
					'label' => __( 'Twitter Feed' ),
					'id' => 'twitter',
					'desc' => __( "Enter the URL to the candidate's twitter feed." ),
					'type' => 'url',
					'std' => '',
				),
				'incumbent_year' => array(
					'label' => __( 'Year Previously Elected' ),
					'id' => 'incumbent',
					'desc' => __( 'If the candidate is the incumbent, enter the year he/she was elected.' ),
					'type' => 'text',
					'std' => '',
				),
				'party_leader' => array(
					'label' => __( 'Party Leader' ),
					'id' => 'party_leader',
					'desc' => __( 'Indicate if the candidate is the party leader.' ),
					'type' => 'checkbox',
					'std' => '',
				),
				'reference' => array(
					'id' => 'reference',
					'type' => 'hidden',
					'std' => '',
				),
			),
			'admin_columns' => array( 'phone', 'email', 'website', 'party_leader' ),
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
		
		$taxonomy_meta = array(
			'party' => array(
				'taxonomy' => $this->taxonomies['party']['name'],
				'fields' => array(
					array(
						'type' => 'color',
						'id' => 'colour',
						'std' => '#000000',
						'desc' => __( 'Select a colour to identify the party.' ),
						'label' => __( 'Colour' ),
					),
					array(
						'type' => 'image',
						'id' => 'logo',
						'desc' => __( 'Select a logo for the party.' ),
						'label' => __( 'Logo' ),
						'std' => '',
					),
					array(
						'type' => 'url',
						'id' => 'website',
						'desc' => __( "Enter the URL to the party's web site." ),
						'label' => __( 'Web Site URL' ),
						'std' => '',
					),
					array(
						'type' => 'text',
						'id' => 'phone',
						'desc' => __( "Enter the party's phone number." ),
						'label' => __( 'Phone Number' ),
						'std' => '',
					),
					array(
						'type' => 'text',
						'id' => 'address',
						'desc' => __( "Enter the party's address." ),
						'label' => __( 'Address' ),
						'std' => '',
					),
					array(
						'type' => 'email',
						'id' => 'email',
						'desc' => __( "Enter the party's email address." ),
						'label' => __( 'Email Address' ),
						'std' => '',
					),
					array(
						'type' => 'url',
						'id' => 'facebook',
						'desc' => __( "Enter the URL to the party's facebook page." ),
						'label' => __( 'Facbook Page' ),
						'std' => '',
					),
					array(
						'type' => 'url',
						'id' => 'youtube',
						'desc' => __( "Enter the URL to the party's youtube channel or video" ),
						'label' => __( 'Youtube Channel or Video' ),
						'std' => '',
					),
					array(
						'type' => 'url',
						'id' => 'twitter',
						'desc' => __( "Enter the URL to the party's twitter feed." ),
						'label' => __( 'Twitter Feed' ),
						'std' => '',
					),
					array(
						'type' => 'hidden',
						'id' => 'reference',
						'std' => '',
					),
				),
			),
			'constituency' => array(
				'taxonomy' => $this->taxonomies['constituency']['name'],
				'fields' => array(
					array(
						'type' => 'image',
						'id' => 'map',
						'desc' => __( "A map of the child constituencies." ),
						'label' => __( "Constituency Map" ),
						'std' => '',
					),
					array(
						'type' => 'text',
						'id' => 'coordinates',
						'desc' => __( 'HTML map coordinates for constituency location on parent constituencies map. You can generate these coordinates by using an online map tool available <a href="https://www.google.com/search?q=html+map+generator+online">here</a>' ),
						'label' => __( 'Coordinates' ),
						'std' => '',
					),
					array(
						'type' => 'wysiwyg',
						'id' => 'details',
						'desc' => __( 'A description of the constituency. ' ),
						'label' => __( 'Details' ),
						'std' => '',
					),
				),
			),
		);
		
		$meta = new Post_Meta( 
			$custom_post_meta['meta_box'],
			$custom_post_meta['fields'],
			$custom_post_meta['admin_columns']
		);
		
		foreach ( $taxonomy_meta as $name => $tax_meta_config ) {
			$tax_meta = new Tax_Meta( $tax_meta_config['taxonomy'], $tax_meta_config['fields'], array( 'Description', ) );
		}
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
	}
	
	// Identifies the columns to display in the administrative interface.
	function define_columns( $columns ) {
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
	
	// Identifies the sortable columns in the administrative interface.
	function sort_columns( $columns ) {
		foreach ( $this->taxonomies as $taxonomy ) {
			$columns["taxonomy-{$taxonomy['name']}"] = "taxonomy-{$taxonomy['name']}";
		}
		
		return $columns;
	}

	// Changes search query for taxonomies so that posts can be sorted by taxonomy.
	function taxonomy_clauses( $clauses, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['orderby'] ) ) {
			foreach ( $this->taxonomies as $taxonomy ) {
				if ( "taxonomy-{$taxonomy['name']}" == $wp_query->query['orderby'] ) {
					$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} tr2 ON {$wpdb->posts}.ID=tr2.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
LEFT OUTER JOIN {$wpdb->terms} t2 on tt2.term_id = t2.term_id
SQL;

					$clauses['where'] .= " AND (tt2.taxonomy = '" . $taxonomy['name'] . "' OR tt2.taxonomy IS NULL)";
					$clauses['groupby'] = "tr2.object_id";
					$clauses['orderby']  = "GROUP_CONCAT(t2.name ORDER BY name ASC) ";
					$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
				}
			}
		}
		
		return $clauses;
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
		
		if ( $current_screen->id == "edit-{$this->custom_post['name']}" && ( isset( $this->custom_post['args']['hide_quick_edit_fields'] ) || isset( $this->custom_post['args']['quick_edit_column_names'] ) ) ) {
			wp_register_script( 'quick-edit-' . $this->custom_post['name'], plugin_dir_url( __FILE__ )  . 'js/quick-edit.js', array( 'jquery', 'inline-edit-post' ), '', true  );
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
	
	function define_hooks( )
	{
		add_action( 'admin_init', array( $this, 'admin' ) );
		add_filter( "manage_edit-{$this->custom_post['name']}_columns", array( $this, 'define_columns' ) );
		add_filter( 'manage_edit-' . $this->custom_post['name'] . '_sortable_columns', array(  $this, 'sort_columns' ) );
		add_filter( 'posts_clauses', array(  $this, 'taxonomy_clauses' ), 10, 2 );
	    add_action( 'restrict_manage_posts', array(  $this, 'filter_lists' ) );
		add_action( 'admin_enqueue_scripts', array(  $this, 'setup_admin_scripts' ) );
		if ( isset( $this->custom_post['args']['remove_admin_column_date_filter'] ) ) {
			add_filter( 'months_dropdown_results', array(  $this, 'remove_dates' ) );
		}
		
		if ( isset( $this->custom_post['args']['enter_title_here'] ) ) {
			add_filter( 'enter_title_here',  array( $this, 'update_title' ) );
		}

		add_action( 'init',  array( $this, 'initialize' ) );
		add_filter( 'template_include',  array( $this, 'include_template_function' ), 1 );
		add_filter( 'wp_enqueue_scripts',  array( $this, 'setup_public_scripts' ) );
	}	
 }