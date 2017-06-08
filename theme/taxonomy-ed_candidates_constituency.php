<?php

$constituency = get_constituency( $party_id = get_queried_object()->term_id );
$constituency_id = $constituency['id']; 

get_header();?>
<?php if ( $constituency['children'] ) : ?> 
	<h2>Select Your <?php echo $constituency['name']; ?> Constituency</h2>
	<p class="small grey hidden_block_when_mobile">Find by name or click the map.</p>
	<div class='flow_it'>
		<?php if ( $constituency['map_id'] ) : ?>
			<div class='two_columns hidden_block_when_mobile'>
				<?php echo wp_get_attachment_image($constituency['map_id'], 'map', false, array( 'alt' => $constituency['name'], 'usemap' => '#constituency_map', 'class' => 'highmap'	) ); ?>
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
	$candidate_references = array();
	?>
	<h2><?php echo $constituency['name']; ?></h2>
	<p>
		There are <?php echo $wp_query->post_count; ?> candidates in this electoral division.
		<em class="small grey">(Candidates are displayed in random order.)</em>
	</p>
	<div class="flow_it politicians">
		<?php display_constituency_candidates( $wp_query, $constituency, $candidate_references ); ?>
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
			<h2 id="news">Latest Candidate News</h2>
			<p class="grey small">Recent articles that mention candidates from this race.</p>
			<br>
			<?php $article_count = Election_Data_Option::get_option('news-count-constituency', 10);
			display_news_titles( $candidate_references, false, $article_count ); ?>
		</div>
	</div>

<?php endif; ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
