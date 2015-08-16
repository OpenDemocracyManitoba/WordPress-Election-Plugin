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
    wp_enqueue_style( 'style', get_stylesheet_uri() );
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

$candidate_name = 'ed_candidates';
$party_name = "{$candidate_name}_party";
$constituency_name = "{$candidate_name}_constituency";
$news_article_name = 'ed_news_articles';
$reference_name = "{$news_article_name}_reference";
$source_name = "{$news_article_name}_source";

function get_constituency( $constituency_id, $get_extra_data = true ) {
	global $constituency_name;
	$all_terms = get_terms( $constituency_name, array( 'include' => $constituency_id, 'hide_empty' => false ) );
	$constituency_terms = $all_terms[0];
	$results = array(
		'id' => $constituency_terms->term_id,
		'name' => $constituency_terms->name,
		'url' => get_term_link( $constituency_id, $constituency_name ),
		'reference' => get_post_meta( $constituency_terms->term_id, 'reference' ),
	);
	if ( $get_extra_data ) {
		$results['details'] = get_tax_meta( $constituency_terms->term_id, 'details' );
		$map_image = get_tax_meta( $constituency_terms->term_id, 'map' );
		if ( $map_image )
		{
			$results['map'] = $map_image['url'];
		}
		else
		{
			$results['map'] = '';
		}
		
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
		$constituency_terms = $all_terms[0];
		return get_constituency( $constituency_terms->term_id, false );
	} else {
		return  array(
			'id' => 0,
			'name' =>'',
			'url' => '',
			'reference' => '',
		);
	}
}

function get_party( $party_id, $get_extra_data = true ) {
	global $party_name;
	$all_terms = get_terms( $party_name, array( 'include' => $party_id, 'hide_empty' => false ) );
	$party_terms = $all_terms[0];
	
	$results = array(
		'name' => $party_terms->name,
		'colour' => get_tax_meta( $party_id, 'colour' ),
		'url' => get_term_link( $party_id, $party_name ),
		'long_title' => $party_terms->description,
		'reference_id' => get_tax_meta( $party_id, 'reference' ),
	);
		
	if ( $get_extra_data ) {
		$party_logo = get_tax_meta( $party_id, 'logo' );
		if ( $party_logo && $party_logo['url'] ) {
			$results['logo_url'] = $party_logo['url'];
		} else {
			$results['logo_url'] = plugins_url( 'images/missing_party.jpg', __FILE__ );
		}
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
		$party_terms = $all_terms[0];
		$party_id = $party_terms->term_id;
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
	if ( $image_id ) {
		$image_url = wp_get_attachment_url( $image_id );
	} else {
		$image_url = plugins_url( 'images/missing_candidate.jpg', __FILE__ );
	}

	
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
		'image_url' => $image_url,
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

function get_news( $reference_id, $page = 0, $articles_per_page = 20 ) {
	global $news_article_name, $reference_name;
	$args = array(
		'post_type' => $news_article_name,
		'post_status' => 'publish',
		'tax_query' => array(
			array(
				'taxonomy' => $reference_name,
				'field' => 'term_id',
				'terms' => $reference_id,
			),
		),
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
	
	$news_query = new WP_Query( $args ); 
	return array(
		'count' => $news_query->found_posts,
		'articles' => $news_query,
		'reference_id' => $reference_id,
	);
}


function display_news_titles ( $reference_ids ) {
	global $source_name, $reference_name, $party_name, $candidate_name;
	$news = get_news( $reference_ids );
	$articles = $news['articles'];
	$last_date = '';
	if ( $articles->have_posts() ) :
		while ( $articles->have_posts() ) :
			$articles->the_post();
			$article_id = $articles->post->ID;
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
				if ( ! in_array( $reference->term_id, $reference_ids ) ) {
					continue;
				}
				$reference_id = get_tax_meta( $reference->term_id, 'reference_post_id' );
				switch ( get_tax_meta( $reference->parent, 'reference_post_id' ) ) :
					case $party_name:
						$url = get_term_link( $reference_id, $party_name );
						break;
					case $candidate_name:
						$url = get_permalink( $reference_id );
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
	<?php else : ?>
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

function display_news_summaries ( $reference_ids, $type ) {
	$articles_per_page = 20;
	switch ( $type ) {
		case 'Candidate':
			$page = get_query_var( 'page' );
			$args = array(
				'current' => $page ? $page : 1,
				'format' =>'?page=%#%',
				'add_fragment' => "#news",
			);
			break;
		case 'Party':
			$page = get_query_var( 'paged' );
			$args = array(
				'current' => $page ? $page : 1,
				'add_fragment' => "#news",
			);
			break;
	}
	
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
			<img alt="<?php echo $party['name']; ?> Logo" src="<?php echo esc_attr( $party['logo_url'] ); ?>"/>
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
			<img alt="<?php echo $candidate['name'] ?>" src="<?php echo esc_attr( $candidate['image_url'] ); ?>" />
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
		<div class="news <?php echo $display_news ? '' : 'hidden'; ?>">News: <a href="<?php echo $candidate['url']; ?>"><?php echo esc_html( $news['count'] ); ?> Related Articles</a></div>
		<div class="candidate-party <?php echo $display_party ? '' : 'hidden' ?>">Political Party: <a href="<?php echo $party['url']; ?>"><?php echo esc_html( $party['name'] ); ?></a></div>
		<div class="phone <?php echo $candidate['phone'] ? '' : 'hidden' ?>">Phone: <?php echo esc_html( $candidate['phone'] ); ?></div>
	</div>
<?php }


function display_party_candidates( $candidate_query, $party, &$references )
{
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$references[] = $candidate['reference_id'];
		$candidate_news = get_news( $candidate['reference_id'] ); 
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
		$candidate_news = get_news( $candidate['reference_id'] );
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'news' ), 'name' );
	}
}

