<?php

/**
 * The file that defines movie review custom content
 *
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */

/**
 * The moview review custom post type.
 *
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Your Name <email@example.com>
 */
class Election_Data_Movie_Review {
	function create_movie_review() {
		register_post_type( 'movie_reviews',
			array(
				'labels' => array(
					'name' => 'Movie Reviews',
					'singular_name' => 'Movie Review',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New Movie Review',
					'edit' => 'Edit',
					'edit_item' => 'Edit Movie Review',
					'new_item' => 'New Movie Review',
					'view' => 'View',
					'view_item' => 'View Movie Review',
					'search_items' => 'Search Movie Reviews',
					'not_found' => 'No Movie Reviews found',
					'not_found_in_trash' => 'No Movie Reviews found in Trash',
					'parent' => 'Parent Movie Review'
				),
	 
				'public' => true,
				'menu_position' => 15,
				'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),
				'taxonomies' => array( '' ),
				'menu_icon' => plugins_url( 'images/image.png', dirname(__FILE__ )),
				'has_archive' => true
			)
		);
	}

	function my_admin() {
		add_meta_box( 'movie_review_meta_box',
			'Movie Review Details',
			array( $this, 'display_movie_review_meta_box'),
			'movie_reviews', 'normal', 'high'
		);
	}

