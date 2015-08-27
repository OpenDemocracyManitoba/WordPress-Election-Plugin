<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); 

?>
<div id="primary">
    <div id="content" role="main">
		<?php display_all_candidates( $wp_query ); ?>
	</div>
</div>
<?php get_footer(); ?>