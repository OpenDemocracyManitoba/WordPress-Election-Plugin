<?php

/**
 * The file that defines the news articles custom post type.
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

function get_post_id_by_slug( $post_name, $post_type ) {
	global $wpdb;
	$sql = $wpdb->prepare( "
							SELECT ID
							FROM $wpdb->posts
							WHERE post_name = %s
							AND post_type= = %s
					", $post_name, $post_type );
  
	return $wpdb->get_var( $sql );
}

function get_post_ids_by_title( $post_title, $post_type ) {
	$sql = $wpdb->prepare( "
							SELECT ID
							FROM $wpdb->posts
							WHERE post_title = %s
							AND post_type = %s
	                ", $post_title, $post_type );
	return $wpdb->get_col( $sql );
}

/**
 * Sets up and handles the news articles custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */
class Election_Data_News_Article {
	/**
	 * The ED_Custom_Post_Type object representing the news article custom post type,
	 * and the news article and news source taxonomies.
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
	 * stores the Candidate post type and taxonomy names.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected $candidate;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 * @param string $candidate_post_type
	 * @param string $party_taxonomy_name
	 * @param boolean $define_hooks
	 *
	 */
	public function __construct( $candidate_post_type, $party_taxonomy_name, $define_hooks = true ) {
		$this->candidate = array (
			'post_type' => $candidate_post_type,
			'party' => $party_taxonomy_name,
		);
		$this->post_type = 'ed_news_articles';
		$this->taxonomies = array( 
			'reference' => "{$this->post_type}_reference",
			'source' => "{$this->post_type}_source",
		);
		$args = array(
			'custom_post_args' => array(
				'labels' => array(
					'name' => __( 'News Articles' ),
					'singular_name' => __( 'News Article' ),
					'add_new_item' => __( 'Add New News Article' ),
					'edit_item' => __( 'Edit News Article' ),
					'new_item' => __( 'New News Article' ),
					'view_item' => __( 'View News Article' ),
					'search_items' => __( 'Search News Articles' ),
					'not_found' => __( 'No News Articles found' ),
					'not_found_in_trash', __( 'No News Articles found in Trash' ),
				),
				'description' => __( 'A News article about a candidate or party in the election.' ),
				'public' => true,
				'menu_position' => 6,
				'show_ui' => true,
				//'menu_icon' => plugins_url( 'images/NewsArticle.png', dirname( __FILE__ ) ), //TODO: Create a News Article image,
				'supports' => array( 'title', ),
				'taxonomies' => array( '' ),
				'has_archive' => true,
				'query_var' => 'news_article',
				'rewrite' => array( 'slug' => __( 'news_articles' ), 'with_front' => false ),
			),
			'taxonomy_filters' => array( $this->taxonomies['source'], $this->taxonomies['reference'] ),
			'sortable_filters' => array( $this->taxonomies['source'], $this->taxonomies['reference'] ),
			'custom_post_meta' => array(
				'meta_box' => array( 
					'id' => 'election_data_news_article_meta_box',
					'title' => __( 'News Article Details' ),
					'post_type' => $this->post_type,
					'context' => 'normal',
					'priority' => 'high',
				),
				'fields' => array(
					'url' => array(
						'label' => __( 'URL' ),
						'id' => 'url',
						'desc' => __( 'The URL to the news article.' ),
						'type' => 'url',
						'std' => '',
						'imported' => true,
					),
					'moderation' => array(
						'label' => __( 'Moderated' ),
						'id' => 'moderation',
						'desc' => __( 'Whether the article is to be displaed in the news sections ore not.' ),
						'type' => 'pulldown',
						'std' => 'new',
						'imported' => true,
						'options' => array(
							'approved' => 'Approved',
							'new' => 'New',
							'rejected' => 'Rejected',
						),
					),
					'summaries' => array(
						'label' => __( 'Summaries' ),
						'id' => 'summaries',
						'desc' => __( 'Summary of the news article with the reference highlighted' ),
						'type' => 'text',
						'std' => array(),
						'imported' => false,
					),
				),
				'admin_columns' => array( 'url', 'moderation' ),
				'filters' => array( 
					'moderation' => array(
						'0' => 'All Moderations',
						'New' => 'New',
						'Approved' => 'Approved',
						'Rejected' => 'Rejected',
					),
				),
			),
			'taxonomy_args' => array(
				$this->taxonomies['reference'] => array(
					'labels' => array(
						'name' => _x( 'References', 'taxonomy general name' ),
						'singular_name' => _x( 'Reference', 'taxonomy general name' ),
						'all_items' => __( 'All References' ),
						'edit_item' => __( 'Edit References' ),
						'view_item' => __( 'View References' ),
						'update_item' => __( 'Update References' ),
						'add_new_item' => __( 'Add New Reference' ),
						'new_item_name' => __( 'New Reference' ),
						'search_items' => __( 'Search References' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => true, //TODO Change back to false
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'show_in_quick_edit' => true,
					//'meta_box_cb' =>''   //TOOD: Try adding Meta Box Call Back
					'hierarchical' => true,
					'query_var' => 'reference',
				),
				$this->taxonomies['source'] => array(
					'labels' => array(
						'name' => _x( 'Sources', 'taxonomy general name' ),
						'singular_name' => _x( 'Source', 'taxonomy general name' ),
						'all_itmes' => __( 'All Sources' ),
						'edit_item' => __( 'Edit Source' ),
						'view_item' => __( 'View Source' ),
						'update item' => __( 'Update Source' ),
						'add_new_item' => __( 'Add New Source' ),
						'new_item_name' => __( 'New Source' ),
						'search_items' => __( 'Search Sources' ),
						'parent_item' => null,
						'parent_item_colon' => null,
					),
					'public' => false,
					'show_tagcloud' => false,
					'show_admin_column' => true,
					'show_ui' => true,
					'hierarchical' => true,
					'query_var' => 'source',
				),
			),
			'taxonomy_meta' => array(
				'reference' => array(
					'taxonomy' => $this->taxonomies['reference'],
					'fields' => array(
						array(
							'type' => 'hidden',
							'id' => 'reference_post_id',
							'std' => '',
							'label' => __( 'Reference Id' ),
							'desc' => __( 'The post id for the reference.' ),
							'imported' => false,
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
	 * Gets the references to the parties. If a reference doesn't exists, it is created.
	 * References to parties that no longer exist are removed.
	 *
	 * @access protected
	 * @since 1.0
	 * @param int $parent_id
	 *
	 */
	protected function get_party_references( $parent_id )
	{
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_id,
		);
		$party_reference_names_by_id = get_terms( $this->taxonomies['reference'], $args );
		$references_by_party_id = array();
		$references_by_name = array();
		foreach ( $party_reference_names_by_id as $id => $name ) {
			$party_id = get_tax_meta( $id, 'reference_post_id' );
			$references_by_party_id[$party_id] = $id;
			$references_by_name[$name] = $id;
		}
		
		// Get the Parties.
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
		);
		$reload_party_names = false;
		$parties_by_id = get_terms( $this->candidate['party'], $args );
		foreach ( $parties_by_id as $id => $name ) {
			if ( !isset( $references_by_party_id[$id] ) ) {
				// Add a party reference if it doesn't exist.
				$term = wp_insert_term( $name, $this->taxonomies['reference'], array( 'parent' => $parent_id ) );
				update_tax_meta( $term['term_id'], 'reference_post_id', $id );
				update_tax_meta( $id, 'reference', $term['term_id'] );
				$references_by_name[$name] = $term['term_id'];
			} elseif ( $party_reference_names_by_id[$references_by_party_id[$id]] != $name ) {
				// Update a party reference if it exists, but the name has changed.
				wp_update_term( $references_by_party_id[$id], $this->taxonomies['reference'], array( 'name' => $name ) );
				$reload_party_names = true;
				unset( $party_reference_names_by_id[$references_by_party_id[$id]] );
			} else {
				unset( $party_reference_names_by_id[$references_by_party_id[$id]] );
			}
		}
		
		if ( $reload_party_names ) {
			$args = array(
				'fields' => 'id=>name',
				'hide_empty' => false,
				'parent' => $parent_id,
			);
			$party_reference_names_by_id = get_terms( $this->taxonomies['reference'], $args );
			$references_by_name = array();
			foreach ( $party_reference_names_by_id as $id => $name ) {
				$references_by_name[$name] = $id;
			}
		}

		foreach ( $party_reference_names_by_id as $id => $name ) {
			wp_delete_term( $id, $this->taxonomies['reference'] );
		}
		
		return $references_by_name;
	}
	
	/**
	 * Gets the references to the candidates. If a reference doesn't exists, it is created.
	 * References to candidates that no longer exist are removed.
	 *
	 * @access protected
	 * @since 1.0
	 * @param int $parent_id
	 *
	 */
	protected function get_candidate_references( $parent_id ) {
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_id,
		);
		$candidate_reference_names_by_id = get_terms( $this->taxonomies['reference'], $args );
		$references_by_candidate_id = array();
		$references_by_name = array();
		foreach ( $candidate_reference_names_by_id as $id => $name ) {
			$references_by_candidate_id[get_tax_meta( $id, 'reference_post_id' )] = $id;
			$references_by_name[$name] = $id;
		}

		// Get the candidates.
		$args = array( 
			'post_type' => $this->candidate['post_type'],
			'post_status' => 'publish',
			'nopaging' => true,
		);
		
		$reload_candidate_names = false;
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$name = get_the_title();
			$id = get_the_ID();
			if ( !isset( $references_by_candidate_id[$id] ) ) {
				// Add a candidate reference if it doesn't exist.
				$term = wp_insert_term( $name, $this->taxonomies['reference'], array( 'parent' => $parent_id ) );
				update_tax_meta( $term['term_id'], 'reference_post_id', $id );
				update_post_meta( $id, 'reference', $term['term_id'] );
				$references_by_name[$name] = $term['term_id'];
			} elseif ( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] != $name ) {
				// Update a candidate reference if it exists, but the name has changed.
				wp_update_term( $references_by_candidate_id[$id], $this->taxonomies['reference'], array( name => $name ) );
				unset( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] );
				$reload_candidate_names = true;
			} else {
				unset( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] );
			}
		}
		
		foreach ( $candidate_reference_names_by_id as $id => $name )
		{
			wp_delete_term( $id, $this->taxonomies['reference'] );
		}
		
		if ( $reload_candidate_names )
		{
			$args = array(
				'fields' => 'id=>name',
				'hide_empty' => false,
				'parent' => $parent_id,
			);
			$candidate_reference_names_by_id = get_terms( $this->taxonomies['reference'], $args );
			$references_by_name = array();
			foreach ( $candidate_reference_names_by_id as $id => $name ) {
				$references_by_name[$name] = $id;
			}
		}
		
		return $references_by_name;
	}
	
	/**
	 * Gets the references to the candidates and parties. If a reference doesn't exists, it is created.
	 * References to candidates and  parties that no longer exist are removed.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function update_references() {
		// Get Top level Referenes.
		$parent_ids = $this->custom_post->get_or_create_root_taxonomy_terms( $this->taxonomies['reference'], array( 'Party', 'Candidate' ) );
		$party_references = $this->get_party_references( $parent_ids['Party'] );
		$candidate_references = $this->get_candidate_references( $parent_ids['Candidate'] );
		return array( 'Candidate' => $candidate_references, 'Party' => $party_references );
	}
	
	/**
	 * Gets the root sources and their children. If the root children do not exist, they are created.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function get_sources() {
		$parent_ids = $this->custom_post->get_or_create_root_taxonomy_terms( $this->taxonomies['source'], array( 'Automatically Approve', 'Automatically Reject', 'Manually Approve', 'New' ) );
		
		$all_sources = array();
		
		foreach ( $parent_ids as $parent_name => $parent_id ) {
			$args = array(
				'fields' => 'id=>name',
				'hide_empty' => false,
				'parent' => $parent_id,
			);
			$sources_by_id = get_terms( $this->taxonomies['source'], $args );

			$sources = array();
			foreach ( $sources_by_id as $id => $name )
			{
				$sources[$name] = $id;
			}
			
			$all_sources[$parent_name] = $sources;
		}
		
		return array( 'parents' => $parent_ids, 'children' => $all_sources );
	}
	
	/**
	 * Gets all articles using the URL as the key to the array.
	 *
	 * @access protected
	 * @since 1.0
	 *
	 */
	protected function get_articles_by_url( $url ) {
		$args = array( 
			'post_type' => $this->post_type,
			'post_status' => array ( 'publish',),
			'meta_query' => array(
				array(
					'key' => 'url',
					'value' => $url,
					'compare' => '=',
				),
			),
			'nopaging' => true,
		);
		
		$query = new WP_Query( $args );
		$articles = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$articles[] = $query->post->ID;
		}
		return $articles;
	}
	/**
	 * Updates the news articles (AJAX version)
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function ajax_update_news_articles()
	{
		$this->update_news_articles( 'ajax' );
	}
		
	/**
	 * Updates the news articles
	 *
	 * @access public
	 * @since 1.0
	 * @param string $mode
	 *
	 */
	public function update_news_articles( $mode = 'non-ajax' ) {
		$references = $this->update_references();
		$sources_data = $this->get_sources();
		$auto_publish_sources = $sources_data['children']['Automatically Approve'];
		$auto_reject_sources = $sources_data['children']['Automatically Reject'];
		$sources = array();
		foreach ( $sources_data['children'] as $source ) {
			$sources += $source;
		}
		$source_parents = $sources_data['parents'];
		
		error_log( "Begin Scraping Articles" );
		foreach ( $references as $reference_type => $references_by_name ) {
			error_log( " - Begin Scraping for $reference_type" );
			foreach ( $references_by_name as $reference_name => $reference_id ) {
				error_log( " - - Begin Scraping for $reference_name" );
				$mentions = $this->get_individual_news_articles( $reference_name );
				$mentions += $this->get_individual_news_articles( $reference_name, Election_Data_Option::get_option( 'location' ) );
				foreach ( $mentions as $mention ) {
					if ( !isset( $sources[$mention['base_url']] ) ) {
						$term = wp_insert_term( $mention['base_url'], $this->taxonomies['source'], array( 'parent' => $source_parents['New'], 'description' => $mention['source'] ) );
						$sources[$mention['base_url']] = $term['term_id'];
						$sources_by_parent['New'][$mention['base_url']] = $term['term_id'];
						continue;
					} 
					
					if ( isset( $auto_publish_sources[$mention['base_url']] ) ) {
						$mention['moderation'] = 'approved';
					} elseif ( isset( $auto_reject_sources[$mention['base_url']] ) ) {
						$mention['moderation'] = 'rejected';
					} else {
						$mention['moderation'] = 'new';
					}
									
					$existing_articles = $this->get_articles_by_url( $mention['url'] );
					if ( $existing_articles ) {
						$article_id = $existing_articles[0];
						$post = get_post( $article_id );
						if ( $post->post_title != $mention['title'] ) {
							$args = array ( 'ID' => $post->ID, 'post_title' => $mention['title'] );
							wp_update_post( $args );
						}
						
						if ( 'Candidate' == $reference_type ) {
							$summaries = get_post_meta( $article_id, 'summaries', true );
							$summaries[$reference_id] = $mention['summary'];
							
							update_post_meta( $article_id, 'summaries', $summaries );
						}
					}
					else {
						$post = array(
							'post_title' => $mention['title'],
							'post_status' => 'publish',
							'post_type' => $this->post_type,
							'post_date' => $mention['publication_date']
						);
						$article_id = wp_insert_post( $post );
						update_post_meta( $article_id, 'url', $mention['url'] );
						$summaries = 'Candidate' == $reference_type ? array( $reference_id => $mention['summary'] ) : array();
						update_post_meta( $article_id, 'summaries', $summaries );
						update_post_meta( $article_id, 'moderation', $mention['moderation'] );
						wp_set_object_terms( $article_id, $sources[$mention['base_url']], $this->taxonomies['source']);
					}
				
					wp_set_object_terms( $article_id, $reference_id, $this->taxonomies['reference'], true ); 
				}
			}
		}
		
		$args = array( 
			'post_type' => $this->post_type,
			'nopaging' => true,
			'meta_query' => array(
				array(
					'key' => 'moderation',
					'value' => 'new',
					'compare' => '=',
				),
			),
		);
		
		$query = new WP_Query( $args );
		$to_be_updated = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$article_id = $query->post->ID;
			if ( count( wp_get_object_terms( $article_id, $this->taxonomies['reference'] ) ) > 1 ) {
				$to_be_updated[] = $article_id;
			}
		}
		
