<?php get_header(); 
$party_id = get_queried_object()->term_id;
require_once plugin_dir_path( __FILE__ ) . 'ed_candidates_party.php'; 

$display_constituency = true;
$display_name = true;
$display_news = true;
$display_party = false;
$display_incumbent = 'constituency';

?>

<div id="primary">
    <div id="content" role="main">
		<article id="post-party-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php require plugin_dir_path( __FILE__ ) . 'ed_candidates_party_details.php'; ?>
		</article>
		<?php $args = array(
			'post_type' => 'ed_candidates',
			'meta_query' => array(
				array(
					'key' => 'party_leader',
					'value' => 'true',
				),
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'ed_candidates_party',
					'terms' => array( $party_id ),
					'field' => 'term_id',
				),
			),
			'orderby' => 'name',
			'order' => 'ASC',
		);
		$the_query = new WP_Query( $args );
		while ($the_query->have_posts() ) :
			$the_query->the_post();
			$candidate_id = get_the_ID();
			unset( $constituency_id );
			require plugin_dir_path( __FILE__ ) . 'ed_candidates.php';
			require plugin_dir_path( __FILE__ ) . 'ed_candidates_constituency.php';
			require plugin_dir_path( __FILE__ ) . 'ed_news_articles.php'; ?>
			<article id="post-leader-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php require plugin_dir_path( __FILE__ ) . 'ed_candidate_details.php'; ?>
			</article>
		<?php endwhile ?>
			
		
		<?php $args = array(
			'post_type' => 'ed_candidates',
			'tax_query' => array(
				array(
					'taxonomy' => 'ed_candidates_party',
					'terms' => array( $party_id ),
					'field' => 'term_id',
				),
			),
			'orderby' => 'name',
			'order' => 'ASC',
		);

		$the_query = new WP_Query( $args );

		while ( $the_query->have_posts() ) :
			$the_query->the_post();
			$candidate_id = get_the_ID();
			unset( $constituency_id );
			require plugin_dir_path( __FILE__ ) . 'ed_candidates.php';
			require plugin_dir_path( __FILE__ ) . 'ed_candidates_constituency.php';
			require plugin_dir_path( __FILE__ ) . 'ed_news_articles.php'; ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php require plugin_dir_path( __FILE__ ) . 'ed_candidate_details.php'; ?>
			</article>
		<?php endwhile ?>
	</div>
</div>
<?php get_footer(); ?>
