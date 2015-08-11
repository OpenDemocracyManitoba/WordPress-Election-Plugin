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
	
	protected $post_meta;
	
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
					'id' => 'incumbent_year',
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
		
		$this->post_meta = new Post_Meta( 
			$custom_post_meta['meta_box'],
			$custom_post_meta['fields'],
			$custom_post_meta['admin_columns']
		);
		
		$this->taxonomy_meta = array();
		foreach ( $taxonomy_meta as $name => $tax_meta_config ) {
			$this->taxonomy_meta[$name] = new Tax_Meta( $tax_meta_config['taxonomy'], $tax_meta_config['fields'], array( 'Description', ) );
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
		
	function export_xml( $xml ) {
	}
	
	function write_csv_row( $file, $data, $order ) {
		$csv_data = array();
		foreach ( $order as $field_name )
		{
			$csv_data[] = $data[$field_name];
		}
		fputcsv( $file, $csv_data );
	}
	
	function export_candidate( $file, $type, $write_headings ) {
		$post_fields = array(
			'post_title' => 'name',
			'post_name' => 'slug',
			'image' => array( 
				'base_64' => 'photo_base64',
				'filename' => 'photo_filename',
				'url' => 'photo_url',
				'' => 'photo' ),
		);
		
		$taxonomy_fields = array();
		foreach ( $this->taxonomies as $name => $taxonomy ) {
			$taxonomy_fields[$taxonomy['name']] = $name;
		}
		
		$meta_fields = $this->post_meta->get_field_names();
		
		$headings = array();
		foreach ( array_merge( $post_fields, $taxonomy_fields, $meta_fields ) as $field )
		{
			if ( is_array( $field ) ) {
				$headings += $field;
			} else {
				$headings[] = $field;
			}
		}

		if ( $write_headings ) {
			$heading_data = array_combine( $headings, $headings );
			call_user_func( array($this, "write_{$type}_row") , $file, $heading_data, $headings );
		}
		
		$args = array(
			'post_type' => $this->custom_post['name'],
			'orderby' => 'name',
			'order' => 'ASC',
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$data = $this->post_meta->get_field_values( $query->post->ID );
			
			foreach ( $post_fields as $field => $label )
			{
				if ( 'image' == $field )
				{
					$image_id = get_post_thumbnail_id( $query->post->ID );
					if ( $image_id ){
						$image_meta = wp_get_attachment_metadata( $image_id );
						$upload_dir = wp_upload_dir();
						$image_filename = "{$upload_dir['basedir']}/{$image_meta['file']}";
						$data[$label[0]] = base64_encode( file_get_contents( $image_filename ) );
						$data[$label[1]] = basename( $image_Filename );
					} else {
						$data[$label[0]] = '';
						$data[$label[1]] = '';
					}
				} else {
					$data[$label] = $query->post->$field;
				}
			}
			
			foreach ( $taxonomy_fields as $name => $label )
			{
				$terms = get_the_terms( $query->post->ID, $name );
				if ( isset( $terms[0] ) ) {
					$term = $terms[0];
					$data[$label] = $term->slug;
				} else {
					$data[$label] = '';
				}
			}
			
			call_user_func( array( $this, "write_{$type}_row" ), $file, $data, $headings );
		}
	}
	
	function export_taxonomy ( $file, $taxonomy, $taxonomy_fields, $type, $write_headings, $parent = null )
	{
		$taxonomy_meta = $this->taxonomy_meta[$taxonomy];
		$meta_fields = $taxonomy_meta->get_field_names();
		$headings = array();
		foreach ( array_merge( $taxonomy_fields, $meta_fields ) as $field_name ) {
			if ( is_array( $field_name ) ) {
				$headings[] = $field_name['base64'];
				$headings[] = $field_name['filename'];
			} else {
				$headings[] = $field_name;
			}
		}
		
		if ( $write_headings ) {
			$headings_data = array_combine( $headings, $headings );
			call_user_func( array($this, "write_{$type}_row") , $file, $headings_data, $headings );
		}
		
		$args = array(
			'hide_empty' => false,
			'fields' => 'all',
			'orderby' => 'name',
			'order' => 'ASC'
		);
		if ( $parent !== null ) {
			$args['parent'] = $parent;
		}
		$terms = get_terms( $this->taxonomies[$taxonomy]['name'], $args );
		foreach ( $terms as $term ) {
			$data = $taxonomy_meta->get_field_values( $term->term_id);
			foreach ( $taxonomy_fields as $field )
			{
				if ( 'parent' == $field ) {
					$parent_term = $term->parent ? get_term( $term->parent, $this->taxonomies[$taxonomy]['name'] ) : '';
					$data[$field] = $parent_term ? $parent_term->slug : '';
				} else {
					$data[$field] = $term->$field;
				}
			}
			
			call_user_func( array( $this, "write_{$type}_row" ), $file, $data, $headings );
			
			if ( $parent !== null ) {
				$this->export_taxonomy ( $file, $taxonomy, $taxonomy_fields, $type, false, $term->term_id );
			}
		}		
	}
	
	function export_party( $file, $type, $write_headings ) {
		$party_fields = array( 'name', 'slug', 'description' );
		$this->export_taxonomy( $file, 'party', $party_fields, $type, $write_headings );
	}
	
	function export_constituency( $file, $type, $write_headings ) {
		$constituency_fields = array( 'name', 'slug', 'parent' );
		$this->export_taxonomy( $file, 'constituency', $constituency_fields, $type, $write_headings, 0 );
	}
	
	function export_csv ( $type ) {
		$file_name = tempnam( 'tmp', 'csv' );
		$file = fopen( $file_name, 'w' );
		call_user_func( array( $this, "export_{$type}" ), $file, 'csv', true );
		
		fclose( $file );
		return $file_name;
	}
	
	function read_csv_line( $csv, $headings )
	{
		$data = fgetcsv( $csv );
		return array_combine( $headings, $data );
	}
	
	function get_or_create_term( $taxonomy, $data, $post_fields, $parent_field, $mode ) {
		$args = array();
		$term = null;
		$name = $data['name'];
		$slug = $data['slug'];
		$description = in_array( 'description', $post_fields ) ? $data['description'] : '';
		if ( empty( $data[$parent_field] ) ) {
			$parent = 0;
		} else {
			$parent_term = get_term_by( 'slug', $data[$parent_field], $taxonomy );
			$parent = $parent_term ? $parent_term->term_id : 0;
		}
		
		if ( term_exists( $name, $taxonomy ) )
		{
			$term = get_term_by( 'name', $name, $taxonomy, ARRAY_A );
		}
		
		if ( !empty( $slug ) ) {
			$args['slug'] = $slug;
			$term_slug = get_term_by( 'slug', $slug, $taxonomy, ARRAY_A );
			if ( $term_slug )
			{
				if ( $term ) {
					if ( $term_slug['term_id'] != $term['term_id'] && 'overwrite' == $mode ) {
						return false;
					}
				} else {
					$term = $term_slug;
				}
			}
		}
		
		if ( !$term ) {
			if ( $description ) {
				$args['description'] = $description;
			}
			if ( $parent ) {
				$args['parent'] = $parent;
			}
			
			$term = wp_insert_term( $name, $taxonomy, $args );
		} else {
			$args = array( 'name' => $name );
			if ( !empty( $slug ) && 'overwrite' == $mode) {
				$args['slug'] = $slug;
			}
			if ( !empty( $parent ) && ( !$term['parent'] || 'overwrite' == $mode ) ) {
				$args['parent'] = $parent;
			}
			if ( !empty( $description ) && ( !$term['description'] || 'overwrite' == $mode ) ) {
				$args['description'] = $description;
			}
			wp_update_term( $term['term_id'], $taxonomy, $args );
				
		}
		return $term;
	}
	
	function add_image_data( $data, $field_name, $post_id = 0 ) {
		$attachment_id = 0;
		if ( !empty( $data["{$field_name}_base64"] ) && !empty( $data["{$field_name}_filename"] ) ) {
			$file_name = $data["{$field_name}_filename"];
			$src_name = tempnam( "tmp", "img" );
			$src = fopen( $src_name, 'wb' );
			fputs( $src, base64_decode( $data["{$field_name}_base64"] ) );
			fclose( $src );
		} elseif ( !empty( $data["{$field_name}_url"] ) ) {
			$file_name = $data["{$field_name}_url"];
			$src_name = wp_download( $file_name );
			if ( is_wp_error( $src_name ) )
			{
				return 0;
			}
		} else {
			return 0;
		}
		$desc = !empty( $data["{$field_name}_description"] ) ? $data["{$field_name}_description"] : '';
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_name, $matches );
		$file_array = array();
		$file_array['name'] = basename( $matches[0] );
		$file_array['tmp_name'] = $src_name;
		
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		
		return $id;
	}
	
	function get_taxonomy_terms( $taxonomies, $data ) {
		$taxonomy_data = array();
		foreach ( $taxonomies as $taxonomy ) {
			if ( !empty( $data[$taxonomy] ) ) {
				$term = get_term_by( 'slug', $data[$taxonomy], $this->taxonomies[$taxonomy]['name'] );
				$taxonomy_data[$this->taxonomies[$taxonomy]['name']][] = $term->term_id;
			}
		}
		
		return $taxonomy_data;
	}
	
	function get_or_create_post( $current_posts, $data, $post_fields, $mode ) {
		$posts_by_title = $current_posts['post_title'];
		$posts_by_name = $current_posts['post_name'];
		$post = null;
		$post_type = $this->custom_post['name'];
		if ( isset( $post_fields['post_name'] ) && !empty( $data[$post_fields['post_name']] ) && !empty( $posts_by_title[$data[$post_fields['post_name']]] ) ) {
			$post = $posts_by_name[$data[$post_fields['post_name']]];
		}
		if ( isset( $post_fields['post_title'] ) && !empty( $data[$post_fields['post_title']] ) && !empty( $posts_by_title[$data[$post_fields['post_title']]] ) ) {
			$posts_title = $posts_by_title[$data[$post_fields['post_title']]];
			if ( count( $posts_title ) == 1 ) {
				if ( $post && $post->ID != $posts_title[0]->ID ) {
					return 0;
				} elseif ( !$post ) {
					$post = $posts_title[0];
				}
			} elseif ( $post && count( $posts_title ) > 1 ) {
				$found = false;
				foreach ( $posts_title as $post_title ) {
					if ( $post_title->ID == $post->ID ) {
						$found = true;
						break;
					}
				}
				
				if ( !$found )
				{
					return 0;
				}
			} else {
				return 0;
			}
		}
		
		if ( !$post ) {
			$args = array( 'post_type' => $post_type, 'post_status' => 'publish' );
			foreach ( $post_fields as $post_name => $field_name ) {
				$args[$post_name] = $data[$field_name];
			}
			
			$post_id = wp_insert_post( $args );
			return get_post( $post_id );
		}
		
		$args = array( 'post_type' => $post_type, 'ID' => $post->ID );
		$updated = false;
		foreach ( $post_fields as $post_name => $field_name ) {
			if ( 'overwrite' == $mode || empty( $post->$post_name ) ) {
				$args[$post_name] = $data[$field_name];
				$updated = true;
			}
		}
		
		if ( $updated ) {
			wp_update_post( $args );
		}
		
		return $post;
	}
	
	function get_current_candidates() {
		$query = new WP_Query( array( 'post_type' => $this->custom_post['name'] ) );
		$candidates = array('post_title' => array(), 'post_name' => array());
		while ( $query->have_posts() ) {
			$query->the_post();
			$post = $query->post;
			$candidates['post_title'][$post->post_title][] = $post;
			$candidates['post_name'][$post->post_name] = $post;
		}
		
		return $candidates;
	}

	function import_candidate_csv( $csv, $mode ) {
		error_log( 'Importing Candidates' );
		$post_fields = array(
			'post_title' => 'name',
			'post_name' => 'slug',
		);
		$post_image_fields = array( 
			'base64' => 'photo_base64',
			'filename' => 'photo_filename',
			'url' => 'photo_url',
			'' => 'photo',
		);
		$taxonomies = array( 'party', 'constituency' );
		$meta_fields = $this->post_meta->get_field_names();
		$headings = fgetcsv( $csv );
		$found = true;
		$current_candidates = $this->get_current_candidates();
		foreach ( array_merge( $post_fields, $taxonomies, $meta_fields ) as $field ) {
			$found &= in_array( $field, $headings );
		}
		$found &= ( in_array( $post_image_fields['base64'], $headings ) && in_array( $post_image_fields['filename'], $headings ) ) || in_array( $post_image_fields['url'], $headings );
		
		if ( !$found ) {
			return false;
		}
		
		while ( ( $data = fgetcsv( $csv ) ) !== false ) {
			$data = array_combine( $headings, $data );
			$post = $this->get_or_create_post( $current_candidates, $data, $post_fields, $mode );
			error_log( print_r ($post, true ) );
			
			foreach ( $taxonomies as $taxonomy ) {
				$taxonomy_name = $this->taxonomies[$taxonomy]['name'];
				$new_term = get_term_by( 'slug', $data[$taxonomy], $taxonomy_name );
				$existing_terms = wp_get_post_terms( $post->ID, $taxonomy_name, array( 'fields' => 'ids' ) );
				if ( 'overwite' == $mode || !$existing_terms ) {
					wp_set_object_terms( $post->ID, $new_term->term_id, $taxonomy_name );
				}
				
				$image_id = get_post_thumbnail_id( $post->ID );
				if ( 'overwrite' == $mode || empty( $image_id ) ) {
					$attachment_id = $this->add_image_data( $data, $post_image_fields[''] );
					set_post_thumbnail( $post, $attachment_id );
				}

				$this->post_meta->update_field_values( $post->ID, $data, $mode );
			}
		}
	}
	
	function import_taxonomy_csv( $csv, $mode, $taxonomy, $taxonomy_fields, $parent_field = '') {
		$headings = fgetcsv( $csv );
		$found = true;
		$taxonomy_meta = $this->taxonomy_meta[$taxonomy];
		$meta_fields = $taxonomy_meta->get_field_names( 'non_image' );
		$image_fields = $taxonomy_meta->get_field_names( 'image' );
		foreach ( array_merge( $taxonomy_fields, $meta_fields ) as $field ) {
			$found &= in_array( $field, $headings );
		}
		if ( $parent_field ) {
			$found &= in_array( $parent_field, $headings );
		}
		foreach ( $image_fields as $field ) {
			$found &= ( in_array( $field['base64'], $headings ) && in_array( $field['filename'], $headings ) ) || in_array( $field['url'], $headings );
		}
		if ( !$found )
		{
			return false;
		}

		while ( ( $data = fgetcsv( $csv ) ) !== false ) {
			$data = array_combine( $headings, $data );
			$term = $this->get_or_create_term( $this->taxonomies[$taxonomy]['name'], $data, $taxonomy_fields, $parent_field, $mode );
			$current_meta = get_tax_meta_all( $term['term_id'] );
			foreach ( $meta_fields as $field_name ) {
				if ( $mode == 'overwrite' || empty( $current_meta[$field_name] ) ) {
					update_tax_meta( $term['term_id'], $field_name, $data[$field_name] );
				}
			}
			foreach ( $image_fields as $field_name ) {
				if ( $mode == 'overwrite' || empty( $current_meta[$field_name['']] ) ) {
					$attachment_id = $this->add_image_data( $data, $field_name[''] );
					if ( $attachment_id ) {
						$image_data = array (
							'id' => $attachment_id,
							'url' => wp_get_attachment_url( $attachment_id ),
						);
						update_tax_meta( $term['term_id'], $field_name[''], $image_data );
					}
				}
			}
		}
		
		return true;
	}
	
	function import_party_csv( $csv, $mode ) {
		error_log( 'Importing Parties' );
		$taxonomy_fields = array( 'name', 'slug', 'description' );

		return $this->import_taxonomy_csv( $csv, $mode, 'party', $taxonomy_fields );
	}
	
	function import_constituency_csv( $csv, $mode ) {
		error_log( 'Importing Constituencies' );
		$taxonomy_fields = array( 'name', 'slug' );
		$parent_field = 'parent';
		
		return $this->import_taxonomy_csv( $csv, $mode, 'constituency', $taxonomy_fields, $parent_field );
	}
	
	function import_csv( $type, $csv, $mode )
	{
		return call_user_func( array( $this, "import_{$type}_csv" ), $csv, $mode );
	}
	
	function erase_data()
	{
		$args = array(
			'post_type' => $this->custom_post['name'],
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			wp_delete_post( $query->post->ID, true );
		}
		
		foreach ( $this->taxonomies as $taxonomy ) {
			$taxonomies[] = $taxonomy['name'];
			$args = array(
				'hide_empty' => false,
				'fields' => 'ids',
				'get' => 'all',
			);
			$term_ids = get_terms( $taxonomy['name'], $args );
			foreach ( $term_ids as $term_id ) {
				wp_delete_term( $term_id, $taxonomy['name'] );
			}
		}
	}
}