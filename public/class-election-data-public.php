<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Election_Data
 * @subpackage Election_Data/public
 * @author     Your Name <email@example.com>
 */
class Election_Data_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Election_Data_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Election_Data_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/election-data-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Election_Data_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Election_Data_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/election-data-public.js', array( 'jquery' ), $this->version, false );
	}
}

function get_constituency( $constituency, $get_extra_data = true ) {
	global $ed_taxonomies;
	$constituency = get_term( $constituency, $ed_taxonomies['candidate_constituency'] );
	$constituency_id = $constituency->term_id;
	$results = array(
		'id' => $constituency_id,
		'name' => $constituency->name,
		'url' => get_term_link( $constituency, $ed_taxonomies['candidate_constituency'] ),
	);
	if ( $get_extra_data ) {
		$results['details'] = get_tax_meta( $constituency_id, 'details' );
		$map_image = get_tax_meta( $constituency_id, 'map' );
		$results['map_id'] = $map_image ? $map_image : '';
		
		$child_terms = get_terms( $ed_taxonomies['candidate_constituency'], array( 'parent' =>$constituency_id, 'hide_empty' => false ) );
		$results['children'] = array();
		foreach ( $child_terms as $child )
		{
			$results['children'][$child->name] = array(
				'url' => get_term_link( $child, $ed_taxonomies['candidate_constituency'] ),
				'coordinates' => get_tax_meta( $child->term_id, 'coordinates' ),
			);
		}
	}
	
	return $results;
}

function get_constituency_from_candidate( $candidate_id ) {
	global $ed_taxonomies;
	$all_terms = get_the_terms( $candidate_id, $ed_taxonomies['candidate_constituency'] );
	if ( isset( $all_terms[0] ) ) {
		return get_constituency( $all_terms[0], false );
	} else {
		return  array(
			'id' => 0,
			'name' =>'',
			'url' => '',
		);
	}
}

function get_root_constituencies() {
	global $ed_taxonomies;
	$args = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => false,
		'fields' => 'ids',
		'parent' => 0,
	);
	
	$terms = get_terms( $ed_taxonomies['candidate_constituency'] , $args );
	return $terms;
}

function get_parties_random() {
	global $ed_taxonomies;
	$args = array(
		'orderby' => 'id',
		'order' => 'ASC',
		'hide_empty' => false,
		'fields' => 'ids',
	);
	
	$terms = get_terms( $ed_taxonomies['candidate_party'] , $args );
	shuffle( $terms );
	return $terms;
}

function get_all_parties() {
    global $ed_taxonomies;
    $args = array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
   );
   
    $terms = get_terms( $ed_taxonomies['candidate_party'], $args );
    $parties = array();
    foreach ( $terms as $term ) {
        $parties[] = get_party( $term );
    }
    
    return $parties;
}

