<?php
/**
 *
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 */
/**
 * The get_option functionality of the plugin.
 *
 *
 * @package    Election_Data
 * @subpackage Election_Data/includes
 * @author     Your Name <email@example.com>
 */


class Election_Data_Option {

	/**
	 * Get an option
	 *
	 * Looks to see if the specified setting exists, returns default if not.
	 *
	 * @since 	1.0.0
	 * @return 	mixed 	$value 	Value saved / $default if key if not exist
	 */
	static public function get_option( $key, $default = false ) {

		if ( empty( $key ) ) {
			return $default;
		}

		$plugin_options = get_option( 'election_data_settings', array() );

		$value = isset( $plugin_options[ $key ] ) ? $plugin_options[ $key ] : $default;

		return $value;
	}
}
