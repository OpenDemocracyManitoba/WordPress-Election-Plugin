<?php

$constituencies = get_root_constituencies();
$parties = get_parties_random();

get_header(); ?>

<div class="flow_it">
	<?php $summary = Election_Data_Option::get_option( 'summary' );
	if ( $summary ) : ?>
		<div class="one_column medium_row">
			<?php echo $summary; ?>
		</div>
	<?php endif;
	if ( $constituencies ) : ?>
		<div class="one_column medium_row">
			<h2><?php echo Election_Data_Option::get_option( 'constituency-label', 'Constituencies' ); ?></h2>
			<p class="small grey"><?php echo Election_Data_Option::get_option( 'constituency-subtext' ); ?></p>
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
	<?php endif;
	$facebook = esc_attr( Election_Data_Option::get_option( 'facebook-page' ) );
	$twitter = esc_attr( Election_Data_Option::get_option( 'twitter' ) );
	$google_plus_one = Election_Data_Option::get_option( 'google-plus-one' );
	if ( $facebook || $twitter || $google_plus_one ) : ?>
		<div class="one_column medium_row social">
			<?php if ( $facebook ) : ?>
				<div class="fb-page" data-href="<?php echo $facebook; ?>" data-width="275" data-height="255" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="false">
					<div class="fb-xfbml-parse-ignore">
						<blockquote cite="<?php echo $facebook; ?>">
							<a href="<?php echo $facebook; ?>">Manitoba Election</a>
						</blockquote>
					</div>
				</div>
			<?php endif;
			if ( $twitter ) : ?>
				<p><a href="http://twitter.com/<?php echo $twitter; ?>" class="twitter-follow-button">Follow @<?php echo $twitter; ?></a></p>
			<?php endif;
			if ( $google_plus_one ) : ?>
				<p><g:plusone></g:plusone></p>
			<?php endif; ?>
			
		</div>
	<?php endif;
	$news_count = Election_Data_Option::get_option( 'news-count-front', 10 );
	if ( $news_count ) : ?>
		<div class="one_column latest_news_small">
			<h2>Latest Election News</h2>
			<?php display_news_titles( null, true, $news_count ); ?>
		</div>
	<?php endif;
	if ( $parties ) : ?>
		<div class="two_columns">
			<h2><?php echo Election_Data_Option::get_option( 'party-label', 'The Political Parties' ); ?></h2>
			<p class="small grey"><?php echo Election_Data_Option::get_option( 'party-subtext' ); ?></p>
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
</div>
<?php get_footer(); ?>
