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

global $ed_post_types;
$ed_post_types['candidate'] = 'ed_candidates';
global $ed_taxonomies;
$ed_taxonomies['candidate_party'] = "{$ed_post_types['candidate']}_party";
$ed_taxonomies['candidate_constituency'] = "{$ed_post_types['candidate']}_constituency";


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
		global $ed_post_types;
		global $ed_taxonomies;
		
		$this->post_type = $ed_post_types['candidate'];
		$this->taxonomies = array(
			'party' => $ed_taxonomies['candidate_party'],
			'constituency' => $ed_taxonomies['candidate_constituency'],
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
					'contact_email' => array(
						'label' => __( 'Private Contact Email' ),
						'id' => 'contact_email',
						'desc' => __( "A private email address to contact the candidate (not shown on the website)." ),
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
					'news_article_candidate_id' => array(
						'id' => 'news_article_candidate_id',
						'type' => 'hidden',
						'std' => '',
						'imported' => false,
					),
					'qanda_token' => array(
						'id' => 'qanda_token',
						'type' => 'text_with_load_value_button',
						'std_callback' => array($this, 'qanda_random_token'),
						'imported' => true,
						'desc' => __( 'The token required to edit the questionnaire.' ),
						'label' => __( 'Questionnaire Token' ),
						'button_label' => __( 'Generate Token' ),
						'ajax_callback' => 'ed_qanda_random_token',
					),
					'qanda_sent' => array(
						'id' => 'qanda_sent',
						'type' => 'checkbox',
						'std' => false,
						'desc' => __( 'Indicates that a questionnaire has been sent out. Uncheck to have the candidate included when the questionnaire is next sent out.' ),
						'label' => __( 'Quesitonnaire Sent' ),
						'imported' => true,
					),
					'qanda_candidate_id' => array(
						'id' => 'qanda_candidate_id',
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
							'id' => 'qanda_token',
							'type' => 'text_with_load_value_button',
							'std_callback' => array( $this, 'qanda_random_token' ),
							'imported' => true,
							'desc' => __( 'The token required to edit the questionnaire.' ),
							'label' => __( 'Questionnaire Token' ),
							'button_label' => __( 'Generate Token' ),
							'ajax_callback' => 'ed_qanda_random_token',
							'std' => '',
						),
						array(
							'id' => 'qanda_sent',
							'type' => 'checkbox',
							'std' => false,
							'desc' => __( 'Indicates that a questionnaire has been sent out. Uncheck to have the party included when the questionnaire is next sent out.' ),
							'label' => __( 'Quesitonnaire Sent' ),
							'imported' => true,
						),
						array(
							'type' => 'hidden',
							'id' => 'qanda_party_id',
							'std' => '',
							'imported' => false,
						),
					),
					'renamed' => array(
						'description' => 'Alternate Name',
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
					'hidden' => array( 'description' ),
				),
			),
		);
		
		$this->custom_post = new ED_Custom_Post_Type( $this->post_type, $args, $define_hooks );

		if ( $define_hooks ) {
			add_filter( 'pre_get_posts', array( $this, 'set_main_query_parameters' ) );
			add_action( 'wp_ajax_ed_qanda_random_token', array( $this, 'ajax_qanda_random_token' ) );
			add_action( "create_{$this->taxonomies['party']}", array( $this, 'create_party' ), 10, 2 );
			add_action( "create_{$this->taxonomies['constituency']}", array( $this, 'create_constituency' ), 10, 2 );
			add_action( "edited_{$this->taxonomies['constituency']}", array( $this, 'edited_constituency' ), 10, 2 );
		}
		add_image_size( 'candidate', 9999, 100, false );
			
		add_image_size( 'map_thumb', 100, 9999, false );
		add_image_size( 'map', 598, 9999, false );
		add_image_size( 'party', 175, 175, false );
	}
	
	public static function qanda_random_token() {
		return wp_generate_password( 30, false );
	}
	
	public function ajax_qanda_random_token() {
		echo $this->qanda_random_token();
		wp_die();
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
	
	public function create_party( $term_id, $tt_id) {
		$term = get_term( $term_id, $this->taxonomies['party'], 'ARRAY_A' );
		$this->create_menu_item( __( 'Party' ), $this->taxonomies['party'], $term );
	}
	
	public function create_constituency( $term_id, $tt_id ) {
		$term = get_term( $term_id, $this->taxonomies['constituency'], 'ARRAY_A' );
		if ( $term['parent'] == 0 ) {
			$this->create_menu_item( __( 'Constituency' ), $this->taxonomies['constituency'], $term );
		}
	}
	
	public function edited_constituency( $term_id, $tt_id ) {
		$term = get_term( $term_id, $this->taxonomies['constituency'], 'ARRAY_A' );
		$menu_item_id = $this->get_menu_item( $this->taxonomies['constituency'], $term );
		if ( $menu_item_id and $term['parent'] != 0 ) {
			wp_delete_post( $menu_item_id );
		} else if ( ! $menu_item_id and $term['parent'] == 0 ) {
			$this->create_menu_item( __( 'Constituency' ), $this->taxonomies['constituency'], $term );
		}
	}
	
	public function get_menu_item( $taxonomy, $term ) {
		$menu_name = __( 'Election Data Navigation Menu' );
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu );
			foreach ( $menu_items as $menu_item ) {
				if ( 'taxonomy' == $menu_item->type
					&& $taxonomy == $menu_item->object
					&& $term['term_id'] == $menu_item->object_id ) {
					return $menu_item->ID;
				}
			}
		}
		
		return 0;
	}
	
	public function create_menu_item( $parent_menu_item_name, $taxonomy, $term ) {
		$menu_name = __( 'Election Data Navigation Menu' );
		$menu = wp_get_nav_menu_object( $menu_name );
		if ( $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu );
			foreach ( $menu_items as $menu_item ) {
				if ( $parent_menu_item_name == $menu_item->title ) {
					$args = array(
						'menu-item-title' => $term['name'],
						'menu-item-parent-id' => $menu_item->ID, 
						'menu-item-status' => 'publish',
						'menu-item-object' => $taxonomy,
						'menu-item-object-id' => $term['term_id'],
						'menu-item-type' => 'taxonomy'
						);
					wp_update_nav_menu_item( $menu->term_id, 0, $args
					 );
					break;
				}
			}
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
		$required_fields = array( 'name', 'description' );
		return Post_Import::import_taxonomy_csv( $csv, $mode, 'party', $this->taxonomies['party'], $party_fields, $this->custom_post->taxonomy_meta['party'], null, array(), $required_fields );
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
