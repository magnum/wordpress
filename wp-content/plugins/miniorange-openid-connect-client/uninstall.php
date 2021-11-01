<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

delete_option('host_name');
delete_option('mo_oauth_admin_email');
delete_option('mo_oauth_admin_phone');
delete_option('verify_customer');
delete_option('mo_oauth_admin_customer_key');
delete_option('mo_oauth_admin_api_key');
delete_option('customer_token');
delete_option('mo_oauth_google_enable');
delete_option('mo_oauth_google_scope');
delete_option('mo_oauth_google_client_id');
delete_option('mo_oauth_google_client_secret');
delete_option('mo_oauth_google_message');
delete_option('mo_oauth_facebook_enable');
delete_option('mo_oauth_facebook_scope');
delete_option('mo_oauth_facebook_client_id');
delete_option('mo_oauth_facebook_client_secret');
delete_option('mo_oauth_facebook_message');
delete_option('mo_oauth_eveonline_enable');
delete_option('mo_oauth_new_customer');
delete_option('mo_oauth_eveonline_scope');
delete_option('mo_oauth_eveonline_client_id');
delete_option('mo_oauth_eveonline_client_secret');
delete_option('mo_oauth_eveonline_message');
delete_option('message');
delete_option('mo_eve_api_key');
delete_option('mo_eve_verification_code');
delete_option('mo_eve_allowed_corps');
delete_option('mo_eve_allowed_alliances');
delete_option('mo_eve_allowed_char_name');
delete_option('new_registration');
delete_option('mo_oauth_registration_status');
delete_option('mo_oauth_client_show_mo_server_message');

?>