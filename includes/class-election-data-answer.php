<?php

/**
 * The file that defines the questionnaire custom post type and taxonomies.
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
$ed_post_types['answer'] = 'ed_answers';
global $ed_taxonomies;
$ed_taxonomies['answer_question'] = "{$ed_post_types['answer']}_question";
$ed_taxonomies['answer_candidate'] = "{$ed_post_types['answer']}_candidate";
$ed_taxonomies['answer_party'] = "{$ed_post_types['answer']}_party";


/**
 * Sets up and handles the questionnaire custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */
class Election_Data_Answer {
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
	 * Keeps track of emails that have been sent.
	 *
	 * @var integer
	 * @access private
	 * @since 1.0
	 *
	 */
	private $emails_sent = 0;

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
		
		$this->post_type = $ed_post_types['answer'];
		$this->taxonomies = array(
			'question' => $ed_taxonomies['answer_question'],
			'candidate' => $ed_taxonomies['answer_candidate'],
			'party' => $ed_taxonomies['answer_party'],
		);
		$args = array(
			'custom_post_args' => array(
				'labels' => array(
					'name' => __( 'Answers' ),
					'singular_name' => __( 'Answer' ),
					'add_new_item' => __( 'Add New Answer' ),
					'edit_item' => __( 'Edit Answer' ),
					'new_item' => __( 'New Answer' ),
					'view_item' => __( 'View Answer' ),
					'search_items' => __( 'Search Answers' ),
					'not_found' => __( 'No Answers found' ),
					'not_found_in_trash', __( 'No Answers found in Trash' ),
				),
				'description' => __( 'Candidate answers to the questionnaire.' ),
				'public' => true,
				'menu_position' => 5,
				//'menu_icon' => plugins_url( 'images/candidate.png', dirname( __FILE__ ) ), //TODO: Create a candidate image,
				'supports' => array( 'title', 'editor', 'revisions' ),
				'taxonomies' => array( '' ),
				'has_archive' => true,
				'query_var' => false,
				'rewrite' => array( 'slug' => __( 'answers' ), 'with_front' => false ),
				'capability_type' => 'post',
				'capabilities' => array(
					'create_posts' => false,
				),
				'map_meta_cap' => true,
			),
			'hidden_admin_columns' => array( 'date' ),
			'hidden_admin_fields' => array( 'date' ),
			'hidden_admin_filters' => array( 'date' ),
			'taxonomy_filters' => array( $this->taxonomies['question'], $this->taxonomies['candidate'], $this->taxonomies['party'] ),
			'sortable_taxonomies' => array( $this->taxonomies['question'], $this->taxonomies['candidate'], $this->taxonomies['party'] ),
			'taxonomy_args' => array(
				$this->taxonomies['question'] => array(
					'labels' => array(
						'name' => _x( 'Questions', 'taxonomy general name' ),
						'singular_name' => _x( 'Question', 'taxonomy general name' ),
						'all_items' => __( 'All Questions' ),
						'edit_item' => __( 'Edit Question' ),
						'view_item' => __( 'View Questions' ),
						'update_item' => __( 'Update Question' ),
						'add_new_item' => __( 'Add New Question' ),
						'new_item_name' => __( 'New Question Name' ),
						'search_items' => __( 'Search Questions' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => true,
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'hierarchical' => true,
					'query_var' => false,
					'rewrite' => false,
				),
				$this->taxonomies['candidate'] => array(
					'labels' => array(
						'name' => _x( 'Candidates', 'taxonomy general name' ),
						'singular_name' => _x( 'Candidate', 'taxonomy general name' ),
						'all_items' => __( 'All Candidates' ),
						'edit_item' => __( 'Edit Candidate' ),
						'view_item' => __( 'View Candidate' ),
						'update_item' => __( 'Update Candidate' ),
						'add_new_item' => __( 'Add New Candidate' ),
						'new_item_name' => __( 'New Candidate Name' ),
						'search_items' => __( 'Search Candidates' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => true,
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'hierarchical' => true,
					'query_var' => false,
					'rewrite' => array( 'slug' => 'candidate_qanda', 'with_front' => false ),
				),
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
					'query_var' => false,
					'rewrite' => array( 'slug' => 'party_qanda', 'with_front' => false ),
				),
			),
			'taxonomy_meta' => array(
				'candidate' => array(
					'taxonomy' => $this->taxonomies['candidate'],
					'fields' => array(
						array(
							'type' => 'hidden',
							'id' => 'candidate_id',
							'std' => '',
							'label' => __( 'Candidate Id' ),
							'desc' => __( 'The post id for the candidate.' ),
							'imported' => false,
						),
					),
				),
				'party' => array(
					'taxonomy' => $this->taxonomies['party'],
					'fields' => array(
						array(
							'type' => 'hidden',
							'id' => 'candidate_party_term_id',
							'std' => '',
							'label' => __( 'Party Id' ),
							'desc' => __( 'The taxonomy id for the party.' ),
							'imported' => false,
						),
					),
				),
				'question' => array(
					'taxonomy' => $this->taxonomies['question'],
					'fields' => array(
						array(
							'type' => 'checkbox',
							'id' => 'party',
							'std' => false,
							'label' => __( 'Party Question' ),
							'desc' => __( 'Indicates that the question is targeted towards the party as opposed to a candidate.' ),
							'imported' => true,
						),
						array(
							'type' => 'wysiwyg',
							'id' => 'question',
							'std' => '',
							'label' => __( 'The Question' ),
							'desc' => __( "The question for the candidate or for the party. The following substitutions will occur for both candidate and party questions:<br><list><li>*party* → The name of the candidate's party</li><li>*party_alt* → The alternate name of the candidate's party</li></list><br>Additionally, the following substitution will occur for candidate questions:<br><list><li>*candidate* → The name of the candidate</li></list>" ),
							'imported' => true,
						),
					),
				),
			),
		);
		
		$this->custom_post = new ED_Custom_Post_Type( $this->post_type, $args, $define_hooks );

		if ( $define_hooks ) {
			$this->define_hooks();
		}
	}
	
	/**
	 * Allows the 'token' query var to be used to allow editing of the questionnaire answers.
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function add_query_vars_filter( $vars ) {
		$vars[] = 'token';
		return $vars;
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
	 * Updates and returns the candidate taxonomy for answers. If a term doesn't exists, it is created.
	 * Terms for candidates that no longer exist are removed.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function get_candidate_taxonomy_terms() {
		global $ed_post_types, $ed_taxonomies;
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => 0,
		);
		$existing_terms = get_terms( $this->taxonomies['candidate'], $args );
		
		$args = array(
			'post_type' => $ed_post_types['candidate'],
			'nopaging' => true,
			'post_status' => 'publish',
		);
		$query = new WP_Query( $args );
		
		$candidate_terms = array();
		
		while ( $query->have_posts() ) {
			$query->the_post();
			$post = $query->post;
			$constituencies = wp_get_object_terms( $post->ID, $ed_taxonomies['candidate_constituency'], 'names' );
			$parties = wp_get_object_terms( $post->ID, $ed_taxonomies['candidate_party'], 'names' );
			$constituency = isset( $constituencies[0] ) ? " ({$constituencies[0]->name})": '';
			$party = isset( $parties[0] ) ? " {$parties[0]->name}" : '';
			$name = get_the_title( $post ) . "{$constituency}{$party}";
			$candidate_id = (int) get_post_meta( $post->ID, 'qanda_candidate_id', true );
			if ( empty( $candidate_id ) || !isset( $existing_terms[$candidate_id] ) ) {
				$term = wp_insert_term( $name, $this->taxonomies['candidate'] );
				
				update_tax_meta( $term['term_id'], 'candidate_id', $post->ID );
				update_post_meta( $post->ID, 'qanda_candidate_id', $term['term_id'] );
				$candidate_terms[$term['term_id']] = $name;
			} else {
				if ( $name != $existing_terms[$candidate_id] ) {
					wp_update_term( $candidate_id, $this->taxonomies['candidate'], array( 'name' => $name ) );
				}
				$candidate_terms[$candidate_id] = $name;
				unset ( $existing_terms[$candidate_id] );
			}
		}

		foreach ( $existing_terms as $id => $name )
		{
			wp_delete_term( $id, $this->taxonomies['candidate'] );
		}
		
		return $candidate_terms;
	}
	
	/**
	 * Updates and returns the party taxonomy for answers. If a term doesn't exists, it is created.
	 * Terms for parties that no longer exist are removed.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function get_party_taxonomy_terms() {
		global $ed_taxonomies;
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => 0,
		);
		
		$existing_terms = get_terms( $this->taxonomies['party'], $args );
		$candidate_party_terms = get_terms( $ed_taxonomies['candidate_party'], $args );

		$party_terms = array();

		foreach ( $candidate_party_terms as $party_id => $party_name ) {
			$qanda_party_id = (int) get_tax_meta( $party_id, 'qanda_party_id' );
			if ( empty( $qanda_party_id ) ) {
				$term = wp_insert_term( $party_name, $this->taxonomies['party'] );
				update_tax_meta( $term['term_id'], 'candidate_party_term_id', $party_id );
				update_tax_meta( $party_id, 'qanda_party_id', $term['term_id'] );
				$party_terms[$term['term_id']] = $party_name;
			} else {
				if ( $party_name != $existing_terms[$qanda_party_id] ) {
					wp_update_term( $qanda_party_id, $this->taxonomies['party'], array( 'name' => $party_name ) );
				}
				$party_terms[$qanda_party_id] = $party_name;
				unset ( $existing_terms[$qanda_party_id] );
			}
		}
		
		foreach ( $existing_terms as $id => $name )
		{
			wp_delete_term( $id, $this->taxonomies['party'] );
		}
		
		return $party_terms;
	}
	
	/**
	 * Ajax call to create answer posts.
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function ajax_create_answers()
	{
		$this->create_answers();
		wp_die();
	}
	
	/**
	 * Ensures that an answer post has been created for each question for each candidate/party.
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function create_answers() {
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => 0,
		);
		$questions = get_terms( $this->taxonomies['question'], $args );
		$candidates = $this->get_candidate_taxonomy_terms();
		$parties = $this->get_party_taxonomy_terms();
		
		foreach ( $questions as $question_id => $question_name ) {
			$is_party = get_tax_meta( $question_id, 'party' );
			$term_ids = $is_party ? $parties : $candidates;
			$taxonomy = $this->taxonomies[$is_party ? 'party' : 'candidate'];
			foreach ( $term_ids as $term_id => $term_name ) {
				$args = array(
					'post_type' => $this->post_type,
					'post_status' => array( 'publish', 'trash' ),
					'posts_per_page' => 1,
					'tax_query' => array(
						'relation' => 'AND',
						array( 
							'taxonomy' => $taxonomy,
							'field' => 'term_id',
							'terms' => $term_id,
						),
						array(
							'taxonomy' => $this->taxonomies['question'],
							'field' => 'term_id',
							'terms' => $question_id,
						),
					),
				);
				$query = new WP_Query( $args );
				if ( 0 == $query->found_posts ) {
					$answer = array(
						'post_title' => "$question_name: $term_name",
						'post_status' => 'publish',
						'post_type' => $this->post_type,
					);
					$answer_id = wp_insert_post( $answer );
					wp_set_object_terms( $answer_id, $term_id, $taxonomy, true );
					wp_set_object_terms( $answer_id, $question_id, $this->taxonomies['question'], true );
				}
			}
		}
	}
	
	/**
	 * Defines the action and filter hooks used by the class.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function define_hooks()
	{
		add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
		add_action( 'wp_ajax_election_data_create_answers', array( $this, 'ajax_create_answers' ) );
		add_action( 'wp_ajax_election_data_send_email', array( $this, 'ajax_send_all_email' ) );
        add_action( 'wp_ajax_election_data_send_candidate_email', array( $this, 'ajax_send_candidate_email' ) );
        add_action( 'wp_ajax_election_data_send_party_email', array( $this, 'ajax_send_party_email' ) );
		add_action( 'wp_ajax_election_data_reset_party_questionnaire', array( $this, 'ajax_reset_party_questionnaire' ) );
		add_action( 'wp_ajax_election_data_reset_candidate_questionnaire', array( $this, 'ajax_reset_candidate_questionnaire' ) );
        add_action( 'wp_ajax_election_data_reset_questionnaire_unanswered', array( $this, 'ajax_reset_questionnaire_unanswered' ) );
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
	
	
	public function export_answer_csv( $csv ) {
		$post_fields = array(
			'post_title' => 'name',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( 
			$this->taxonomies['question'] => 'question',
			$this->taxonomies['candidate'] => 'candidate',
			$this->taxonomies['party'] => 'party',
		);
		
		Post_Export::export_post_csv( $csv, $this->post_type, $this->custom_post->post_meta, $post_fields, null, $taxonomies );
	}
	
	protected function export_question_csv( $csv ) {
		$question_fields = array( 'name', 'slug', 'description' );
		
		Post_Export::export_taxonomy_csv( $csv, 'question', $this->taxonomies['question'], $question_fields, $this->custom_post->taxonomy_meta['question'] );
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
	
	public function get_term_by_slug_or_name( $label, $taxonomy ) {
		$term = get_term_by( 'slug', $label, $taxonomy );
		if ( empty( $term ) ) {
			$term = get_term_by( 'name', $label, $taxonomy );
		}
		return $term;
	}

	public function import_answer_csv( $csv, $mode ){
		global $ed_taxonomies;
		global $ed_post_types;
		$this->get_candidate_taxonomy_terms();
		$this->get_party_taxonomy_terms();

		$required_fields = array( 'candidate', 'party', 'constituency', 'question', 'answer' );
		$headings = fgetcsv( $csv );
		$found = true;
		foreach ( $required_fields as $field ) {
			$found &= in_array( $field, $headings );
		}
		if ( !$found ) {
			return false;
		}

		$current_posts = Post_import::get_current_posts( $this->post_type );

		while ( ( $data = Post_import::read_csv_line( $csv, $headings ) ) !== false ) {
			$question = $this->get_term_by_slug_or_name( $data['question'], $this->taxonomies['question'] );
			$is_party = get_tax_meta( $question->term_id, 'party' );
			$terms = array( $this->taxonomies['question'] => $question );
			$candidate_party_term = $this->get_term_by_slug_or_name( $data['party'], $ed_taxonomies['candidate_party'] );
			if ( $is_party ) {
				$title = $candidate_party_term->name;
				$terms[$this->taxonomies['party']] = get_term( get_tax_meta( $candidate_party_term->term_id, 'qanda_party_id' ), $this->taxonomies['party'] );
			} else {
				$taxonomy_query = array( 'relation' => 'AND' );
				if ( ! empty( $candidate_party_term ) ) {
					$taxonomy_query[] = array(
						'taxonomy' => $ed_taxonomies['candidate_party'],
						'field' => 'term_id',
						'terms' => $candidate_party_term->term_id,
					);
				}
				$candidate_constituency_term = $this->get_term_by_slug_or_name( $data['constituency'], $ed_taxonomies['candidate_constituency'] );
				if ( ! empty( $candidate_constituency_term ) ) {
					$taxonomy_query[] = array(
						'taxonomy' => $ed_taxonomies['candidate_constituency'],
						'field' => 'term_id',
						'terms' => $candidate_constituency_term->term_id,
					);
				}
				$query_args = array(
					'post' => $ed_post_types['candidate'],
					'nopaging' => true,
				);
				if ( 2 >= count( $taxonomy_query ) ) {
					unset( $taxonomy_query['relation'] );
				}
				if ( $taxonomy_query ) {
					$query_args['tax_query'] = $taxonomy_query;
				}
				$query = new WP_Query( $query_args );
				if ( 1 != $query->found_posts )
				{
					return false;
				}
				$query->the_post();
				$candidate=get_term( get_post_meta( $query->post->ID, 'qanda_candidate_id', true ), $this->taxonomies['candidate'] );
				$terms[$this->taxonomies['candidate']] = $candidate;
				$title = "{$candidate->name}";
			}
			
			$title = "{$question->name}: $title";
			$post_fields = array(
				'post_title' => 'title',
				'post_content' => 'answer',
			);
			$post_data = array( 
				'title' => $title,
				'answer' => $data['answer'],
			);
			$post = Post_import::get_or_create_post( $this->post_type, $current_posts, $post_data, $post_fields, $mode );
			if ( ! $post ) {
				continue;
			}
			
			foreach ( $terms as $taxonomy_name => $term ) {
				$existing_terms = wp_get_post_terms( $post->ID, $taxonomy_name, array( 'fields' => 'ids' ) );
				if ( 'overwite' == $mode || !$existing_terms ) {
					wp_set_object_terms( $post->ID, $term->term_id, $taxonomy_name );
				}
			}
		}
		//return Post_import::import_post_csv( $csv, $mode, $this->post_type, $this->custom_post->post_meta, $post_fields, null, $taxonomies, array(), $required_fields );
	}

	protected function import_question_csv( $csv, $mode ) {
		$question_fields = array( 'name', 'slug', );
		$required_fields = array( 'name', 'party', 'question' );
		return Post_Import::import_taxonomy_csv( $csv, $mode, 'question', $this->taxonomies['question'], $question_fields, $this->custom_post->taxonomy_meta['question'], null, array(), $required_fields );
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
	
	public function send_email( $message_contents ){
		require_once 'Html2Text.php';
		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->Host = Election_Data_Option::get_option( 'smtp-server' );
		$mail->Port = Election_Data_Option::get_option( 'smtp-port' );
		$smtp_user = Election_Data_Option::get_option( 'smtp-user' );
		if ( $smtp_user ) {
			$mail->SMTPAuth = true;
			$mail->Username = $smtp_user;
			$mail->Password = Election_Data_Option::get_option( 'smtp-password' );
		} else {
			$mail->SMTPAuth = false;
		}
		$mail->SMTPSecure = Election_Data_Option::get_option( 'smtp-encryption' );
		if ( !empty( Election_Data_Option::get_option( 'reply-to' ) ) ) {
			$mail->AddReplyTo( Election_Data_Option::get_option( 'reply-to' ) );
		}
		$mail->SetFrom(Election_Data_Option::get_option( 'from-email-address' ), Election_Data_Option::get_option( 'from-email-name' ) );
		$mail->Subject = $message_contents['subject'];
		$mail->Body = $message_contents['body'];
		$html = new \Html2Text\Html2Text( $message_contents['body'] );
		$mail->AltBody = $html->getText();
		$mail->isHTML = true;
		$mail->AddAddress( $message_contents['recipient'], $message_contents['recipient-name'] );
		$mail->Send();
		$this->emails_sent++;
	}
	
	public function get_pattern_replacements( $type, $term ) {
		global $ed_taxonomies;
		
		$replacements = array();
		switch( $type ) {
			case 'party':
				$party_id = get_tax_meta( $term->term_id, 'candidate_party_term_id' );
				$party = get_term( $party_id, $ed_taxonomies['candidate_party'] );
				$token = get_tax_meta( $party_id, 'qanda_token' );
                if ( empty( $token ) ) {
                   $token = Election_Data_Candidate::qanda_random_token();
                   update_tax_meta( $party_id, 'qanda_token', $token );
                }
				break;
			case 'candidate':
				$candidate_id = get_tax_meta( $term->term_id, 'candidate_id' );
				$candidate = get_post( $candidate_id );
				$replacements['candidate'] = get_the_title( $candidate );
				$parties = get_the_terms( $candidate, $ed_taxonomies['candidate_party'] );
				$party = $parties[0];
				$token = get_post_meta( $candidate_id, 'qanda_token', true );
                if ( empty( $token ) ) {
                   $token = Election_Data_Candidate::qanda_random_token();
                   update_post_meta( $candidate_id, 'qanda_token', $token );
                }
				break;
		}

		$url = get_term_link( $term, $this->taxonomies[$type] );
		$replacements['question_url'] = "<a href='$url?token=$token'>$url?token=$token</a>";
		$replacements['party'] = $party->name;
		$replacements['party_alt'] = $party->description;
		$replacements['question'] = implode( get_qanda_questions( $type, $term ) );
		$pattern = array();
		$replace = array();
		foreach ( $replacements as $old => $new ) {
			$pattern[] = "/\*$old\*/";
			$replace[] = $new;
		}
		
		return array( 'pattern' => $pattern, 'replacement' => $replace );
	}
		
