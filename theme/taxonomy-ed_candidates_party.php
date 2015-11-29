<?php 

$party = get_party( get_queried_object() );
$party_id = $party['id'];
global $ed_post_types;
global $ed_taxonomies;

$args = array(
	'post_type' => $ed_post_types['candidate'],
	'meta_query' => array(
		array(
			'key' => 'party_leader',
			'value' => true,
		),
	),
	'tax_query' => array(
		array(
			'taxonomy' => $ed_taxonomies['candidate_party'],
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
			<?php if ( $leader_query->post_count > 0 ) : ?>
				<div class="politicians">
					<?php display_party_candidates( $leader_query, $party, $leader_references ); ?>
				</div>
			<?php endif; ?>
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
	<?php if ( $leader_query->post_count > 0 ) : ?>
		<div class="one_column latest_news_small row_height_medium">
			<h2 id="news">News that mentions the <?php echo $party['name']; ?> party leader</h2>
			<p class="grey small">News articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the party name.</p>
			<?php display_news_summaries( $leader_references, 'Party' ); ?>
		</div>
	<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>