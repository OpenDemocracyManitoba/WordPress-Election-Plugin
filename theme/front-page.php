<?php

$constituencies = get_root_constituencies();
$parties = get_parties_random();

get_header(); ?>

<div class="flow_it">
	<div class="one_column medium_row">
		Put Customized data from options here.
	</div>
	<div class="one_column medium_row">
		<h2>The <?php echo "XXXX"; ?> Candidates</h2>
		<?php foreach ( $constituencies as $constituency_id ) :
			$constituency = get_constituency( $constituency_id ); ?>
			<div class="mini_maps">
				<p class="small"><a href="<?php echo $constituency['url']; ?>"><?php echo $constituency['name']; ?></a></p>
				<a href="<?php echo $constituency['url']; ?>" title="Click to see the candidates.">
					<?php echo wp_get_attachment_image($constituency['map_id'], 'map_thumb', false, array( 'alt' => $constituency['name'] ) ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="one_column medium_row social">
		Put Social Media from options here.
	</div>
	<div class="one_column latest_news_small">
		<h2>Latest Election News</h2>
		<?php display_news_titles( array(), true ); ?>
	</div>
	<?php if ( $parties ) : ?>
		<div class="two_columns">
			<h2>The Political Parties</h2>
			<p class="small grey"><?php echo "XXX" ?> Parties are displayed in random order.</p>
			<br>
			<div class="parties_thumb" >
				<?php foreach ( $parties as $party_id ) :
					$party = get_party( $party_id ); ?>
					<div class="party_thumb" >
						<p><a href="<?php echo $party['url']; ?>"><?php echo $party['name']; ?></a></p>
						<div>
						<a href="<?php echo $party['url']; ?>">
							<?php echo wp_get_attachment_image($party['logo_id'], 'party', false, array( 'alt' => "{$party['name']} Logo" ) ); ?>
						</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
<?php get_footer(); 