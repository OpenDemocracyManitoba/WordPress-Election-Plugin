<?php
/* Assumes one of $constituency_id or $candidate_id has been provided. $constituency_id takes precendence over $candidate_id.
*/

$candidate_name = 'ed_candidates';
$party_name = $candidate_name . '_party';
$constituency_name = $candidate_name . '_constituency';
$constituency_query_var = 'constituency';

if ( !isset( $constituency_id ) )
{
	$constituency_terms = get_the_terms( $candidate_id, $constituency_name )[0];
	$constituency_id = $constituency_terms->term_id;
}
else
{
	$constituency_terms = get_terms( $constituency_name, array( 'include' => $constituency_id ) )[0];
}

$constituency = $constituency_terms->name;
$constituency_url = get_site_url() . '?' . $constituency_query_var . '=' . $constituency_terms->slug;
