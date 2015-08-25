<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Your Name <email@example.com>
 */
class Election_Data {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Election_Data_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	protected $candidate;
	
	protected $news_artice;
		
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'election-data';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->candidate = new Election_Data_Candidate();
		
		$this->news_article = new Election_Data_News_Article( $this->candidate->post_type, $this->candidate->taxonomies['party']);
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Election_Data_Loader. Orchestrates the hooks of the plugin.
	 * - Election_Data_i18n. Defines internationalization functionality.
	 * - Election_Data_Admin. Defines all hooks for the dashboard.
	 * - Election_Data_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-election-data-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-election-data-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-option.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-election-data-callback-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-election-data-meta-box.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-election-data-sanitization-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-election-data-settings-definition.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-election-data-settings.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-candidate.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-activator.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-election-data-news-article.php';

		$this->loader = new Election_Data_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Election_Data_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Election_Data_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Election_Data_Admin( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add the options page and menu item.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_name . '.php' );
		$this->loader->add_action( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Built the option page
		$settings_callback = new Election_Data_Callback_Helper( $this->plugin_name );
		$settings_sanitization = new Election_Data_Sanitization_Helper( $this->plugin_name );
		$plugin_settings = new Election_Data_Settings( $this->get_plugin_name(), $settings_callback, $settings_sanitization );
		$this->loader->add_action( 'admin_init' , $plugin_settings, 'register_settings' );

		$plugin_meta_box = new Election_Data_Meta_Box( $this->get_plugin_name() );
		$this->loader->add_action( 'load-toplevel_page_' . $this->get_plugin_name() , $plugin_meta_box, 'add_meta_boxes' );

		$this->loader->add_action( 'wp_ajax_election_data_erase_site', $this, 'erase_data' );
		$this->loader->add_action( 'admin_notices', 'Election_Data_Activator', 'display_activation_warnings' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Election_Data_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Election_Data_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	public static function import( $file_type, $file_data, $mode ) {
		set_time_limit( 60 * 60 );
		$candidate = new Election_Data_Candidate( false );
		$news_article = new Election_Data_News_Article( $candidate->post_type, $candidate->taxonomies['party'], false );
		
		$file_name = $file_data['tmp_name'];

		switch ( $file_type ) {
			case 'xml':
				$success = true;
				break;
			case 'csv_zip':
				$success = true;
				$zip = new ZipArchive();
				$zip->open( $file_name );
				$types = array( 'party', 'constituency', 'candidate' );
				foreach( $types as $type ) {
					$csv = $zip->getStream( "$type.csv" );
					$success |= $candidate->import_csv( $type, $csv, $mode );
					fclose( $csv );
				}

				$types = array( 'news_source', 'news_article', 'news_mention' );
				foreach( $types as $type ) {
					$csv = $zip->getStream( "$type.csv" );
					$success |= $news_article->import_csv( $type, $csv, $mode );
					fclose( $csv );
				}
				break;
			case 'csv_candidate':
			case 'csv_party':
			case 'csv_constituency':
				$type = substr( $file_type, 4 );
				$csv = fopen( "$file_name", 'r');
				$success = $candidate->import_csv( $type, $csv, $mode );
				break;
			case 'csv_news_source':
			case 'csv_news_article':
			case 'csv_news_mention':
				$type = substr( $file_type, 4 );
				$csv = fopen( "$file_name", 'r');
				$success = $news_article->import_csv( $type, $csv, $mode );
				break;
			default:
				$success = false;
				break;
		}
		
		return $success;
	}
	
	public static function export( $file_type ) {
		set_time_limit( 60 * 60 );
		$candidate = new Election_Data_Candidate( false );
		$news_article = new Election_Data_News_Article( $candidate->post_type, $candidate->taxonomies['party'], false );

		switch ( $file_type ) {
			case 'xml':
				$file = tempnam( 'tmp', 'xml' );
				$xml = new XMLWriter();
				$xml->openURI( "file://$file" );
				$xml->startDocument( '1.0' );
				$candidate->export_xml( $xml );
				$news_article->export_xml ( $xml );
				$xml->endDocument();
				$xml->flush();
				$content_type = 'application/xml';
				$file_name = 'Election_Data.xml';
				break;
			case 'csv_zip':
				$file = tempnam( 'tmp', 'zip' );
				$zip = new ZipArchive();
				$zip->open( $file, ZipArchive::OVERWRITE );
				$types = array( 'candidate', 'party', 'constituency' );
				$csv_files = array();
				foreach ( $types as $type ) {
					$csv_file = $candidate->export_csv( $type );
					$zip->addFile( $csv_file, "$type.csv" );
					$csv_files[] = $csv_file;
				}
				$types = array( 'news_article', 'news_source', 'news_mention' );
				foreach ( $types as $type ) {
					$csv_file = $news_article->export_csv( $type );
					$zip->addFile( $csv_file, "$type.csv" );
					$csv_files[] = $csv_file;
				}
				
				$zip->close();
				foreach ( $csv_files as $csv_file ) {
					unlink( $csv_file );
				}
				$content_type = 'application/zip';
				$file_name = 'Election_Data.zip';
				break;
			case 'csv_candidate':
			case 'csv_party':
			case 'csv_constituency':
				$type = substr( $file_type, 4 );
				$file = $candidate->export_csv( $type );
				$content_type = 'text/csv';
				$file_name = "$type.csv";
				break;
			case 'csv_news_article':
			case 'csv_news_source':
			case 'csv_news_mention':
				$type = substr( $file_type, 4 );
				$file = $news_article->export_csv( $type );
				$content_type = 'text/csv';
				$file_name = "$type.csv";
				break;
			default:
				return;
		}
		
		header( "Content-Type: $content_type" );
		header( 'Content-Length: ' . filesize( $file ) );
		header( "Content-Disposition: attachment; filename=\"$file_name\"" );
		readfile( $file );
		unlink( $file );
		exit();
	}
	
	function erase_data()
	{
		$this->candidate->erase_data();
		$this->news_article->erase_data();
		wp_die();
	}
}


/*add_filter('found_posts', 'update_found_posts_for_party', 1, 2 );
function update_found_posts_for_party( $found_posts, $query ) {
	error_log( "Running the filter" ) ;
	remove_filter('found_posts', 'update_found_posts_for_party', 1 );
	return $found_posts;
}*/