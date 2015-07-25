<?php
/* Assumes one of $constituency_id or $candidate_id has been provided. $constituency_id takes precendence over $candidate_id.
*/

$candidate_name = 'ed_candidates';
$party_name = $candidate_name . '_party';
$constituency_name = $candidate_name . '_constituency';
$constituency_perma_link = 'constituencies';

if ( !isset( $constituency_id ) )
{
	$all_terms = get_the_terms( $candidate_id, $constituency_name );
	$constituency_terms = $all_terms[0];
	$constituency_id = $constituency_terms->term_id;
}
else
{
	$all_terms = get_terms( $constituency_name, array( 'include' => $constituency_id ) );
	$constituency_terms = $all_terms[0];
}

$constituency = $constituency_terms->name;
$constituency_url = get_site_url() . '/' . $constituency_perma_link . '/' . $constituency_terms->slug;