function get_all_candidates() {
    global $ed_post_types;
    $query_args = array(
        'post_type' => $ed_post_types['candidate'],
        'nopaging' => true,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $candidates = array();
    $query = new WP_Query( $query_args );
    while ( $query->have_posts() ) {
        $query->the_post();
        
        $candidates[$query->post->ID] = get_candidate( $query->post->ID, true );
    }
    
    return $candidates;
}

function get_party( $party, $get_extra_data = true ) {
	global $ed_taxonomies;
	$party = get_term( $party, $ed_taxonomies['candidate_party'] );
	$party_id = $party->term_id;
	
	$results = array(
		'id' => $party_id,
		'name' => $party->name,
		'colour' => get_tax_meta( $party_id, 'colour' ),
		'url' => get_term_link( $party, $ed_taxonomies['candidate_party'] ),
		'long_title' => $party->description,
	);
		
	if ( $get_extra_data ) {
		$results['answers'] = get_qanda_answers( 'party', $party );
		$party_logo = get_tax_meta( $party_id, 'logo' );
		$results['logo_id'] = $party_logo ? $party_logo : Election_Data_Option::get_option( 'missing_party' );
		$results['website'] = get_tax_meta( $party_id, 'website' );
		$results['phone'] = get_tax_meta( $party_id, 'phone' );
		$results['address'] = get_tax_meta( $party_id, 'address' );
		$results['icon_data'] = array();
		$results['facebook'] = get_tax_meta( $party_id, 'facebook' );
		$results['youtube'] = get_tax_meta( $party_id, 'youtube' );
		$results['twitter'] = get_tax_meta( $party_id, 'twitter' );
		$results['email'] = get_tax_meta( $party_id, 'email' );
		$results['qanda'] = empty( $results['answers'] ) ? '' : "{$results['url']}#qanda";
		$results['qanda_token'] = get_tax_meta( $party_id, 'qanda_token' );
		foreach ( array('email', 'facebook', 'youtube', 'twitter', 'qanda' ) as $icon_type ) {
			$value = $results[$icon_type];
			if ( $value ) {
				switch ( $icon_type ) {
					case 'email':
						$url = "mailto:$value";
						break;
					case 'facebook':
					case 'youtube':
					case 'twitter':
					case 'qanda':
						$url = $value;
						break;
					default:
						$url = '';
				}

				$alt = "{$icon_type}_active";
			} else {
				$url = '';
				$alt = "{$icon_type}_inactive";
			}

			$results['icon_data'][$icon_type] = array( 'url' => $url, 'type' => $alt, 'alt' => ucfirst( $alt ) );
		}
	}
	
	return $results;
}

function get_party_from_candidate( $candidate_id ) {
	global $ed_taxonomies;
	$all_terms = get_the_terms( $candidate_id, $ed_taxonomies['candidate_party'] );
	if ( isset( $all_terms[0] ) ) {
		return get_party( $all_terms[0], false );
	} else {
		return array(
			'id' => 0,
			'name' => '',
			'colour' => '0x000000',
			'url' => '',
			'long_title' => '',
		);
	}
}

function get_candidate_party_from_answer_party( $answer_party ) {
	$party_id = get_tax_meta( $answer_party->term_id, 'candidate_party_term_id' );
	return get_party( $party_id, true );
}

function get_candidate_from_answer_candidate( $answer_candidate ) {
	$candidate_id = get_tax_meta( $answer_candidate->term_id, 'candidate_id' );
	return get_candidate( $candidate_id, true );
}

function get_news_article( $news_article_id ) {
	global $ed_taxonomies;
	
	$results = array(
		'id' => $news_article_id,
		'title' => get_the_title( $news_article_id ),
		'url' => get_post_meta( $news_article_id, 'url', true ),
		'summaries' => get_post_meta( $news_article_id, 'summaries', true ),
		'mentions' => array(),
		'source_name' => '',
		'summary' => '',
	);
	
	if ( is_array( $results['summaries'] ) && count( $results['summaries'] > 0 ) ) {
		$results['summary'] = $results['summaries'][array_rand( $results['summaries'] ) ];
	} else {
		$results['summary'] = '';
	}
	
	$candidates = get_the_terms( $news_article_id, $ed_taxonomies['news_article_candidate'] );
	foreach ( $candidates as $candidate ) {
		$candidate_id = get_tax_meta( $candidate->term_id, 'reference_post_id' );
		$results['mentions'][$candidate->term_id] = array(
			'name' => get_the_title( $candidate_id ),
			'url' => get_permalink( $candidate_id ),
		);
	}
	
	$source = get_the_terms( $news_article_id, $ed_taxonomies['news_article_source'] );
	if ( isset( $source[0] ) ) {
		$results['source_name'] = $source[0]->description;
	} else {
		$results['source_name'] = '';
	}
	return $results;
}

function get_qanda_questions( $type, $term ) {
	global $ed_post_types;
	global $ed_taxonomies;
	$query_args = array(
		'post_type' => $ed_post_types['answer'],
		'nopaging' => true,
		'post_status' => 'publish',
        'orderby' => "taxonomy-{$ed_taxonomies['answer_question']}",
		'order' => 'ASC',
	);
	switch ( $type ) {
		case 'party':
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $ed_taxonomies['answer_party'],
					'terms' => $term->term_id,
				),
			);
			$party_id = get_tax_meta( $term->term_id, 'candidate_party_term_id' );
			$party = get_term( $party_id, $ed_taxonomies['candidate_party'] );
			$pattern = array( '/\*party\*/', '/\*party_alt\*/' );
			$replacement = array( $party->name, $party->description );
			break;
		case 'candidate':
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $ed_taxonomies['answer_candidate'],
					'terms' => $term->term_id,
				),
			);
			$candidate_id = get_tax_meta( $term->term_id, 'candidate_id' );
			$candidate = get_post( $candidate_id );
			$pattern = array( '/\*candidate\*/', '/\*party\*/', '/\*party_alt\*/' );
			$parties = get_the_terms( $candidate, $ed_taxonomies['candidate_party'] );
			$party = $parties[0];
			$replacement = array( get_the_title( $candidate ), $party->name, $party->description );
			break;
	}
	$questions = array();
	$query = new WP_Query( $query_args );
	while ( $query->have_posts() ) {
		$query->the_post();
		$post = $query->post;
		$answer_questions = wp_get_post_terms( $post->ID, $ed_taxonomies['answer_question'] );
		if ( count( $answer_questions ) != 1 ) {
			continue;
		}
		$question = $answer_questions[0];
		$questions[$post->ID] = preg_replace( $pattern, $replacement, get_tax_meta( $question->term_id, 'question' ) );
	}

	return $questions;
}

