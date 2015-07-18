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

class Election_Data_Candidate {
	// Definition of the Candidate post type.
	public $candidate;
	
	// Definition of the Candidate meta fields.
	public $candidate_meta;
	
	// Definition of the Party and Constituency taxonomies.
	public $taxonomies;
	
	// Definition of the Party and Constituency meta fields.
	public $taxonomy_meta;
	
	function __construct() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'Tax-meta-class/Tax-meta-class.php';
		$this->candidate = array(
			'name' => 'ed_candidates',
			'args' => array(
				'labels' => array (
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
				'taxonomies' => array ( '' ),
				'has_archive' => true,
				'query_var' => __( 'candidate' ),
				'rewrite' => array( 'slug' => __( 'candidates' ), 'with_front' => false )
			)
		);
		$this->candidate_meta = array(
			'id' => 'election_data_candidate_meta_box',
			'title' => __( 'Candidate Details' ),
			'post_type' => $this->candidate['name'],
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
				'incumbent' => array(
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
				'name' => $this->candidate['name'] . '_party',
				'post_type' => $this->candidate['name'],
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
					//'meta_box_cb' => '',   //TOOD: Try adding Meta Box Call Back
					'hierarchical' => true,
					'query_var' => 'party',
					'rewrite' => array( 'slug' => 'parties', 'with_front' => false )
				),
			),
			'constituency' => array(
				'name' => $this->candidate['name'] . '_constituency',
				'post_type' => $this->candidate['name'],
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
					//'meta_box_cb' => '',   //TOOD: Add Meta Box Call Back
					'hierarchical' => true,
					'query_var' => 'constituency',
					'rewrite' => array( 'slug' => 'constituencies', 'with_front' => false )
				),
			),
		);
		
		$this->taxonomy_meta = array(
			'party' => array(
				'fields' => array(
					'colour' => array(
						'label' => 'Party Colour',
						'meta_id' => 'colour',
						'desc' => __( 'Select a colour to identify the party.' ),
						'id' => 'party_colour',
						'type' => 'color',
						'std' => '#000000',
					),
					'logo' => array(
						'label' => 'Party Logo',
						'meta_id' => 'logo',
						'desc' => __( 'Select a logo for the party.' ),
						'id' => 'party_logo',
						'type' => 'image',
						'std' => __( 'Browse' ),
					),
				),
			),
			'constituency' => array(
				'fields' => array(),
			),
		);
	}
	
	// Sets up the custom post type and the taxonomies.
	function initialize() {
		register_post_type( $this->candidate['name'], $this->candidate['args'] );
		
		foreach( $this->taxonomies as $taxonomy ) {
			register_taxonomy( $taxonomy['name'], $taxonomy['post_type'], $taxonomy['args'] );
		}
	}
	
	// Changes the title text to identify it as the candidate name.
	function update_title( $label )
	{
		global $post_type;
	
		if ( is_admin() && $this->candidate['name'] == $post_type )
		{
			return __( 'Enter Candidate Name' );
		}
		
		return $label;
	}
	
	// Initialize the administrative interface.
	function admin()
	{
		add_meta_box( 
			$this->candidate_meta['id'],
			$this->candidate_meta['title'],
			array( $this, 'render_candidate_meta_box' ),
			$this->candidate_meta['post_type'], 
			$this->candidate_meta['context'],
			$this->candidate_meta['priority']
		);
	}
	
	// The call back to create the candidate meta box.
	function render_candidate_meta_box( $candidate ) {
		$fields = $this->candidate_meta['fields'];
		require plugin_dir_path( __FILE__ ) . 'partials/election-data-candidate-meta-box-display.php';
	}	
	
	// Stores the meta data from the meta box for the candidate.
	function save_candidate_fields( $candidate_id, $candidate ) {
		global $meta_box;
		
		// check autosave
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $candidate_id;
		}
		
		// check if a candidate post type
		if( !$this->candidate['name'] == $candidate->post_type ) {
			return $candidate_id;
		}
		
		// check permissions
		if( !current_user_can( 'edit_post', $candidate_id ) ) {
			return $candidate_id;
		}
			
		foreach ( $this->candidate_meta['fields'] as $field ) {
			$old = get_post_meta( $candidate_id, $field['meta_id'], true );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : '';
			if ( $new && $new != $old )
			{
				update_post_meta( $candidate_id, $field['meta_id'], $new );
			} elseif( '' == $new && $old )
			{
				delete_post_meta( $candidate_id, $field['meta_id'], $old );
			}
		}
	}

	// Identifies the columns to display in the administrative interface.
	function define_columns( $columns ) {
		foreach ( $this->candidate_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $this->candidate_meta['fields'][$field]['label'];
		}
		
		$columns['title'] = 'Candidate Name';
		unset( $columns['date'] );
		return $columns;
	}
	
	// Fills the columns data.
	function populate_columns( $column ) {
		if( isset( $this->candidate_meta['admin_column_fields'][$column] ) ) {
			echo "<div id='$column-" . get_the_ID() . "'>";
			$field = $this->candidate_meta['fields'][$column];
			$value = get_post_meta( get_the_ID(), $field['meta_id'], true );
			switch( $field['type'] ) {
				case 'checkbox':
					echo $value ? 'X' : '';
					break;
				default:
					echo esc_html( $value );
			}
			echo '</div>';
		}
	}

	// Adds meta data to the custom box used when quick or bulk editting the candidate.
	function bulk_quick_edit_custom_box( $column_name ) {
		if( isset( $this->candidate_meta['admin_column_fields'][$column_name] ) ) {
			$field = $this->candidate_meta['fields'][$column_name];-
			require plugin_dir_path( __FILE__ ) . 'partials/election-data-candidate-quick-edit-display.php' ;
		}
	}
	
	// Identifies the sortable columns in the administrative interface.
	function sort_columns( $columns ) {
		foreach ( $this->candidate_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $field;
		}
		foreach ( $this->taxonomies as $taxonomy ) {
			$columns['taxonomy-' . $taxonomy['name']] = 'taxonomy-' . $taxonomy['name'];
		}
		
		//error_log( print_r( $columns, true ) );
		return $columns;
	}

	// Changes search query for taxonomies so that candidates without the taxonomy are included in the results when sorting by a taxonomy.
	function taxonomy_clauses ( $clauses, $wp_query ) {
		global $wpdb;
		if ( isset( $wp_query->query['orderby'] ) ) {
			foreach( $this->taxonomies as $taxonomy ) {
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
		if ( isset( $vars['post_type'] ) && $this->candidate['name'] == $vars['post_type'] && isset( $vars['orderby'] ) ) {
			foreach( $this->candidate_meta['admin_column_fields'] as $field => $value ) {
				if( $this->candidate_meta['fields'][$field]['meta_id'] == $vars['orderby'] ) {
					$vars = array_merge( $vars, array( 'meta_key' => $vars['orderby'], 'orderby' => 'meta_value' ) );
				}
			}
		}
		
		return $vars;
	}
	
	// Removes the date filter from the admin column.
	function remove_dates( $vars )
	{
		if ( $this->candidate['name'] == get_post_type() ) {
			return array();
		}
		
		return $vars;
	}
	
	// Adds filters for the party and constituency.
	function filter_lists() {
		$screen = get_current_screen();
		global $wp_query;
		
		if ( $this->candidate['name'] == $screen->post_type ) {
			foreach( $this->taxonomies as $taxonomy ) {
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
		switch( get_query_var( 'post_type' ) ) {
			case $this->candidate['name']:
				if ( is_single() )
				{
					$template_file = 'single-' . $this->candidate['name'] . '.php';
				}
				else
				{
					$template_file = 'archive-' . $this->candidate['name'] . '.php';
				}
				break;
			case '':
				$taxonomy = get_query_var( 'taxonomy' );
				switch( $taxonomy ) {
					case $this->taxonomies['party']['name']:
					case $this->taxonomies['constituency']['name']:
						$template_file = "single-$taxonomy.php";
				}
				break;
		}
		
		if( $template_file ) {
			if( $theme_file = locate_template( array ( $template_file ) ) ) {
				$template_path = $theme_file;
			} else {
				$template_path = plugin_dir_path( dirname( __FILE__ ) ) . "template/$template_file";
			}
		}
		
		return $template_path;
	}
	
	function add_party_fields() {
		$fields = $this->taxonomy_meta['party']['fields'];
		require plugin_dir_path( __FILE__ ) . 'partials/election-data-party-add-meta-display.php';	
	}
	
	function edit_party_fields( $term ) {
		$fields = $this->taxonomy_meta['party']['fields'];
		require plugin_dir_path( __FILE__ ) . 'partials/election-data-party-edit-meta-display.php';
	}
	
	function save_party_custom_meta( $term_id ) {
		$term_meta = get_option( "taxonomy_$term_id", array() );
		foreach ( $this->party_fields as $field => $value ) {
			$term_meta[$field] = $_POST["party_$field"];
		}
		
		update_option( "taxonomy_$term_id", $term_meta );
	}
	
	function setup_scripts() {
		global $current_screen;
		
		switch( $current_screen->id ) {
			case 'edit-' . $this->candidate['name']:
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'inline-edit-post' );
				wp_enqueue_script( 'quick-edit-candidate', plugin_dir_url( __FILE__ )  . 'js/quick-edit-candidate.js', array( 'jquery', 'inline-edit-post' ), '', true );
				break;
			case 'edit-' . $this->taxonomies['party']['name']:
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'edit-canidiate_party', plugin_dir_url( __FILE__ ) . 'js/edit-candidate_party.js', array( 'jquery', 'media-upload', 'thickbox' ) );
				break;
		}
		
		if( isset( $_GET['taxonomoy'] ) && $_GET['taxonomy'] == $this->taxonomies['party']['name'] ) {
			wp_enqueue_styles( 'thickbox' );
		}
	}
	
	function setup_styles() {

	}
	
	function setup_public_scripts() {
		wp_enqueue_style('ed_candidate_style', plugin_dir_url( __FILE__ ) . 'css/application.css');
	}
	
	function admin_init( $loader )
	{
		$loader->add_action( 'admin_init', $this, 'admin' );
		$loader->add_action( 'save_post', $this, 'save_candidate_fields', 10, 2 );
		$loader->add_filter( 'manage_edit-' . $this->candidate['name'] . '_columns', $this, 'define_columns' );
		$loader->add_action( 'manage_posts_custom_column', $this, 'populate_columns' );
		$loader->add_action( 'bulk_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_action( 'quick_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_filter( 'manage_edit-' . $this->candidate['name'] . '_sortable_columns', $this, 'sort_columns' );
		$loader->add_filter( 'posts_clauses', $this, 'taxonomy_clauses', 10, 2 );
		$loader->add_filter( 'request', $this, 'column_orderby' );
		$loader->add_filter( 'months_dropdown_results', $this, 'remove_dates' );
	    $loader->add_action( 'restrict_manage_posts', $this, 'filter_lists' );
		$loader->add_action( $this->taxonomies['party']['name'] . '_add_form_fields', $this, 'add_party_fields', 10, 2 );
		$loader->add_action( $this->taxonomies['party']['name'] . '_edit_form_fields', $this, 'edit_party_fields', 10, 2 );
		$loader->add_action( 'edited_' . $this->taxonomies['party']['name'], $this, 'save_party_custom_meta', 10, 2 );
		$loader->add_action( 'create' . $this->taxonomies['party']['name'], $this, 'save_party_custom_meta', 10, 2 );
		$loader->add_action( 'admin_enqueue_scripts', $this, 'setup_admin_scripts' );
		
		//require plugin_dir_path( __FILE__ ) . 'debug.php';
	}
	
	function init( $loader )
	{
		$loader->add_action( 'init', $this, 'initialize' );
		$loader->add_filter( 'enter_title_here', $this, 'update_title' );
		$loader->add_filter( 'template_include', $this, 'include_template_function', 1 );
		$loader->add_filter( 'wp_enqueue_scripts', $this, 'setup_public_scripts' );
	}	
 }