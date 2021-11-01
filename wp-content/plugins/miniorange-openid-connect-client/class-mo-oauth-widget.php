<?php

//use Pheal\Pheal;
//use Pheal\Core\Config;

class Mo_OpenIDConnect_Widget extends WP_Widget {

	public function __construct() {
		update_option( 'host_name', 'https://login.xecurify.com' );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		add_action( 'login_form', array( $this, 'mo_oauth_wplogin_form_button' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'mo_oauth_wplogin_form_style' ) );
		parent::__construct( 'mo_openidconnect_widget', 'miniOrange OpenID Connect Login', array( 'description' => __( 'Login to Apps with OpenIDConnect', 'flw' ), ) );

	 }

	 function mo_oauth_wplogin_form_style(){
		wp_enqueue_style( 'mo_oauth_fontawesome', plugins_url( 'css/font-awesome.css', __FILE__ ) );
		wp_enqueue_style( 'mo_oauth_wploginform', plugins_url( 'css/login-page.css', __FILE__ ) );
	}

	
	function mo_oauth_wplogin_form_button() {
		$appslist = get_option('mo_oauth_apps_list');
		if(is_array($appslist) && sizeof($appslist) > 0){
			$this->mo_oauth_load_login_script();
			foreach($appslist as $key => $item){

				if(isset($item['show_on_login_page']) && $item['show_on_login_page'] === 1){

					$this->mo_oauth_wplogin_form_style();
					
					echo '<br>';
					echo '<h4>Connect with :</h4><br>';
					echo '<div class="row">';
					$logo_class = 'fa fa-lock';
					if( $item['appId']=='fbapps') {
						$logo_class='fa fa-facebook';
					}
					elseif( $item['appId']=='gapps') {
						$logo_class='fa fa-google-plus';
					}
					elseif( $item['appId']=='slack') {
						$logo_class='fa fa-slack';
					}
					elseif( $item['appId']=='paypal') {
						$logo_class='fa fa-paypal ';
					}
					elseif( $item['appId']=='azure') {
						$logo_class='fa fa-windows ';
					}
					elseif( $item['appId']=='amazon') {
						$logo_class='fa fa-amazon ';
					}
					elseif( $item['appId']=='github') {
						$logo_class='fa fa-github ';
					}
					elseif( $item['appId']=='yahoo') {
						$logo_class='fa fa-yahoo ';
					}
					elseif( $item['appId']=='openidconnect') {
						$logo_class='fa fa-openid ';
					}
					elseif( $item['appId']=='bitrix24') {
						$logo_class='fa fa-clock-o';
					}
					elseif( $item['appId']=='cognito') {
						$logo_class='fa fa-amazon';
					}
					elseif( $item['appId']=='adfs') {
						$logo_class='fa fa-windows';
					}
					echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\''.$key.'\');"><div class="mo_oauth_login_button"><i class="'.$logo_class.' mo_oauth_login_button_icon"></i><h3 class="mo_oauth_login_button_text">Login with '.ucwords($key).'</h3></div></a>';	
					echo '</div><br><br>';
				}
			}
		}
	}

	function mo_oauth_start_session() {
		if( ! session_id() && ! is_ajax_request() && ! is_rest_api_call() ) {
			session_start();
		}

		if(isset($_REQUEST['option']) and $_REQUEST['option'] == 'testattrmappingconfig'){
			$mo_oauth_app_name = $_REQUEST['app'];
			wp_redirect(site_url().'?option=oauthredirect&app_name='. urlencode($mo_oauth_app_name)."&test=true");
			exit();
		}

	}

	function mo_oauth_end_session() {
		if( ! session_id() )
		{ 	session_start();
        }
		session_destroy();
	}

	public function widget( $args, $instance ) {
		extract( $args );

		echo $args['before_widget'];
		if ( ! empty( $wid_title ) ) {
			echo $args['before_title'] . $wid_title . $args['after_title'];
		}
		echo $this->mo_oauth_login_form();
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		if(isset($new_instance['wid_title']))
			$instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
			
		return $instance;
	}

