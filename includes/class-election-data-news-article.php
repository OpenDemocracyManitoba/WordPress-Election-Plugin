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

/**
 * The news articles custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Robert Burton <RobertBurton@gmail.com>
 */

require_once plugin_dir_path( __FILE__ ) . 'class-post-meta.php';
require_once plugin_dir_path( __FILE__ ) . 'class-taxonomy-meta.php';
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

class Election_Data_News_Article {
	// Definition of the custom post type.
	protected $custom_post;
	
	// Definition of the Party and Constituency taxonomies.
	protected $taxonomies;
	
	protected $post_meta;
	
	protected $taxonomy_meta;
	
	function __construct() {
		$this->custom_post = array(
			'name' => 'ed_news_articles',
			'args' => array(
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
				'public' => false,
				'menu_position' => 6,
				'show_ui' => true,
				//'menu_icon' => plugins_url( 'images/NewsArticle.png', dirname( __FILE__ ) ), //TODO: Create a News Article image,
				'supports' => array( 'title', ),
				'taxonomies' => array( '' ),
				'has_archive' => false,
				'query_var' => 'news_article'
			)
		);
		
		$custom_post_meta = array(
			'meta_box' => array( 
				'id' => 'election_data_news_article_meta_box',
				'title' => __( 'News Article Details' ),
				'post_type' => $this->custom_post['name'],
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
					'label' => __( 'Moderation' ),
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
		);

		$this->taxonomies = array(
			'reference' => array(
				'name' => $this->custom_post['name'] . '_reference',
				'post_type' => $this->custom_post['name'],
				'args' => array(
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
			),
			'source' => array(
				'name' => $this->custom_post['name'] . '_source',
				'post_type' => $this->custom_post['name'],
				'args' => array(
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
		);

		$taxonomy_meta = array(
			'reference' => array(
				'taxonomy' => $this->taxonomies['reference']['name'],
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
		);
		
		$this->post_meta = new Post_Meta( 
			$custom_post_meta['meta_box'],
			$custom_post_meta['fields'],
			$custom_post_meta['admin_columns']
		);
		
		$this->taxonomy_meta = array();
		foreach ( $taxonomy_meta as $name => $tax_meta_config ) {
			$this->taxonomy_meta[$name] = new Tax_Meta( $tax_meta_config['taxonomy'], $tax_meta_config['fields'] );
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
					$taxonomy['args']['meta_box_cb'] = array( $this, 'taxonomy_radio_meta_box' );
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
			if ( isset($taxonomy['args']['show_admin_column']) && $taxonomy['args']['show_admin_column'] ) {
				$columns["taxonomy-{$taxonomy['name']}"] = "taxonomy-{$taxonomy['name']}";
			}
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
				if ( $taxonomy['args']['show_admin_column'] )
				{
					$query = $taxonomy['args']['query_var'];
					$name = $taxonomy['name'];
					$selected = '';
					if ( isset( $wp_query->query[$query] ) ) {
						$term = get_term_by( 'slug', $wp_query->query[$query], $name );
						if ( $term ) {
							$selected = (int)$term->term_id;
						}
					}
					
					$args = array(
						'show_option_all' => "All {$taxonomy['args']['labels']['name']}",
						'taxonomy' => $name,
						'name' => $query,
						'orderby' => 'name',
						'selected' => $selected,
						'hierarchical' => true,
						'depth' => 3,
						'show_count' => false,
						'hide_empty' => false,
						'value_field' => 'slug'
					);
					
					wp_dropdown_categories( $args );
				}
			}
		}
	}
	
	// Allow for default template files to be a part of the plugin.
	function include_template_function( $template_path ) {
/*		$template_file = '';
		switch ( get_query_var( 'post_type' ) ) {
			case $this->custom_post['name']:
				if ( is_single() )
				{
					$template_file = "single-{$this->custom_post['name']}.php";
				}
				else
				{
					$template_file = "archive-{$this->custom_post['name']}.php";
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
		*/
		return $template_path;
	}
	
	function setup_admin_scripts() {
		global $current_screen;
		
		if ( $current_screen->id == "edit-{$this->custom_post['name']}" ) {
			wp_register_script( "quick-edit-{$this->custom_post['name']}", plugin_dir_url( __FILE__ )  . 'js/quick-edit.js', array( 'jquery', 'inline-edit-post' ), '', true  );
			$translation_array = array();
			if ( isset( $this->custom_post['args']['hide_quick_edit_fields'] ) ) {
				foreach ( $this->custom_post['args']['hide_quick_edit_fields'] as $column ) {
					$translation_array[ucfirst($column)] = '';
				}
			}

			wp_localize_script( "quick-edit-{$this->custom_post['name']}", 'ed_remove_columns', $translation_array );
			
			$translation_array = array();
			if ( isset( $this->custom_post['args']['quick_edit_column_names'] ) ) {
				foreach ( $this->custom_post['args']['quick_edit_column_names'] as $column => $name ) {
					$translation_array[ucfirst($column)	] = $name;
				}
			}
			
			wp_localize_script( "quick-edit-{$this->custom_post['name']}", 'ed_rename_columns', $translation_array );
			
			wp_enqueue_script( "quick-edit-{$this->custom_post['name']}" );
		}
	}
	
	function setup_public_scripts() {
		wp_enqueue_style( "ed_{$this->custom_post['name']}_style", plugin_dir_url( __FILE__ ) . 'css/application.css' );
	}
	
	function get_or_create_parent_taxonomy_terms( $taxonomy_name, $parent_names ) {
		$args = array(
			'fields' => 'all',
			'hide_empty' => false,
			'parent' => 0,
		);
		$terms = get_terms( $taxonomy_name, $args );
		$parent_ids = array();
		foreach ( $terms as $term ) {
			$parent_ids[$term->name] = $term->term_id;
		}
		
		// If a required reference is not there, create it.
		foreach ( $parent_names as $name ) {
			if ( !isset( $parent_ids[$name] ) ) {
				$ids = wp_insert_term( $name, $taxonomy_name, array( 'parent' => 0 ) ); 
				$parent_ids[$name] = $ids['term_id'];
			}
		}	
		
		return $parent_ids;
	}
	
	function get_party_references( $parent_id )
	{
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_id,
		);
		$party_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
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
		$parties_by_id = get_terms( 'ed_candidates_party', $args );
		foreach ( $parties_by_id as $id => $name ) {
			if ( !isset( $references_by_party_id[$id] ) ) {
				// Add a party reference if it doesn't exist.
				$term = wp_insert_term( $name, $this->taxonomies['reference']['name'], array( 'parent' => $parent_id ) );
				update_tax_meta( $term['term_id'], 'reference_post_id', $id );
				update_tax_meta( $id, 'reference', $term['term_id'] );
				$references_by_name[$name] = $term['term_id'];
			} elseif ( $party_reference_names_by_id[$references_by_party_id[$id]] != $name ) {
				// Update a party reference if it exists, but the name has changed.
				wp_update_term( $references_by_party_id[$id], $this->taxonomies['reference']['name'], array( 'name' => $name ) );
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
			$party_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
			$references_by_name = array();
			foreach ( $party_reference_names_by_id as $id => $name ) {
				$references_by_name[$name] = $id;
			}
		}

		foreach ( $party_reference_names_by_id as $id => $name ) {
			wp_delete_term( $id, 'ed_news_articles_reference' );
		}
		
		return $references_by_name;
	}
	
	function get_candidate_references( $parent_id ) {
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_id,
		);
		$candidate_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
		$references_by_candidate_id = array();
		$references_by_name = array();
		foreach ( $candidate_reference_names_by_id as $id => $name ) {
			$references_by_candidate_id[get_tax_meta( $id, 'reference_post_id' )] = $id;
			$references_by_name[$name] = $id;
		}

		// Get the candidates.
		$args = array( 
			'post_type' => 'ed_candidates',
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
				$term = wp_insert_term( $name, $this->taxonomies['reference']['name'], array( 'parent' => $parent_id ) );
				update_tax_meta( $term['term_id'], 'reference_post_id', $id );
				update_post_meta( $id, 'reference', $term['term_id'] );
				$references_by_name[$name] = $term['term_id'];
			} elseif ( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] != $name ) {
				// Update a candidate reference if it exists, but the name has changed.
				wp_update_term( $references_by_candidate_id[$id], $this->taxonomies['reference']['name'], array( name => $name ) );
				unset( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] );
				$reload_candidate_names = true;
			} else {
				unset( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] );
			}
		}
		
