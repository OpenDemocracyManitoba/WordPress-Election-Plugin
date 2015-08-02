<?php 
$candidate_name = 'ed_candidates';
$party_name = $candidate_name . '_party';
$constituency_name = $candidate_name . '_constituency';
$candidate_perma_link = 'candidates';
$party_perma_link = 'parties';
$constituency_perma_link = 'constituencies';

function get_constituency( $constituency_id, $get_extra_data = true ) {
	global $constituency_name, $constituency_perma_link;
	$all_terms = get_terms( $constituency_name, array( 'include' => $constituency_id, 'hide_empty' => false ) );
	$constituency_terms = $all_terms[0];
	$results = array(
		'id' => $constituency_terms->term_id,
		'name' => $constituency_terms->name,
		'url' => get_site_url() . "/{$constituency_perma_link}/{$constituency_terms->slug}"
	);
	if ( $get_extra_data ) {
		$child_terms = get_terms( $constituency_name, array( 'parent' =>$constituency_id, 'hide_empty' => false ) );
		$results['has_children'] = count( $child_terms ) > 0;
		$results['child_coordinates'] = array();
		foreach ( $child_terms as $child )
		{
			$results['child_coordinates'][$child->name] = get_tax_meta( $child->term_id, 'coordinates' );
		}
	}
	
	return $results;
}

function get_constituency_from_candidate( $candidate_id ) {
	global $constituency_name;
	$all_terms = get_the_terms( $candidate_id, $constituency_name );
	$constituency_terms = $all_terms[0];
	return get_constituency( $constituency_terms->term_id, false );
}

function get_party( $party_id, $get_extra_data = true ) {
	global $party_name, $party_perma_link;
	$all_terms = get_terms( $party_name, array( 'include' => $party_id ) );
	$party_terms = $all_terms[0];
	
	$results = array(
		'name' => $party_terms->name,
		'colour' => get_tax_meta( $party_id, 'colour' ),
		'url' => get_site_url() . '/' . $party_perma_link . '/' . $party_terms->slug,
		
	);
		
	if ( $get_extra_data ) {
		$party_logo = get_tax_meta( $party_id, 'logo' );
		if ( $party_logo ) {
			$results['logo_url'] = $party_logo['url'];
		} else {
			$results['logo_url'] = plugins_url( 'images/missing_party.jpg', __FILE__ );
		}
		$results['website'] = get_tax_meta( $party_id, 'website' );
		$results['phone'] = get_tax_meta( $party_id, 'phone' );
		$results['address'] = get_tax_meta( $party_id, 'address' );
		$results['icon_data'] = array();
		foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
			$value = get_tax_meta( $party_id, $icon_type );
			if ( $value ) {
				switch ( $icon_type ) {
					case 'email':
						$url = "mailto:$value";
						break;
					case 'facebook':
					case 'youtube':
					case 'twitter':
						$url = $value;
						break;
					default:
						$url = '';
				}

				$alt = "{$icon_type}_active";
			} else {
				$url = '';
				$alt = "{$icon_type}_inactive";
			}
				
			$src = plugins_url( "images/$alt.jpg", __FILE__ );
			$results['icon_data'][$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => ucfirst( $alt ) );
		}
	}
	
	return $results;
}

function get_party_from_candidate( $candidate_id ) {
	global $party_name;
	$all_terms = get_the_terms( $candidate_id, $party_name );
	$party_terms = $all_terms[0];
	$party_id = $party_terms->term_id;

	return get_party( $party_id, false );
}

function get_candidate( $candidate_id ) {
	global $candidate_perma_link;
	$image_id = get_post_thumbnail_id( $candidate_id );
	if ( $image_id ) {
		$image_url = wp_get_attachment_url( $image_id );
	} else {
		$image_url = plugins_url( 'images/missing_candidate.jpg', __FILE__ );
	}
	$icon_data = array();
	foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
		$value = get_post_meta( $candidate_id, $icon_type, true );
		if ( $value ) {
			switch ( $icon_type ) {
				case 'email':
					$url = 'mailto:' . $value;
					break;
				case 'facebook':
				case 'youtube':
				case 'twitter':
					$url = $value;
					break;
				default:
					$url = '';
			}

			$alt = $icon_type . '_active';
		} else {
			$url = '';
			$alt = $icon_type . '_inactive';
		}
			
		$src = plugins_url( 'images/'. $alt . '.jpg', __FILE__ );
		$icon_data[$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => ucfirst( $alt ) );
	}

	return array(
		'image_url' => $image_url,
		'icon_data' => $icon_data,
		'name' => get_the_title( $candidate_id ),
		'phone' => get_post_meta( $candidate_id, 'phone', true ),
		'website' => get_post_meta( $candidate_id, 'website', true ),
		'email' => get_post_meta( $candidate_id, 'email', true ),
		'facebook' => get_post_meta( $candidate_id, 'facebook', true ),
		'youtube' => get_post_meta( $candidate_id, 'youtube', true ),
		'twitter' => get_post_meta( $candidate_id, 'twitter', true ),
		'incumbent_year' => get_post_meta( $candidate_id, 'incumbent_year', true ),
		'party_leader' => get_post_meta( $candidate_id, 'party_leader', true ),
		'url' => get_site_url() . "/$candidate_perma_link/" . get_post_field( 'post_name', $candidate_id ),
	);
}

