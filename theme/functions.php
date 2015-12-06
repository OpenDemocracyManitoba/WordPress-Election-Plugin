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
	global $ed_taxonomies;
		
    wp_enqueue_style( 'style', get_stylesheet_uri() );
	if ( is_front_page() ) {
		wp_enqueue_script( 'facebook', get_template_directory_uri() . '/js/facebook.js' );
		wp_enqueue_script( 'twitter', 'http://platform.twitter.com/widgets.js' );
		wp_enqueue_script( 'google', 'https://apis.google.com/js/platform.js' );
	}
	
	if ( is_tax( $ed_taxonomies['candidate_constituency'] ) ) {
		wp_enqueue_script( 'jquery-map-highlight', get_template_directory_uri() . '/js/jquery.maphilight.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'map-highlight', get_template_directory_uri() . '/js/map_highlight.js', array( 'jquery-map-highlight' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'election_data_theme_scripts' );

function configure_menu() {
	$menu_name = 'Election Data Navigation Menu';
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( $menu ) {
		$locations = get_theme_mod( 'nav_menu_locations' );
		if ( empty( $locations['header-menu'] ) ) {
			$locations['header-menu'] = $menu->term_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
}

add_action( 'after_switch_theme', 'configure_menu' );

function election_data_init() {
	register_nav_menu('header-menu', __( 'Header Menu' ) );
	add_theme_support( 'custom-header' );
}

add_action( 'init', 'election_data_init' );

function display_news_titles ( $candidate_ids = null, $show_more = false, $count = 20, $pagination = false ) {
	$news = get_news( $candidate_ids, 1, $count );
	$articles = $news['articles'];
	news_titles( $articles, $show_more, $candidate_ids );
}

function news_titles( $article_query, $paging_type, $candidate_ids = null, $paging_args = array() ) {
	global $ed_post_types, $ed_taxonomies;
	$last_date = '';
	if ( $article_query->have_posts() ) :
		$date_format = get_option( 'date_format' );
		while ( $article_query->have_posts() ) :
			$article_query->the_post();
			$article_id = $article_query->post->ID;
			$date = get_the_date( $date_format, $article_id );
			if ( $date != $last_date ) :
				if ( $last_date != '' ) : ?>
					</ul>
				<?php endif;
				$last_date = $date; ?>
				<h4><?php echo $date; ?></h4>
				<ul class="news">
			<?php endif;
			$candidates = wp_get_post_terms( $article_id, $ed_taxonomies['news_article_candidate'] );
			$news_article_candidate_ids = array();
			foreach ( $candidates as $candidate ) :
				$news_article_candidate_ids[] = $candidate->term_id;
			endforeach;
			$args = array( 
				'post_type' => $ed_post_types['candidate'],
				'meta_query' => array(
					array(
						'key' => 'news_article_candidate_id',
						'value' => $news_article_candidate_ids,
						'compare' => 'IN'
					),
				),
			);
			$mentions = array();
			$query = new WP_Query( $args );
			while ( $query->have_posts() ) :
				$query->the_post();
				$url = get_permalink( $query->post );
				$name = esc_attr( get_the_title( $query->post ) );
				$mentions[] = "<a href='$url'>$name</a>";
			endwhile; ?>
			<li>
				<p class="link"><a href="<?php echo esc_attr( get_post_meta( $article_id, 'url', true ) ); ?>"><?php echo get_the_title( $article_id ); ?></a></p>
				<p class="mentions">Mentions:
				<?php echo implode (', ', $mentions); ?>
				</p>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php if ( $paging_type === true ) : ?>
			<p class="more"><a href="<?php echo get_post_type_archive_link( $ed_post_types['news_article'] ); ?>">More News...</a></p>
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


function display_news_summaries ( $candidate_ids, $type, $count = 20 ) {
	$articles_per_page = 20;
	$page = get_current_page( $type );
	$args = get_paging_args( $type, $page );
	$args['add_fragment'] = "#news";

	if ( ! is_array( $candidate_ids ) ) {
		$candidate_ids = array( $candidate_ids );
	}
	global $ed_taxonomies;
	$news = get_news( $candidate_ids, $page, $articles_per_page );
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
			$date_format = get_option( 'date_format' );
			foreach ( $candidate_ids as $candidate_id ) :
				if( empty( $summaries[$candidate_id] ) ){
					continue;
				}
				$summary = $summaries[$candidate_id];
				$sources = wp_get_post_terms( $article->ID, $ed_taxonomies['news_article_source'] );
				$source = $sources[0];
				$source_label = esc_html( $source->description ? $source->description : $source->name ); ?>
				<div class="news-article">	
					<h3><a href="<?php echo esc_attr( get_post_meta( $article->ID, 'url', true ) ); ?>"><?php echo get_the_title( $article->ID ); ?></a></h3>
					<p class="date"><?php echo get_the_date( $date_format, $article->ID ); ?></p>
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

function display_news_article( $article, $candidates = false ){
	$date_format = get_option( 'date_format' );
	if ( is_array( $candidates ) ) {
		$summary = $articles['summaries'][$candidate['id']];
		$mention_data = array();
		$summaries = array();
		foreach ( $candidates as $candidate ) {
			if ( isset( $articles['mentions'][$candidate['id']] ) ) {
				$mention_data[$candidate['id']] = $articles['mentions'][$candidate['id']];
			}
			if ( isset( $articles['summaries'][$candidate['id']] ) ) {
				$summaries[$candidate['id']] = $articles['summaries'][$candidate['id']];
			}
		}
		
		if ( $summaries ) {
			$summary = $summaries[array_rand( $summaries )];
		} else {
			$summary = $article['summary'];
		}
	} else {
		$summary = $article['summary'];
		$mention_data = $candidates ? $article['mentions'] : array();
	}
	
	$mentions = array();
	foreach ( $mention_data as $mention ) {
		$mentions[] = "<a href='{$mention['url']}'>{$mention['name']}</a>";
		}
	?>
	<div class="news_article">
		<h3><a href="<?php echo $article['url']; ?>"><?php echo $article['title'] ?></a></h3>
		<p class="date"><?php echo get_the_date( $date_format, $article['id'] ); ?></p>
		<p class="summary"><em><?php echo $article['source_name']; ?></em> - <?php echo $summary ?></p>
		<?php if ( $mentions ) : ?>
			<p class="mentions">Mentions: <?php echo implode (', ', $mentions); ?></p>
		<?php endif; ?>
	</div>
<?php }

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
			<img alt="<?php echo esc_attr( $icon['alt'] ); ?>" src="<?php echo esc_attr( get_template_directory_uri() . "/images/{$icon['type']}.jpg" ); ?>" />
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
				<img alt="<?php echo esc_attr( $icon['alt'] ); ?>" src="<?php echo esc_attr( get_template_directory_uri() . "/images/{$icon['type']}.jpg" ); ?>" />
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

function display_search_results( $search_query ) {
	global $ed_post_types;
	while ( $search_query->have_posts() ) {
		$search_query->the_post();
		switch ( $search_query->post->post_type ) {
			case $ed_post_types['candidate']:
				$candidate_id = $search_query->post->ID;
				$candidate = get_candidate( $candidate_id );
				$constituency = get_constituency_from_candidate( $candidate_id );
				$party  = get_party_from_candidate( $candidate_id );
				$candidate_news = get_news( $candidate['news_article_candidate_id'], 1, 1 );
				display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'constituency', 'news' ), 'name' );
				break;
			case $ed_post_types['news_article']:
				//$news_article = get_news_article( $search_query->post );
				//display_news_article( $news_article );
				break;
		}
	}
}

function display_all_candidates( $candidate_query ) {
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$party  = get_party_from_candidate( $candidate_id );
		$candidate_news = get_news( $candidate['news_article_candidate_id'], 1, 1 );
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'constituency', 'news' ), 'name' );
	}
}

function display_party_candidates( $candidate_query, $party, &$candidates ) {
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$candidates[] = $candidate['news_article_candidate_id'];
		$candidate_news = get_news( $candidate['news_article_candidate_id'], 1, 1 ); 
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'constituency', 'news' ), 'constituency' );
	}
}

function display_constituency_candidates( $candidate_query, $constituency, &$candidates ) {
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$party = get_party_from_candidate( $candidate_id );
		$candidates[] = $candidate['news_article_candidate_id'];
		$candidate_news = get_news( $candidate['news_article_candidate_id'], 1, 1 );
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'party', 'news' ), 'name' );
	}
}