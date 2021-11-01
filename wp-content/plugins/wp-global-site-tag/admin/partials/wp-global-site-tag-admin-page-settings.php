<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package    WP_Global_Site_Tag
 * @subpackage WP_Global_Site_Tag/admin/partials
 */
?>

<?php
    $tabs = array(
        array(
            "href"  => "basic_settings",
            "title" => "Basic"
        ),
        array(
            "href"  => "advanced_settings",
            "title" => "Advanced"
        )
    );
?>

<div class="wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content" class="daabd-admin-body">
                <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

                <?php if ( empty( $this->options[$this->plugin_name  . '-tracking-id'] ) && $this->get_current_tab() != 'basic_settings' ) { ?>

                    <div class="notice notice-error">
                        <p><?php _e( 'Please <a href="' . admin_url( 'admin.php?page=' . $this->plugin_name . '-settings-page&tab=basic_settings' ) . '">setup tracking ID</a> first!', 'wp-global-site-tag' ); ?></p>
                    </div>

                <?php } else { ?>

                    <h3 class="nav-tab-wrapper">
                        <?php
                            foreach ($tabs as $tab) {

                                $href = admin_url( 'admin.php?page=' . $this->plugin_name . '-settings-page&tab=' . $tab['href'] );
                                $css_class = ( $this->get_current_tab() == $tab['href'] ) ? 'nav-tab-active' : '';

                                echo '<a href="' . $href . '" class="nav-tab ' . $css_class . '">' . $tab['title'] . '</a>';

                            }
                        ?>
                    </h3>

                    <form method="post" action="options.php"><?php

                        if( $this->get_current_tab() == 'advanced_settings' ) {

                            settings_fields( $this->plugin_name . '-advanced-settings-group' );
                            do_settings_sections( $this->plugin_name . '-advanced-settings-page' );
                            submit_button( 'Save Settings' );

                        } else {

                            settings_fields( $this->plugin_name . '-basic-settings-group' );
                            do_settings_sections( $this->plugin_name . '-basic-settings-page' );
                            submit_button( 'Save Settings' );

                        }

                    ?></form>

                <?php } ?>

            </div>

        <?php $this->page_sidebar(); ?>

        <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
