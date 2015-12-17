<?php
/**
 * The template for displaying all single posts and attachments
 *
 */

//get_header(); 
$candidate_id = get_the_ID();
$candidate = get_candidate( $candidate_id, true );
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );
$candidate_news = get_news( $candidate['news_article_candidate_id'] );
$has_qanda = count( $candidate['answers'] ) > 0;
 
get_header(); ?>
<h2 class="title"><?php echo $candidate['name']; ?></h2>
<?php if ( $has_qanda ) : ?> 
<div class="one_column_flow" >
<?php else : ?>
<div class="flow_it">
<?php endif; ?>
	<div class="politicians">
		<?php display_candidate( $candidate, $constituency, $party, array( 'constituency', 'party' ), 'constituency' ); ?>
	</div>
	<?php  if ( $has_qanda ) :  ?>
	<div class="one_column">
	<?php else : ?>
	<div class="three_columns">
	<?php endif; ?>
		<h2 id="news">News that mentions <?php echo $candidate['name']; ?></h2>
		<p class="news-article-notice">Articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the candidate's full name.</p>
		<?php $article_count = Election_Data_Option::get_option('news-count-candidate', 10);
		display_news_summaries( $candidate['news_article_candidate_id'], 'Candidate', $article_count ); ?>
	</div>
</div>
<?php if ( $has_qanda ) : ?>
<div class="two_columns_early_shrink">
	<h2 id="qanda">Questionnaire Response</h2>
	<div class="questionnaire">
		<p class="visible_block_when_mobile" ><?php echo "{$candidate['name']} - {$constituency['name']}"; ?></p>
		<?php foreach ( $candidate['answers'] as $question => $answer ) :?>
			<p><strong><?php echo $question; ?></strong></p>
			<p><?php echo $answer; ?></p>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>