	public function email_party_questions() {
		global $ed_taxonomies;
		$email_limit = intval( Election_Data_Option::get_option( 'email-limit' ) );
		$email_delay = intval( Election_Data_Option::get_option( 'email-delay' ) ) * 1000;

		$args = array(
			'fields' => 'ids',
		);
		$answer_party_ids = get_terms( $this->taxonomies['party'], $args );
		foreach ( $answer_party_ids as $answer_party_id ) {
			if ( ( $email_limit > 0 ) && ( $this->emails_sent >= $email_limit ) ) {
				break;
			}
			$party_id = get_tax_meta( $answer_party_id, 'candidate_party_term_id' );
			if ( get_tax_meta( $party_id, 'qanda_sent' ) ) {
				continue;
			}
			
			$party = get_term( $party_id, $ed_taxonomies['candidate_party'] );
			$replacements = $this->get_pattern_replacements( 'party', get_term( $answer_party_id, $this->taxonomies['party'] ) );
			$pattern = $replacements['pattern'];
			$replacement = $replacements['replacement'];
			$message = array(
				'subject' => preg_replace( $pattern, $replacement, Election_Data_Option::get_option( 'subject-party' ) ),
				'recipient' => get_tax_meta( $party_id, 'email' ),
				'recipient-name' => '',
				'body' => '<html><head></head><body>' . preg_replace( $pattern, $replacement, Election_Data_Option::get_option( 'email-party' ) ) . '</body></html>',
			);
			$this->send_email( $message );
			update_tax_meta( $party_id, 'qanda_sent', true );
			if ( $email_delay > 0 ) {
				usleep( $email_delay );
			}
		}
	}
	
