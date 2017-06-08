<?php
/**
 * The template for displaying all single posts and attachments
 *
 */

get_header(); 

?>
<div id="flow_it">
    <div class="three_columns latest_news_large">
		<h3>Latest News Articles</h3>
		<div class="grey small">
			<p>News articles are automatically gathered from Google News by searching for the full names of the candidates in the upcoming Manitoba election.</p>
		</div>
		<?php news_titles( $wp_query, 'News Article' ); ?>
	</div>
</div>
<?php get_footer(); ?>
