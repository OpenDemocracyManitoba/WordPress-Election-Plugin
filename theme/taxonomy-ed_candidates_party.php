<?php 

$party_id = get_queried_object()->term_id;
$party = get_party( $party_id );

$args = array(
	'post_type' => $candidate_name,
	'meta_query' => array(
		array(
			'key' => 'party_leader',
			'value' => true,
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

$candidate_references = array();

get_header(); ?>
<h2 class="title"><?php echo $party['long_title']; ?></h2>
<div class="flow_it">
	<div class="two_column_flow">
		<div class="flow_it">
			<div class="parties">
				<?php display_party( $party ); ?>	
			</div>
			<div class="politicians">
				<?php display_party_candidates( $leader_query, $party, $leader_references ); ?>
			</div>
		</div>
		<h3>The <?php echo $wp_query->post_count; ?> Candidates</h3>
		<p class="small grey" >Candidates are displayed alphabetically by constituency.</p>
		<div class="flow_it unshuffled_politicians">
			<?php display_party_candidates( $wp_query, $party, $candidate_references ); ?>
		</div>
	</div>
	<div class="one_column latest_news_small row_height_medium">
		<h2>Latest <?php echo $party['name']; ?> party news</h2>
		<p class="grey small">Recent articles that mention candidates from this party.</p>
		<?php display_news_titles( $candidate_references ); ?>
	</div>
	<div class="one_column latest_news_small row_height_medium">
		<h2 id="news">News that mentions the <?php echo $party['name']; ?> party leader</h2>
		<p class="grey small">News articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the party name.</p>
		<?php display_news_summaries( $leader_references, 'Party' ); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>