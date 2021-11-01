<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/admin
 * @author     Digital Apps <support@digitalapps.co>
 */
class WP_Global_Site_Tag_Admin {

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
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->options = $this->set_options();

	}

    /**
     * Register the stylesheets for the admin area.
     *
     * @since   1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in AdUnblocker_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The AdUnblocker_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-global-site-tag-admin.css', array(), $this->version, 'all' );

    }

	/**
     * Adds a settings sub-page link under Settings
     *
     * @link            https://codex.wordpress.org/Administration_Menus
     * @since           1.0.3
     * @return          void
     */
    public function add_menu() {

        // Top-level page
        // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        // Submenu Page
        // add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        // Add the menu item and page

        $page_title = 'WP Global Site Tag Settings';
        $menu_title = 'WP Global Site Tag';
        $capability = 'manage_options';
        $slug = $this->plugin_name . '-settings-page';
        $callback = array( $this, 'page_options' );

        add_options_page( $page_title, $menu_title, $capability, $slug, $callback );

    } // add_menu()

    /**
     * Adds a settings link next to Deactivate on the Plugins page
     *
     * @since           1.0.3
     */
    public function add_settings_link( $links ) {

        $settings_link = '<a href="options-general.php?page=' . $this->plugin_name . '-settings-page">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );

        return $links;

    }

    /**
     * This function runs when WordPress completes its upgrade process
     * It iterates through each plugin updated to see if ours is included
     *
     * @since           1.0.3
     * @param $upgrader_object Array
     * @param $options Array
     */
    public function upgrade_completed( $upgrader_object, $options ) {

        // The path to our plugin's main file
        $our_plugin = $this->plugin_name . '/' . $this->plugin_name . '.php';

        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == $our_plugin ) {
                    // Set a transient to record that our plugin has just been updated
                    set_transient( 'wp_gst_updated', 1 );
                }
            }
        }
    }

    /**
     * Show a notice to anyone who has just updated this plugin
     * This notice shouldn't display to anyone who has just installed the plugin for the first time
     *
     * @since           1.0.3
     */
    public function display_update_notice() {
        // Check the transient to see if we've just updated the plugin
        if( get_transient( 'wp_gst_updated' ) ) {
            echo '<div class="notice notice-success"><p>' . __( 'WP Global Site Tag: Settings page has been moved under <b>Settings > WP Global Site Tag</b>', 'wp-upe' ) . '</p></div>';
            delete_transient( 'wp_gst_updated' );
        }
    }

    /**
     * Show a notice to anyone who has just installed the plugin for the first time
     * This notice shouldn't display to anyone who has just updated this plugin
     */
    public function display_install_notice() {

        // Check the transient to see if we've just activated the plugin
        if( get_transient( 'wp_gst_activated' ) ) {
            echo '<div class="notice notice-success"><p>' . __( 'Thanks for installing WP Global Site Tag. Settings page can be found under <b>Settings > WP Global Site Tag</b>', 'wp-upe' ) . '</p></div>';
            // Delete the transient so we don't keep displaying the activation message
            delete_transient( 'wp_gst_activated' );
        }
    }

    /**
     * Sets the class variable $options
     */
    private function set_options() {

        return get_option( $this->plugin_name . '-options' );

    } // set_options()

    /**
     * Creates the options page
     *
     * @since           1.0.0
     * @return          void
     */
    public function page_options() {

        include( plugin_dir_path( __FILE__ ) . 'partials/wp-global-site-tag-admin-page-settings.php' );

    } // page_options()

    /**
     * Creates the options page
     *
     * @since           1.0.0
     * @return          void
     */
    public function page_sidebar() {

        include( plugin_dir_path( __FILE__ ) . 'partials/wp-global-site-tag-admin-page-sidebar.php' );

    } // page_options()

    /**
     * #1 Registers settings sections with WordPress
     */
    public function register_sections() {

        // add_settings_section( $id, $title, $callback, $menu_slug );

        add_settings_section(
            $this->plugin_name . '-basic',
            apply_filters( $this->plugin_name . 'section-title', esc_html__( '', 'wp-global-site-tag' ) ),
            array( $this, 'section_basic' ),
            $this->plugin_name . '-basic-settings-page'
        );

        add_settings_section(
            $this->plugin_name . '-advanced',
            apply_filters( $this->plugin_name . 'section-title', esc_html__( '', 'wp-global-site-tag' ) ),
            array( $this, 'section_advanced' ),
            $this->plugin_name . '-advanced-settings-page'
        );

    } // register_sections()

    /**
     * #2 Registers settings fields with WordPress
     */
    public function register_fields() {

    	// add_settings_field( $id, $title, $callback, $menu_slug, $section, $args );

    	add_settings_field(
            $this->plugin_name . '-tracking-id',
            apply_filters( $this->plugin_name . 'label-title', esc_html__( 'Measurement ID (Tracking ID):', 'wp-global-site-tag' ) ),
            array( $this, 'field_text' ),
            $this->plugin_name . '-basic-settings-page',
            $this->plugin_name . '-basic',
            array(
                'id'                => $this->plugin_name . '-tracking-id',
                'description'       => 'G-XXXXXXXXXX',
                'value'             => ''
            )
        );

        add_settings_field(
            $this->plugin_name . '-snippets',
            apply_filters( $this->plugin_name . 'label-content', esc_html__( 'JavaScript:', 'wp-global-site-tag' ) ),
            array( $this, 'field_editor' ),
            $this->plugin_name . '-advanced-settings-page',
            $this->plugin_name . '-advanced',
            array(
                'id'                => $this->plugin_name . '-snippets',
                'description'       => 'Paste your additional JS here. To reset: Empty the text area and click save.'
            )
        );

    }

    /**
     * Registers plugin settings
     *
     * @since           1.0.0
     * @return          void
     */
    public function register_settings() {

        // register_setting( $option_group, $option_name, $sanitize_callback );

        register_setting(
            $this->plugin_name . '-basic-settings-group',
            $this->plugin_name . '-options',
            array( $this , 'validate_options')
        );

        register_setting(
            $this->plugin_name . '-advanced-settings-group',
            $this->plugin_name . '-options',
            array( $this , 'validate_options')
        );

    }

    /**
     * Creates a text field
     *
     * @param           array       $args       The arguments for the field
     * @return          string                  The HTML field
     */
    public function field_text( $args ) {

        $defaults['class']          = 'text widefat';
        $defaults['description']    = '';
        $defaults['label']          = '';
        $defaults['name']           = $this->plugin_name . '-options[' . $args['id'] . ']';
        $defaults['placeholder']    = '';
        $defaults['type']           = 'text';
        $defaults['value']          = '';
        $defaults['attribute']      = '';

        apply_filters( $this->plugin_name . '-field-text-options-defaults', $defaults );
        $atts = wp_parse_args( $args, $defaults );

        if ( ! empty( $this->options[$atts['id']] ) ) {

            $atts['value'] = $this->options[$atts['id']];

        }

        include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-text.php' );

    } // field_text()

    /**
     * Creates an editor field
     *
     * NOTE: ID must only be lowercase letter, no spaces, dashes, or underscores.
     *
     * @param           array       $args       The arguments for the field
     * @return          string                  The HTML field
     */
    public function field_editor( $args ) {

        $defaults['description']    = '';
        $defaults['settings']       = array( 'textarea_name' => $this->plugin_name . '-options[' . $args['id'] . ']' );
        $defaults['value']          = "gtag('config', 'GA_TRACKING_ID');";
        $defaults['name']           = $this->plugin_name . '-options[' . $args['id'] . ']';
        apply_filters( $this->plugin_name . '-field-editor-options-defaults', $defaults );

        $atts = wp_parse_args( $args, $defaults );

        if ( ! empty( $this->options[$this->plugin_name  . '-tracking-id'] ) && empty( $this->options[$this->plugin_name  . '-snippets'] ) ) {

            $atts['value'] = "gtag('config', '" . $this->options[$this->plugin_name  . '-tracking-id'] . "');";

        }

        if ( ! empty( $this->options[$atts['id']] ) ) {

            $atts['value'] = $this->options[$atts['id']];

        }

        include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-editor.php' );

    } // field_editor()

    /**
     * Creates a settings section
     *
     * @since           1.0.0
     * @param           array       $params     Array of parameters for the section
     * @return          mixed                   The settings section
     */
    public function section_basic( $params ) {

        include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-section-basic.php' );

    } // section_options()

    /**
     * Creates a settings section
     *
     * @since           1.0.0
     * @param           array       $params     Array of parameters for the section
     * @return          mixed                   The settings section
     */
    public function section_advanced( $params ) {

        include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-section-advanced.php' );

    } // section_content()

    private function sanitizer( $type, $data ) {

        if ( empty( $type ) ) { return; }
        // if ( empty( $data ) ) { return; }

        $return = '';

        $sanitizer = new WP_Global_Site_Tag_Sanitize( $this->plugin_name );
        $sanitizer->set_data( $data );
        $sanitizer->set_type( $type );
        $return = $sanitizer->clean();

        unset( $sanitizer );

        return $return;

    } // sanitizer()

    /**
     * Returns an array of options names, fields types, and default values
     *
     * @return          array             An array of options
     */
    public function get_options_list() {

        $options = array();
        $options[] = array( $this->plugin_name . '-tracking-id', 'text' );
        $options[] = array( $this->plugin_name . '-snippets', 'editor' );

        return $options;

    } // get_options_list()

    /**
     * Validates saved options
     *
     * @since   1.0.0
     * @param   array       $input      array of submitted plugin options
     * @return  array       array of validated plugin options
     */
    public function validate_options( $input ) {

        if ( null == $input ) {
            add_settings_error(
                'requiredTextFieldEmpty',
                'empty',
                'Cannot be empty',
                'error'
            );
        }

        $valid          = array();
        $options        = $this->get_options_list();
        $settings       = $this->options;

        foreach ( $options as $option ) {
            $name = $option[0]; // wp-global-site-tag-status
            $type = $option[1]; // text

            $valid[$name] = $this->sanitizer( $type, $input[$name] );

            if( empty( $valid[$name] ) && ! array_key_exists( $name, $input )) {
                $valid[$name] = $settings[$name];
            }
        }

        return $valid;

    } // validate_options()

    /**
     * Displays admin notices
     *
     * @return  string          Admin notices
     */
    public function display_admin_notices() {
        settings_errors( 'requiredTextFieldEmpty' );
    } // display_admin_notices()

	/**
     * Gets a parament from a GET request
     *
     * @return	string		Currentnly set GET request value or default value which is hard coded
     */
    public function get_current_tab() {
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'basic_settings';
        return $active_tab;
    }

}
