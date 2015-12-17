<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://opendemocracymanitoba.ca/
 * @since             1.0.0
 * @package           Election_Data
 *
 * @wordpress-plugin
 * Plugin Name:       Election Data
 * Plugin URI:        http://opendemocracymanitoba.ca/election-data
 * Description:       Allows the distribution of voter resources for an election.
 * Version:           1.0.0
 * Author:            Robert Burton
 * Author URI:        http://opendemocracymanitoba.ca/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       election-data
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-election-data-activator.php
 */
function activate_election_data() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-election-data-activator.php';
	Election_Data_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-election-data-deactivator.php
 */
function deactivate_election_data() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-election-data-deactivator.php';
	Election_Data_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_election_data' );
register_deactivation_hook( __FILE__, 'deactivate_election_data' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-election-data.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_election_data() {

	$plugin = new Election_Data();
	$plugin->run();

}
run_election_data();
	