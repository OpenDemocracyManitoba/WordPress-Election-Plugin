<?php

get_header(); 
require_once plugin_dir_path( __FILE__ ) . 'ed_functions.php';
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

$the_query = new WP_Query( $args );

$constituency = get_constituency( $constituency_id );
?>
<div id="primary">
    <div id="content" role="main">
		<?php while ( $the_query->have_posts() ) :
			$the_query->the_post();
			$candidate_id = get_the_ID();
			$candidate = get_candidate( $candidate_id );
			$party = get_party_from_candidate( $candidate_id );
			$news = get_news( $candidate_id )?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php display_candidate( $candidate, $constituency, $party, $news, array( 'constituency' ), 'name' ); ?>
			</article>
		<?php endwhile ?>
	</div>
</div>
<?php get_footer(); ?>
