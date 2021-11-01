<?php

/**
 * Fired during plugin activation
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/includes
 * @author     Digital Apps <support@digitalapps.co>
 */
class WP_Global_Site_Tag_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        set_transient( 'wp_gst_activated', 1 );
	}

}
