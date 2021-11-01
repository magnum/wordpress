<?php
/**
* Plugin Name: OpenID Connect Login ( OpenID Connect Client)
* Plugin URI: http://miniorange.com
* Description: OpenID Connect Client plugin allows login (Single Sign On) with any OpenID provider that conforms to the OpenID Connect 1.0 standard.
* Version: 2.1.5
* Author: miniOrange
* Author URI: https://www.miniorange.com
* License: MIT/Expat
* License URI: https://docs.miniorange.com/mit-license
*/

require('handler/oauth_handler.php');
include_once dirname( __FILE__ ) . '/class-mo-oauth-widget.php';
require('class-customer.php');
require plugin_dir_path( __FILE__ ) . 'includes/class-mo-oauth-client.php';
require('includes/manage-avatar.php');
require('views/feedback_form.php');
require_once 'views/PointersManager.php';

class mo_openidconnect {

	function __construct() {
		
		add_action( 'admin_init',  array( $this, 'miniorange_oauth_save_settings' ) );
		add_action('admin_init',array($this,'miniorange_oauth_add_options'));
		add_action( 'plugins_loaded',  array( $this, 'mo_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_oauth_deactivate'));
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'tutorial' ) );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_shortcode('mo_openidconnect_login', array( $this,'mo_oauth_shortcode_login'));
		add_action( 'admin_footer', array( $this, 'mo_oauth_client_feedback_request' ) );
		
		if(get_option('mo_oauth_eveonline_enable') == 1 )
			add_action( 'admin_notices', array($this,'mooauth_admin_notice__success'));
	}
	
	function tutorial($page) {
		$file = plugin_dir_path( __FILE__ ) . 'pointers.php';
		// Arguments: pointers php file, version (dots will be replaced), prefix
		$manager = new PointersManager( $file, '4.8.52', 'custom_admin_pointers' );
		$manager->parse();
		$pointers = $manager->filter( $page );
		// print_r($pointers);
		if ( empty( $pointers ) ) { // nothing to do if no pointers pass the filter
			return;
		}
		wp_enqueue_style( 'wp-pointer' );
		$js_url = plugins_url( 'js/cards.js', __FILE__ );
		wp_enqueue_script( 'custom_admin_pointers', $js_url, array('wp-pointer'), NULL, TRUE );
		// data to pass to javascript
		$data = array(
			'next_label' => __( 'Next' ),
			'close_label' => __('Close'),
			'pointers' => $pointers
		);
		wp_localize_script( 'custom_admin_pointers', 'MyAdminPointers', $data );
	}
	
	function mooauth_admin_notice__success(){
			?>
			<div class="notice notice-success is-dismissible">
				<h2>Are you having issues with Eve Online app?</h2>
				<p>Eve online has updated API's and removed support for old apis.</p>
				<p>Download updated plugin with <a href="https://wordpress.org/plugins/oauth-client/">this link</a> to continue using Eve Online SSO</p>
			</div>
		<?php
	}
	
	function miniorange_oauth_add_options()
	{
		global $wpdb;
		$query='SELECT option_name, option_value FROM '.$wpdb->prefix.'options WHERE (option_name LIKE "mo_openidconnect%" OR option_name LIKE "mo_openid_connect%")';
		$results=$wpdb->get_results($query);
		
		foreach ($results as $key => $option) {

			$newkey="";
			if(strpos($option->option_name,"mo_openidconnect")!==false)
			{
				if(strpos($option->option_name,"login_icon_custom"))
				{
					$newkey=str_replace("mo_openidconnect_login_icon_custom","mo_oauth_icon",$option->option_name);
				}
				else
				{
					$newkey=str_replace("mo_openidconnect", "mo_oauth", $option->option_name);
				}
			}else
			{	
				$newkey=str_replace("mo_openid_connect", "mo_oauth", $option->option_name);
			}
			
			if(strpos($newkey, "apps_list")!==false)
			{

				$defaultapps =  file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR .'admin/partials/apps/partials/defaultapps.json');
				$defaultappsjson =json_decode($defaultapps);
				$appslist=array();
					if($option->option_value!=null)
					$appslist=maybe_unserialize($option->option_value);
					
					foreach ($appslist as $appname => $app) {
						
						$defapp="";
						$found=false;
							foreach ($defaultappsjson as $defappname => $values) {
								
								if($values->authorize==$app["authorizeurl"])
								{
									$found=true;
									if(!array_search("accesstokenurl", $app) )
									{
										$app["accesstokenurl"]=$values->token;
									}
									$app["resourceownerdetailsurl"]=$values->userinfo;
									$app["send_headers"]="1";
									$app["send_body"]="0";
									$app["appId"]="";
									$app["show_on_login_page"]=1;
									break;
								}
							}
					if(!$found)
					{
						$app["accesstokenurl"]=$app["idtokenurl"];
						$app["resourceownerdetailsurl"]="";
						$app["send_headers"]="1";
						$app["send_body"]="0";
						$app["appId"]="";
						$app["show_on_login_page"]=1;
					}				
					$appslist[$appname]=$app;
					break;
					}
					if(!get_option($newkey))
					{	
					update_option($newkey,$appslist);
				}
					continue;
			}
			if(!get_option($newkey)){
			update_option($newkey,$option->option_value);
		}			
		}
		
		
	}

	function mo_oauth_success_message() {
		$class = "error";
		$message = get_option('message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function mo_oauth_client_feedback_request() {
		mo_oauth_client_display_feedback_form();
	}
	
	function mo_oauth_error_message() {
		$class = "updated";
		$message = get_option('message');
		echo "<div class='" . $class . "'><p>" . $message . "</p></div>";
	}

	public function mo_oauth_deactivate() {
		//delete all stored key-value pairs
		delete_option('host_name');
		delete_option('new_registration');
		delete_option('mo_oauth_admin_phone');
		delete_option('verify_customer');
		delete_option('mo_oauth_admin_customer_key');
		delete_option('mo_oauth_admin_api_key');
		delete_option('mo_oauth_new_customer');
		delete_option('customer_token');
		delete_option('message');
		delete_option('mo_oauth_registration_status');
		delete_option('mo_oauth_client_show_mo_server_message');
	}

	private $settings = array(
		'mo_oauth_facebook_client_secret'	=> '',
		'mo_oauth_facebook_client_id' 		=> '',
		'mo_oauth_facebook_enabled' 		=> 0
	);



	function plugin_settings_style() {
		wp_enqueue_style( 'mo_oauth_admin_settings_style', plugins_url( 'css/style_settings.css', __FILE__ ) );
		wp_enqueue_style( 'mo_oauth_admin_settings_font_awesome', plugins_url( 'css/font-awesome.css', __FILE__ ));
		wp_enqueue_style( 'mo_oauth_admin_settings_phone_style', plugins_url( 'css/phone.css', __FILE__ ) );
		wp_enqueue_style( 'mo_oauth_admin_settings_datatable', plugins_url( 'css/jquery.dataTables.min.css', __FILE__ ) );
	}

	function plugin_settings_script() {
		wp_enqueue_script( 'mo_oauth_admin_settings_script', plugins_url( 'js/settings.js', __FILE__ ) );
		wp_enqueue_script( 'mo_oauth_admin_settings_phone_script', plugins_url('js/phone.js', __FILE__ ) );
		wp_enqueue_script( 'mo_oauth_admin_settings_datatable', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ) );
	}

	function mo_login_widget_text_domain(){
		load_plugin_textdomain( 'flw', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	private function mo_oauth_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
	}

	private function mo_oauth_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
	}

	public function mo_oauth_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	function miniorange_oauth_save_settings(){

		if ( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_client_mo_server_message" ) {
			update_option( 'mo_oauth_client_show_mo_server_message', 1 );
			return;
		}

		if ( isset( $_POST['option'] ) and $_POST['option'] == "clear_pointers" ) {
			update_user_meta(get_current_user_id(),'dismissed_wp_pointers','');
			return;
		}
		
		if ( isset( $_POST['option'] ) and $_POST['option'] == "change_miniorange" ) {
			$this->mo_oauth_deactivate();
			return;
		}
		
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_register_customer" ) {	//register the admin to miniOrange
			//validation and sanitization
			$email = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			$fname = '';
			$lname = '';
			$company = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['phone'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) || $this->mo_oauth_check_empty_or_null( $_POST['confirmPassword'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else if( strlen( $_POST['password'] ) < 8 || strlen( $_POST['confirmPassword'] ) < 8){
				update_option( 'message', 'Choose a password with minimum length 8.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$phone = stripslashes( $_POST['phone'] );
				$password = stripslashes( $_POST['password'] );
				$confirmPassword = stripslashes( $_POST['confirmPassword'] );
				$fname = stripslashes( $_POST['fname'] );
				$lname = stripslashes( $_POST['lname'] );
				$company = stripslashes( $_POST['company'] );
			}

			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'mo_oauth_admin_phone', $phone );
			update_option( 'mo_oauth_admin_fname', $fname );
			update_option( 'mo_oauth_admin_lname', $lname );
			update_option( 'mo_oauth_admin_company', $company );

			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}

			if( strcmp( $password, $confirmPassword) == 0 ) {
				update_option( 'password', $password );
				$customer = new Customer();
				$email=get_option('mo_oauth_admin_email');
				$content = json_decode($customer->check_customer(), true);
				if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
					$response = json_decode($customer->create_customer(), true);

					if(strcasecmp($response['status'], 'SUCCESS') == 0) {
						$content = $customer->get_customer_key();
                        $customerKey = json_decode( $content, true );
                        if( json_last_error() == JSON_ERROR_NONE ) {
                            update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
                            update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
                            update_option( 'customer_token', $customerKey['token'] );
                            update_option( 'mo_oauth_admin_phone', $customerKey['phone'] );
                            delete_option( 'password' );
                            update_option( 'message', 'Customer created & retrieved successfully');
                            delete_option( 'verify_customer' );
                            $this->mo_oauth_show_success_message();
                        }
                        wp_redirect( admin_url( '/admin.php?page=mo_oauth_settings&tab=account' ), 301 );
      					exit;
                    } else {
						update_option( 'message', 'Failed to create customer. Try again.');
                    }
                    $this->mo_oauth_show_success_message();
				} elseif(strcasecmp( $content['status'], 'SUCCESS') == 0 ) {
                    update_option( 'message', 'Account already exist. Please Login.');
                } else {
                    update_option( 'message', $content['status'] );
                }
			} else {
				update_option( 'message', 'Passwords do not match.');
				delete_option('verify_customer');
				$this->mo_oauth_show_error_message();
			}
		} 

		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_client_goto_login" ) {
			delete_option( 'new_registration' );
			update_option( 'verify_customer', 'true' );
		}

		 if(isset($_POST['option']) and $_POST['option'] == "mo_oauth_validate_otp"){
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$otp_token = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['mo_oauth_otp_token'] ) ) {
				update_option( 'message', 'Please enter a value in OTP field.');
				update_option('mo_oauth_registration_status','MO_OTP_VALIDATION_FAILURE');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$otp_token = stripslashes( $_POST['mo_oauth_otp_token'] );
			}

			$customer = new Customer();
			$content = json_decode($customer->validate_otp_token($_SESSION['mo_oauth_transactionId'], $otp_token ),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				$this->create_customer();
			}else{
				update_option( 'message','Invalid one time passcode. Please enter a valid OTP.');
				update_option('mo_oauth_registration_status','MO_OTP_VALIDATION_FAILURE');
				$this->mo_oauth_show_error_message();
			}
		}
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_verify_customer" ) {	//register the admin to miniOrange
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$email = '';
			$password = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$password = stripslashes( $_POST['password'] );
			}

			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'password', $password );
			$customer = new Customer();
			$content = $customer->get_customer_key();
			$customerKey = json_decode( $content, true );
			if( json_last_error() == JSON_ERROR_NONE ) {
				update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
				update_option( 'customer_token', $customerKey['token'] );
				update_option( 'mo_oauth_admin_phone', $customerKey['phone'] );
				delete_option('password');
				update_option( 'message', 'Customer retrieved successfully');
				delete_option('verify_customer');
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Invalid username or password. Please try again.');
				$this->mo_oauth_show_error_message();
			}
		}
		//save API KEY for eveonline from eveonline setup
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_eve_save_api_key" ){
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$apiKey = '';
			$verificationCode = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['mo_eve_api_key'] ) || $this->mo_oauth_check_empty_or_null( $_POST['mo_eve_verification_code'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter Key ID and Verfication code to save API Key details.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$apiKey = stripslashes( $_POST['mo_eve_api_key'] );
				$verificationCode = stripslashes( $_POST['mo_eve_verification_code'] );
			}

			update_option( 'mo_eve_api_key' ,$apiKey);
			update_option('mo_eve_verification_code', $verificationCode);
			if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
				update_option( 'message', 'Your API Key details have been saved');
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Please enter Key ID and Verfication code to save API Key details');
				$this->mo_oauth_show_error_message();
			}
		}
		//save allowed corporations, alliances and character names
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_eve_save_allowed" ){
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//sanitization of corporations and alliance fields
			$corps = stripslashes( $_POST['mo_eve_allowed_corps'] );
			$alliances = stripslashes( $_POST['mo_eve_allowed_alliances'] );
			$charName = stripslashes( $_POST['mo_eve_allowed_char_name'] );

			update_option( 'mo_eve_allowed_corps' ,$corps );
			update_option( 'mo_eve_allowed_alliances', $alliances );
			update_option( 'mo_eve_allowed_char_name', $charName );
			if( get_option('mo_eve_allowed_corps') || get_option('mo_eve_allowed_alliances') || get_option('mo_eve_allowed_char_name') ) {
				if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
					update_option( 'message', 'Your allowed Corporations, Alliances and Characters have been saved');
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Please enter Key ID and Verification code to filter Characters. Your allowed Corporations, Alliances and Characters have been saved.');
					$this->mo_oauth_show_error_message();
				}
			} else {
				if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
					update_option( 'message', 'Characters of all Corporations and Alliances will be allowed.');
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Please enter Key ID and Verification code to filter Characters. Characters of all Corporations and Alliances will be allowed.');
					$this->mo_oauth_show_error_message();
				}
			}
		}
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_add_app" ) {
			$scope = '';
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_client_id']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_client_secret'])) {
				update_option( 'message', 'Please enter valid Client ID and Client Secret.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$scope = stripslashes( $_POST['mo_oauth_scope'] );
				$clientid = stripslashes( trim( $_POST['mo_oauth_client_id'] ) );
				$clientsecret = stripslashes( trim( $_POST['mo_oauth_client_secret'] ) );
				$appname = stripslashes( $_POST['mo_oauth_custom_app_name'] );
				$ssoprotocol = stripslashes( $_POST['mo_oauth_app_type'] );
				$selectedapp = stripslashes( $_POST['mo_oauth_app_name'] );
				$send_headers =  sanitize_post($_POST['mo_oauth_authorization_header']);
				$send_body = sanitize_post($_POST['mo_oauth_body']);
				$show_on_login_page = isset($_POST['mo_oauth_show_on_login_page']) ? (int)filter_var( $_POST['mo_oauth_show_on_login_page'], FILTER_SANITIZE_NUMBER_INT) : 0;

				if( $selectedapp == 'wso2' ) {
					update_option( 'mo_oauth_client_custom_token_endpoint_no_csecret', true );
				}

				if(get_option('mo_oauth_apps_list'))
					$appslist = get_option('mo_oauth_apps_list');
				else
					$appslist = array();

				$email_attr = "";
				$name_attr = "";
				$newapp = array();

				$isupdate = false;
				foreach($appslist as $key => $currentapp){
					if($appname == $key){
						$newapp = $currentapp;
						$isupdate = true;
						break;
					}
				}

				if(!$isupdate && sizeof($appslist)>0){
					update_option( 'message', 'You can only add 1 application with free version. Upgrade to enterprise version if you want to add more applications.');
					$this->mo_oauth_show_error_message();
					return;
				}


				$newapp['clientid'] = $clientid;
				$newapp['clientsecret'] = $clientsecret;
				$newapp['scope'] = $scope;
				$newapp['redirecturi'] = site_url();
				$newapp['ssoprotocol'] = $ssoprotocol;
				$newapp['send_headers'] = $send_headers;
				$newapp['send_body'] = $send_body;
				$newapp['show_on_login_page'] = $show_on_login_page;
				if($appname=="facebook"){
					$authorizeurl = 'https://www.facebook.com/dialog/oauth';
					$accesstokenurl = 'https://graph.facebook.com/v2.8/oauth/access_token';
					$resourceownerdetailsurl = 'https://graph.facebook.com/me/?fields=id,name,email,age_range,first_name,gender,last_name,link&access_token=';
				} else if($appname=="google"){
					$authorizeurl = "https://accounts.google.com/o/oauth2/auth";
					$accesstokenurl = "https://www.googleapis.com/oauth2/v3/token";
					//private static final String VERIFY_TOKEN_ENDPOINT = "https://www.googleapis.com/oauth2/v1/tokeninfo?id_token=";
					//private static final String GET_USER_INFO_ENDPOINT = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=";
					$resourceownerdetailsurl = "https://www.googleapis.com/plus/v1/people/me";
				}  else if($appname=="windows"){
					$authorizeurl = "https://login.live.com/oauth20_authorize.srf";
					$accesstokenurl = "https://login.live.com/oauth20_token.srf";
					$resourceownerdetailsurl = "https://apis.live.net/v5.0/me";
				} else if($appname=="eveonline"){
					update_option( 'mo_oauth_eveonline_enable', 1);
					update_option( 'mo_oauth_eveonline_client_id', $clientid);
					update_option( 'mo_oauth_eveonline_client_secret', $clientsecret);
					if(get_option('mo_oauth_eveonline_client_id') && get_option('mo_oauth_eveonline_client_secret')) {
						$customer = new Customer();
						$message = $customer->add_oauth_application('eveonline', 'EVE Online OAuth');
						if($message == 'Application Created') {
							update_option('message', 'Your settings were saved. Go to Advanced EVE Online Settings for configuring restrictions on user sign in.');
							$this->mo_oauth_show_success_message();
						} else {
							update_option('message', $message);
							$this->mo_oauth_show_error_message();
						}
					}
					$authorizeurl = "";
					$accesstokenurl = "";
					$resourceownerdetailsurl = "";
				} else {
					$authorizeurl = stripslashes($_POST['mo_oauth_authorizeurl']);
					$accesstokenurl = stripslashes($_POST['mo_oauth_accesstokenurl']);
					$appname = stripslashes( $_POST['mo_oauth_custom_app_name'] );
					//$email_attr = stripslashes( $_POST['mo_oauth_email_attr'] );
					//$name_attr = stripslashes( $_POST['mo_oauth_name_attr'] );
				}

				$newapp['authorizeurl'] = $authorizeurl;
				$newapp['accesstokenurl'] = $accesstokenurl;
				if(isset($_POST['mo_oauth_app_type'])) {
					$newapp['apptype'] = stripslashes( $_POST['mo_oauth_app_type'] );
				} else {
					$newapp['apptype'] = stripslashes( 'oauth' );
				}
				
				if($newapp['apptype'] == 'oauth' || isset($_POST['mo_oauth_resourceownerdetailsurl'])) {
					$resourceownerdetailsurl = stripslashes($_POST['mo_oauth_resourceownerdetailsurl']);
					if($resourceownerdetailsurl != '') {
						$newapp['resourceownerdetailsurl'] = $resourceownerdetailsurl;
					}
				}
				if(isset($_POST['mo_oauth_app_name'])) {
					$newapp['appId'] = stripslashes( $_POST['mo_oauth_app_name'] );
					if($_POST['mo_oauth_app_name'] === "gapps") {
						$newapp['authorizeurl'] = "https://accounts.google.com/o/oauth2/auth";
						$newapp['accesstokenurl'] = "https://www.googleapis.com/oauth2/v4/token";
						$newapp['resourceownerdetailsurl'] = "https://www.googleapis.com/oauth2/v1/userinfo";
					}
				}
				//$newapp['email_attr'] = $email_attr;
				//$newapp['name_attr'] = $name_attr;
				$appslist[$appname] = $newapp;
				update_option('mo_oauth_apps_list', $appslist);
				//update_option( 'message', 'Your settings are saved successfully.' );
				//$this->mo_oauth_show_success_message();
				wp_redirect('admin.php?page=mo_oauth_settings&tab=config&action=update&app='.urlencode($appname));
			}
		}
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_app_customization" ) {
			update_option( 'mo_oauth_icon_width', stripslashes($_POST['mo_oauth_icon_width']));
			update_option( 'mo_oauth_icon_height', stripslashes($_POST['mo_oauth_icon_height']));
			update_option( 'mo_oauth_icon_margin', stripslashes($_POST['mo_oauth_icon_margin']));
			update_option('mo_oauth_icon_configure_css', stripcslashes(stripslashes($_POST['mo_oauth_icon_configure_css'])));
			update_option( 'message', 'Your settings were saved' );
			$this->mo_oauth_show_success_message();
		}
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_attribute_mapping" ) {
			$appname = stripslashes( $_POST['mo_oauth_app_name'] );
			$username_attr = stripslashes( $_POST['mo_oauth_username_attr'] );
			$attr_option = stripslashes( $_POST['mo_attr_option'] );
			if ( empty( $appname ) ) {
				update_option( 'message', 'You MUST configure an application before you map attributes.' );
				$this->mo_oauth_show_error_message();
				return;
			}
			$appslist = get_option('mo_oauth_apps_list');
			foreach($appslist as $key => $currentapp){
					if($appname == $key){
						$currentapp['username_attr'] = $username_attr;
						$appslist[$key] = $currentapp;
					break;
				}
			}
		
			update_option('mo_oauth_apps_list', $appslist);
			update_option('mo_attr_option', $attr_option);
			update_option( 'message', 'Your settings are saved successfully.' );
			$this->mo_oauth_show_success_message();
			wp_redirect('admin.php?page=mo_oauth_settings&tab=attributemapping');
		}
		//submit google form
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_google" ) {
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$scope = '';
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_scope']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_client_id']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_client_secret'])) {
				update_option( 'message', 'Please enter Client ID and Client Secret to save settings.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$scope = stripslashes( $_POST['mo_oauth_google_scope'] );
				$clientid = stripslashes( $_POST['mo_oauth_google_client_id'] );
				$clientsecret = stripslashes( $_POST['mo_oauth_google_client_secret'] );
			}

			if(mo_oauth_is_customer_registered()) {
				update_option( 'mo_oauth_google_enable', isset( $_POST['mo_oauth_google_enable']) ? $_POST['mo_oauth_google_enable'] : 0);
				update_option( 'mo_oauth_google_scope', $scope);
				update_option( 'mo_oauth_google_client_id', $clientid);
				update_option( 'mo_oauth_google_client_secret', $clientsecret);
				if(get_option('mo_oauth_google_client_id') && get_option('mo_oauth_google_client_secret')) {
					$customer = new Customer();
					$message = $customer->add_oauth_application( 'google', 'Google OAuth' );
					if($message == 'Application Created') {
						update_option( 'message', 'Your settings were saved' );
						$this->mo_oauth_show_success_message();
					} else {
						update_option( 'message', $message );
						$this->mo_oauth_show_error_message();
					}
				} else {
					update_option( 'message', 'Please enter Client ID and Client Secret to save settings');
					update_option( 'mo_oauth_google_enable', false);
					$this->mo_oauth_show_error_message();
				}
			} else {
				update_option('message', 'Please register customer before trying to save other configurations');
				$this->mo_oauth_show_error_message();
			}
		}
		//submit eveonline form
		else if(isset($_POST['option']) and $_POST['option'] == "mo_oauth_eveonline"){
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_eveonline_client_secret']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_eveonline_client_secret'])) {
				update_option( 'message', 'Please enter Client ID and Client Secret to save settings.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$clientid = stripslashes($_POST['mo_oauth_eveonline_client_id']);
				$clientsecret = stripslashes($_POST['mo_oauth_eveonline_client_secret']);
			}

			if(mo_oauth_is_customer_registered()) {
				update_option( 'mo_oauth_eveonline_enable', isset($_POST['mo_oauth_eveonline_enable']) ? $_POST['mo_oauth_eveonline_enable'] : 0);
				update_option( 'mo_oauth_eveonline_client_id', $clientid);
				update_option( 'mo_oauth_eveonline_client_secret', $clientsecret);
				if(get_option('mo_oauth_eveonline_client_id') && get_option('mo_oauth_eveonline_client_secret')) {
					$customer = new Customer();
					$message = $customer->add_oauth_application('eveonline', 'EVE Online OAuth');
					if($message == 'Application Created') {
						update_option('message', 'Your settings were saved. Go to Advanced EVE Online Settings for configuring restrictions on user sign in.');
						$this->mo_oauth_show_success_message();
					} else {
						update_option('message', $message);
						$this->mo_oauth_show_error_message();
					}
				} else {
					update_option( 'message', 'Please enter Client ID and Client Secret to save settings');
					update_option( 'mo_oauth_eveonline_enable', false);
					$this->mo_oauth_show_error_message();
				}
			} else {
				update_option('message', 'Please register customer before trying to save other configurations');
				$this->mo_oauth_show_error_message();
			}
		}
		// submit facebook app
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_facebook" ) {
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			//validation and sanitization
			$scope = '';
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_facebook_scope']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_facebook_client_id']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_facebook_client_secret'])) {
				update_option( 'message', 'Please enter Client ID and Client Secret to save settings.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$scope = stripslashes( $_POST['mo_oauth_facebook_scope'] );
				$clientid = stripslashes( $_POST['mo_oauth_facebook_client_id'] );
				$clientsecret = stripslashes( $_POST['mo_oauth_facebook_client_secret'] );
			}

			if(mo_oauth_is_customer_registered()) {
				update_option( 'mo_oauth_facebook_enable', isset( $_POST['mo_oauth_facebook_enable']) ? $_POST['mo_oauth_facebook_enable'] : 0);
				update_option( 'mo_oauth_facebook_scope', $scope);
				update_option( 'mo_oauth_facebook_client_id', $clientid);
				update_option( 'mo_oauth_facebook_client_secret', $clientsecret);
				if(get_option('mo_oauth_facebook_client_id') && get_option('mo_oauth_facebook_client_secret')) {
					$customer = new Customer();
					$message = $customer->add_oauth_application( 'facebook', 'Facebook OAuth' );
					if($message == 'Application Created') {
						update_option( 'message', 'Your settings were saved' );
						$this->mo_oauth_show_success_message();
					} else {
						update_option( 'message', $message );
						$this->mo_oauth_show_error_message();
					}
				} else {
					update_option( 'message', 'Please enter Client ID and Client Secret to save settings');
					update_option( 'mo_oauth_google_enable', false);
					$this->mo_oauth_show_error_message();
				}
			} else {
				update_option('message', 'Please register customer before trying to save other configurations');
				$this->mo_oauth_show_error_message();
			}
		}
		elseif( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_contact_us_query_option" ) {
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
			// Contact Us query
			$email = $_POST['mo_oauth_contact_us_email'];
			$phone = $_POST['mo_oauth_contact_us_phone'];
			$query = $_POST['mo_oauth_contact_us_query'];
			$send_config = isset( $_POST['mo_oauth_send_plugin_config'] );
			$customer = new Customer();
			if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $query ) ) {
				update_option('message', 'Please fill up Email and Query fields to submit your query.');
				$this->mo_oauth_show_error_message();
			} else {
				// $submited = json_decode( $customer->mo_oauth_send_email_alert( $email, $phone, $query, "Query for WP OAuth Single Sign On - ".$email ), true );
				// update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
				// $this->mo_oauth_show_success_message();
				$submited = $customer->submit_contact_us( $email, $phone, $query, $send_config );
				if ( $submited == false ) {
					update_option('message', 'Your query could not be submitted. Please try again.');
					$this->mo_oauth_show_error_message();
				} else {
					update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
					$this->mo_oauth_show_success_message();
				}
			}
		}
		
		elseif( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_client_demo_request_form" ) {
			// if( mo_oauth_is_curl_installed() == 0 ) {
			// 	return $this->mo_oauth_show_curl_error();
			// }
			$email = $_POST['mo_oauth_client_demo_email'];
			$demo_plan = $_POST['mo_oauth_client_demo_plan'];
			$query = $_POST['mo_oauth_client_demo_usecase'];
			$customer = new Customer();
			if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $demo_plan ) || $this->mo_oauth_check_empty_or_null( $query ) ) {
				update_option('message', 'Please fill up Usecase, Email field and Requested demo plan to submit your query.');
				$this->mo_oauth_show_error_message();
			} else {
				$submited = json_decode( $customer->mo_oauth_send_demo_alert( $email, $demo_plan, $query, "WP OAuth/OpenID Connect Single Sign On Demo Request - ".$email ), true );
				update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
				$this->mo_oauth_show_success_message();
			}
		}

		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_resend_otp_email" ) {
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}

			$customer = new Customer();
			$email=get_option('mo_oauth_admin_email');
			$content = json_decode($customer->send_otp_token($email, ''), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_option( 'message', ' A one time passcode is sent to ' . get_option('mo_oauth_admin_email') . ' again. Please check if you got the otp and enter it here.');
					$_SESSION['mo_oauth_transactionId'] = $content['txId'];
					update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_SUCCESS');
					$this->mo_oauth_show_success_message();
			}else{
					update_option('message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_FAILURE');
					$this->mo_oauth_show_error_message();
			}
		} else if (isset($_POST ['option']) and $_POST ['option'] == "mo_oauth_resend_otp_phone") {

				if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}
				$phone = get_option('mo_oauth_admin_phone');
				$customer = new Customer();
				$content = json_decode($customer->send_otp_token('', $phone, FALSE, TRUE), true);
				if (strcasecmp($content ['status'], 'SUCCESS') == 0) {
					update_option('message', ' A one time passcode is sent to ' . $phone . ' again. Please check if you got the otp and enter it here.');
					update_option('mo_oauth_transactionId', $content ['txId']);
					update_option('mo_oauth_registration_status', 'MO_OTP_DELIVERED_SUCCESS_PHONE');
					$this->mo_oauth_show_success_message();
				} else {
					update_option('message', 'There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_oauth_registration_status', 'MO_OTP_DELIVERED_FAILURE_PHONE');
					$this->mo_oauth_show_error_message();
				}
			}else if (isset($_POST ['option']) && $_POST ['option'] == 'mo_oauth_forgot_password_form_option') {
				if (! mo_oauth_is_curl_installed()) {
					update_option('mo_oauth_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
					$this->mo_oauth_show_error_message();
					return;
				}

				$email = get_option('mo_oauth_admin_email');

				$customer = new Customer();
				$content = json_decode($customer->mo_oauth_forgot_password($email), true);
				
				if (strcasecmp($content ['status'], 'SUCCESS') == 0) {
					update_option('message', 'Your password has been reset successfully. Please enter the new password sent to ' . $email . '.');
					$this->mo_oauth_show_success_message();
			}
		} else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_change_email" ) {
			//Adding back button
			update_option('verify_customer', '');
			update_option('mo_oauth_registration_status','');
			update_option('new_registration','true');
		} else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_register_with_phone_option" ) {
			if(!mo_oauth_is_curl_installed()) {
				return $this->mo_oauth_show_curl_error();
			}
			$phone = stripslashes($_POST['phone']);
			$phone = str_replace(' ', '', $phone);
			$phone = str_replace('-', '', $phone);
			update_option('mo_oauth_admin_phone', $phone);
			$customer = new Customer();
			$content=json_decode( $customer->send_otp_token('', $phone, FALSE, TRUE),true);
			if($content) {
				update_option( 'message', ' A one time passcode is sent to ' . get_site_option('mo_oauth_admin_phone') . '. Please enter the otp here to verify your email.');
				$_SESSION['mo_oauth_transactionId'] = $content['txId'];
				update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_SUCCESS_PHONE');
				$this->mo_oauth_show_success_message();
			}else{
				update_option('message','There was an error in sending SMS. Please click on Resend OTP to try again.');
				update_option('mo_oauth_registration_status','MO_OTP_DELIVERED_FAILURE_PHONE');
				$this->mo_oauth_show_error_message();
			}
		}
		
		else if ( isset( $_POST['option'] ) and $_POST['option'] == 'mo_oauth_client_skip_feedback' ) {
			deactivate_plugins( __FILE__ );
			update_option( 'message', 'Plugin deactivated successfully' );
			$this->mo_oauth_show_success_message();
		}
		else if ( isset( $_POST['mo_oauth_client_feedback'] ) and $_POST['mo_oauth_client_feedback'] == 'true' ) {
			$user = wp_get_current_user();
			$message = 'Plugin Deactivated:';
			$deactivate_reason         = array_key_exists( 'deactivate_reason_radio', $_POST ) ? $_POST['deactivate_reason_radio'] : false;
			$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? $_POST['query_feedback'] : false;
			if ( $deactivate_reason ) {
				$message .= $deactivate_reason;
				if ( isset( $deactivate_reason_message ) ) {
					$message .= ':' . $deactivate_reason_message;
				}
				$email = get_option( "mo_oauth_admin_email" );
				if ( $email == '' ) {
					$email = $user->user_email;
				}
				$phone = get_option( 'mo_oauth_admin_phone' );
				//only reason
				$feedback_reasons = new Customer();
				$submited = json_decode( $feedback_reasons->mo_oauth_send_email_alert( $email, $phone, $message, "Feedback: WordPress OpenID Connect Login ( OpenID Connect Client)" ), true );
				deactivate_plugins( __FILE__ );
				update_option( 'message', 'Thank you for the feedback.' );
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Please Select one of the reasons ,if your reason is not mentioned please select Other Reasons' );
				$this->mo_oauth_show_error_message();
			}
		}
				

	}

	function mo_oauth_get_current_customer(){
		$customer = new Customer();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
			update_option( 'customer_token', $customerKey['token'] );
			update_option('password', '' );
			update_option( 'message', 'Customer retrieved successfully' );
			delete_option('verify_customer');
			delete_option('new_registration');
			$this->mo_oauth_show_success_message();
			//mo_register();
		} else {
			update_option( 'message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('verify_customer', 'true');
			///delete_option('new_registration');
			//mo_register();
			$this->mo_oauth_show_error_message();

		}
	}

	function create_customer(){
		$customer = new Customer();
		$customerKey = json_decode( $customer->create_customer(), true );
		if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
			$this->mo_oauth_get_current_customer();
			delete_option('mo_oauth_new_customer');
		} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
			update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
			update_option( 'customer_token', $customerKey['token'] );
			update_option( 'password', '');
			update_option( 'message', 'Registered successfully.');
			update_option('mo_oauth_registration_status','MO_OAUTH_REGISTRATION_COMPLETE');
			update_option('mo_oauth_new_customer',1);
			delete_option('verify_customer');
			delete_option('new_registration');
			$this->mo_oauth_show_success_message();
		}
	}

	function mo_oauth_show_curl_error() {
		if( mo_oauth_is_curl_installed() == 0 ) {
			update_option( 'message', '<a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled. Please enable it to continue.');
			$this->mo_oauth_show_error_message();
			return;
		}
	}

	function mo_oauth_shortcode_login(){
		if(mo_oauth_hbca_xyake() || !mo_oauth_is_customer_registered()) {
			return '<div class="mo_oauth_premium_option_text" style="text-align: center;border: 1px solid;margin: 5px;padding-top: 25px;"><p>This feature is supported only in standard and higher versions.</p>
				<p><a href="'.get_site_url(null, '/wp-admin/').'admin.php?page=mo_oauth_settings&tab=licensing">Click Here</a> to see our full list of Features.</p></div>';
		}
		$mowidget = new Mo_Oauth_Widget;
		return $mowidget->mo_oauth_login_form();
	}

	function export_plugin_config( $share_with = false ) {
		$appslist = get_option('mo_oauth_apps_list');
		$currentapp_config = null;
		if ( is_array( $appslist ) ) {
			foreach( $appslist as $key => $value ) {
				$currentapp_config = $value;
				break;
			}
		}
		if ( $share_with ) {
			unset( $currentapp_config['clientid'] );
			unset( $currentapp_config['clientsecret'] );
		}
		return $currentapp_config;
	}

}

	function mo_oauth_my_show_extra_profile_fields($user) {
		?>
		<h3>Extra profile information</h3>
		<table class="form-table">
			<tr>
				<th><label for="characterName">Character Name</label></th>
				<td>
					<input type="text" id="characterName" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_character_name', true ); ?>" class="regular-text" /><br />
				</td>
				<td rowspan="3"><?php echo mo_oauth_avatar_manager_get_custom_avatar( $user->ID, '128' ); ?></td>
			</tr>
			<tr>
				<th><label for="corporation">Corporation Name</label></th>
				<td>
					<input type="text" id="corporation" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_corporation_name', true ); ?>" class="regular-text" /><br />
				</td>
			</tr>
			<tr>
				<th><label for="alliance">Alliance Name</label></th>
				<td>
					<input type="text" id="alliance" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_alliance_name', true ); ?>" class="regular-text" /><br />
				</td>
			</tr>
		</table>
	<?php
	}

	function mo_oauth_is_customer_registered() {
		$email 			= get_option('mo_oauth_admin_email');
		$phone 			= get_option('mo_oauth_admin_phone');
		$customerKey 	= get_option('mo_oauth_admin_customer_key');
		if( ! $email || ! $phone || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

	function mo_oauth_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else {
			return 0;
		}
	}

new mo_openidconnect;
function run_mo_oauth_client() { $plugin = new Mo_OAuth_Client();$plugin->run();}
run_mo_oauth_client();
