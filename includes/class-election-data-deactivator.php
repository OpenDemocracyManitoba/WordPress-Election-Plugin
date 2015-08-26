<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Your Name <email@example.com>
 */
class Election_Data_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-news-article.php';
		
		$news_articles = new Election_Data_News_Article( '', '', false );
		$news_articles->stop_cron();
		
		$previous_theme = Election_Data_Option::get_option( 'previous_theme' );
		if ( $previous_theme ) {
			$theme = wp_get_theme( $previous_theme );
			if ( $theme->exists() ) {
				switch_theme( $previous_theme );
			}
		}
	}
}