		foreach ( $to_be_updated as $article_id ) {
			wp_publish_post( $article_id );
		}
		
		if ( $mode == 'ajax' )
		{
			wp_die();
		}
	}
	
	/**
	 * Get the articles from Google News.
	 *
	 * @access protected
	 * @since 1.0
	 * @param string $candidate
	 * @param string $location
	 *
	 */
	protected function get_individual_news_articles( $candidate, $location='' ) {
		$gnews_url = "http://news.google.ca/news?ned=ca&hl=en&as_drrb=q&as_qdr=a&scoring=r&output=rss&num=75&q=\"$candidate\"";

		if ( $location ) {
			$gnews_url .= "&geo=$location";
		}
		
		$feed = fetch_feed( $gnews_url );
		
		$articles = array();
		if ( !is_wp_error( $feed ) ) {
			foreach ( $feed->get_items() as $feed_item ) {
				$item = array();
				$title_elements = explode( '-', $feed_item->get_title() );
				$item['source'] = array_pop( $title_elements );
				$item['title'] = implode( ' ', $title_elements );
				$item['publication_date'] = $feed_item->get_date( 'Y-m-d H:i:s' );
				$dom = new DOMDocument;
				$dom->loadHTML( $feed_item->get_description() );
				$xpath = new DOMXpath($dom);
				$summary = $xpath->query('.//font[@size=-1]');
				$summary_doc = new DOMDocument();
				$summary_doc->appendChild($summary_doc->importNode($summary->item(1)->cloneNode(true), true ) );
				$item['summary'] = $summary_doc->saveHTML();
				$urls = explode( 'url=', $feed_item->get_link( 0 ) );
				$url = $urls[1];
				$item['url'] = $url;
				$item['base_url'] = parse_url( $url, PHP_URL_HOST );
				$item['moderation'] = 'new';
				$articles[] = $item;
			}
		}
		
		return $articles;
	}	
	
	/**
	 * Sets up the wordpress cron to scan for articles.
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function setup_cron() {
		$timestamp = wp_next_scheduled( 'ed_update_news_articles' );
		if ( $timestamp == false ) {
			$this->schedule_cron( Election_Data_Option::get_option( 'time' ), Election_Data_Option::get_option( 'frequency' ) );
		}
	}

	/**
	 * Stops the wordpress cron from scanning for articles.
	 *
	 * @access public
	 * @since 1.0
	 *
	 */
	public function stop_cron() {
		wp_clear_scheduled_hook( 'ed_update_news_articles' );
	}
	
	/**
	 * Schedules the next and recurring runs of the news article scanning.
	 *
	 * @access protected
	 * @since 1.0
	 * @param string $time_string
	 * @param int $frequency
	 *
	 */
	protected function schedule_cron( $time_string, $frequency )
	{
		$time = strtotime( $time_string );
		if ( $time and $time < time() ) {
			$time = strtotime( "$time_string tomorrow" );
		}
		
		if ( $time ) {
			wp_schedule_event($time, $frequency, 'ed_update_news_articles' );
		}
		
		wp_schedule_event($time, $frequency, 'ed_update_news_articles' );
	}
	
	/**
	 * Changes the frequency at which the news article scanning cron job runs.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $frequency
	 *
	 */
	public function change_cron_frequency( $frequency ) {
		$this->stop_cron();
		$this->schedule_cron( Election_Data_Option::get_option( 'time' ), $frequency );
	}
	
	/**
	 * Changes the time at whcih the news article scanning cron job runs.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $time
	 *
	 */
	public function change_cron_time( $time ) {
		$this->stop_cron();
		$this->schedule_cron( $time, Election_Data_Option::get_option( 'frequency' ) );
	}
	
	/**
	 * Validates the time
	 *
	 * @access public
	 * @since 1.0
	 * @param string $new_value
	 * @param string $old_value
	 * @param string $settings_slug
	 *
	 */
	function validate_time( $new_value, $old_value, $settings_slug )
	{
		if ( !strtotime( $new_value ) && !strtotime( "$new_value tomorrow" ) ) {
			$new_value = $old_value;
			add_settings_error( $settings_slug, 'Invalid_time', __( 'The time must be a valid time without a date.', 'election_data' ), 'error' );
		}
		
		return $new_value;
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
		add_action( 'ed_update_news_articles', array( $this, 'update_news_articles' ) );
		add_action( 'election_data_settings_on_change_time', array( $this, 'change_cron_time' ) );
		add_action( 'election_data_settings_on_change_frequency', array( $this, 'change_cron_frequency' ) );
		add_filter( 'election_data_settings_validate_time', array( $this, 'validate_time' ), 10, 3 );
		add_action( 'wp_ajax_election_data_scrape_news', array( $this, 'ajax_update_news_articles' ) );
	}	
	
	/**
	 * Exports the news_articles and sources to a single xml file.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $xml
	 *
	 */
	public function export_xml( $xml ) {
	}

	
	/**
	 * Exports the news articles to a csv file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_news_article_csv( $csv ) {
		$post_fields = array(
			'post_title' => 'title',
			'post_date' => 'date',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( $this->taxonomies['source'] => 'source' );
		
		Post_Export::export_post_csv( $csv, $this->post_type, $this->custom_post->post_meta, $post_fields, '', $taxonomies );
	}
	
	/**
	 * Exports the news sources to a csv file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_news_source_csv( $csv ) {
		$source_fields = array( 'name', 'slug', 'description', 'parent' );
		Post_Export::export_taxonomy_csv( $csv, 'source', $this->taxonomies['source'], $source_fields, null, 0 );
	}
	
	/**
	 * Exports the news mentions to a csv file.
	 *
	 * @access protected
	 * @since 1.0
	 * @param file_handle $csv
	 *
	 */
	protected function export_news_mention_csv ( $csv ) {
		$headings = array( 'news_article', 'mention_type', 'mention', 'summary' );
		$headings_data = array_combine( $headings, $headings );
		Post_Export::write_csv_row( $csv, $headings_data, $headings );
		
		$args = array(
			'post_type' => $this->post_type,
			'orderby' => 'name',
			'order' => 'ASC',
			'nopaging' => true
		);
			
		$parents = $this->custom_post->get_or_create_root_taxonomy_terms( $this->taxonomies['reference'], array( 'Party', 'Candidate' ) );
		$parent_ids = array();
		foreach ( $parents as $name => $id ) {
			$parent_ids[$id] = $name;
		}
		
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$terms = get_the_terms( $query->post->ID, $this->taxonomies['reference'] );
			if (! is_array( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) 
			{
				$reference = get_tax_meta( $term->term_id, 'reference_post_id' );
				$parent_type = $parent_ids[$term->parent];
				if ( 'Party' == $parent_type ) {
					$party = get_term( $reference, $this->candidate['party'] );
					$mention = $party->slug;
					$summary = '';
				}
				elseif ( 'Candidate' == $parent_type ) {
					$summaries = get_post_meta( $query->post->ID, 'summaries', true );
					$candidate = get_post( $reference );
					$mention = $candidate->post_name;
					$summary = $summaries[$term->term_id];
				}
				$data = array(
					'news_article' => $query->post->post_name,
					'mention_type' => $parent_type,
					'mention' => $mention ,
					'summary' => $summary,
				);
			}
			
			Post_Export::write_csv_row( $csv, $data, $headings );
		}
	}
	
	/**
	 * Imports the news articles from a csv file
	 *
	 * @access protected 
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_news_article_csv( $csv, $mode ) {
		$post_fields = array(
			'post_title' => 'title',
			'post_date' => 'date',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( $this->taxonomies['source'] => 'source' );
		$default_values = array( 'slug' => '' );
		$required_values = array( 'title', 'date' );
		return Post_Import::import_post_csv( $csv, $mode, $this->post_type, $this->custom_post->post_meta, $post_fields, '', $taxonomies, $default_values, $required_values );
	}
	
	/**
	 * Imports the news sources from a csv file
	 *
	 * @access protected 
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_news_source_csv( $csv, $mode ) {
		$source_fields = array( 'name', 'slug', 'description' );
		$parent_field = 'parent';
		$sources = $this->get_sources();
		$news_source = get_term( $sources['parents']['New'], $this->taxonomies['source'] );
		$default_values = array( 'parent' => $news_source->slug, 'slug' => '', 'description' => '');
		$required_values = array( 'name' );
		$result = Post_Import::import_taxonomy_csv( $csv, $mode, 'source', $this->taxonomies['source'], $source_fields, null, $parent_field, $default_values, $required_values );
		foreach ( $sources['parents'] as $parent_id ) {
			wp_update_term( $parent_id, $this->taxonomies['source'], array( 'parent' => 0 ) );
		}
		return $result;
	}
	
	/**
	 * Imports the news mentions from a csv file
	 *
	 * @access protected 
	 * @since 1.0
	 * @param file_handle $csv
	 * @param string $mode
	 *
	 */
	protected function import_news_mention_csv ($csv, $mode ) {
		$headings = fgetcsv( $csv );
		$found = true;
		$fields = array( 'news_article', 'mention_type', 'mention', 'summary' );
		foreach ( $fields as $field ) {
			$found &= in_array( $field, $headings );
		}
		
		if ( !$found ) {
			return false;
		}
		
		$this->update_references();
		$current_articles = Post_Import::get_current_posts( $this->post_type );
		$current_articles['url'] = array();
		foreach ( $current_articles['post_name'] as $article ) {
			$url = get_post_meta( $article->ID, 'url', true );
			$current_articles['url'][$url][] = $article;
		}
		$current_candidates = Post_Import::get_current_posts( $this->candidate['post_type'] );
		while ( ( $data = fgetcsv( $csv ) ) !== false ) {
			$data = array_combine( $headings, $data );
			$articles = array();
			if ( isset( $current_articles['post_name'][$data['news_article']] ) ) {
				$articles[] = $current_articles['post_name'][$data['news_article']];
			} elseif ( isset( $current_articles['url'][$data['news_article']] ) ) {
				$articles = $current_articles['url'][$data['news_article']];
			}
			$references = array();
			if ( $data['mention_type'] == 'Party' )
			{
				$term = get_term_by( 'slug', $data['mention'], $this->candidate['party'] );
				if ( !$term ) {
					$term = get_term_by( 'name', $data['mention'], $this->candidate['party'] );
				}
				if ( $term ) {
					$references[] = get_tax_meta( $term->term_id, 'reference' );
				}
			} elseif ( $data['mention_type'] == 'Candidate' )
			{
				if ( isset( $current_candidates['post_name'][$data['mention']] ) ) {
					$candidate = $current_candidates['post_name'][$data['mention']];
					$references[] = get_post_meta( $candidate->ID, 'reference', true );
				} elseif ( isset( $current_candidates['post_title'][$data['mention']] ) ) {
					$candidates = $current_candidates['post_title'][$data['mention']];
					foreach ( $candidates as $candidate ) {
						$references[] = get_post_meta( $candidate->ID, 'reference', true );
					}
				}
			}
			if ( !empty( $articles ) && !empty( $references ) ) {
				foreach ( $references as $reference ) {
					foreach ($articles as $article ) {
						wp_set_object_terms( $article->ID, (int)$reference, $this->taxonomies['reference'], true );
						if ( 'Candidate' == $data['mention_type'] ) {
							$summaries = get_post_meta( $article->ID, 'summaries', true );
							$summaries[$reference] = $data['summary'];
							update_post_meta( $article->ID, 'summaries', $summaries );
						}
					}
				}
			}	
		}
		return true;
	}
	
	/**
	 * Exports the news articles, news sources or news mentions to a csv file
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
	 * Imports the news_articles, news sources or news mentions from a CSV file.
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
	 * Erases all news articles, news sources and references from the database.
	 * @access public
	 * @since 1.0
	 *
	 */
	public function erase_data()
	{
		$this->custom_post->erase_data();
	}
}