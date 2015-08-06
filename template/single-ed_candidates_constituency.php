<?php

require_once plugin_dir_path( __FILE__ ) . 'functions.php';

function display_candidates( $candidate_query, $constituency, &$references ) {
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

$constituency_id = get_queried_object()->term_id; 
$constituency = get_constituency( $constituency_id );
?>
<div id="container">
	<?php display_header();
	if ( $constituency['children'] ) : ?> 
		<div class='flow_it'>
			<?php if ( $constituency['map'] ) : ?>
				<div class='two_columns hidden_block_when_mobile'>
					<img alt='<?php echo $constituency['name']; ?>' class='highmap' src='<?php echo $constituency['map']; ?>' usemap='#constituency_map' />
					<map id="constituency_map" name="constituency_map">
						<?php foreach ( $constituency['children'] as $name => $child ) :?>
							<?php if ( $child['coordinates'] ) : ?>
								<area alt='<?php echo $name; ?>' coords='<?php echo $child['coordinates']; ?>' href='<?php echo $child['url']; ?>' shape='poly' title='<?php echo $name; ?>'>
							<?php endif; ?>
						<?php endforeach; ?>
					</map>
				</div>
			<?php endif; ?>
			<div class='one_column'>
				<h3>The <?php echo $constituency['name']; ?> Constituencies</h3>
				<ul>
					<?php foreach ( $constituency['children'] as $name => $child ) :?>
						<li><a href="<?php echo $child['url']; ?>"><?php echo $name; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
	   </div>
	<?php else :
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

		$candidate_query = new WP_Query( $args );

		?>
		<div id="main" role="main">
			<h2><?php echo $constituency['name']; ?></h2>
			<p>
				There are <?php echo $candidate_query->post_count; ?> candidates in this electoral division.
				<em>(Candidates are displayed in random order.)</em>
			</p>
			<div class="flow_it politicians">
				<?php display_candidates( $candidate_query, $constituency, $candidate_references ); ?>
			</div>
			<div class="flow_it" >
				<?php if ( !empty( $constituency['details'] ) ) : ?>
					<div class="two_columns constit_description">	
						<b><?php echo $constituency['name']; ?></b>
						<p><?php echo $constituency['details']; ?></p>
					</div>
					<div class="one_column latest_news_small">
				<?php else : ?>
					<div class="three_columns latest_news_small">
				<?php endif; ?>
					<h2>Latest Candidate News</h2>
					<p class="grey small">Recent articles that mention candidates from this race.</p>
					<br>
					<?php display_news_titles( get_news( $candidate_references ) ); ?>
				</div>
			</div>
		</div>

	<?php endif; ?>
</div>
<?php get_footer(); ?>
