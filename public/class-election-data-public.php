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
	global $constituency_name;
	$constituency = get_term( $constituency, $constituency_name );
	$constituency_id = $constituency->term_id;
	$results = array(
		'id' => $constituency_id,
		'name' => $constituency->name,
		'url' => get_term_link( $constituency, $constituency_name ),
		'reference' => get_post_meta( $constituency_id, 'reference' ),
	);
	if ( $get_extra_data ) {
		$results['details'] = get_tax_meta( $constituency_id, 'details' );
		$map_image = get_tax_meta( $constituency_id, 'map' );
		$results['map_id'] = $map_image ? $map_image : '';
		
		$child_terms = get_terms( $constituency_name, array( 'parent' =>$constituency_id, 'hide_empty' => false ) );
		$results['children'] = array();
		foreach ( $child_terms as $child )
		{
			$results['children'][$child->name] = array(
				'url' => get_term_link( $child, $constituency_name ),
				'coordinates' => get_tax_meta( $child->term_id, 'coordinates' ),
			);
		}
	}
	
	return $results;
}

function get_constituency_from_candidate( $candidate_id ) {
	global $constituency_name;
	$all_terms = get_the_terms( $candidate_id, $constituency_name );
	if ( isset( $all_terms[0] ) ) {
		return get_constituency( $all_terms[0], false );
	} else {
		return  array(
			'id' => 0,
			'name' =>'',
			'url' => '',
			'reference' => '',
		);
	}
}

function get_root_constituencies() {
	global $constituency_name;
	$args = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => false,
		'fields' => 'ids',
		'parent' => 0,
	);
	
	$terms = get_terms( $constituency_name , $args );
	return $terms;
}

function get_parties_random() {
	global $party_name;
	$args = array(
		'orderby' => 'id',
		'order' => 'ASC',
		'hide_empty' => false,
		'fields' => 'ids',
	);
	
	$terms = get_terms( $party_name , $args );
	shuffle( $terms );
	return $terms;
}	

function get_party( $party, $get_extra_data = true ) {
	global $party_name;
	$party = get_term( $party, $party_name );
	$party_id = $party->term_id;
	
	$results = array(
		'id' => $party_id,
		'name' => $party->name,
		'colour' => get_tax_meta( $party_id, 'colour' ),
		'url' => get_term_link( $party, $party_name ),
		'long_title' => $party->description,
		'reference_id' => get_tax_meta( $party_id, 'reference' ),
	);
		
	if ( $get_extra_data ) {
		$party_logo = get_tax_meta( $party_id, 'logo' );
		$results['logo_id'] = $party_logo ? $party_logo : Election_Data_Option::get_option( 'missing_party' );
		$results['website'] = get_tax_meta( $party_id, 'website' );
		$results['phone'] = get_tax_meta( $party_id, 'phone' );
		$results['address'] = get_tax_meta( $party_id, 'address' );
		$results['icon_data'] = array();
		$results['reference'] = get_tax_meta( $party_id, 'conference' );
		$results['facebook'] = get_tax_meta( $party_id, 'facebook' );
		$results['youtube'] = get_tax_meta( $party_id, 'youtube' );
		$results['twitter'] = get_tax_meta( $party_id, 'twitter' );
		$results['email'] = get_tax_meta( $party_id, 'email' );
		foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
			$value = $results[$icon_type];
			if ( $value ) {
				switch ( $icon_type ) {
					case 'email':
						$url = "mailto:$value";
						break;
					case 'facebook':
					case 'youtube':
					case 'twitter':
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
	global $party_name;
	$all_terms = get_the_terms( $candidate_id, $party_name );
	if ( isset( $all_terms[0] ) ) {
		return get_party( $all_terms[0], false );
	} else {
		return array(
			'id' => 0,
			'name' => '',
			'colour' => '0x000000',
			'url' => '',
			'long_title' => '',
			'reference_id' => '',
		);
	}
}

function get_news_article( $news_article_id ) {
	global $reference_name, $source_name;
	
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
	}
	
	$references = get_the_terms( $news_article_id, $reference_name );
	foreach ( $references as $reference ) {
		error_log( print_r( $reference, true ) );
		$candidate_id = get_tax_meta( $reference->term_id, 'reference_post_id' );
		$results['mentions'][$reference->term_id] = array(
			'name' => get_the_title( $candidate_id ),
			'url' => get_permalink( $candidate_id ),
		);
	}
	
	$source = get_the_terms( $news_article_id, $source_name );
	if ( isset( $source[0] ) ) {
		$results['source_name'] = $source[0]->description;
	}
	return $results;
}

function get_candidate( $candidate_id ) {
	$image_id = get_post_thumbnail_id( $candidate_id );
	$image_id = $image_id ? $image_id : Election_Data_Option::get_option( 'missing_candidate' );
	
	$results = array(
		'image_id' => $image_id,
		'reference_id' => get_post_meta( $candidate_id, 'reference', true ),
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
	);
	
	$icon_data = array();
	foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
		$value = $results[$icon_type];
		if ( $value ) {
			switch ( $icon_type ) {
				case 'email':
					$url = 'mailto:' . $value;
					break;
				case 'facebook':
				case 'youtube':
				case 'twitter':
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

function get_news( $reference_id = null, $page = 1, $articles_per_page = 20 ) {
	global $news_article_name, $reference_name;
	$args = array(
		'post_type' => $news_article_name,
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
	
	if ( ! empty( $reference_id ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => $reference_name,
				'field' => 'term_id',
				'terms' => $reference_id,
			),
		);
	}

	
	$news_query = new WP_Query( $args ); 
	return array(
		'count' => $news_query->found_posts,
		'articles' => $news_query,
		'reference_id' => $reference_id,
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
