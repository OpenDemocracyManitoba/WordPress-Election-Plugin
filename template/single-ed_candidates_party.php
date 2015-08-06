<?php 
require_once plugin_dir_path( __FILE__ ) . 'functions.php';

function display_candidates( $candidate_query, $party, &$referebces )
{
	while ( $candidate_query->have_posts() ) {
		$candidate_query->the_post();
		$candidate_id = $candidate_query->post->ID;
		$candidate = get_candidate( $candidate_id );
		$constituency = get_constituency_from_candidate( $candidate_id );
		$referebces[] = $candidate['reference_id'];
		$candidate_news = get_news( $candidate['reference_id'] ); 
		display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'name', 'constituency', 'news' ), 'constituency' );
	}
}

$party_id = get_queried_object()->term_id;
$party = get_party( $party_id );

$args = array(
	'post_type' => $candidate_name,
	'meta_query' => array(
		array(
			'key' => 'party_leader',
			'value' => 'true',
		),
	),
	'tax_query' => array(
		array(
			'taxonomy' => $party_name,
			'terms' => array( $party_id ),
			'field' => 'term_id',
		),
	),
	'orderby' => 'name',
	'order' => 'ASC',
);

$leader_query = new WP_Query( $args );
$leader_references = array();

$args = array(
	'post_type' => $candidate_name,
	'tax_query' => array(
		array(
			'taxonomy' => $party_name,
			'terms' => array( $party_id ),
			'field' => 'term_id',
		),
	),
	// The candidate class sets up a filter on taxonomy-$constituency_name to sort the results by constituency name. 
	'orderby' => "taxonomy-$constituency_name",
	'order' => 'ASC',
);

$candidate_query = new WP_Query( $args );
$candidate_references = array();

?>
<div id="container">
	<?php display_header(); ?>
    <div id="main" role="main">
		<h2 class="title"><?php echo $party['long_title']; ?></h2>
		<div class="flow_it">
			<div class="two_column_flow">
				<div class="flow_it">
					<div class="parties">
						<?php display_party( $party ); ?>	
					</div>
					<div class="politicians">
						<?php display_candidates( $leader_query, $party, $leader_references ); ?>
					</div>
				</div>
				<h3>The <?php echo $candidate_query->post_count; ?> Candidates</h3>
				<p class="small grey" >Candidates are displayed alphabetically by constituency.</p>
				<div class="flow_it unshuffled_politicians">
					<?php display_candidates( $candidate_query, $party, $candidate_references ); ?>
				</div>
			</div>
			<div class="one_column latest_news_small row_height_medium">
				<a name="news"></a>
				<h2>Latest <?php echo $party['name']; ?> party news</h2>
				<p class="grey small">Recent articles that mention candidates from this party.</p>
				<?php display_news_titles( get_news( $candidate_references ) ); ?>
			</div>
			<div class="one_column latest_news_small row_height_medium">
				<h2>News that mentions the <?php echo $party['name']; ?> party leader</h2>
				<p class="grey small">News articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the party name.</p>
				<?php display_news_summaries( get_news( $leader_references ), $party['reference_id'] ); ?>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