		foreach ( $candidate_reference_names_by_id as $id => $name )
		{
			wp_delete_term( $id, 'ed_news_articles_reference' );
		}
		
		if ( $reload_candidate_names )
		{
			$args = array(
				'fields' => 'id=>name',
				'hide_empty' => false,
				'parent' => $parent_id,
			);
			$candidate_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
			$references_by_name = array();
			foreach ( $candidate_reference_names_by_id as $id => $name ) {
				$references_by_name[$name] = $id;
			}
		}
		
		return $references_by_name;
	}
	
	// Update the Reference taxonomy, and return a array of id => reference name.
	function update_references() {
		// Get Top level Referenes.
		$parent_ids = $this->get_or_create_parent_taxonomy_terms( $this->taxonomies['reference']['name'], array( 'Party', 'Candidate' ) );
		$party_references = $this->get_party_references( $parent_ids['Party'] );
		$candidate_references = $this->get_candidate_references( $parent_ids['Candidate'] );
		return array( 'Candidate' => $candidate_references, 'Party' => $party_references );
	}
	
	function get_sources() {
		$parent_ids = $this->get_or_create_parent_taxonomy_terms( $this->taxonomies['source']['name'], array( 'Automatically Approve', 'Automatically Reject', 'Manually Approve', 'New' ) );
		
		$all_sources = array();
		
		foreach ( $parent_ids as $parent_name => $parent_id ) {
			$args = array(
				'fields' => 'id=>name',
				'hide_empty' => false,
				'parent' => $parent_id,
			);
			$sources_by_id = get_terms( $this->taxonomies['source']['name'], $args );

			$sources = array();
			foreach ( $sources_by_id as $id => $name )
			{
				$sources[$name] = $id;
			}
			
			$all_sources[$parent_name] = $sources;
		}
		
		return array( 'parents' => $parent_ids, 'children' => $all_sources );
	}
	
	function get_articles_by_url() {
		$args = array( 
			'post_type' => 'ed_news_articles',
			'post_status' => array ( 'publish',),
			'nopaging' => true,
		);
		
		$query = new WP_Query( $args );
		$articles = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$id = get_the_ID();
			$urls = get_post_meta( $id, 'url', true );
			$articles[$urls] = $id;
		}
		return $articles;
	}
	
	function ajax_update_news_articles()
	{
		$this->update_news_articles( 'ajax' );
	}
		
	function update_news_articles( $mode = 'non-ajax' ) {
		$references = $this->update_references();
		$sources_data = $this->get_sources();
		$auto_publish_sources = $sources_data['children']['Automatically Publish'];
		$auto_reject_sources = $sources_data['children']['Automatically Reject'];
		foreach ( $source_data as $source ) {
			$sources += $source;
		}
		$source_parents = $sources_data['parents'];
		$articles_by_url = $this->get_articles();
		
		foreach ( $references as $reference_type => $reference_by_name ) {
			foreach ( $references_by_name as $reference_name => $reference_id )
			{
				$mentions = $this->get_individual_news_articles( $reference_name );
				$mentions += $this->get_individual_news_articles( $reference_name, Election_Data_Option::get_option( 'location' ) );
				foreach ( $mentions as $mention ) {
					if ( !isset( $sources[$mention['base_url']] ) ) {
						$term = wp_insert_term( $mention['base_url'], $this->taxonomies['source']['name'], array( 'parent' => $source_parents['New'], 'description' => $mention['source'] ) );
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
									
					if ( isset( $articles_by_url[$mention['url']] ) ) {
						$article_id = $articles_by_url[$mention['url']];
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
							'post_status' => $mention['published'],
							'post_name' => sanitize_title( $mention['title'] ),
							'post_type' => 'ed_news_articles',
							'post_date' => $mention['publication_date']
						);
						$article_id = wp_insert_post( $post );
						update_post_meta( $article_id, 'url', $mention['url'] );
						$summaries = 'Candidate' == $reference_type ? array( $reference_id => $mention['summary'] ) : array();
						update_post_meta( $article_id, 'summaries', $summaries );
						update_post_meta( $article_id, 'moderation', $mention['moderation'] );
						wp_set_object_terms( $article_id, $sources[$mention['base_url']], 'ed_news_articles_source');
					}
				
					wp_set_object_terms( $article_id, $reference_id, 'ed_news_articles_reference', true ); 
				}
			}
		}
		
		$args = array( 
			'post_type' => 'ed_news_articles',
			'post_status' => array ( 'pending',),
			'nopaging' => true,
		);
		
		$query = new WP_Query( $args );
		$to_be_updated = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$article_id = $query->post->ID;
			if ( count( wp_get_object_terms( $article_id, 'ed_news_articles_reference' ) ) > 1 ) {
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
	
	function get_individual_news_articles( $candidate, $location='' ) {
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
				$item['published'] = 'pending';
				$articles[] = $item;
			}
		}
		
		return $articles;
	}	
	
	function setup_cron() {
		$timestamp = wp_next_scheduled( 'ed_update_news_articles' );
		if ( $timestamp == false ) {
			$this->schedule_cron( Election_Data_Option::get_option( 'time' ), Election_Data_Option::get_option( 'frequency' ) );
		}
	}

	function stop_cron() {
		wp_clear_scheduled_hook( 'ed_update_news_articles' );
	}
	
	function schedule_cron( $time_string, $frequency )
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
	
	function change_cron_frequency( $frequency ) {
		$this->stop_cron();
		$this->schedule_cron( Election_Data_Option::get_option( 'time' ), $frequency );
	}
	
	function change_cron_time( $time ) {
		$this->stop_cron();
		$this->schedule_cron( $time, Election_Data_Option::get_option( 'frequency' ) );
	}
	
	function validate_time( $new_value, $old_value, $settings_slug )
	{
		if ( !strtotime( $new_value ) && !strtotime( "$new_value tomorrow" ) ) {
			$new_value = $old_value;
			add_settings_error( $settings_slug, 'Invalid_time', __( 'The time must be a valid time without a date.', 'election_data' ), 'error' );
		}
		
		return $new_value;
	}
	
	function define_hooks()
	{
		add_action( 'admin_init', array( $this, 'admin' ) );
		add_filter( "manage_edit-{$this->custom_post['name']}_columns", array( $this, 'define_columns' ) );
		add_filter( "manage_edit-{$this->custom_post['name']}_sortable_columns", array( $this, 'sort_columns' ) );
		add_filter( 'posts_clauses', array( $this, 'taxonomy_clauses' ), 10, 2 );
	    add_action( 'restrict_manage_posts', array( $this, 'filter_lists' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
		if ( isset( $this->custom_post['args']['remove_admin_column_date_filter'] ) ) {
			add_filter( 'months_dropdown_results', array( $this, 'remove_dates' ) );
		}
		
		if ( isset( $this->custom_post['args']['enter_title_here'] ) ) {
			add_filter( 'enter_title_here', array( $this, 'update_title' ) );
		}
		add_action( 'ed_update_news_articles', array( $this, 'update_news_articles' ) );
		add_action( 'election_data_settings_on_change_time', array( $this, 'change_cron_time' ) );
		add_action( 'election_data_settings_on_change_frequency', array( $this, 'change_cron_frequency' ) );
		add_filter( 'election_data_settings_validate_time', array( $this, 'validate_time' ), 10, 3 );
		add_action( 'wp_ajax_election_data_scrape_news', array( $this, 'ajax_update_news_articles' ) );
		add_action( 'init', array( $this, 'initialize' ) );
		add_filter( 'template_include', array( $this, 'include_template_function' ), 1 );
		add_filter( 'wp_enqueue_scripts', array( $this, 'setup_public_scripts' ) );
	}	
	
	function export_xml( $xml ) {
	}

	function export_news_article_csv( $csv ) {
		$post_fields = array(
			'post_title' => 'title',
			'post_date' => 'date',
			'post_name' => 'slug',
			'post_status' => 'status',
		);
		
		$taxonomies = array( $this->taxonomies['source']['name'] => 'source' );
		
		Post_Export::export_post_csv( $csv, $this->custom_post['name'], $this->post_meta, $post_fields, '', $taxonomies );
	}
	
	function export_news_source_csv( $csv ) {
		$source_fields = array( 'name', 'slug', 'description', 'parent' );
		Post_Export::export_taxonomy_csv( $csv, 'source', $this->taxonomies['source']['name'], $source_fields, null, 0 );
	}
	
	function export_news_mention_csv ( $csv ) {
		$headings = array( 'news_article', 'mention_type', 'mention', 'summary' );
		$headings_data = array_combine( $headings, $headings );
		Post_Export::write_csv_row( $csv, $headings_data, $headings );
		
		$args = array(
			'post_type' => $this->custom_post['name'],
			'orderby' => 'name',
			'order' => 'ASC',
			'nopaging' => true
		);
			
		$parents = $this->get_or_create_parent_taxonomy_terms( $this->taxonomies['reference']['name'], array( 'Party', 'Candidate' ) );
		$parent_ids = array();
		foreach ( $parents as $name => $id ) {
			$parent_ids[$id] = $name;
		}
		
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$terms = get_the_terms( $query->post->ID, $this->taxonomies['reference']['name'] );
			if (! is_array( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) 
			{
				$reference = get_tax_meta( $term->term_id, 'reference_post_id' );
				$parent_type = $parent_ids[$term->parent];
				if ( 'Party' == $parent_type ) {
					$party = get_term( $reference, 'ed_candidates_party' );
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
	
	function import_news_article_csv( $csv, $mode ) {
		$post_fields = array(
			'post_title' => 'title',
			'post_date' => 'date',
			'post_name' => 'slug',
		);
		
		$taxonomies = array( $this->taxonomies['source']['name'] => 'source' );
		$default_values = array( 'slug' => '' );
		$required_values = array( 'title', 'name' );
		return Post_Import::import_post_csv( $csv, $mode, $this->custom_post['name'], $this->post_meta, $post_fields, '', $taxonomies, $default_values, $required_values );
	}
	
	function import_news_source_csv( $csv, $mode ) {
		$source_fields = array( 'name', 'slug', 'description' );
		$parent_field = 'parent';
		$sources = $this->get_sources();
		$new_source = get_term( $sources['parents']['New'], $this->taxonomies['source']['name'] );
		$default_values = array( 'parent' => $new_source->slug, 'slug' => '', 'description' => '');
		$required_values = array( 'name' );
		return Post_Import::import_taxonomy_csv( $csv, $mode, 'source', $this->taxonomies['source']['name'], $source_fields, null, $parent_field, $default_values, $required_values );
	}
	
	function import_news_mention_csv ($csv, $mode ) {
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
		$current_articles = Post_Import::get_current_posts( $this->custom_post['name'] );
		$current_articles['url'] = array();
		foreach ( $current_articles['post_name'] as $article ) {
			$url = get_post_meta( $article->ID, 'url', true );
			$current_articles['url'][$url][] = $article;
		}
		$current_candidates = Post_Import::get_current_posts( 'ed_candidates' );
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
				$term = get_term_by( 'slug', $data['mention'], 'ed_candidates_party' );
				if ( !$term ) {
					$term = get_term_by( 'name', $data['mention'], 'ed_candidates_party' );
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
						wp_set_object_terms( $article->ID, (int)$reference, $this->taxonomies['reference']['name'], true );
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
	
	function export_csv ( $type ) {
		$file_name = tempnam( 'tmp', 'csv' );
		$file = fopen( $file_name, 'w' );
		call_user_func( array( $this, "export_{$type}_csv" ), $file );
		
		fclose( $file );
		return $file_name;
	}
	
	function import_csv( $type, $csv, $mode ) {
		return call_user_func( array( $this, "import_{$type}_csv" ), $csv, $mode );
	}
	
	function erase_data()
	{
		$args = array(
			'post_type' => $this->custom_post['name'],
			'nopaging' => true,
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
			);
			$term_ids = get_terms( $taxonomy['name'], $args );
			foreach ( $term_ids as $term_id ) {
				wp_delete_term( $term_id, $taxonomy['name'] );
			}
		}
	}
}