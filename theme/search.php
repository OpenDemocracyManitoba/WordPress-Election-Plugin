<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Election_Data_Theme
 * @since Election_Data_Theme 1.0
 */
 
get_header(); ?>
 
        <section id="primary" class="content-area">
            <div id="content" class="site-content" role="main">
 
            <?php if ( have_posts() ) : ?>
 
                <header class="page-header">
                    <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'election_data_theme' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
                </header><!-- .page-header -->
 
                <?php //election_data_theme_content_nav( 'nav-above' ); ?>
 
                <?php /* Start the Loop */ ?>
				
				<?php display_search_results( $wp_query ); ?>
				
                <?php //election_data_theme_content_nav( 'nav-below' ); ?>
 
            <?php else : ?>
 
				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found', 'election_data_theme' ); ?></h1>
					</header><!-- .entry-header -->
				 
					<div class="entry-content">
						<p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'election_data_theme' ); ?></p>
						<?php get_search_form(); ?>
				   </div><!-- .entry-content -->
				</article><!-- #post-0 .post .no-results .not-found -->
            <?php endif; ?>
 
            </div><!-- #content .site-content -->
        </section><!-- #primary .content-area -->

<?php get_footer(); ?>
