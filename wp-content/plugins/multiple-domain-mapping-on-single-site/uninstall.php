<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('falke_mdm_mappings');
delete_option('falke_mdm_settings');
delete_option('falke_mdm_notice');
delete_option('falke_mdm_upgrade_notice');
delete_option('falke_mdm_versionhint');
