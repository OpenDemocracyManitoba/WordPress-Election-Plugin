<?php
/* Assumes one of $party_id or $candidate_id has been provided. $party_id takes precendence over $candidate_id.
*/

$candidate_name = 'ed_candidates';
$party_name = $candidate_name . '_party';
$constituency_name = $candidate_name . '_constituency';
$party_perma_link = 'parties';

if ( !isset( $party_id ) )
{
	$party_terms = get_the_terms( $candidate_id, $party_name )[0];
	$party_id = $party_terms->term_id;
}
else
{
	$party_terms = get_terms( $party_name, array( 'include' => $party_id ) )[0];
}

$party = $party_terms->name;
$party_colour = get_tax_meta( $party_id, 'party_colour' );
$party_logo = get_tax_meta( $party_id, 'party_logo' );
$party_url = get_site_url() . '/' . $party_perma_link . '/' . $party_terms->slug;