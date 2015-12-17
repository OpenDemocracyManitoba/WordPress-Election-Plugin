<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package ElectionData
 * @since 1.0
 */

$article_id = get_the_ID();
$article = get_news_article( $article_id );
get_header(); ?>
<div class="flow_it">
	<div class="news_articles" >
		<?php display_news_article( $article, true ); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>