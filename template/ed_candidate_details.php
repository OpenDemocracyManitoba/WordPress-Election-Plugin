<?php
/* Assunes that ed_candidate.php, ed_candidate_party.php, and ed_candidate_constituency.php have already been included.
   Requires $display_constituency, $display_name, $display_party, $display_news and $display_incumbent to be defined.
	  - These toggle the display of the differency sections.
	  - $display_incumbent toggles the location of the incumbent message. if it is set to 'constituency' or 'name', it adds the message to the corresponding location. if set to anything else, the message does not display.
	  - All of the others are true / false to indicate whether to display the section or not.
*/
?>
<div class='politician'>
	<?php if ( $display_constituency ) : ?>
		<div class='constituency'>
			<a href="<?php echo $constituency_url; ?>"><?php echo $constituency; ?></a>
			<?php if ( $incumbent_year && $display_incumbent == 'constituency') : ?>
				<p class='since'>Incumbent since <?php echo $incumbent_year; ?></p>
			<?php endif ?>
		</div>
	<?php endif ?>
	<div class='image' style='border-bottom: 8px solid <?php echo $party_colour ?>;'>
		<img alt='<?php echo $name ?>' src='<?php echo $image_url ?>' />
		<?php if ( $party_leader ) :?>
			<p>party leader</p>
		<?php endif ?>
	</div>
	<?php if ($display_name ) : ?>
		<p class='name'><strong><a href='<?php echo $candidate_url ?>'><?php echo $name; ?></a></strong>
		<?php if ( $incumbent_year && $display_incumbent == 'name' ) : ?>
			<p class='since'>Incumbent since <?php echo $incumbent_year; ?></p>
		<?php endif ?>
		</p>
	<?php endif ?>
	<?php if ( $website ) : ?>
		<p class='election_website'>
			<a href='<?php echo $website ?>'>Election Website</a>
		</p>
	<?php endif ?>
	<p class='icons'>
		<?php foreach ( $icon_data as $icon ) : ?>
			<?php if ( $icon['url'] ) : ?>
				<a href='<?php echo $icon['url']; ?>'>
			<?php endif ?>
			<img alt='<?php echo $icon['alt']; ?>' src='<?php echo $icon['src']; ?>' />
			<?php if ( $icon['url'] ): ?>
				</a>
			<?php endif ?>
		<?php endforeach ?>
	</p>
	<?php if ( $display_news ) : ?>
		<p class='news'>News: <a href='<?php echo $candidate_url; ?>'><?php echo $news_count; ?> Related Articles</a></p>
	<?php endif ?>
	<?php if ( $display_party ) : ?>
		<p class='candidate_party'>Political Party: <a href='<?php echo $party_url; ?>'><?php echo $party_name; ?></a></p>
	<?php endif ?>
	<?php if ( $phone ) : ?>
		<p class='phone'>Phone: <?php echo $phone; ?></p>
	<?php endif ?>
</div>