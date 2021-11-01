<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://digitalapps.com
 * @since             1.0.0
 * @package           WP_GST
 *
 * @wordpress-plugin
 * Plugin Name:       WP Global Site Tag
 * Plugin URI:        https://digitalapps.com/wp-global-site-tag/
 * Description:       WP Global Site Tag (gtag.js) plugin - Analytics replacement from Google. Supports multiple properties.
 * Version:           1.0.6
 * Author:            Digital Apps
 * Author URI:        https://digitalapps.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-global-site-tag
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently pligin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DA_WP_GST', '1.0.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-global-site-tag-activator.php
 */
function activate_wp_global_site_tag() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-global-site-tag-activator.php';
	WP_Global_Site_Tag_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-global-site-tag-deactivator.php
 */
function deactivate_wp_global_site_tag() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-global-site-tag-deactivator.php';
	WP_Global_Site_Tag_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_global_site_tag' );
register_deactivation_hook( __FILE__, 'deactivate_wp_global_site_tag' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-global-site-tag.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_global_site_tag() {

	$plugin = new WP_Global_Site_Tag();
	$plugin->run();

}
run_wp_global_site_tag();
