<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/public
 * @author     Digital Apps <support@digitalapps.co>
 */
class WP_Global_Site_Tag_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function print_gtag() {
		$options 		= get_option( $this->plugin_name . '-options' );
		$tracking_id 	= $options[$this->plugin_name . '-tracking-id'];
		$snippets 		= $options[$this->plugin_name . '-snippets'];
		$output			= '';

		if( empty($snippets) ) {
			$snippets = "gtag('config', '" . $tracking_id . "');";
		}

		$output .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $tracking_id . '" type="text/javascript"></script>';
		$output .= '<script type="text/javascript">';
		$output .= 'window.dataLayer = window.dataLayer || [];';
		$output .= 'function gtag(){dataLayer.push(arguments);}';
		$output .= 'gtag(\'js\', new Date());';
		$output .= $snippets;
		$output .= '</script>';

		echo trim($output);
	}

}