function get_news( $post_id ) {
	return array(
		'count' => 0,
		'articles' => array (),
	);
}

function display_party( $party )
{
	?>
	<div class='party'>
		<div class='image' style='border-bottom: 8px solid <?php echo $party['colour']; ?>'>
			<img alt='<?php echo $party['name']; ?> Logo' src='<?php echo $party['logo_url']; ?>'/>
		</div>
		<p class='name' ><?php echo $party['name']; ?></p>
		<?php if ( $party['website'] ) : ?>
			<p class='webiste' ><a href="<?php echo $party['website']; ?>">Party Website</a></p>
		<?php endif ?>
		<p class='icons'>
		<?php foreach ( $party['icon_data'] as $icon ) : ?>
			<?php if ( $icon['url'] ) : ?>
				<a href='<?php echo $icon['url']; ?>'>
			<?php endif ?>
			<img alt='<?php echo $icon['alt']; ?>' src='<?php echo $icon['src']; ?>' />
			<?php if ( $icon['url'] ): ?>
				</a>
			<?php endif ?>
		<?php endforeach ?>
		</p>
		<?php if ( $party['phone'] ) : ?>
			<p class='phone'><?php echo $party['phone']; ?></p>
		<?php endif ?>
		
		<?php if ( $party['address'] ) : ?>
			<p class='address'><?php echo $party['address']; ?></p>
		<?php endif ?>
		<p class='news'>News: <a href='<?php echo $party['url']; ?>#news'>The Latest <?php echo $party['name']; ?> News</a></p>
	</div>
	<?php
}

function display_candidate( $candidate, $constituency, $party, $news, $remove_fields=array(), $incumbent_location='name' ) {
	$display_name = !in_array( 'name', $remove_fields );
	$display_party = !in_array('party', $remove_fields );
	$display_constituency = !in_array( 'constituency', $remove_fields );
	$display_news = !in_array( 'news', $remove_fields );
	
	?>
	<div class='politician'>
		<?php if ( $display_constituency ) : ?>
			<div class='constituency'>
				<a href="<?php echo $constituency['url']; ?>"><?php echo $constituency['name']; ?></a>
				<?php if ( $candidate['incumbent_year'] && $incumbent_location == 'constituency') : ?>
					<p class='since'>Incumbent since <?php echo $candidate['incumbent_year']; ?></p>
				<?php endif ?>
			</div>
		<?php endif ?>
		<div class='image' style='border-bottom: 8px solid <?php echo $party['colour'] ?>;'>
			<img alt='<?php echo $candidate['name'] ?>' src='<?php echo $candidate['image_url'] ?>' />
			<?php if ( $candidate['party_leader'] ) :?>
				<p>party leader</p>
			<?php endif ?>
		</div>
		<?php if ($display_name ) : ?>
			<p class='name'><strong><a href='<?php echo $candidate['url'] ?>'><?php echo $candidate['name']; ?></a></strong>
			<?php if ( $candidate['incumbent_year'] && $incumbent_location == 'name' ) : ?>
				<p class='since'>Incumbent since <?php echo $candidate['incumbent_year']; ?></p>
			<?php endif ?>
			</p>
		<?php endif ?>
		<?php if ( $candidate['website'] ) : ?>
			<p class='election_website'>
				<a href='<?php echo $candidate['website'] ?>'>Election Website</a>
			</p>
		<?php endif ?>
		<p class='icons'>
			<?php foreach ( $candidate['icon_data'] as $icon ) : ?>
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
			<p class='news'>News: <a href='<?php echo $candidate['url']; ?>'><?php echo $news['count']; ?> Related Articles</a></p>
		<?php endif ?>
		<?php if ( $display_party ) : ?>
			<p class='candidate_party'>Political Party: <a href='<?php echo $party['url']; ?>'><?php echo $party['name']; ?></a></p>
		<?php endif ?>
		<?php if ( $candidate['phone'] ) : ?>
			<p class='phone'>Phone: <?php echo $candidate['phone']; ?></p>
		<?php endif ?>
	</div>
	<?php
}