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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'Tax-meta-class/Tax-meta-class.php';

class Election_Data_News_Article {
	// Definition of the custom post type.
	protected $custom_post;
	
	// Definition of the custom post meta fields.
	protected $custom_post_meta;
	
	// Definition of the Party and Constituency taxonomies.
	protected $taxonomies;
	
	// Definition of the Party and Constituency meta fields.
	protected $taxonomy_meta;
	
	function __construct() {
		$this->custom_post = array(
			'name' => 'ed_news_article',
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
		
		$this->custom_post_meta = array(
			'id' => 'election_data_news_article_meta_box',
			'title' => __( 'News Article Details' ),
			'post_type' => $this->custom_post['name'],
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				'url' => array(
					'label' => __( 'URL' ),
					'meta_id' => 'url',
					'desc' => __( 'The URL to the news article.' ),
					'id' => 'news_article_url',
					'type' => 'url',
					'std' => '',
				),
				'summary' => array(
					'label' => __( 'Summary ' ),
					'meta_id' => 'summary',
					'desc' => __( 'A short summary of the news article.' ),
					'id' => 'news_article_summary',
					'type' => 'text',
					'std' => '',
					'style' => 'xxx'
				),
			),
			'admin_column_fields' => array( 'url' => '' ),
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
					'show_admin_column' => false,
					'show_ui' => true,
					'hierarchical' => true,
					'query_var' => 'source',
				),
			),
		);

		$this->taxonomy_meta = array(
			'reference' => array(
				'id' => $this->taxonomies['reference']['name'] . '_meta_box',
				'title' => 'Reference Details',
				'pages' => array( $this->taxonomies['reference']['name'] ),
				'context' => 'normal',
				'fields' => array(
					array(
						'type' => 'text',
						'id' => 'reference_post_id',
						'std' => '',
						'desc' => __( 'The post id for the reference.' ),
						'name' => __( 'Reference Id' ),
						'style' => ''
					),
				),
				'local_images' => true,
			),
			'source' => array(
				'id' => $this->taxonomies['source']['name'] . '_meta_box',
				'title' => 'Source Details',
				'pages' => array( $this->taxonomies['source']['name'] ),
				'context' => 'normal',
				'fields' => array(
					array(
						'type' => 'url',
						'id' => 'source_base_url',
						'std' => '',
						'desc' => __( 'The base URL for the source.' ),
						'name' => __( 'Base URL' ),
						'style' => '',
					),
					array(
						'type' => 'checkbox',
						'id' => 'source_is_local',
						'std' => false,
						'desc' => __( 'Indicates whether the source is local to the election.' ),
						'name' => __( 'Source Is Local' ),
						'style' => '',
					),
				),
				'local_images' => true,
			),
		);
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
		
		foreach ( $this->taxonomy_meta as $meta_config ) {
			$meta = new Tax_Meta_Class( $meta_config );
			$meta->Finish();
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
		add_meta_box( 
			$this->custom_post_meta['id'],
			$this->custom_post_meta['title'],
			array( $this, 'render_custom_post_meta_box' ),
			$this->custom_post_meta['post_type'], 
			$this->custom_post_meta['context'],
			$this->custom_post_meta['priority']
		);
	}
	
	// The call back to create the custom post meta box.
	function render_custom_post_meta_box( $post ) {
		$fields = $this->custom_post_meta['fields'];
		require plugin_dir_path( __FILE__ ) . 'partials/election-data-meta-box-display.php';
	}	
	
	function save_post_bulk_edit( ) {
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			wp_die();
		}
		
		$post_ids = empty( $_POST['post_ids'] ) ? '' : $_POST['post_ids'];
		if ( is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$post_type = get_post_type( $post_id );
								
				// check if the Custom Post post type
				if ( !$this->custom_post['name'] == $post_type ) {
					continue;
				}
				
				// check permissions
				if ( !current_user_can( 'edit_post', $post_id ) ) {
					continue;
				}
					
				foreach ( $this->custom_post_meta['fields'] as $field => $value ) {
					if ( !empty( $_POST['field_' . $field] ) ) {
						$new = $_POST['field_' . $field];
						update_post_meta( $post_id, $value['meta_id'], $new );
					}
				}
			}
		}
		
		wp_die();
	}
	
	// Stores the meta data from the meta box for the custom post.
	function save_custom_post_fields( $post_id, $post ) {		
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// check permissions
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
			
		foreach ( $this->custom_post_meta['fields'] as $field ) {
			if ( isset( $_POST[$field['id']] ) ) {
				$new = $_POST[$field['id']];
				update_post_meta( $post_id, $field['meta_id'], $new );
			}
		}
	}

	// Identifies the columns to display in the administrative interface.
	function define_columns( $columns ) {
		foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $this->custom_post_meta['fields'][$field]['label'];
		}
		
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
	
	// Fills the columns data.
	function populate_columns( $column ) {
		if ( isset( $this->custom_post_meta['admin_column_fields'][$column] ) ) {
			echo "<div id='$column-" . get_the_ID() . "'>";
			$field = $this->custom_post_meta['fields'][$column];
			$value = get_post_meta( get_the_ID(), $field['meta_id'], true );
			switch ( $field['type'] ) {
				case 'checkbox':
					echo $value ? 'X' : '';
					break;
				case 'url':
					echo "<a href='$value'>$value</a>";
					break;
				default:
					echo esc_html( $value );
			}
			echo '</div>';
		}
	}

	// Adds meta data to the custom box used when quick or bulk editting the custom post.
	function bulk_quick_edit_custom_box( $column_name ) {
		if ( isset( $this->custom_post_meta['admin_column_fields'][$column_name] ) ) {
			$field = $this->custom_post_meta['fields'][$column_name];-
			require plugin_dir_path( __FILE__ ) . 'partials/election-data-quick-edit-display.php' ;
		}
	}
	
	// Identifies the sortable columns in the administrative interface.
	function sort_columns( $columns ) {
		foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
			$columns[$field] = $field;
		}
		foreach ( $this->taxonomies as $taxonomy ) {
			$columns['taxonomy-' . $taxonomy['name']] = 'taxonomy-' . $taxonomy['name'];
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
	
	// allows meta data columns to be sorted.
	function column_orderby( $vars ) {
		if ( !is_admin() )
			return $vars;
		if ( isset( $vars['post_type'] ) && $this->custom_post['name'] == $vars['post_type'] && isset( $vars['orderby'] ) ) {
			foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
				if ( $this->custom_post_meta['fields'][$field]['meta_id'] == $vars['orderby'] ) {
					$vars = array_merge( $vars, array( 'meta_key' => $vars['orderby'], 'orderby' => 'meta_value' ) );
				}
			}
		}
		
		return $vars;
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
		
		if ( $current_screen->id == 'edit-' . $this->custom_post['name'] ) {
			wp_register_script( 'quick-edit-' . $this->custom_post['name'], plugin_dir_url( __FILE__ )  . 'js/quick-edit.js', array( 'jquery', 'inline-edit-post' ), '', true  );
			$translation_array = array();
			foreach ( $this->custom_post_meta['admin_column_fields'] as $field => $value ) {
				$translation_array[$field] = $this->custom_post_meta['fields'][$field]['id'];
			}
			
			wp_localize_script( 'quick-edit-' . $this->custom_post['name'], 'ed_quick_edit', $translation_array );
			
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
	
	// Update the Reference taxonomy, and return a array of id => reference name.
	function update_references() {
		// Get Top level Referenes.
		$args = array(
			'fields' => 'all',
			'hide_empty' => false,
			'parent' => 0,
		);
		$references = get_terms( $this->taxonomies['reference']['name'], $args ); 
		$parent_references = array( 'Party', 'Candidate' );
		$parent_ids = array();
		foreach ( $references as $reference ) {
			$parent_ids[$reference->name] = $reference->term_id;
		}
		
		// If a required reference is not there, create it.
		foreach ( $parent_references as $name ) {
			if ( !isset( $parent_ids[$name] ) ) {
				$ids = wp_insert_term( $name, $this->taxonomies['reference']['name'], array( 'parent' => 0 ) ); 
				$parent_ids[$name] = $ids['term_id'];
			}
		}
		
		// Get the Party references.
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_ids['Party'],
		);
		$party_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
		$references_by_party_id = array();
		foreach ( $party_reference_names_by_id as $id => $name ) {
			$references_by_party_id[get_tax_meta( $id, 'reference_post_id' )] = $id;
		}
		
		// Get the Parties.
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
		);
		$parties_by_id = get_terms( 'ed_candidates_party', $args );
		foreach ( $parties_by_id as $id => $name ) {
			if ( !isset( $references_by_party_id[$id] ) ) {
				// Add a party reference if it doesn't exist.
				$ids = wp_insert_term( $name, $this->taxonomies['reference']['name'], array( 'parent' => $parent_ids['Party'] ) );
				update_tax_meta( $ids['term_id'], 'reference_post_id', $id );
				$party_reference_names_by_id[$ids['term_id']] = $name;
				$references_by_party_id[$id] = $ids['term_id'];
			} elseif ( $party_reference_names_by_id[$references_by_party_id[$id]] != $name ) {
				// Update a party reference if it exists, but the name has changed.
				wp_update_term( $references_by_party_id[$id], $this->taxonomies['reference'][
			'name'], array( 'name' => $name ) );
				$party_reference_names_by_id[$references_by_party_id[$id]] = $name;
			}
		}
		
		// Get the candidate references.
		$args = array(
			'fields' => 'id=>name',
			'hide_empty' => false,
			'parent' => $parent_ids['Candidate'],
		);
		$candidate_reference_names_by_id = get_terms( $this->taxonomies['reference']['name'], $args );
		$references_by_candidate_id = array();
		foreach ( $candidate_reference_names_by_id as $id => $name ) {
			$references_by_candidate_id[get_tax_meta( $id, 'reference_post_id' )] = $id;
		}

		// Get the candidates.
		$args = array( 
			'post_type' => 'ed_candidates',
			'post_status' => 'publish',
		);
		
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$name = get_the_title();
			$id = get_the_ID();
			if ( !isset( $references_by_candidate_id[$id] ) ) {
				// Add a candidate reference if it doesn't exist.
				$ids = wp_insert_term( $name, $this->taxonomies['reference']['name'], array( 'parent' => $parent_ids['Candidate'] ) );
				update_tax_meta( $ids['term_id'], 'reference_post_id', $id );
				$candidate_reference_names_by_id[$ids['term_id']] = $name;
				$references_by_candidate_id[$id] = $ids['term_id'];
			} elseif ( $candidate_reference_names_by_id[$references_by_candidate_id[$id]] != $name ) {
				// Update a candidate reference if it exists, but the name has changed.
				wp_update_term( $references_by_candidate_id[$id], $this->taxonomies['reference']['name'], array( name => $name ) );
				$candidate_reference_names_by_id[$references_by_party_id[$id]] = $name;
			}
		}
		
		return $candidate_reference_names_by_id + $party_reference_names_by_id;
	}
	
	function get_news_feeds( $candidate )
	{
	}	
	
	function admin_init( $loader )
	{
		$loader->add_action( 'admin_init', $this, 'admin' );
		$loader->add_action( 'save_post_' . $this->custom_post['name'], $this, 'save_custom_post_fields', 10, 2 );
		$loader->add_action( 'wp_ajax_save_post_bulk_edit', $this, 'save_post_bulk_edit' );
		$loader->add_filter( 'manage_edit-' . $this->custom_post['name'] . '_columns', $this, 'define_columns' );
		$loader->add_action( 'manage_posts_custom_column', $this, 'populate_columns' );
		$loader->add_action( 'bulk_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_action( 'quick_edit_custom_box', $this, 'bulk_quick_edit_custom_box' );
		$loader->add_filter( 'manage_edit-' . $this->custom_post['name'] . '_sortable_columns', $this, 'sort_columns' );
		$loader->add_filter( 'posts_clauses', $this, 'taxonomy_clauses', 10, 2 );
		$loader->add_filter( 'request', $this, 'column_orderby' );
	    $loader->add_action( 'restrict_manage_posts', $this, 'filter_lists' );
		$loader->add_action( 'admin_enqueue_scripts', $this, 'setup_admin_scripts' );
		if ( isset( $this->custom_post['args']['remove_admin_column_date_filter'] ) ) {
			$loader->add_filter( 'months_dropdown_results', $this, 'remove_dates' );
		}
		
		if ( isset( $this->custom_post['args']['enter_title_here'] ) ) {
			$loader->add_filter( 'enter_title_here', $this, 'update_title' );
		}
		$loader->add_action( 'admin_footer', $this, 'update_references' );
	}
	
	function init( $loader )
	{
		$loader->add_action( 'init', $this, 'initialize' );
		$loader->add_filter( 'template_include', $this, 'include_template_function', 1 );
		$loader->add_filter( 'wp_enqueue_scripts', $this, 'setup_public_scripts' );
	}	
 }