	function display_movie_review_meta_box( $movie_review ) {
		// Retrieve current name of the Director and Movie Rating based on review ID
		$movie_director = esc_html( get_post_meta( $movie_review->ID, 'movie_director', true ) );
		$movie_rating = intval( get_post_meta( $movie_review->ID, 'movie_rating', true ) );
		?>
		<table>
			<tr>
				<td style="width: 100%">Movie Director</td>
				<td><input type="text" size="80" name="movie_review_director_name" value="<?php echo $movie_director; ?>" /></td>
			</tr>
			<tr>
				<td style="width: 150px">Movie Rating</td>
				<td>
					<select style="width: 100px" name="movie_review_rating">
					<?php
					// Generate all items of drop-down list
					for ( $rating = 5; $rating >= 1; $rating -- ) {
					?>
						<option value="<?php echo $rating; ?>" <?php echo selected( $rating, $movie_rating ); ?>>
						<?php echo $rating; ?> stars <?php } ?>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	function add_movie_review_fields( $movie_review_id, $movie_review ) {
		// Check post type for movie reviews
		if ( $movie_review->post_type == 'movie_reviews' ) {
			// Store data in post meta table if present in post data
			if ( isset( $_POST['movie_review_director_name'] ) && $_POST['movie_review_director_name'] != '' ) {
				update_post_meta( $movie_review_id, 'movie_director', $_POST['movie_review_director_name'] );
			}
			if ( isset( $_POST['movie_review_rating'] ) && $_POST['movie_review_rating'] != '' ) {
				update_post_meta( $movie_review_id, 'movie_rating', $_POST['movie_review_rating'] );
			}
		}
	}

	function include_template_function( $template_path ) {
		if ( get_post_type() == 'movie_reviews' ) {
			if ( is_single() ) {
				// checks if the file exists in the theme first,
				// otherwise serve the file from the plugin
				if ( $theme_file = locate_template( array ( 'single-movie_reviews.php' ) ) ) {
					$template_path = $theme_file;
				} else {
					$template_path = plugin_dir_path( __FILE__ ) . '/single-movie_reviews.php';
				}
			}
			elseif ( is_archive() ) {
				if ( $theme_file = locate_template( array ( 'archive-movie_reviews.php' ) ) ) {
					$template_path = $theme_file;
				} else { $template_path = plugin_dir_path( __FILE__ ) . '/archive-movie_reviews.php';
	 
				}
			}
		}
		return $template_path;
	}

	function create_my_taxonomies() {
		register_taxonomy(
			'movie_reviews_movie_genre',
			'movie_reviews',
			array(
				'labels' => array(
					'name' => 'Movie Genre',
					'add_new_item' => 'Add New Movie Genre',
					'new_item_name' => "New Movie Type Genre"
				),
				'show_ui' => true,
				'show_admin_column' => true,
				'show_tagcloud' => false,
				'hierarchical' => false
			)
		);
	}

	function my_columns( $columns ) {
		$columns['movie_reviews_director'] = 'Director';
		$columns['movie_reviews_rating'] = 'Rating';
		unset( $columns['comments'] );
		return $columns;
	}

	function populate_columns( $column ) {
		if ( 'movie_reviews_director' == $column ) {
			$movie_director = esc_html( get_post_meta( get_the_ID(), 'movie_director', true ) );
			echo $movie_director;
		}
		elseif ( 'movie_reviews_rating' == $column ) {
			$movie_rating = get_post_meta( get_the_ID(), 'movie_rating', true );
			echo $movie_rating . ' stars';
		}
	}

	function sort_me( $columns ) {
		$columns['movie_reviews_director'] = 'movie_reviews_director';
		$columns['movie_reviews_rating'] = 'movie_reviews_rating';
	 
		return $columns;
	}

	function column_orderby ( $vars ) {
		if ( !is_admin() )
			return $vars;
		if ( isset( $vars['orderby'] ) && 'movie_reviews_director' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array( 'meta_key' => 'movie_director', 'orderby' => 'meta_value' ) );
		}
		elseif ( isset( $vars['orderby'] ) && 'movie_reviews_rating' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array( 'meta_key' => 'movie_rating', 'orderby' => 'meta_value_num' ) );
		}
		return $vars;
	}

	function my_filter_list() {
		$screen = get_current_screen();
		global $wp_query;

		if ( $screen->post_type == 'movie_reviews' ) {
			error_log( print_r( $wp_query->query, true ) );

			wp_dropdown_categories( array(
				'show_option_all' => 'Show All Movie Genres',
				'taxonomy' => 'movie_reviews_movie_genre',
				'name' => 'movie_reviews_movie_genre',
				'orderby' => 'name',
				'selected' => ( isset( $wp_query->query['movie_reviews_movie_genre'] ) ? $wp_query->query['movie_reviews_movie_genre'] : '' ),
				'hierarchical' => false,
				'depth' => 3,
				'show_count' => false,
				'hide_empty' => true,
				'value_field' => 'slug',
			) );
		}
	}

	function perform_filtering( $query ) {
		$qv = &$query->query_vars;
		if ( ( array_key_exists('movie_reviews_movie_genre', $qv) ) && ( $qv['movie_reviews_movie_genre'] ) && is_numeric( $qv['movie_reviews_movie_genre'] ) ) {
			$term = get_term_by( 'id', $qv['movie_reviews_movie_genre'], 'movie_reviews_movie_genre' );
			$qv['movie_reviews_movie_genre'] = $term->slug;
		}
	}

	function init( $loader )
	{
		$loader->add_action( 'init', $this, 'create_movie_review' );
		$loader->add_action( 'init', $this, 'create_my_taxonomies', 0 );
	}
	function admin_init( $loader )
	{
		//$loader->add_action( 'admin_init', $this, 'my_admin' );
		//$loader->add_action( 'save_post', $this, 'add_movie_review_fields', 10, 2 );
		//$loader->add_filter( 'template_include', $this, 'include_template_function', 1 );
		//$loader->add_filter( 'manage_edit-movie_reviews_columns', $this, 'my_columns' );
		//$loader->add_action( 'manage_posts_custom_column', $this, 'populate_columns' );
		//$loader->add_filter( 'manage_edit-movie_reviews_sortable_columns', $this, 'sort_me' );
		//$loader->add_filter( 'request', $this, 'column_orderby' );
	    $loader->add_action( 'restrict_manage_posts', $this, 'my_filter_list' );
		//$loader->add_filter( 'parse_query', $this,'perform_filtering' );
	}
}