	public function email_candidate_questions() {
		$email_limit = intval( Election_Data_Option::get_option( 'email-limit' ) );
		$email_delay = intval( Election_Data_Option::get_option( 'email-delay' ) ) * 1000;

		$args = array(
			'fields' => 'ids',
		);
		$answer_candidate_ids = get_terms( $this->taxonomies['candidate'], $args );
		foreach ( $answer_candidate_ids as $answer_candidate_id ) {
			if ( ( $email_limit > 0 ) && ( $this->emails_sent >= $email_limit ) ) {
				break;
			}
			$candidate_id = get_tax_meta( $answer_candidate_id, 'candidate_id' );

			$email = get_post_meta( $candidate_id, 'contact_email', true );
			if (empty( $email )) {
				$email = get_post_meta( $candidate_id, 'email', true );
			}
			if (empty( $email )) {
				continue;
			}

			if ( get_post_meta( $candidate_id, 'qanda_sent', true ) ) {
				continue;
			}
			
			$candidate = get_post( $candidate_id );
			$replacements = $this->get_pattern_replacements( 'candidate', get_term( $answer_candidate_id, $this->taxonomies['candidate'] ) );
			$pattern = $replacements['pattern'];
			$replacement = $replacements['replacement'];
			$message = array(
				'subject' => preg_replace( $pattern, $replacement, Election_Data_Option::get_option( 'subject-candidate' ) ),
				'recipient' => $email,
				'recipient-name' => get_the_title( $candidate ),
				'body' => '<html><head></head><body>' . preg_replace( $pattern, $replacement, Election_Data_Option::get_option( 'email-candidate' ) ) . '</body></html>',
			);
			$this->send_email( $message );
			update_post_meta( $candidate_id, 'qanda_sent', true );
			if ( $email_delay > 0 ) {
				usleep( $email_delay );
			}
		}
	}
	
