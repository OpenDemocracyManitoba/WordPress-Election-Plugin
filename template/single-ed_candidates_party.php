<?php get_header(); 
require_once plugin_dir_path( __FILE__ ) . 'ed_functions.php';

$party_id = get_queried_object()->term_id;
$party = get_party( $party_id );

function display_candidates( $the_query, $article_id )
{
	global $party;
	
	while ( $the_query->have_posts() ) :
		$the_query->the_post();
		$candidate_id = get_the_ID();
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$news = get_news( $candidate_id ); 
		?>
		<article id="<?php echo $article_id; ?>-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php display_candidate( $candidate, $constituency, $party, $news, array( 'party' ), 'constituency' ); ?>
		</article>
	<?php endwhile; 
}

?>
<div id="primary">
    <div id="content" role="main">
		<article id="post-party-<?php echo $party_id; ?>" <?php post_class(); ?>>
			<?php display_party( $party ); ?>
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

		display_candidates($the_query, 'post-leader' );?>
		
		<?php unset( $args['meta_query'] );
		$the_query = new WP_Query( $args ); ?>
		
		<h3>The <?php echo $the_query->post_count; ?> Candidates</h3>
		<p class='small grey' >Candidates are displayed alphabetically by constituency.</p>
		<?php display_candidates( $the_query, 'post' );
		?>

	</div>
</div>
<?php get_footer(); ?>
