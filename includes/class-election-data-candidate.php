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
 
require_once plugin_dir_path( __FILE__ ) . 'class-custom-post.php';
require_once plugin_dir_path( __FILE__ ) . 'class-post-import.php';
require_once plugin_dir_path( __FILE__ ) . 'class-post-export.php';

/**
 * Sets up and handles the candidate custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */
class Election_Data_Candidate {
	/**
	 * The ED_Custom_Post_Type object representing the candidates custom post type, and the party and constituency taxonomies.
	 *
	 * @var object
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $custom_post;
	
	/**
	 * The definition of the taxonomy names.
	 *
	 * @var array
	 * @access public
	 * @since 1.0
	 *
	 */
	public $taxonomies;
	
	/**
	 * Stores the name of the custom post type.
	 *
	 * @var string
	 * @access public
	 * @since 1.0
	 *
	 */
	public $post_type;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 * @param boolean $define_hooks
	 *
	 */
	public function __construct( $define_hooks = true ) {
		$this->post_type = 'ed_candidates';
		$this->taxonomies = array(
			'party' => "{$this->post_type}_party",
			'constituency' => "{$this->post_type}_constituency",
		);
		$args = array(
			'custom_post_args' => array(
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
				
			),
			'admin_column_names' => array( 'title' => __( 'Candidate Name' ) ),
			'admin_field_names' => array( 'title' => __( 'Name' ), 'enter_title_here' =>  __( 'Enter Candidate Name' ) ),
			'hidden_admin_columns' => array( 'date' ),
			'hidden_admin_fields' => array( 'password', 'date' ),
			'hidden_admin_filters' => array( 'date' ),
			'taxonomy_filters' => array( $this->taxonomies['party'], $this->taxonomies['constituency'] ),
			'sortable_taxonomies' => array( $this->taxonomies['party'], $this->taxonomies['constituency'] ),
			'custom_post_meta' => array(
				'meta_box' => array(
					'id' => 'election_data_candidate_meta_box',
					'title' => __( 'Candidate Details' ),
					'post_type' => $this->post_type,
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
						'imported' => true,
					),
					'website' => array(
						'label' => __( 'Website' ),
						'id' => 'website',
						'desc' => __( "Enter the URL to the candidate's website." ),
						'type' => 'url',
						'std' => '',
						'imported' => true,
					),
					'email' => array(
						'label' => __( 'Email Address' ),
						'id' => 'email',
						'desc' => __( "Enter the candidate's email address." ),
						'type' => 'email',
						'std' => '',
						'imported' => true,
					),
					'facebook' => array(
						'label' => __( 'Facbook Page' ),
						'id' => 'facebook',
						'desc' => __( "Enter the URL to the canidate's facebook page." ),
						'type' => 'url',
						'std' => '',
						'imported' => true,
					),
					'youtube' => array(
						'label' => __( 'Youtube Channel or Video' ),
						'id' => 'youtube',
						'desc' => __( "Enter the URL to the candidate's youtube channel or video" ),
						'type' => 'url',
						'std' => '',
						'imported' => true,
					),
					'twitter' => array(
						'label' => __( 'Twitter Feed' ),
						'id' => 'twitter',
						'desc' => __( "Enter the URL to the candidate's twitter feed." ),
						'type' => 'url',
						'std' => '',
						'imported' => true,
					),
					'incumbent_year' => array(
						'label' => __( 'Year Previously Elected' ),
						'id' => 'incumbent_year',
						'desc' => __( 'If the candidate is the incumbent, enter the year he/she was elected.' ),
						'type' => 'text',
						'std' => '',
						'imported' => true,
					),
					'party_leader' => array(
						'label' => __( 'Party Leader' ),
						'id' => 'party_leader',
						'desc' => __( 'Indicate if the candidate is the party leader.' ),
						'type' => 'checkbox',
						'std' => '',
						'imported' => true,
					),
					'reference' => array(
						'id' => 'reference',
						'type' => 'hidden',
						'std' => '',
						'imported' => false,
					),
				),
				'admin_columns' => array( 'phone', 'email', 'website', 'party_leader' ),
			),
			'taxonomy_args' => array(
				$this->taxonomies['party'] => array(
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
				$this->taxonomies['constituency'] => array(
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
			'taxonomy_meta' => array(
				'party' => array(
					'taxonomy' => $this->taxonomies['party'],
					'fields' => array(
						array(
							'type' => 'color',
							'id' => 'colour',
							'std' => '#000000',
							'desc' => __( 'Select a colour to identify the party.' ),
							'label' => __( 'Colour' ),
							'imported' => true,
						),
						array(
							'type' => 'image',
							'id' => 'logo',
							'desc' => __( 'Select a logo for the party.' ),
							'label' => __( 'Logo' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'url',
							'id' => 'website',
							'desc' => __( "Enter the URL to the party's web site." ),
							'label' => __( 'Web Site URL' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'text',
							'id' => 'phone',
							'desc' => __( "Enter the party's phone number." ),
							'label' => __( 'Phone Number' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'text',
							'id' => 'address',
							'desc' => __( "Enter the party's address." ),
							'label' => __( 'Address' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'email',
							'id' => 'email',
							'desc' => __( "Enter the party's email address." ),
							'label' => __( 'Email Address' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'url',
							'id' => 'facebook',
							'desc' => __( "Enter the URL to the party's facebook page." ),
							'label' => __( 'Facbook Page' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'url',
							'id' => 'youtube',
							'desc' => __( "Enter the URL to the party's youtube channel or video" ),
							'label' => __( 'Youtube Channel or Video' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'url',
							'id' => 'twitter',
							'desc' => __( "Enter the URL to the party's twitter feed." ),
							'label' => __( 'Twitter Feed' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'hidden',
							'id' => 'reference',
							'std' => '',
							'imported' => false,
						),
					),
				),
				'constituency' => array(
					'taxonomy' => $this->taxonomies['constituency'],
					'fields' => array(
						array(
							'type' => 'image',
							'id' => 'map',
							'desc' => __( "A map of the child constituencies." ),
							'label' => __( "Constituency Map" ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'text',
							'id' => 'coordinates',
							'desc' => __( 'HTML map coordinates for constituency location on parent constituencies map. You can generate these coordinates by using an online map tool available <a href="https://www.google.com/search?q=html+map+generator+online">here</a>' ),
							'label' => __( 'Coordinates' ),
							'std' => '',
							'imported' => true,
						),
						array(
							'type' => 'wysiwyg',
							'id' => 'details',
							'desc' => __( 'A description of the constituency. ' ),
							'label' => __( 'Details' ),
							'std' => '',
							'imported' => true,
						),
					),
				),
			),
		);
		
		$this->custom_post = new ED_Custom_Post_Type( $this->post_type, $args, $define_hooks );

		if ( $define_hooks ) {
			add_filter( 'pre_get_posts', array( $this, 'set_main_query_parameters' ) );
		}
		add_image_size( 'candidate', 9999, 100, false );
		add_image_size( 'map_thumb', 100, 9999, false );
		add_image_size( 'map', 598, 9999, false );
		add_image_size( 'party', 175, 175, false );
	}
	
	/**
	 * Initializes the custom_post and taxonomies (Used during activation)
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function initialize() {
		$this->custom_post->initialize();
	}
	
	/**
	 * Sets up the main query for displaying candidates by constituency, or by party'
	 * 
	 * @access public
	 * @since 1.0
	 *
	 */
	public function set_main_query_parameters( $query ) {
		if( is_admin() || !$query->is_main_query() ) {
			return;
		}
		
		if ( is_tax( $this->taxonomies['party'] ) ) {
			$query->set( 'orderby', "taxonomy-{$this->taxonomies['constituency']}" );
			$query->set( 'order', 'ASC' );
			$query->set( 'nopaging', 'true' );
		} elseif ( is_tax( $this->taxonomies['constituency'] ) ) {
			$query->set( 'orderby', 'rand' );
			$query->set( 'nopaging', 'true' );
		} elseif ( is_post_type_archive( $this->post_type ) ) {
			$query->set( 'orderby', 'rand' );
			$query->set( 'nopaging', 'true' );
		}
	}
	
	/**
	 * Exports the candidates, parties and constituencies to a single xml file.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $xml
	 *
	 */
	public function export_xml( $xml ) {
	}
	
	/**
	 * Exports the candidates to a csv file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_candidate_csv( $csv ) {
		$post_fields = array(
			'post_title' => 'name',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( 
			$this->taxonomies['party'] => 'party',
			$this->taxonomies['constituency'] => 'constituency'
		);
		
		Post_Export::export_post_csv( $csv, $this->post_type, $this->custom_post->post_meta, $post_fields, 'photo', $taxonomies );
	}
	
	/**
	 * Exports the parties to a csv file
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_party_csv( $csv ) {
		$party_fields = array( 'name', 'slug', 'description' );
		
		Post_Export::export_taxonomy_csv( $csv, 'party', $this->taxonomies['party'], $party_fields, $this->custom_post->taxonomy_meta['party'] );
	}
	
	/**
	 * Exports the constituencies to a csv file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_constituency_csv( $csv ) {
		$constituency_fields = array( 'name', 'slug', 'parent' );
		
		Post_Export::export_taxonomy_csv( $csv, 'constituency', $this->taxonomies['constituency'], $constituency_fields, $this->custom_post->taxonomy_meta['constituency'], 0 );
	}
	
	/**
	 * Exports the candidates, parites or constituencies to a csv file
	 *
	 * @access public
	 * @since 1.0
	 * @param string $type
	 *
	 */
	public function export_csv ( $type ) {
		$file_name = tempnam( 'tmp', 'csv' );
		$file = fopen( $file_name, 'w' );
		call_user_func( array( $this, "export_{$type}_csv" ), $file );
		fclose( $file );
		return $file_name;
	}
	
	/**
	 * Imports the candidates from a csv file
	 *
	 * @access protected 
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_candidate_csv( $csv, $mode ) {
		$post_fields = array(
			'post_title' => 'name',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( 
			$this->taxonomies['party'] => 'party',
			$this->taxonomies['constituency'] => 'constituency'
		);
		
		return Post_import::import_post_csv( $csv, $mode, $this->post_type, $this->custom_post->post_meta, $post_fields, 'photo', $taxonomies );
	}
	
	/**
	 * Imports the parties from a CSV file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_party_csv( $csv, $mode ) {
		$party_fields = array( 'name', 'slug', 'description' );
		
		return Post_Import::import_taxonomy_csv( $csv, $mode, 'party', $this->taxonomies['party'], $party_fields, $this->custom_post->taxonomy_meta['party'] );
	}
	
	/**
	 * Imports the constituencies from a CSV file.
	 *
	 * @access protected 
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_constituency_csv( $csv, $mode ) {
		$constituency_fields = array( 'name', 'slug' );
		$parent_field = 'parent';
		
		return Post_Import::import_taxonomy_csv( $csv, $mode, 'constituency', $this->taxonomies['constituency'], $constituency_fields, $this->custom_post->taxonomy_meta['constituency'], $parent_field );
	}
	
	/**
	 * Imports the candidates, constituencies or parties from a CSV file.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $type
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	public function import_csv( $type, $csv, $mode ) {
		return call_user_func( array( $this, "import_{$type}_csv" ), $csv, $mode );
	}
	
	/**
	 * Erases all candidates, parties and constituencies from the database.
	 * @access public
	 * @since 1.0
	 *
	 */
	public function erase_data() {
		$this->custom_post->erase_data();
	}
}