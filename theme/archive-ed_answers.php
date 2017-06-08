<?php
/**
 * The template for displaying all single posts and attachments
 *
 */

get_header(); 

if ( current_user_can( 'edit_posts' ) ) : ?>
<style>
    td, th { padding: 5px; border: 1px solid black; }
</style>
<div id="primary">
    <div id="content" role="main">
		<?php display_party_answer_stats( ); ?>
        <?php display_candidate_answer_stats(); ?>
        </div>
</div>
<?php else: ?>
    <div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">
 
            <article id="post-0" class="post error404 not-found">
                <header class="entry-header">
                    <h1 class="entry-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'election_data_theme' ); ?></h1>
                </header><!-- .entry-header -->
 
                <div class="entry-content">
                    <p><?php _e( 'It looks like nothing was found at this location. Maybe try one of the links above or a search?', 'election_data_theme' ); ?></p>
 
                    <?php get_search_form(); ?>
 
                 </div><!-- .entry-content -->
            </article><!-- #post-0 .post .error404 .not-found -->
 
        </div><!-- #content .site-content -->
    </div><!-- #primary .content-area -->
<?php endif; ?>
<?php get_footer(); ?>
