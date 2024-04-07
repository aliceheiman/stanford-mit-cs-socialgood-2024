<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://ascentria.streamlit.app/
 * @since      1.0.0
 *
 * @package    Hackathon_Matching
 * @subpackage Hackathon_Matching/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Hackathon_Matching
 * @subpackage Hackathon_Matching/includes
 * @author     CS Social Good <hackathon@gmail.com>
 */
class Hackathon_Matching_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'hackathon-matching',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
