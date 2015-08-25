<?php
/**
 * Election Data functions and definitions
 *
 * @package Election_Data_Theme
 * @since Election_Data_Theme 1.0
 *
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Election_Data_Theme 1.0
 */
if ( ! isset( $content_width ) )
    $content_width = 654; /* pixels */

if ( ! function_exists( 'election_data_theme_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since Election_Data_Theme 1.0
 */
function election_data_theme_setup() {
 
    /**
     * Custom template tags for this theme.
     */
    require( get_template_directory() . '/inc/template-tags.php' );
 
    /**
     * Custom functions that act independently of the theme templates
     */
    require( get_template_directory() . '/inc/tweaks.php' );
 
    /**
     * Make theme available for translation
     * Translations can be filed in the /languages/ directory
     */
    load_theme_textdomain( 'election_data_theme', get_template_directory() . '/languages' );
 
}
endif; // election_data_theme_setup
add_action( 'after_setup_theme', 'election_data_theme_setup' );

/**
 * Enqueue scripts and styles
 *
 * @since Election_Data_Theme 1.0
 */
function election_data_theme_scripts() {
	global $constituency_name;
		
    wp_enqueue_style( 'style', get_stylesheet_uri() );
	if ( is_front_page() ) {
		wp_enqueue_script( 'facebook', get_template_directory_uri() . '/js/facebook.js' );
		wp_enqueue_script( 'twitter', get_template_directory_uri() . '/js/twitter.js' );
		wp_enqueue_script( 'google', 'https://apis.google.com/js/platform.js' );
	}
	
	if ( is_tax( $constituency_name ) ) {
		wp_enqueue_script( 'jquery-map-highlight', get_template_directory_uri() . '/js/jquery.maphilight.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'map-highlight', get_template_directory_uri() . '/js/map_highlight.js', array( 'jquery-map-highlight' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'election_data_theme_scripts' );

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since Election_Data_Theme 1.0
 */
function election_data_theme_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Primary Widget Area', 'election_data_theme' ),
        'id' => 'sidebar-1',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ) );
 
    register_sidebar( array(
        'name' => __( 'Secondary Widget Area', 'election_data_theme' ),
        'id' => 'sidebar-2',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ) );
}
add_action( 'widgets_init', 'election_data_theme_widgets_init' );

function election_data_init() {
	register_nav_menu('header-menu', __( 'Header Menu' ) );
}

add_action( 'init', 'election_data_init' );

$candidate_name = 'ed_candidates';
$party_name = "{$candidate_name}_party";
$constituency_name = "{$candidate_name}_constituency";
$news_article_name = 'ed_news_articles';
$reference_name = "{$news_article_name}_reference";
$source_name = "{$news_article_name}_source";

function get_constituency( $constituency_id, $get_extra_data = true ) {
	global $constituency_name;
	$all_terms = get_terms( $constituency_name, array( 'include' => $constituency_id, 'hide_empty' => false ) );
	$constituency_term = $all_terms[0];
	$results = array(
		'id' => $constituency_term->term_id,
		'name' => $constituency_term->name,
		'url' => get_term_link( $constituency_term, $constituency_name ),
		'reference' => get_post_meta( $constituency_term->term_id, 'reference' ),
	);
	if ( $get_extra_data ) {
		$results['details'] = get_tax_meta( $constituency_term->term_id, 'details' );
		$map_image = get_tax_meta( $constituency_term->term_id, 'map' );
		$results['map_id'] = $map_image ? $map_image['id'] : '';
		
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
		$constituency_term = $all_terms[0];
		return get_constituency( $constituency_term->term_id, false );
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
		'hide_empty' => 'false',
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
		'hide_empty' => 'false',
		'fields' => 'ids',
	);
	
	$terms = get_terms( $party_name , $args );
	shuffle( $terms );
	return $terms;
}	

function get_party( $party_id, $get_extra_data = true ) {
	global $party_name;
	$all_terms = get_terms( $party_name, array( 'include' => $party_id, 'hide_empty' => false ) );
	$party_term = $all_terms[0];
	
	$results = array(
		'name' => $party_term->name,
		'colour' => get_tax_meta( $party_id, 'colour' ),
		'url' => get_term_link( $party_term, $party_name ),
		'long_title' => $party_term->description,
		'reference_id' => get_tax_meta( $party_id, 'reference' ),
	);
		
	if ( $get_extra_data ) {
		$party_logo = get_tax_meta( $party_id, 'logo' );
		$results['logo_id'] = $party_logo ? $party_logo['id'] : '';//Election_Data_Options::get_option( 'missing_party' );

		$results['website'] = get_tax_meta( $party_id, 'website' );
		$results['phone'] = get_tax_meta( $party_id, 'phone' );
		$results['address'] = get_tax_meta( $party_id, 'address' );
		$results['icon_data'] = array();
		$results['reference'] = get_tax_meta( $party_id, 'conference' );
		foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
			$value = get_tax_meta( $party_id, $icon_type );
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
				
			$src = get_template_directory_uri() . "/images/$alt.jpg";
			$results['icon_data'][$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => ucfirst( $alt ) );
		}
	}
	
	return $results;
}

function get_party_from_candidate( $candidate_id ) {
	global $party_name;
	$all_terms = get_the_terms( $candidate_id, $party_name );
	if ( isset( $all_terms[0] ) ) {
		$party_term = $all_terms[0];
		$party_id = $party_term->term_id;
		return get_party( $party_id, false );
	} else {
		return array(
			'name' => '',
			'colour' => '0x000000',
			'url' => '',
			'long_title' => '',
			'reference_id' => '',
		);
	}
}

function get_candidate( $candidate_id ) {
	$image_id = get_post_thumbnail_id( $candidate_id );
	$image_id = $image_id ? $image_id : ''; //Election_Data_Options::get_option( 'missing_candidate' );
	
	$icon_data = array();
	foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
		$value = get_post_meta( $candidate_id, $icon_type, true );
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

			$alt = $icon_type . '_active';
		} else {
			$url = '';
			$alt = $icon_type . '_inactive';
		}
			
		$src = get_template_directory_uri() . "/images/$alt.jpg";
		$icon_data[$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => ucfirst( $alt ) );
	}

	return array(
		'image_id' => $image_id,
		'icon_data' => $icon_data,
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

function display_news_titles ( $reference_ids = null, $show_more = false, $count = 20, $pagination = false ) {
	global $source_name, $reference_name, $party_name, $candidate_name, $news_article_name;
	$news = get_news( $reference_ids, 1, $count );
	$articles = $news['articles'];
	news_titles( $articles, $show_more, $reference_ids );
}

function news_titles( $article_query, $paging_type, $reference_ids = array(), $paging_args = array() ) {
	global $reference_name, $news_article_name;
	$last_date = '';
	if ( $article_query->have_posts() ) :
		while ( $article_query->have_posts() ) :
			$article_query->the_post();
			$article_id = $article_query->post->ID;
			$date = get_the_date( 'l, j, F Y', $article_id );
			if ( $date != $last_date ) :
				if ( $last_date != '' ) : ?>
					</ul>
				<?php endif;
				$last_date = $date; ?>
				<h4><?php echo $date; ?></h4>
				<ul class="news">
			<?php endif;
			$references = wp_get_post_terms( $article_id, $reference_name );
			$mentions = array();
			foreach ( $references as $reference ) :
				if ( $reference_ids && ! in_array( $reference->term_id, $reference_ids ) ) {
					continue;
				}
				$reference_id = get_tax_meta( $reference->term_id, 'reference_post_id' );
				$parent = get_term( $reference->parent, $reference_name );
				switch ( $parent->name ) :
					case 'Party':
						$url = get_term_link( get_term( $reference_id, $reference ), $party_name );
						break;
					case 'Candidate':
						$url = get_permalink( get_post( $reference_id ) );
						break;
					default:
						$url = '';
				endswitch;
				$name = esc_attr( $reference->name );
				$mentions[] = "<a href='$url'>$name</a>";
			endforeach; ?>
			<li>
				<p class="link"><a href="<?php echo esc_attr( get_post_meta( $article_id, 'url', true ) ); ?>"><?php echo get_the_title( $article_id ); ?></a></p>
				<p class="mentions">Mentions:
				<?php echo implode (', ', $mentions); ?>
				</p>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php if ( $paging_type === true ) : ?>
			<p class="more"><a href="<?php echo get_post_type_archive_link( $news_article_name ); ?>">More News...</a></p>
		<?php elseif ( $paging_type ) :
			$page = get_current_page( $paging_type );
			display_news_pagination( get_paging_args( $paging_type, $page ) );
		endif;
	else : ?>
		<em>No articles found yet.</em>
	<?php endif;
}


function display_news_pagination( $args ) {
	$default_args = array(
		'mid_size' => 1,
	);

	$args = wp_parse_args( $args, $default_args );

	echo paginate_links( $args );
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

function display_news_summaries ( $reference_ids, $type, $count = 20 ) {
	$articles_per_page = 20;
	$page = get_current_page( $type );
	$args = get_paging_args( $type, $page );
	$args['add_fragment'] = "#news";

	if ( ! is_array( $reference_ids ) ) {
		$reference_ids = array( $reference_ids );
	}
	global $source_name;
	$news = get_news( $reference_ids, $page, $articles_per_page );
	$articles = $news['articles'];

	$args['total'] = round( $news['count'] / $articles_per_page );
	if ( $articles->have_posts() ) :
		if ( $news['count'] > $articles_per_page ) {
			display_news_pagination( $args );
		} ?>
		<?php while ( $articles->have_posts() ) :
			$articles->the_post();
			$article = $articles->post;
			$summaries = get_post_meta( $article->ID, 'summaries', true );
			foreach ( $reference_ids as $reference_id ) :
				if( empty( $summaries[$reference_id] ) ){
					continue;
				}
				$summary = $summaries[$reference_id];
				$sources = wp_get_post_terms( $article->ID, $source_name );
				$source = $sources[0];
				$source_label = esc_html( $source->description ? $source->description : $source->name ); ?>
				<div class="news-article">	
					<h3><a href="<?php echo esc_attr( get_post_meta( $article->ID, 'url', true ) ); ?>"><?php echo get_the_title( $article->ID ); ?></a></h3>
					<p class="date"><?php echo get_the_date( 'l, j F Y', $article->ID ); ?></p>
					<p class="summary" >
						<em><?php echo $source_label; ?></em>
						- <?php echo $summary; ?>
					</p>
				</div>
			<?php endforeach;
		endwhile;
	else : ?>
		<em>No articles found yet.</em>
	<?php endif;
}

function display_party( $party ) {
	?>
	<div class="party">
		<div class="image" style="border-bottom: 8px solid <?php echo esc_attr( $party['colour'] ); ?>">
			<?php echo wp_get_attachment_image($party['logo_id'], 'party', false, array( 'alt' => "{$party['name']} Logo" ) ); ?>
		</div>
		<div class="name" >
			<?php echo $party['name']; ?>
		</div>
		<div class="website <?php echo $party['website'] ? '' : 'hidden'; ?>" >
			<a href="<?php echo esc_attr( $party['website'] ); ?>">Party Website</a>
		</div>
		<div class="icons">
		<?php foreach ( $party['icon_data'] as $icon ) : ?>
			<?php if ( $icon['url'] ) : ?>
				<a href="<?php echo esc_attr( $icon['url'] ); ?>">
			<?php endif; ?>
			<img alt="<?php echo esc_attr( $icon['alt'] ); ?>" src="<?php echo esc_attr( $icon['src'] ); ?>" />
			<?php if ( $icon['url'] ): ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
		<div class="phone <?php echo $party['phone'] ? '' : 'hidden'; ?>">
			<?php echo esc_html( $party['phone'] ); ?>
		</div>
		<div class="address" <?php echo $party['address'] ? '' : 'hidden'; ?>>
			<?php echo esc_html( $party['address'] ); ?>
		</div>
		<div class="news">
			News: <a href="<?php echo esc_attr( $party['url'] ); ?>#news">The Latest <?php echo esc_html( $party['name'] ); ?> News</a>
		</div>
	</div>
<?php }

function display_candidate( $candidate, $constituency, $party, $news, $show_fields=array(), $incumbent_location='name' ) {
	$display_name = in_array( 'name', $show_fields );
	$display_party = in_array('party', $show_fields );
	$display_constituency = in_array( 'constituency', $show_fields );
	$display_news = in_array( 'news', $show_fields );
	
	?><div class="politician show_constituency">
		<div class="image" style="border-bottom: 8px solid <?php echo esc_attr( $party['colour'] ); ?>;">
			<?php echo wp_get_attachment_image($candidate['image_id'], 'candidate', false, array( 'alt' => $candidate['name'] ) ); ?>
			<?php if ( $candidate['party_leader'] ) :?>
			<div class="leader">party leader</div>
			<?php endif; ?>
		</div>
		<div class="constituency <?php echo $display_constituency ? '' : 'hidden'; ?>">
			<a href="<?php echo $constituency['url']; ?>"><?php echo esc_html( $constituency['name'] ); ?></a>
			<?php if ( $candidate['incumbent_year'] && $incumbent_location == 'constituency') : ?>
				<div class="since">Incumbent since <?php echo esc_html( $candidate['incumbent_year'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="name <?php echo $display_name ? '' : 'hidden'; ?>">
			<strong><a href="<?php echo $candidate['url'] ?>"><?php echo esc_html( $candidate['name'] ); ?></a></strong>
			<?php if ( $candidate['incumbent_year'] && $incumbent_location == 'name' ) : ?>
				<div class="since">Incumbent since <?php echo esc_html( $candidate['incumbent_year'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="election-website <?php echo $candidate['website'] ? '': 'hidden'; ?>">
			<a href="<?php echo esc_html( $candidate['website'] ); ?>">Election Website</a>
		</div>
		<div class="icons">
			<?php foreach ( $candidate['icon_data'] as $icon ) :
				if ( $icon['url'] ) : ?>
					<a href="<?php echo esc_attr( $icon['url'] ); ?>">
				<?php endif; ?>
				<img alt="<?php echo esc_attr( $icon['alt'] ); ?>" src="<?php echo esc_attr( $icon['src'] ); ?>" />
				<?php if ( $icon['url'] ): ?>
					</a>
				<?php endif;
			endforeach; ?>
		</div>
		<div class="news <?php echo $display_news ? '' : 'hidden'; ?>">News: <a href="<?php echo "{$candidate['url']}#news"; ?>"><?php echo esc_html( $news['count'] ); ?> Related Articles</a></div>
		<div class="candidate-party <?php echo $display_party ? '' : 'hidden' ?>">Political Party: <a href="<?php echo $party['url']; ?>"><?php echo esc_html( $party['name'] ); ?></a></div>
		<div class="phone <?php echo $candidate['phone'] ? '' : 'hidden' ?>">Phone: <?php echo esc_html( $candidate['phone'] ); ?></div>
	</div>
<?php }

function display_all_candidates( $candidate_query )
{
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$party  = get_party_from_candidate( $candidate_id );
		$candidate_news = get_news( $candidate['reference_id'], 1, 1 );
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'constituency', 'news' ), 'name' );
	}
}

function display_party_candidates( $candidate_query, $party, &$references )
{
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$references[] = $candidate['reference_id'];
		$candidate_news = get_news( $candidate['reference_id'], 1, 1 ); 
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'constituency', 'news' ), 'constituency' );
	}
}

function display_constituency_candidates( $candidate_query, $constituency, &$references ) {
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$party = get_party_from_candidate( $candidate_id );
		$references[] = $candidate['reference_id'];
		$candidate_news = get_news( $candidate['reference_id'], 1, 1 );
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'news' ), 'name' );
	}
}