	public function mo_oauth_login_form() {
		global $post;
		$this->error_message();
		$temp = '';
		$appsConfigured = get_option('mo_oauth_google_enable') | get_option('mo_oauth_eveonline_enable') | get_option('mo_oauth_facebook_enable');

		$appslist = get_option('mo_oauth_apps_list');
		if($appslist && sizeof($appslist)>0)
			$appsConfigured = true;

		if( ! is_user_logged_in() ) {
			
			if( $appsConfigured ) {

				$this->mo_oauth_load_login_script();

				$style = get_option('mo_oauth_icon_width') ? "width:".get_option('mo_oauth_icon_width').";" : "";
				$style .= get_option('mo_oauth_icon_height') ? "height:".get_option('mo_oauth_icon_height').";" : "";
				$style .= get_option('mo_oauth_icon_margin') ? "margin:".get_option('mo_oauth_icon_margin').";" : "";
				$custom_css = get_option('mo_oauth_icon_configure_css');
				if(empty($custom_css))
					$temp .= '<style>.oauthloginbutton{background: #7272dc;height:40px;padding:8px;text-align:center;color:#fff;}</style>';
				else
					$temp .= '<style>'.$custom_css.'</style>';

				if( get_option('mo_oauth_google_enable') ) {
				$temp .= "<a href=\"javascript:void(0)\" onClick=\"moOAuthLogin('google');\"><img src=\"". plugins_url( 'images/icons/google.jpg', __FILE__ )."\"></a>";
				}
				if( get_option('mo_oauth_eveonline_enable') ) { 
				$temp .= "<a href=\"javascript:void(0)\" onClick=\"moOAuthLogin('eveonline');\"><img src=\"". plugins_url( 'images/icons/eveonline.png', __FILE__ )."\"></a>";
				}
				if( get_option('mo_oauth_facebook_enable') ) { 
				$temp .= "<a href=\"javascript:void(0)\" onClick=\"moOAuthLogin('facebook');\"><img src=\"". plugins_url( 'images/icons/facebook.png', __FILE__ )."\"></a>";
				}
				
				if (is_array($appslist)) {
					foreach($appslist as $key=>$app){/*
						if($key=="eveonline")
							continue;
							$imageurl = "";
						if($key=='facebook')
							$imageurl = plugins_url( 'images/fblogin.png', __FILE__ );
						else if($key=='google')
							$imageurl = plugins_url( 'images/googlelogin.png', __FILE__ );
						else if($key=='windows')
							$imageurl = plugins_url( 'images/windowslogin.png', __FILE__ );

						if(!empty($imageurl) && empty($custom_css)){
						$temp .= "<br/><div><a href=\"javascript:void(0)\" onClick=\"moOAuthLogin('".$key."');\"><img style=\"".$style."\" src=\"".$imageurl."\"></a></div>";
						} else {*/ 
							$logo_class = 'fa fa-lock';
							if( $app['appId']=='fbapps') {
								$logo_class='fa fa-facebook';
							}
							elseif( $app['appId']=='gapps') {
								$logo_class='fa fa-google-plus';
							}
							elseif( $app['appId']=='slack') {
								$logo_class='fa fa-slack';
							}
							elseif( $app['appId']=='paypal') {
								$logo_class='fa fa-paypal ';
							}
							elseif( $app['appId']=='azure') {
								$logo_class='fa fa-windows ';
							}
							elseif( $app['appId']=='amazon') {
								$logo_class='fa fa-amazon ';
							}
							elseif( $app['appId']=='github') {
								$logo_class='fa fa-github ';
							}
							elseif( $app['appId']=='yahoo') {
								$logo_class='fa fa-yahoo ';
							}
							elseif( $app['appId']=='openidconnect') {
								$logo_class='fa fa-openid ';
							}
							elseif( $app['appId']=='bitrix24') {
								$logo_class='fa fa-clock-o';
							}
							elseif( $app['appId']=='cognito') {
								$logo_class='fa fa-amazon';
							}
							elseif( $app['appId']=='adfs') {
								$logo_class='fa fa-windows';
							}
							$temp .= '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\''.$key.'\');"><div class="mo_oauth_login_button_widget"><i class="'.$logo_class.' mo_oauth_login_button_icon_widget"></i><h3 class="mo_oauth_login_button_text_widget">Login with '.ucwords($key).'</h3></div></a>';			
						}

					
				}


			} else {
				$temp .= '<div>No apps configured.</div>';
			}
		} else {
			$current_user = wp_get_current_user();
			$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
			$temp .= "<div id=\"logged_in_user\" class=\"login_wid\">
			<li>".$link_with_username." | <a href=\"".wp_logout_url( site_url() )."\" >Logout</a></li>
		</div>";
			
		}
		return $temp;
	}

