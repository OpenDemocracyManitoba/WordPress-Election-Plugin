<?php
/* Assumes one of $party_id or $candidate_id has been provided. $party_id takes precendence over $candidate_id.
*/

$candidate_name = 'ed_candidates';
$party_name = $candidate_name . '_party';
$constituency_name = $candidate_name . '_constituency';
$party_perma_link = 'parties';

if ( !isset( $party_id ) )
{
	$all_terms = get_the_terms( $candidate_id, $party_name );
	$party_terms = $all_terms[0];
	$party_id = $party_terms->term_id;
}
else
{
	$all_terms = get_terms( $party_name, array( 'include' => $party_id ) );
	$party_terms = $all_terms[0];
}

$party_name = $party_terms->name;
$party_colour = get_tax_meta( $party_id, 'colour' );
$party_logo = get_tax_meta( $party_id, 'logo' );
if ( $party_logo ) {
	$party_logo_url = $party_logo['url'];
} else {
	$party_logo_url = plugins_url( 'images/missing_party.jpg', __FILE__ );
}
$party_url = get_site_url() . '/' . $party_perma_link . '/' . $party_terms->slug;
$party_website = get_tax_meta( $party_id, 'website' );
$party_phone = get_tax_meta( $party_id, 'phone' );
$party_address = get_tax_meta( $party_id, 'address' );

$party_icon_data;
foreach ( array('email', 'facebook', 'youtube', 'twitter' ) as $icon_type ) {
	$value = get_tax_meta( $party_id, $icon_type );
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
	$party_icon_data[$icon_type] = array( 'url' => $url, 'src' => $src, 'alt' => ucfirst( $alt ) );
}