function get_qanda_answers( $type, $id, $count = null ) {
	global $ed_post_types;
	global $ed_taxonomies;
	$query_args = array(
		'post_type' => $ed_post_types['answer'],
		'post_status' => 'publish',
        'orderby' => "taxonomy-{$ed_taxonomies['answer_question']}",
		'order' => 'ASC',
	);
	if ( isset ( $count ) ) {
		$query_args['posts_per_page'] = $count;
	} else {
		$query_args['nopaging'] = true;
	}
	switch ( $type ) {
		case 'party':
			$party = get_term( $id, $ed_taxonomies['candidate_party'] );
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $ed_taxonomies['answer_party'],
					'terms' => get_tax_meta( $party->term_id, 'qanda_party_id', true ),
				),
			);
			$pattern = array( '/\*party\*/', '/\*party_long\*/' );
			$replacement = array( $party->name, $party->description );
			break;
		case 'candidate':
			$candidate = get_post( $id );
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $ed_taxonomies['answer_candidate'],
					'terms' => get_post_meta( $candidate->ID, 'qanda_candidate_id', true ),
				),
			);
			$pattern = array( '/\*candidate\*/', '/\*party\*/', '/\*party_long\*/' );
			$parties = get_the_terms( $candidate, $ed_taxonomies['candidate_party'] );
			$party = $parties[0];
			$replacement = array( get_the_title( $candidate ), $party->name, $party->description );
			break;
	}
	$answers = array();
	$query = new WP_Query( $query_args );
	while ( $query->have_posts() ) {
		$query->the_post();
		$post = $query->post;
		$answer = apply_filters( 'the_content', get_the_content() );
		if ( empty( $answer ) ) {
			continue;
		}
		$questions = wp_get_post_terms( $post->ID, $ed_taxonomies['answer_question'] );
		if ( count( $questions ) != 1 ) {
			continue;
		}
		$question = $questions[0];
		$question_text = preg_replace( $pattern, $replacement, get_tax_meta( $question->term_id, 'question' ) );
		$answers[$question_text] = $answer;
	}

	return $answers;
}

function get_candidate( $candidate_id, $get_qanda = false ) {
	$image_id = get_post_thumbnail_id( $candidate_id );
	$image_id = $image_id ? $image_id : Election_Data_Option::get_option( 'missing_candidate' );
	
	$results = array(
		'id' => $candidate_id,
		'image_id' => $image_id,
		'name' => get_the_title( $candidate_id ),
		'phone' => get_post_meta( $candidate_id, 'phone', true ),
		'website' => get_post_meta( $candidate_id, 'website', true ),
		'email' => get_post_meta( $candidate_id, 'email', true ),
		'facebook' => get_post_meta( $candidate_id, 'facebook', true ),
		'youtube' => get_post_meta( $candidate_id, 'youtube', true ),
		'twitter' => get_post_meta( $candidate_id, 'twitter', true ),
		'incumbent_year' => get_post_meta( $candidate_id, 'incumbent_year', true ),
		'party_leader' => get_post_meta( $candidate_id, 'party_leader', true ),
		'url' => get_permalink( $candidate_id ),
		'news_article_candidate_id' => get_post_meta( $candidate_id, 'news_article_candidate_id', true ),
	);
	if ( $get_qanda ) {
		$results['answers'] = get_qanda_answers( 'candidate', $candidate_id );
		$results['qanda_token'] = get_post_meta( $candidate_id, 'qanda_token', true );
		$has_qanda = ! empty( $results['answers'] );
	} else {
		$answers = get_qanda_answers( 'candidate', $candidate_id, 1 );
		$has_qanda = ! empty( $answers );
	}
	$news = get_news( $results['news_article_candidate_id'], 1, 1 );
	$results['news_count'] = $news['count'];
	$results['qanda'] = $has_qanda ? "{$results['url']}#qanda" : '';
	$icon_data = array();
	foreach ( array('email', 'facebook', 'youtube', 'twitter', 'qanda' ) as $icon_type ) {
		$value = $results[$icon_type];
		if ( $value ) {
			switch ( $icon_type ) {
				case 'email':
					$url = 'mailto:' . $value;
					break;
				case 'facebook':
				case 'youtube':
				case 'twitter':
				case 'qanda':
					$url = $value;
					break;
				default:
					$url = '';
			}

			$alt = "{$icon_type}_active";
		} else {
			$url = '';
			$alt = "{$icon_type}_inactive";
		}
			
		$icon_data[$icon_type] = array( 'url' => $url, 'type' => $alt, 'alt' => ucfirst( $alt ) );
	}
	
	$results['icon_data'] = $icon_data;

	return $results;
}

