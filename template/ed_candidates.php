<?php
/*  Assumes that $candidate_id has been defined with the id of the candidate to be displayed.
*/

$image_id = get_post_thumbnail_id( $candidate_id );
if ( $image_id ) {
	$image_url = wp_get_attachment_url( $image_id );
} else {
	$image_url = plugins_url( 'images/missing_candidate.jpg', __FILE__ );
}
$name = get_the_title( $candidate_id );
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
	$icon_data[$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => $alt );
}

$phone = get_post_meta( $candidate_id, 'phone', true );
$website = get_post_meta( $candidate_id, 'website', true );
$email = get_post_meta( $candidate_id, 'email', true );
$facebook = get_post_meta( $candidate_id, 'facebook', true );
$youtube = get_post_meta( $candidate_id, 'youtube', true );
$twitter = get_post_meta( $candidate_id, 'twitter', true );
$incumbent_year = get_post_meta( $candidate_id, 'incumbent_year', true );
$party_leader = get_post_meta( $candidate_id, 'party_leader', true );
$news = '';
$party_terms = get_the_terms( $candidate_id, 'ed_candidates_party' )[0];
$party = $party_terms->name;
$party_meta = get_option( 'taxonomy_' . $party_terms->term_id );
$party_colour = $party_meta['colour'];
$constituency_terms = get_the_terms( $candidate_id, 'ed_candidates_constituency' )[0];
$constituency = $constituency_terms->name;
$constituency_url = get_site_url() . '?constituency=' . $constituency_terms->slug;
$candidate_url = get_site_url() . '?candidate=' . get_post_field( 'post_name', $candidate_id );
$incumbent_url = get_site_url() . '/incumbents/';
$party_url = get_site_url() . '?party=' . $party_terms->slug;
