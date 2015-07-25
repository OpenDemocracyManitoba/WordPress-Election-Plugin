<?php

get_header(); 
$constituency_id = get_queried_object()->term_id; 
$args = array(
	'post_type' => 'ed_candidates',
	'tax_query' => array(
		array(
			'taxonomy' => 'ed_candidates_constituency',
			'terms' => array( $constituency_id ),
			'field' => 'term_id',
		),
	),
	'orderby' => 'rand',
);

$display_constituency = false;
$display_name = true;
$display_news = true;
$display_party = true;
$display_incumbent = 'name';

$the_query = new WP_Query( $args );
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates_constituency.php';
?>
<div id="primary">
    <div id="content" role="main">
		<?php while ( $the_query->have_posts() ) :
			$the_query->the_post();
			$candidate_id = get_the_ID();
			unset( $party_id );
			require plugin_dir_path( __FILE__ ) . 'ed_candidates.php';
			require plugin_dir_path( __FILE__ ) . 'ed_candidates_party.php';
			require plugin_dir_path( __FILE__ ) . 'ed_news_articles.php'; ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php require plugin_dir_path( __FILE__ ) . 'ed_candidate_details.php'; ?>
			</article>
		<?php endwhile ?>
	</div>
</div>
<?php get_footer(); ?>
