<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/includes
 * @author     Digital Apps <support@digitalapps.co>
 */
class WP_Global_Site_Tag {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Global_Site_Tag_Loader    $loader    Maintains and registers all hooks for the plugin.
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

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DA_WP_GST' ) ) {
			$this->version = DA_WP_GST;
		} else {
			$this->version = '1.0.6';
		}
		$this->plugin_name = 'wp-global-site-tag';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Global_Site_Tag_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Global_Site_Tag_i18n. Defines internationalization functionality.
	 * - WP_Global_Site_Tag_Admin. Defines all hooks for the admin area.
	 * - WP_Global_Site_Tag_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-global-site-tag-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-global-site-tag-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-global-site-tag-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-global-site-tag-public.php';

		/**
         * The class responsible for sanitizing user input
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-global-site-tag-sanitize.php';

		$this->loader = new WP_Global_Site_Tag_Loader();
		$this->sanitizer = new WP_Global_Site_Tag_Sanitize( $this->plugin_name );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Global_Site_Tag_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Global_Site_Tag_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Global_Site_Tag_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_sections' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_fields' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notices' );
        $this->loader->add_filter( 'plugin_action_links_wp-global-site-tag/wp-global-site-tag.php', $plugin_admin, 'add_settings_link' );
        $this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'upgrade_completed', 10, 2 );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_update_notice' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_install_notice' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Global_Site_Tag_Public( $this->get_plugin_name(), $this->get_version() );

		// add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 )
		$this->loader->add_action( 'wp_head', $plugin_public, 'print_gtag' );

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
	 * @return    WP_Global_Site_Tag_Loader    Orchestrates the hooks of the plugin.
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

}