function get_news( $candidate_id = null, $page = 1, $articles_per_page = null ) {
	global $ed_post_types;
	global $ed_taxonomies;
	
	if ( ! $articles_per_page ) {
		$articles_per_page = get_option( 'posts_per_page' );
	}
	$args = array(
		'post_type' => $ed_post_types['news_article'],
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => 'moderation',
				'value' => 'approved',
				'compare' => '=',
			),
		),
		'paged' => $page,
		'posts_per_page' => $articles_per_page,
	);
	
	if ( ! is_null( $candidate_id ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => $ed_taxonomies['news_article_candidate'],
				'field' => 'term_id',
				'terms' => $candidate_id,
			),
		);
	}
	
	$news_query = new WP_Query( $args ); 
	return array(
		'count' => $news_query->found_posts,
		'articles' => $news_query,
		'candidate_id' => $candidate_id,
	);
}

function get_paging_args( $type, $page ) {
	switch ( $type ) {
		case 'Candidate':
		case 'Single':
			$args = array(
				'current' => $page ? $page : 1,
				'format' =>'?page=%#%',
			);
			break;
		case 'News Article':
		case 'Party':
		case 'Constituency':
		case 'Archive':
			$args = array(
				'current' => $page ? $page : 1,
			);
			break;
	}
	return $args;
}

function get_current_page( $type ) {
	switch ( $type ) {
		case 'Candidate':
		case 'Single':
			$page = get_query_var( 'page' );
			break;
		case 'News Article':
		case 'Party':
		case 'Constituency':
		case 'Archive':
			$page = get_query_var( 'paged' );
			break;
	}
	return $page;
}

function get_answer( $answer ) {
	global $ed_post_types;
	
	$result = array(
		'token' => 'abc123',
	);
	
	return $result;
}

function can_edit_answers( $type, $id ) {
	switch ( $type ) {
		case 'party':
			$token = get_tax_meta( $id, 'qanda_token' );
			break;
		case 'candidate':
			$token = get_post_meta( $id, 'qanda_token', true );
			break;
	}
    
	return current_user_can( 'edit_posts' ) || get_query_var( 'token' ) == $token && ! empty( $token );
}

function get_source_count() {
    global $ed_taxonomies;
    global $ed_post_types;
    $sources = array();
    $terms = get_terms( $ed_taxonomies['news_article_source'], array( 'childless' => true ) );
    foreach ( $terms as $term ) {
        $args = array( 
            'post_type' => $ed_post_types['news_article'],
            'tax_query' => array (
                array (
                    'taxonomy' => $ed_taxonomies['news_article_source'],
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'meta_query' => array (
                array (
                    'key' => 'moderation',
                ),
            ),
            'nopaging' => true,
        );

        $sources[$term->name] = array();
        foreach ( array( 'approved', 'new', 'rejected' ) as $moderation ) {
            $args['meta_query'][0]['value'] = $moderation;
            $query = new WP_Query( $args );
            $sources[$term->name][] = $query->post_count;
        }
    }
    
    arsort( $sources );
    return $sources;
}