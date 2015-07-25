<?php
/* Assunes that ed_candidate_party.php has already been included.
*/
?>

<div class='party'>
	<div class='image' style='border-bottom: 8px solid <?php echo $party_colour; ?>'>
		<img alt='<?php echo $party_name; ?> Logo' src='<?php echo $party_logo_url; ?>'/>
	</div>
	<p class='name' ><?php echo $party_name; ?></p>
	<?php if ( $party_website ) : ?>
		<p class='webiste' ><a href="<?php echo $party_website; ?>">Party Website</a></p>
	<?php endif ?>
	<p class='icons'>
	<?php foreach ( $party_icon_data as $icon ) : ?>
		<?php if ( $icon['url'] ) : ?>
			<a href='<?php echo $icon['url']; ?>'>
		<?php endif ?>
		<img alt='<?php echo $icon['alt']; ?>' src='<?php echo $icon['src']; ?>' />
		<?php if ( $icon['url'] ): ?>
			</a>
		<?php endif ?>
	<?php endforeach ?>
	</p>
	<?php if ( $party_phone ) : ?>
		<p class='phone'><?php echo $party_phone; ?></p>
	<?php endif ?>
	
	<?php if ( $party_address ) : ?>
		<p class='address'><?php echo $party_address; ?></p>
	<?php endif ?>
	<p class='news'>News: <a href='<?php echo $party_url; ?>#news'>The Latest <?php echo $party_name; ?> News</a></p>

</div>
