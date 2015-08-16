<?php
/**
* The Sidebar containing the main widget areas.
*
* @package Election_Data_Theme
* @since Election_Data_Theme 1.0
*/
?>
<div id="secondary" class="widget-area" role="complementary">
    <?php do_action( 'before_sidebar' ); ?>

</div><!-- #secondary .widget-area -->
<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
 
     <div id="tertiary" class="widget-area" role="supplementary">
     </div><!-- #tertiary .widget-area -->
 
<?php endif; ?>