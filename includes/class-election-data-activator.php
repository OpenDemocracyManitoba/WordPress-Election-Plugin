<?php

/**
 * Fired during plugin activation
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Your Name <email@example.com>
 */
class Election_Data_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-candidate.php';
		
		$candidate = new Election_Data_Candidate( false );
		$candidate->initialize();
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-news-article.php';
		
		$news_articles = new Election_Data_News_Article( '', '', false );
		$news_articles->initialize();
		
		flush_rewrite_rules();
		
		$news_articles->setup_cron();
		
		//Election_Data_Options::update_option(
		
		switch_theme('ElectionData' );

		$search_page = self::get_or_add_search_page();
	
		self::register_navigation( $news_articles, $search_page->ID );
	}
	
	public static function get_or_add_search_page() {
		$search_pages = get_pages( array( 
			'meta_key' => '_wp_page_template',
			'meta_value' => 'searchpage.pgp',
		) );
		
		if ( count( $search_pages ) > 0 ) {
			return $search_pages[0];
		}
		
		$search_page = array(
			'post_title' => 'Search',
			'post_status' => 'publish',
			'post_type' => 'page',
			'page_template' => 'searchpage.php',
		);
		
		return get_post( wp_insert_post( $search_page ) );
	}
	
	public static function register_navigation( $news_articles, $seach_page_id ) {
		$menu_name = 'Election Data Navigation Menu';
		$menu_location = 'header-menu';
		if ( ! wp_get_nav_menu_object( $menu_name ) ) {
			$menu_id = wp_create_nav_menu( $menu_name );
			error_log( "menu_id: $menu_id" );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'Home' ),
				'menu-item-url' => home_url( '/' ),
				'menu-item-status' => 'publish',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'Candidates' ),
				'menu-item-status' => 'publish',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'Latest News' ),
				'menu-item-url' => get_post_type_archive_link( $news_articles->post_type ),
				'menu-item-status' => 'publish',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'Election Info' ),
				'menu-item-status' => 'publish',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'Search' ),
				'menu-item-status' => 'publish',
				'menu-item-object' => 'page',
				'menu-item-object-id' => $seach_page_id,
				'menu-item-type' => 'post_type',
			) );
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title' => __( 'About' ),
				'menu-item-status' => 'publish',
			) );
		}
	}
	
}