	private function mo_oauth_load_login_script() {
	?>
	<script type="text/javascript">

		function HandlePopupResult(result) {
			window.location.href = result;
		}

		function moOAuthLogin(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=generateDynmicUrl&app_name=' + app_name;
		}
		function moOAuthLoginNew(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=oauthredirect&app_name=' + app_name;
		}
	</script>
	<?php
	}



	public function error_message() {
		if( isset( $_SESSION['msg'] ) and $_SESSION['msg'] ) {
			echo '<div class="' . $_SESSION['msg_class'] . '">' . $_SESSION['msg'] . '</div>';
			unset( $_SESSION['msg'] );
			unset( $_SESSION['msg_class'] );
		}
	}

	public function register_plugin_styles() {
		wp_enqueue_style( 'style_login_widget', plugins_url( 'css/style_login_widget.css', __FILE__ ) );
	}


}

function mo_oauth_update_email_to_username_attr($currentappname){
	$appslist = get_option('mo_oauth_apps_list');
	$appslist[$currentappname]['username_attr'] = $appslist[$currentappname]['email_attr'];
	update_option('mo_oauth_apps_list',$appslist);
}

	function mo_oauth_login_validate(){

		/* Handle Eve Online old flow */
		if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthredirect' ) !== false ) {
			$appname = $_REQUEST['app_name'];
			$appslist = get_option('mo_oauth_apps_list');
			if(isset($_REQUEST['redirect_url'])){
				update_option('mo_oauth_redirect_url',$_REQUEST['redirect_url']);
			}

			if(isset($_REQUEST['test']))
				setcookie("mo_oauth_test", true);
			else
				setcookie("mo_oauth_test", false);

			if($appslist == false){
				exit("Looks like you have not configured OAuth provider, please try to configure OAuth provider first");
			}

			foreach($appslist as $key => $app){
				if($appname==$key){

					$state = base64_encode($appname);
					$authorizationUrl = $app['authorizeurl'];
					if(strpos($authorizationUrl, "google") !== false) {
						$authorizationUrl = "https://accounts.google.com/o/oauth2/auth";
					}
					if(strpos($authorizationUrl, '?' ) !== false)
					$authorizationUrl = $authorizationUrl."&client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;
				    else 
					$authorizationUrl = $authorizationUrl."?client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;

					if(session_id() == '' || !isset($_SESSION))
						session_start();
					$_SESSION['oauth2state'] = $state;
					$_SESSION['appname'] = $appname;

					header('Location: ' . $authorizationUrl);
					exit;
				}
			}
		}

		else if(strpos($_SERVER['REQUEST_URI'], "/oauthcallback") !== false || isset($_GET['code'])) {  

			if(session_id() == '' || !isset($_SESSION))
				session_start();

			// OAuth state security check
			/*
			if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
				if (isset($_SESSION['oauth2state'])) {
					unset($_SESSION['oauth2state']);
				}
				exit('Invalid state');
			} */

			if (!isset($_GET['code'])){
				if(isset($_GET['error_description']))
					exit($_GET['error_description']);
				else if(isset($_GET['error']))
					exit($_GET['error']);
				exit('Invalid response');
			} else {

				try {

					$currentappname = "";

					if (isset($_SESSION['appname']) && !empty($_SESSION['appname']))
						$currentappname = $_SESSION['appname'];
					else if (isset($_GET['state']) && !empty($_GET['state'])){
						$currentappname = base64_decode($_GET['state']);
					}

					if (empty($currentappname)) {
						exit('No request found for this application.');
					}

					$appslist = get_option('mo_oauth_apps_list');
					$username_attr = "";
					$currentapp = false;
					foreach($appslist as $key => $app){
						if($key == $currentappname){
							$currentapp = $app;
							if(isset($app['username_attr']) && $app["username_attr"]!=""){
								$username_attr = $app['username_attr'];
							}else if(isset($app['email_attr']) && $app["email_attr"]!=""){
									mo_oauth_update_email_to_username_attr($currentappname);
									$username_attr = $app['email_attr'];	
							}
						}
					}

					if (!$currentapp)
						exit('Application not configured.');

					$mo_oauth_handler = new Mo_OAuth_Hanlder();

                        if(!isset($currentapp['send_headers']))
                        	$currentapp['send_headers']=false;
                        if(!isset($currentapp['send_body']))
                        	$currentapp['send_body']=false;

					if(isset($currentapp['apptype']) && $currentapp['apptype']=='openidconnect') {
							// OpenId connect
						// echo "OpenID Connect";
						$tokenResponse = $mo_oauth_handler->getIdToken($currentapp['accesstokenurl'], 'authorization_code',
								$currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body']);

						$idToken = isset($tokenResponse["id_token"]) ? $tokenResponse["id_token"] : $tokenResponse["access_token"];

								
						if(!$idToken)
							exit('Invalid token received.');
						else
							$resourceOwner = $mo_oauth_handler->getResourceOwnerFromIdToken($idToken);

					} else {
						// echo "OAuth";
						$accessTokenUrl = $currentapp['accesstokenurl'];
						if(strpos($accessTokenUrl, "google") !== false) {
							$accessTokenUrl = "https://www.googleapis.com/oauth2/v4/token";
						}
                        


						$accessToken = $mo_oauth_handler->getAccessToken($accessTokenUrl, 'authorization_code', $currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body']);
                                                
                        
						if(!$accessToken)
							exit('Invalid token received.');

						$resourceownerdetailsurl = $currentapp['resourceownerdetailsurl'];
						if (substr($resourceownerdetailsurl, -1) == "=") {
							$resourceownerdetailsurl .= $accessToken;
						}
						if(strpos($resourceownerdetailsurl, "google") !== false) {
							$resourceownerdetailsurl = "https://www.googleapis.com/oauth2/v1/userinfo";
						}
						$resourceOwner = $mo_oauth_handler->getResourceOwner($resourceownerdetailsurl, $accessToken);
					}
					$username = "";
					update_option('mo_oauth_attr_name_list', $resourceOwner);
					//TEST Configuration
					if(isset($_COOKIE['mo_oauth_test']) && $_COOKIE['mo_oauth_test']){
						echo '<div style="font-family:Calibri;padding:0 3%;">';
						echo '<style>table{border-collapse:collapse;}th {background-color: #eee; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#212121;}tr:nth-child(odd) {background-color: #f2f2f2;} td{padding:8px;border-width:1px; border-style:solid; border-color:#212121;}</style>';
						echo "<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>";
						testattrmappingconfig("",$resourceOwner);
						echo "</table>";
						echo '<div style="padding: 10px;"></div><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
						exit();
					}

					if(!empty($username_attr))
						$username = getnestedattribute($resourceOwner, $username_attr); //$resourceOwner[$email_attr];

					if(empty($username) || "" === $username)
						exit('Username not received. Check your <b>Attribute Mapping</b> configuration.');
					
					if ( ! is_string( $username ) ) {
						wp_die( 'Username is not a string. It is ' . get_proper_prefix( gettype( $username ) ) );
					}

					$user = get_user_by("login",$username);
					// if(!$user)
					// 	$user = get_user_by( 'email', $username);

					if($user){
						$user_id = $user->ID;
					} else {
						$user_id = 0;
						if(mo_oauth_hbca_xyake()) {
								$user = mo_oauth_jhuyn_jgsukaj($username);
						} else {
							$user = mo_oauth_hjsguh_kiishuyauh878gs($username);
						}
						
					}
					if($user){
						wp_set_current_user($user->ID);
						wp_set_auth_cookie($user->ID);
						$user  = get_user_by( 'ID',$user->ID );
						do_action( 'wp_login', $user->user_login, $user );
						$redirect_to = get_option('mo_oauth_redirect_url');

						if($redirect_to == false){
							$redirect_to = home_url();
						}

						wp_redirect($redirect_to);						
						exit;
					}


				} catch (Exception $e) {

					// Failed to get the access token or user details.
					//print_r($e);
					exit($e->getMessage());

				}

			}

		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'generateDynmicUrl' ) !== false ) {
			$client_id = get_option('mo_oauth_' . $_REQUEST['app_name'] . '_client_id');
			$timestamp = round( microtime(true) * 1000 );
			$api_key = get_option('mo_oauth_admin_api_key');
			$token = $client_id . ':' . number_format($timestamp, 0, '', '') . ':' . $api_key;

			$customer_token = get_option('customer_token');
			$method = 'AES-128-ECB';
			$ivSize = openssl_cipher_iv_length($method);
			$iv     = openssl_random_pseudo_bytes($ivSize);
			$token_params_encrypt = openssl_encrypt ($token, $method, $customer_token,OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING, $iv);
			$token_params_encode = base64_encode( $iv.$token_params_encrypt );
			$token_params = urlencode( $token_params_encode );

			$return_url = urlencode( site_url() . '/?option=mooauth' );
			$url = get_option('host_name') . '/moas/oauth/client/authorize?token=' . $token_params . '&id=' . get_option('mo_oauth_admin_customer_key') . '&encrypted=true&app=' . $_REQUEST['app_name'] . '_oauth&returnurl=' . $return_url;
			wp_redirect( $url );
			exit;
		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'mooauth' ) !== false ){

			//do stuff after returning from oAuth processing
			$access_token 	= $_POST['access_token'];
			$token_type	 	= $_POST['token_type'];
			$user_email = '';
			if(array_key_exists('email', $_POST))
				$user_email 	= $_POST['email'];


			if( $user_email ) {
				if( email_exists( $user_email ) ) { // user is a member
					  $user 	= get_user_by('email', $user_email );
					  $user_id 	= $user->ID;
					  wp_set_auth_cookie( $user_id, true );
				} else { // this user is a guest
					  $random_password 	= wp_generate_password( 10, false );
					  $user_id 			= wp_create_user( $user_email, $random_password, $user_email );
					  wp_set_auth_cookie( $user_id, true );
				}
			} else if( $_POST['CharacterID'] ) {		//the user is trying to login through eve online
				$_SESSION['character_id'] = $_POST['CharacterID'];
				$_SESSION['character_name'] = $_POST['CharacterName'];
				Config::getInstance()->access = new \Pheal\Access\StaticCheck();

				$keyID = get_option('mo_eve_api_key');
				$vCode = get_option('mo_eve_verification_code');
				if( $keyID && $vCode ) {

					$pheal = new Pheal( $keyID, $vCode, "eve" );

					try{
						$response = $pheal->CharacterInfo(array("characterID" => $_SESSION['character_id']));
						$_SESSION['corporation_name']	= $response->corporation;
						$_SESSION['alliance_name'] 		= $response->alliance;
					} catch (\Pheal\Exceptions\PhealException $e) {
						/*echo sprintf(
							"an exception was caught! Type: %s Message: %s",
							get_class($e),
							$e->getMessage()
						);*/
					}

					$corporations 	= get_option('mo_eve_allowed_corps') ? get_option('mo_eve_allowed_corps') : false;
					$alliances 		= get_option('mo_eve_allowed_alliances') ? get_option('mo_eve_allowed_alliances') : false;
					$characterNames = get_option('mo_eve_allowed_char_name') ? get_option('mo_eve_allowed_char_name') : false;
					$valid_char 	= false;

					if( ! $corporations && ! $alliances && ! $characterNames ) {
						$valid_char = true;
					} else {
						if(isset($_SESSION['corporation_name']))
							$valid_corp 			= mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_corps'), $_SESSION['corporation_name'], 'corporation_name');
						else
							$valid_corp = "";
						if(isset($_SESSION['alliance_name']))
							$valid_alliance = mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_alliances'), $_SESSION['alliance_name'], 'alliance_name');
						else
							$valid_alliance = "";
						if(isset($_SESSION['character_name']))
							$valid_character_name 	= mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_char_name'), $_SESSION['character_name'], 'character_name');
						else
							$character_name = "";

						$valid_char = $valid_corp || $valid_alliance || $valid_character_name;
					}
					if( $valid_char ) {			//if corporation or alliance or character name is valid
						$characterID = $_SESSION['character_id'];
						$eveonline_email = $characterID . '.eveonline@wordpress.com';
						if( username_exists( $characterID ) ) {
							$user = get_user_by( 'login', $characterID );
							$user_id = $user->ID;

							update_user_meta( $user_id, 'user_eveonline_corporation_name', $_SESSION['corporation_name'] );
							update_user_meta( $user_id, 'user_eveonline_alliance_name', $_SESSION['alliance_name'] );
							update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
							set_avatar( $user_id, $characterID );
							wp_set_auth_cookie( $user_id, true );
						} else {
							$random_password = wp_generate_password( 10, false );
							$userdata = array(
								'user_login'	=>	$characterID,
								'user_email'	=>	$eveonline_email,
								'user_pass'		=>	$random_password,
								'display_name'	=>	$_SESSION['character_name'],
								'last_name'		=>	$_SESSION['character_name']
							);

							$user_id = wp_insert_user( $userdata ) ;
							update_user_meta($user_id, 'user_eveonline_corporation_name', $_SESSION['corporation_name']);
							update_user_meta($user_id, 'user_eveonline_alliance_name', $_SESSION['alliance_name']);
							update_user_meta($user_id, 'user_eveonline_character_name', $_SESSION['character_name']);
							set_avatar( $user_id, $characterID );
							wp_set_auth_cookie( $user_id, true );
						}
					} else{
						error_reporting(0);
						?>
						<table>

								<div class="rectangle" style="width:700px; height:180px;  margin:5% auto;">
								<h1 style="text-align:center">Access Denied!</h1>
								<div style="font-size:22px; color:#222;padding:20px;text-align:center;background:#F1F1F1;border:1.5px solid grey;  box-shadow: 10px 10px 5px grey;">It seems that either of your Corporation, Alliance or Character Name is not allowed to access this site.<br><br>
								Please contact site Administrator to get access.<br></div>
								</div>

						</table>
						<?php
						exit();
					}
				} else {
					// If API and vCode is not setup - login the user using Character ID
					$characterID = $_SESSION['character_id'];
					$eveonline_email = $characterID . '.eveonline@wordpress.com';
					if( username_exists( $characterID ) ) {
						$user = get_user_by( 'login', $characterID );
						$user_id = $user->ID;
						update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
						set_avatar( $user_id, $characterID );
						wp_set_auth_cookie( $user_id, true );
					} else {
						$random_password = wp_generate_password( 10, false );
						$userdata = array(
							'user_login'	=>	$characterID,
							'user_email'	=>	$eveonline_email,
							'user_pass'		=>	$random_password,
							'display_name'	=>	$_SESSION['character_name'],
							'last_name'		=>	$_SESSION['character_name']
						);
						$user_id = wp_insert_user( $userdata ) ;
						update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
						set_avatar( $user_id, $characterID );
						wp_set_auth_cookie( $user_id, true );
					}
				}
			}
			wp_redirect( home_url() );
			exit;
		}
		/* End of old flow */
	}

	function mo_oauth_hjsguh_kiishuyauh878gs($username)
	{
		$random_password = wp_generate_password( 10, false );
		// if(is_email($email))
		// 	$user_id = wp_create_user( $email, $random_password, $email );
		// else
		// 	$user_id = wp_create_user( $email, $random_password);	
		$user_id = 	wp_create_user( $username, $random_password);
		$user = get_user_by( 'login', $username);			
		wp_update_user( array( 'ID' => $user_id ) );
		return $user;
	}

	//here entity is corporation, alliance or character name. The administrator compares these when user logs in
	function mo_oauth_check_validity_of_entity($entityValue, $entitySessionValue, $entityName) {

		$entityString = $entityValue ? $entityValue : false;
		$valid_entity = false;
		if( $entityString ) {			//checks if entityString is defined
			if ( strpos( $entityString, ',' ) !== false ) {			//checks if there are more than 1 entity defined
				$entity_list = array_map( 'trim', explode( ",", $entityString ) );
				foreach( $entity_list as $entity ) {			//checks for each entity to exist
					if( $entity == $entitySessionValue ) {
						$valid_entity = true;
						break;
					}
				}
			} else {		//only one entity is defined
				if( $entityString == $entitySessionValue ) {
					$valid_entity = true;
				}
			}
		} else {			//entity is not defined
			$valid_entity = false;
		}
		return $valid_entity;
	}

	function mo_oauth_jhuyn_jgsukaj($temp_var)
	{
		return mo_oauth_jkhuiysuayhbw($temp_var);
	}

	function testattrmappingconfig($nestedprefix, $resourceOwnerDetails){
		foreach($resourceOwnerDetails as $key => $resource){
			if(is_array($resource) || is_object($resource)){
				if(!empty($nestedprefix))
					$nestedprefix .= ".";
				testattrmappingconfig($nestedprefix.$key,$resource);
				$nestedprefix = rtrim($nestedprefix,".");
			} else {
				echo "<tr><td>";
				if(!empty($nestedprefix))
					echo $nestedprefix.".";
				echo $key."</td><td>".$resource."</td></tr>";
			}
		}
	}

	function getnestedattribute($resource, $key){
		//echo $key." : ";print_r($resource); echo "<br>";
		if($key==="")
			return "";

		$keys = explode(".",$key);
		if(sizeof($keys)>1){
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return getnestedattribute($resource[$current_key], str_replace($current_key.".","",$key));
		} else {
			$current_key = $keys[0];
			if(isset($resource[$current_key])) {
				return $resource[$current_key];
			}
		}
	}

	function mo_oauth_jkhuiysuayhbw($ejhi)
	{
		$user = mo_oauth_hjsguh_kiishuyauh878gs($ejhi);						
		return $user;
	}

	function get_proper_prefix( $type ) {
		$letter = substr( $type, 0, 1 );
		$vowels = [ 'a', 'e', 'i', 'o', 'u' ];
		return ( in_array( $letter, $vowels ) ) ? ' an ' . $type : ' a ' . $type;
	}

	function register_mo_oauth_widget() {
		register_widget('mo_openidconnect_widget');
	}

	function is_ajax_request() {
		return defined('DOING_AJAX') && DOING_AJAX;
	}

	function is_rest_api_call() {
		return strpos( $_SERVER['REQUEST_URI'], '/wp-json' ) == false;
	}

	add_action('widgets_init', 'register_mo_oauth_widget');
	add_action( 'init', 'mo_oauth_login_validate' );
?>
