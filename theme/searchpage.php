<?php
/**
 * Template Name: Search Page
 *
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Election_Data_Theme
 * @since Election_Data_Theme 1.0
 */
 
get_header(); ?>
 
        <div id="primary" class="content-area">
            <div id="content" class="site-content" role="main">
				
				<?php get_search_form(); ?>
			
            </div><!-- #content .site-content -->
        </div><!-- #primary .content-area -->
 
<?php get_sidebar(); ?>
<?php get_footer(); ?>
