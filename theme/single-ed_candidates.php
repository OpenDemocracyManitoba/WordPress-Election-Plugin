<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

//get_header(); 
$candidate_id = get_the_ID();
$candidate = get_candidate( $candidate_id );
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );
$candidate_news = get_news( $candidate['news_article_reference_id'] );
 
get_header(); ?>
<h2 class="title"><?php echo $candidate['name']; ?></h2>
<div class="flow_it">
	<div class="politicians">
		<?php display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'constituency', 'party' ), 'constituency' ); ?>
	</div>
	<div class="three_columns">
		<h2 id="news">News that mentions <?php echo $candidate['name']; ?></h2>
		<p class="news-article-notice">Articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the candidate's full name.</p>
		<?php display_news_summaries( $candidate['news_article_reference_id'], 'Candidate' ); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>