	public function ajax_send_all_email() {
		$this->email_candidate_questions();
		$this->email_party_questions();
		wp_die();
	}
    
    public function ajax_send_candidate_email() {
        $this->email_candidate_questions();
        wp_die();
    }
    
    public function ajax_send_party_email() {
        $this->email_party_questions();
        wp_die();
    }
    
	public function reset_party_questionnaire( $only_unanswered = false ) {
		global $ed_taxonomies;
		global $ed_post_types;
		$args = array(
			'hide_empty' => false,
			'fields' => 'ids',
		);
		$term_ids = get_terms( $ed_taxonomies['candidate_party'], $args );
        if ( $only_unanswered ) { 
            foreach ( $term_ids as $term_id ) {
                $answers = get_qanda_answers( 'party', $term_id );
                if ( count ( $answers ) == 0 ) {
                    update_tax_meta( $term_id, 'qanda_sent', false );
                }
            }
        } else {
            foreach ( $term_ids as $term_id ) {
                update_tax_meta( $term_id, 'qanda_sent', false );
            }
        }
	}

	public function reset_candidate_questionnaire( $only_unanswered = false ) {
		global $ed_taxonomies;
		global $ed_post_types;
		$args = array(
			'post_type' => $ed_post_types['candidate'],
			'nopaging' => true,
		);
		$query = new WP_Query( $args );
        if ( $only_unanswered ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $answers = get_qanda_answers( 'candidate', $query->post->ID );
                if ( count( $answers ) == 0 ) {
                    update_post_meta( $query->post->ID, 'qanda_sent', false );
                }
            }
        } else {
            while ( $query->have_posts() ) {
                $query->the_post();
                update_post_meta( $query->post->ID, 'qanda_sent', false );
            }
        }
	}

	
	public function ajax_reset_party_questionnaire() {
		$this->reset_party_questionnaire();
		wp_die();
	}
	
	public function ajax_reset_candidate_questionnaire() {
		$this->reset_candidate_questionnaire();
		wp_die();
	}
    
    public function ajax_reset_questionnaire_unanswered() {
        $this->reset_party_questionnaire( true );
        $this->reset_candidate_questionnaire( true );
        wp_die();
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
