<?php
/*
Plugin Name: Multiple Domain Mapping on single site
Plugin URI:  https://wordpress.org/plugins/multiple-domain-mapping-on-single-site/
Description: Show specific posts, pages, ... within their own, additional domains. Useful for SEO: different domains for landingpages.
Version:     1.0.5
Author:      Matthias Wagner - FALKEmedia
Author URI:  https://www.matthias-wagner.at
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: falke_mdm
Domain Path: /languages

Multiple Domain Mapping on single site is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Multiple Domain Mapping on single site is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Multiple Domain Mapping on single site. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// If this file is called directly, abort.
if( !defined( 'ABSPATH' ) ){
	die('...');
}
// support for older php versions
if( !defined( 'PHP_INT_MIN' ) ){
	define('PHP_INT_MIN', -2147483648);
}

if( !class_exists( 'FALKE_MultipleDomainMapping' ) ){
	class FALKE_MultipleDomainMapping{

		//The unique instance of the plugin.
    private static $instance;

    //Gets an instance of our plugin.
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

		//variables
		private $mappings = false;
		private $settings = false;
		private $originalRequestURI = false;
		private $currentURI = false;
		private $currentMapping = array(
			'match' => false,
			'factor' => PHP_INT_MIN
		);

		//constructor
	  private function __construct(){

			//perform database update check
			require_once(plugin_dir_path( __FILE__ ) . 'includes/upgrades/v_1_0.php');
			add_action('admin_init', array($this, 'handleUpgradeNotice'));

			//retrieve options
			$this->setMappings(get_option('falke_mdm_mappings'));
			$this->setSettings(get_option('falke_mdm_settings'));

			//backend
	  	add_action( 'plugins_loaded', array( $this, 'set_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			//set current uri
			$this->setCurrentURI($_SERVER[(!empty($this->getSettings()) && isset($this->getSettings()['php_server'])) ? $this->getSettings()['php_server'] : 'SERVER_NAME'] . $_SERVER['REQUEST_URI']);

			//process request
			add_filter( 'do_parse_request', array( $this, 'parse_request' ), 10, 3 );
			add_filter( 'redirect_canonical', array( $this, 'check_canonical_redirect' ), 10, 2 );

			//some hooks to change occurences of orignal domain to mapped domain
			$this->replace_uris();

			//hook some stuff into our own actions
			add_action( 'plugins_loaded', array( $this, 'hookMDMAction'), 20);
	  }

		//setters/getters
		private function setMappings($mappings){
			$this->mappings = $mappings;
		}
		public function getMappings(){
			return $this->mappings;
		}
		private function setSettings($settings){
			$this->settings = $settings;
		}
		public function getSettings(){
			return $this->settings;
		}
		private function setCurrentURI($uri){
			$this->currentURI = trailingslashit( $uri );
		}
		public function getCurrentURI(){
			return $this->currentURI;
		}
		private function setCurrentMapping($mapping){
			$this->currentMapping = $mapping;
		}
		public function getCurrentMapping(){
			return $this->currentMapping;
		}
		private function setOriginalRequestURI($uri){
			$this->originalRequestURI = $uri;
		}
		public function getOriginalRequestURI(){
			return $this->originalRequestURI;
		}
		public function getOriginalURI(){
			global $wp;
			return home_url( $wp->request );
		}

		//set textdomain
	  public function set_textdomain(){
			load_plugin_textdomain( 'falke_mdm', false, dirname( plugin_basename( plugin_basename(__FILE__) ) ) . '/languages/' );
	  }

		//enqueue scripts and styles in admin
		public function admin_scripts(){
			//custom assets
			wp_enqueue_style( 'falke_mdm_adminstyle', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );
			wp_register_script( 'falke_mdm_adminscript', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array('jquery', 'jquery-ui-accordion'), false, true );
			wp_localize_script( 'falke_mdm_adminscript', 'localizedObj', array(
				'removedMessage' => sprintf('%s "%s"', esc_html__('Mapping will be removed permanently as soon as you click', 'falke_mdm'), esc_html__('Save Mappings', 'falke_mdm')),
				'undoMessage' => esc_html__('Undo unsaved changes', 'falke_mdm'),
				'dismissMessage' => __( 'Dismiss this notice.' )
			) );
			wp_enqueue_script( 'falke_mdm_adminscript' );
		}

		//we handle the upgrade notice that may be prepared in the included upgrade-script
		public function handleUpgradeNotice(){
			$notice = get_option('falke_mdm_notice');
			if($notice !== false){
				add_action('admin_notices', array($this, 'outputUpgradeNotice'));
			}
		}
		public function outputUpgradeNotice(){
			$notice = get_option('falke_mdm_notice');
			if($notice !== false){
				//here we can also check the current screen and remove the notice if the user has visited our plugin page once
				$screen = get_current_screen();
				if(!is_null($screen)){
					//plugin page is visited
					if(stripos( $screen->id, str_ireplace('.php', '', plugin_basename(__FILE__)) ) !== false){
						delete_option('falke_mdm_notice');
						//this one last time we show the extra notice
						$notice['text'] = $notice['onScreenText'];
					}
				}

				//do the output
				echo '<div class="'.$notice['class'].'">'.$notice['text'].'</div>';
			}
		}

		//generate menu entry
		public function add_menu_page(){
			// check user capabilities
	    if (!current_user_can('manage_options')) {
	        return;
	    }
			add_submenu_page( 'tools.php', esc_html__('Multiple Domain Mapping on single site', 'falke_mdm'), esc_html__('Multidomain', 'falke_mdm'), 'manage_options', plugin_basename(__FILE__), array( $this, 'output_menu_page') );
			$this->register_settings();
		}

		//generate menu page output
		public function output_menu_page(){
			// check user capabilities
	    if (!current_user_can('manage_options')) {
	        return;
	    }

			//find out active tab
			$active_tab = (isset($_GET['tab']) && ($_GET['tab'] == 'settings' || $_GET['tab'] == 'help' )) ? $_GET['tab'] : 'mappings';
			$active_tab_name = (isset($_GET['tab']) && ($_GET['tab'] == 'settings' || $_GET['tab'] == 'help' )) ? ucfirst($_GET['tab']) : esc_html__('Mappings', 'falke_mdm');

			echo '<div class="wrap falke_mdm_wrap">';

				//page title
				echo '<h1>' . get_admin_page_title() . '</h1>';

				//updated notices
				if ( isset( $_GET['settings-updated'] ) ) {
					add_settings_error( 'falke_mdm_messages', 'falke_mdm_message', sprintf(esc_html__( '%s saved', 'falke_mdm' ), $active_tab_name), 'updated' );

					//flush rewrite rules on each update of our settings/mappings, just to be sure...
					flush_rewrite_rules();
				}
				settings_errors( 'falke_mdm_messages' );

				//page intro
				echo sprintf('<p>%s <a title="%s" target="_blank" href="https://de.wordpress.org/plugins/multiple-domain-mapping-on-single-site/">%s</a> %s</p>', esc_html__('With this plugin you can use additional (sub-)domains to show specific pages, posts, archives, ... of your site, which is very useful for landingpages. It requires some important settings in your domains DNS entries and your hosting environment, and will not work "out-of-the-box". Please see the', 'falke_mdm'), esc_html__('WordPress Plugin Repository', 'falke_mdm'), esc_html__( 'description in the plugin repository', 'falke_mdm' ), esc_html__('or the help section below for further information on how to set it up.', 'falke_mdm'));
				echo sprintf('<p>%s <a title="%s" target="_blank" href="https://www.matthias-wagner.at/">%s</a>.</p>', esc_html__( 'If you enjoy this plugin and especially if you use it for commercial projects, please help us maintain support and development with', 'falke_mdm' ), esc_html__('Donations', 'falke_mdm'), esc_html__('a small donation', 'falke_mdm'));

				//tabs
				echo '<h2 class="nav-tab-wrapper">';
					echo '<a href="?page='. plugin_basename(__FILE__) .'&amp;tab=mappings" class="nav-tab ' . ($active_tab == 'mappings' ? 'nav-tab-active ' : '') . '">' . esc_html__('Mappings', 'falke_mdm') . '</a>';
					echo '<a href="?page='. plugin_basename(__FILE__) .'&amp;tab=settings" class="nav-tab ' . ($active_tab == 'settings' ? 'nav-tab-active ' : '') . '">' . esc_html__('Settings', 'falke_mdm') . '</a>';
					echo '<a href="?page='. plugin_basename(__FILE__) .'&amp;tab=help" class="nav-tab ' . ($active_tab == 'help' ? 'nav-tab-active ' : '') . '">' . esc_html__('Help', 'falke_mdm') . '</a>';
				echo '</h2>';

				//main form
				echo '<form action="options.php" method="post">';

					//inputs based on current tab
					switch($active_tab){
						case 'settings':{
							add_settings_section(
								'falke_mdm_section_settings',
								esc_html__('Additional settings', 'falke_mdm'),
								array($this, 'section_settings_callback'),
								plugin_basename(__FILE__)
							);

							add_settings_field(
								'falke_mdm_field_settings_phpserver',
								esc_html__('PHP Server-Variable:', 'falke_mdm'),
								array($this, 'field_settings_phpserver_callback'),
								plugin_basename(__FILE__),
								'falke_mdm_section_settings'
							);

							add_settings_field(
								'falke_mdm_field_settings_compatibilitymode',
								esc_html__('Enhanced compatibility mode:', 'falke_mdm'),
								array($this, 'field_settings_compatibilitymode_callback'),
								plugin_basename(__FILE__),
								'falke_mdm_section_settings'
							);

							do_action('falke_mdma_settings_tab');

							settings_fields('falke_mdm_settings_group');
							do_settings_sections( plugin_basename(__FILE__) );
							break 1;
						}
						case 'help':{
							echo '<p>'.__('We are going to provide some detailed installation instructions soon. Please refer to the <a href="https://de.wordpress.org/plugins/multiple-domain-mapping-on-single-site/" target="_blank">description in the plugin repository</a> in the meantime.').'</p>';
							break 1;
						}
						default:{
							add_settings_section(
								'falke_mdm_section_mappings',
								esc_html__('Domain mappings', 'falke_mdm'),
								array($this, 'section_mappings_callback'),
								plugin_basename(__FILE__)
							);

							add_settings_field(
								'falke_mdm_field_mappings_uris',
								esc_html__('Define your mappings here:', 'falke_mdm'),
								array($this, 'field_mappings_uris_callback'),
								plugin_basename(__FILE__),
								'falke_mdm_section_mappings'
							);
							settings_fields('falke_mdm_mappings_group');
							do_settings_sections( plugin_basename(__FILE__) );
							break 1;
						}
					}

					//dynamic submit button
					if($active_tab != 'help'){
						submit_button(sprintf(esc_html__('Save %s', 'falke_mdm'), $active_tab_name));
					}

				echo '</form>';
			echo '</div>';
		}

		//register settings
		private function register_settings(){
			register_setting( 'falke_mdm_settings_group', 'falke_mdm_settings', array(
				'sanitize_callback' => array($this, 'sanitize_settings_group'),
				'show_in_rest' => true
			) );
			register_setting( 'falke_mdm_mappings_group', 'falke_mdm_mappings', array(
				'sanitize_callback' => array($this, 'sanitize_mappings_group'),
				'show_in_rest' => true
			) );
		}

		//generate options fields output for the settings tab
		public function section_settings_callback(){
			echo esc_html__('Here you find some additional settings which should not be necessary to change in most use cases.', 'falke_mdm');
		}
		public function field_settings_phpserver_callback(){
			$options = $this->getSettings();
			if(empty($options)) $options = array();

			$options['php_server'] = isset($options['php_server']) ? $options['php_server'] : 'SERVER_NAME';

			echo sprintf('<p>%s <a target="_blank" href="https://wordpress.org/support/topic/server_name-instead-of-http_host/">%s</a>.</p>',
				esc_html__('In some cases it is necessary to change the used variable, like reported', 'falke_mdm'),
				esc_html__('in this support-thread', 'falke_mdm')
			);
			echo '<p><label><input type="radio" name="falke_mdm_settings[php_server]" value="SERVER_NAME" '. checked('SERVER_NAME', $options['php_server'], false ) . ' />$_SERVER["SERVER_NAME"] ('. esc_html__('Default', 'falke_mdm') .')</label></p>';
			echo '<p><label><input type="radio" name="falke_mdm_settings[php_server]" value="HTTP_HOST" '. checked('HTTP_HOST', $options['php_server'], false ) .' />$_SERVER["HTTP_HOST"] ('. esc_html__('recommended for nginx', 'falke_mdm') .')</label></p>';
		}
		public function field_settings_compatibilitymode_callback(){
			$options = $this->getSettings();
			if(empty($options)) $options = array();

			$options['compatibilitymode'] = isset($options['compatibilitymode']) ? $options['compatibilitymode'] : 0;

			echo sprintf('<p>%s</p>',
				esc_html__('This will disable the replacement of URIs inside wp-admin. This can be useful if, for example, your page builder fails to load mapped pages.', 'falke_mdm')
			);
			echo '<p><label><input type="radio" name="falke_mdm_settings[compatibilitymode]" value="0" '. checked('0', $options['compatibilitymode'], false ) . ' />Off ('. esc_html__('Default', 'falke_mdm') .')</label></p>';
			echo '<p><label><input type="radio" name="falke_mdm_settings[compatibilitymode]" value="1" '. checked('1', $options['compatibilitymode'], false ) .' />On</label></p>';
		}

		//generate options fields output for the mappings tab
		public function section_mappings_callback(){
			echo __('<b>In the first field</b>, enter your additional (sub-)domain which should show the content from now on. http/https and www/non-www will be detected automatically, so only one line per domain is necessary.<br /><b>In the second field</b>, enter the path to this page, post, archive, ... Please note that all descendant URIs will be mapped as well.', 'falke_mdm');
		}
		public function field_mappings_uris_callback(){
			$options = $this->getMappings();
			if(empty($options)) $options = array();

			echo '<section class="falke_mdm_mappings">';
				if(isset($options['mappings']) && !empty($options['mappings'])){
					$cnt = 0;
					foreach($options['mappings'] as $mapping){
						echo '<article class="'. apply_filters( 'falke_mdmf_mapping_class', 'falke_mdm_mapping' ) .'">';
							echo '<div class="falke_mdm_mapping_header">';
								echo '<div><div class="falke_mdm_input_wrap"><span class="falke_mdm_input_prefix">http[s]://</span><input type="text" name="falke_mdm_mappings[cnt_'.$cnt.'][domain]" value="' . $mapping['domain'] . '" /></div></div>';
								echo '<div class="falke_mdm_mapping_arrow">&raquo;</div>';
								echo '<div><div class="falke_mdm_input_wrap"><span class="falke_mdm_input_prefix">'. get_home_url() .'</span><input type="text" name="falke_mdm_mappings[cnt_'.$cnt.'][path]" value="' . $mapping['path'] . '" /></div></div>';
							echo '</div>';
							echo '<div class="falke_mdm_mapping_body">';
								echo '<span class="falke_mdm_mapping_body_icon falke_mdm_delete_mapping"><a href="#" title="' . esc_html__('Remove mapping', 'falke_mdm') . '">' . esc_html__('Remove mapping', 'falke_mdm') . ' <i>&cross;</i></a></span>';
								echo do_action('falke_mdma_after_mapping_body', $cnt, $mapping);
							echo '</div>';
						echo '</article>';
						$cnt++;
					}
				}
			echo '</section>';

			echo '<section class="falke_mdm_new_mapping">';
				echo '<article class="'. apply_filters( 'falke_mdmf_mapping_class', 'falke_mdm_mapping falke_mdm_mapping_new' ) .'">';
					echo '<div class="falke_mdm_mapping_header">';
						echo '<div><div class="falke_mdm_input_wrap"><span class="falke_mdm_input_prefix">http[s]://</span><input type="text" name="falke_mdm_mappings[cnt_new][domain]" placeholder="[www.]newdomain.com" /></div><div class="falke_mdm_input_hint">' . esc_html__('Enter the domain you want to map.', 'falke_mdm') . '</div></div>';
						echo '<div class="falke_mdm_mapping_arrow">&raquo;</div>';
						echo '<div><div class="falke_mdm_input_wrap"><span class="falke_mdm_input_prefix">'. get_home_url() .'</span><input type="text" name="falke_mdm_mappings[cnt_new][path]" placeholder="/mappedpage" /></div><div class="falke_mdm_input_hint">' . esc_html__('Enter the path to the desired root for this mapping', 'falke_mdm') . '</div></div>';
					echo '</div>';
					echo '<div class="falke_mdm_mapping_body">';
						echo do_action('falke_mdma_after_mapping_body', 'new', false);
					echo '</div>';
				echo '</article>';
			echo '</section>';
		}

		//sanitize options fields input
		public function sanitize_settings_group($options){
			if(empty($options)){
				return $options;
			}

			//be sure that only a correct server-value will be saved
			$options['php_server'] = (isset($options['php_server']) && ( $options['php_server'] == 'SERVER_NAME' || $options['php_server'] == 'HTTP_HOST' )) ? $options['php_server'] : 'SERVER_NAME';

			return apply_filters( 'falke_mdmf_save_settings', $options );
		}
		public function sanitize_mappings_group($options){
			//do nothing on empty input
			if(empty($options)){
				return $options;
			}

			//prepare mappings array
			$mappings = array();

			foreach($options as $key=>$val){

				//search for mappings and prepare them for database
				if(stripos( $key, 'cnt_' ) !== false){

					//only save not empty inputs
					$domain = str_ireplace(']', '', str_ireplace('[', '', trim(trim($val['domain']), '/')));
					$path = trim(trim( isset($val['path']) ? $val['path'] : '' ), '/');
					if($domain != ''/* && $path != ''*/){

						//validate inputs
						$parsedDomain = parse_url($domain);
						$parsedPath = parse_url($path);
						if($parsedDomain != false && $parsedPath != false){

							//if we get only the host-representation we temporary add a protocol, so we can use the benefit from parse_url to strip the query
							//note: this will also be run for each already saved mapping, since we strip the protocol on save...
							if(!isset($parsedDomain['host'])){
								$parsedDomain = parse_url('dummyprotocol://' . $domain);
							}

							//save only host name (and path, if provided) with stripped slashes
							$trimmedDomainPath = trim(trim( (isset($parsedDomain['path']) ? $parsedDomain['path'] : '') ), '/');
							$val['domain'] = trim(trim(isset($parsedDomain['host']) ? $parsedDomain['host'] : ''), '/') . (!empty($trimmedDomainPath) ? '/' . $trimmedDomainPath : '');

							//save path with leading slash
							$val['path'] = '/' . $path;

							//iterate over existing mappings and check, if this path has already been used
							$saveMapping = true;
							foreach($mappings as $existingMapping){
								if($existingMapping['path'] === $val['path']){
									$saveMapping = false;
								}
								if(str_ireplace('www.', '', $existingMapping['domain']) === str_ireplace('www.', '', $val['domain'])){
									$saveMapping = false;
								}
							}
							if($saveMapping){
								//mapping should be saved and is filtered before
								//use domain as index, so we do not have any duplicates -> this index will never be used or stored, but we convert it to md5 so it can not be confusing later
								$mappings[md5($val['domain'])] = apply_filters('falke_mdmf_save_mapping', $val);
							}else{
								//check for existence, since this may be called in an upgrade process earlier, when this is not available yet
								if(function_exists('add_settings_error')) add_settings_error( 'falke_mdm_messages', 'falke_mdm_error_code', esc_html__('At least one mapping with duplicate domain or path has been dropped.', 'falke_mdm'), 'error' );
							}
						}else{
							//check for existence, since this may be called in an upgrade process earlier, when this is not available yet
							if(function_exists('add_settings_error')) add_settings_error( 'falke_mdm_messages', 'falke_mdm_error_code', esc_html__('At least one mapping with bad URI format has been dropped.', 'falke_mdm'), 'error' );
						}
					//if we have only one input filled
					}else if(!($val['domain'] == '' && $val['path'] == '')){
						//check for existence, since this may be called in an upgrade process earlier, when this is not available yet
						if(function_exists('add_settings_error')) add_settings_error( 'falke_mdm_messages', 'falke_mdm_error_code', esc_html__('At least one mapping with only one input filled out has been dropped.', 'falke_mdm'), 'error' );
					}
					//remove original mapping (cnt_) from options array
					unset($options[$key]);
				}
			}

			//sort mappings so they are ordered nicely after each change
			usort($mappings, array($this, 'mappings_sort_helper'));

			//add filtered and sorted mappings to options array
			if(!empty($mappings)) $options['mappings'] = $mappings;

			return apply_filters( 'falke_mdmf_save_mappings', $options );
		}
		private function mappings_sort_helper($a, $b){
			return strcmp($a[apply_filters( 'falke_mdmf_mapping_sort', 'domain' )], $b[apply_filters( 'falke_mdmf_mapping_sort', 'domain' )]);
		}

		//change the request, check for matching mappings
		public function parse_request($do_parse, $instance, $extra_query_vars){
			//store current request uri as fallback for the originalRequestURI variable, no matter if we have a match or not
			$this->setOriginalRequestURI($_SERVER['REQUEST_URI']);

			//definitely no request-mapping in backend
			if(is_admin()) return $do_parse;

			//loop mappings and compare match of mapping against each other
			$mappings = $this->getMappings();
			if(!empty($mappings) && isset($mappings['mappings']) && !empty($mappings['mappings'])){

				foreach($mappings['mappings'] as $mapping){
					//first use our standard matching function
					$matchCompare = $this->uriMatch($this->getCurrentURI(), $mapping, true);
					//then enable custom matching by filtering
					$matchCompare = apply_filters( 'falke_mdmf_uri_match', $matchCompare, $this->getCurrentURI(), $mapping, true );

					//if the current mapping fits better, use this instead the previous one
					if($matchCompare !== false && isset($matchCompare['factor']) && $matchCompare['factor'] > $this->getCurrentMapping()['factor']){
						 $this->setCurrentMapping($matchCompare);
					}
				}

				//we have a matching mapping -> let the magic happen
				if(!empty($this->getCurrentMapping()['match'])){
					//store original request uri
					$this->setOriginalRequestURI($_SERVER['REQUEST_URI']);
					//set request uri to our original mapping path AND if we have a longer query, we need to append it
					$newRequestURI = trailingslashit($this->getCurrentMapping()['match']['path'] . substr(str_ireplace('www.', '', $this->getCurrentURI()), strlen(str_ireplace('www.', '', $this->getCurrentMapping()['match']['domain']))));
					//enable additional filtering on the request_uri
					$_SERVER['REQUEST_URI'] = apply_filters('falke_mdmf_request_uri', $newRequestURI, $this->getCurrentURI(), $this->getCurrentMapping());
				}
			}

			return $do_parse;
		}

		//hook into the canonical redirect to avoid infinite redirection loops
		//so far we only know that this is necessary for paged posts (nextpage-tag), which result in redirect loops otherwise
		public function check_canonical_redirect($redirect_url, $requested_url){

			//are we on a mapped page?
			if($this->getCurrentMapping()['match'] != false){

				//parse the urls
				$parsedRedirectUrl = parse_url($redirect_url);
				$parsedRequestedUrl = parse_url($requested_url);

				//if we have a slug in the domain-part of our mapping like test.com/ball <=> /sports/ball
				$explodedMappingDomain = explode('/', $this->getCurrentMapping()['match']['domain']);
				if(count($explodedMappingDomain)>1){

					//we need to cut out these slug-parts from the parsedRedirectUrl-path
					$explodedRedirectUrlPath = explode('/', $parsedRedirectUrl['path']);

					//but only as long as they "overlap" (like the "ball"-sequence in the example above)
					for($i=1;$i<count($explodedMappingDomain);$i++){
						if(isset($explodedRedirectUrlPath[$i]) && $explodedRedirectUrlPath[$i] === $explodedMappingDomain[$i]){
							unset($explodedRedirectUrlPath[$i]);
						}
					}

					//stick the path together again
					$parsedRedirectUrl['path'] = implode('/', $explodedRedirectUrlPath);
				}

				//now compare if those two urls are the same, and skip this redirect if so
				if(trailingslashit( $this->getCurrentMapping()['match']['path'] . $parsedRedirectUrl['path'] ) == trailingslashit( $parsedRequestedUrl['path']) ){
					return false;
				}
			}

			//standard return value
			return $redirect_url;
		}

		//standard function to check an uri against a mapping
		private function uriMatch($uri, $mapping, $reverse = false){

			//strip protocol from uri
			$uri = str_ireplace('http://', '', str_ireplace('https://', '', $uri));

			//strip www-subdomain from uri for matching purpose
			$uri = str_ireplace('www.', '', $uri);

			//do we check match at parsing the site or when replacing uris in the page?
			if($reverse){
				$arg2 = str_ireplace('www.', '', $mapping['domain']);
				$matchingPosCompare = 0;
			}else{
				$arg2 = $mapping['path'];
				$matchingPosCompare = strlen(str_ireplace('http://', '', str_ireplace('https://', '', str_ireplace('www.', '', get_home_url()))));
			}

			//check if arg2 is part of uri and starts where we want to
			$matchingPos = stripos(trailingslashit( $uri ), trailingslashit( $arg2 ) );
			if( $matchingPos !== false && $matchingPos === $matchingPosCompare ){
				//use length of match as factor
				return array(
					'match' => $mapping,
					'factor' => strlen(trailingslashit($arg2))
				);
			}
			return false;
		}

		//aggregation of all filters to replace the uri in the current page
		private function replace_uris(){
			//retrieve settings for compatibility mode
			$options = $this->getSettings();
			if(empty($options)) $options = array();
			$options['compatibilitymode'] = isset($options['compatibilitymode']) ? $options['compatibilitymode'] : 0;

			//single views
			if( !($options['compatibilitymode'] && is_admin()) ){
				add_filter('page_link', array($this, 'replace_uri'), 20);
				add_filter('post_link', array($this, 'replace_uri'), 20);
				add_filter('post_type_link', array($this, 'replace_uri'), 20);
				add_filter('attachment_link', array($this, 'replace_uri'), 20);
				//get_comment_author_link ... not necessary (seems to use the "author_link")
				//get_comment_author_uri_link ... this is the url the author can fill out - should not be touched
				//comment_reply_link ... leave this out until we manage to keep user logged in on addon-domains
				//remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); ... guess we should not add this...
			}

			//revoke mapping for the preview-button
			add_filter('preview_post_link', array($this, 'unreplace_uri'));

			//archive views
			add_filter('paginate_links', array($this, 'replace_uri'), 10);
			add_filter('day_link', array($this, 'replace_uri'), 20);
			add_filter('month_link', array($this, 'replace_uri'), 20);
			add_filter('year_link', array($this, 'replace_uri'), 20);
			add_filter('author_link', array($this, 'replace_uri'), 10);
			add_filter('term_link', array($this, 'replace_uri'), 10);

			//feed url (if someone matches a domain to a feed...)
			add_filter('feed_link', array($this, 'replace_uri'), 10);
			add_filter('self_link', array($this, 'replace_uri'), 10);
			add_filter('author_feed_link', array($this, 'replace_uri'), 10);

			//nav menu objects that do not use the standard link builders (like custom hrefs in the menu)
			add_filter('wp_nav_menu_objects', array($this, 'replace_menu_uri'));

			//content elements - do not map in wp-admin
			if(!is_admin()){
				add_filter( 'script_loader_src', array($this, 'replace_domain'), 10 );
				add_filter( 'style_loader_src', array($this, 'replace_domain'), 10 );
				add_filter( 'stylesheet_directory_uri', array($this, 'replace_domain'), 10 );
				add_filter( 'template_directory_uri', array($this, 'replace_domain'), 10 );
				add_filter( 'the_content', array($this, 'replace_domain'), 10 );
				add_filter( 'get_header_image_tag', array($this, 'replace_domain'), 10 );
				add_filter( 'wp_get_attachment_image_src', array($this, 'replace_src_domain'), 10 );
				add_filter( 'wp_calculate_image_srcset', array($this, 'replace_srcset_domain'), 10 );
			}

			//yoast sitemaps
			add_filter( 'wpseo_xml_sitemap_post_url', array($this, 'replace_yoast_xml_sitemap_post_url'), 0, 2 );
			add_filter( 'wpseo_sitemap_entry', array($this, 'replace_yoast_sitemap_entry'), 10, 3 );

			//elementor preview url
			add_filter( 'elementor/document/urls/preview', array($this, 'replace_elementor_preview_url') );
		}
		//all the helpers for the above filters
		public function replace_uri($originalURI){

			//loop mappings and compare match of mapping against each other
			$mappings = $this->getMappings();
			if(!empty($mappings) && isset($mappings['mappings']) && !empty($mappings['mappings'])){

				$bestMatch = array(
					'match' => false,
					'factor' => PHP_INT_MIN
				);

				foreach($mappings['mappings'] as $mapping){
					//first use our standard matching function
					$matchCompare = $this->uriMatch($originalURI, $mapping, false);
					//then enable custom matching by filtering
					$matchCompare = apply_filters( 'falke_mdmf_uri_match', $matchCompare, $originalURI, $mapping, false );

					//if the current mapping fits better, use this instead the previous one
					if($matchCompare !== false && isset($matchCompare['factor']) && $matchCompare['factor'] > $bestMatch['factor']){
						 $bestMatch = $matchCompare;
					}
				}

				//we have a matching mapping -> let the magic happen
				if(!empty($bestMatch['match'])){
					$uriParsed = parse_url($originalURI);
					$newURI = str_ireplace( trailingslashit( $uriParsed['host'] . $bestMatch['match']['path'] ), trailingslashit( $bestMatch['match']['domain'] ), $originalURI );
					return apply_filters('falke_mdmf_filtered_uri', $newURI, $originalURI, $bestMatch);
				}
			}

			return $originalURI;
		}
		public function unreplace_uri( $mapped_uri ){

			//loop mappings and compare match of mapping against each other
			$mappings = $this->getMappings();
			if(!empty($mappings) && isset($mappings['mappings']) && !empty($mappings['mappings'])){

				$bestMatch = array(
					'match' => false,
					'factor' => PHP_INT_MIN
				);

				foreach($mappings['mappings'] as $mapping){
					//first use our standard matching function
					$matchCompare = $this->uriMatch($mapped_uri, $mapping, true);

					//then enable custom matching by filtering
					$matchCompare = apply_filters( 'falke_mdmf_uri_match', $matchCompare, $mapped_uri, $mapping, true );

					//if the current mapping fits better, use this instead the previous one
					if($matchCompare !== false && isset($matchCompare['factor']) && $matchCompare['factor'] > $bestMatch['factor']){
						 $bestMatch = $matchCompare;
					}
				}

				//we have a matching mapping -> let the magic happen
				if(!empty($bestMatch['match'])){
					$uriParsed = parse_url($mapped_uri);
					$newURI = str_ireplace( $uriParsed['host'], parse_url(get_home_url())['host'] . $bestMatch['match']['path'], $mapped_uri );
					return apply_filters('falke_mdmf_filtered_uri', $newURI, $mapped_uri, $bestMatch);
				}
			}

			return $mapped_uri;
		}
		public function replace_menu_uri($items){
			//loop menu items and replace uri
			foreach($items as $item){
				$item->url = $this->replace_uri($item->url);
			}
		 	return $items;
		}
		public function replace_src_domain($src){
			//url is in the 0-index of the src-array
			if(!empty($src)){
				$src[0] = $this->replace_domain($src[0]);
			}
			return $src;
		}
		public function replace_srcset_domain($srcset){
			//iterate through srcset and change uri on all sources
			if(!empty($srcset)){
				foreach($srcset as $key => $val){
					$srcset[$key]['url'] = $this->replace_domain($val['url']);
				}
			}
			return $srcset;
		}
		public function replace_domain($input){
			//check if we are on a mapped page and replace original domain with mapped domain
			if(!empty($this->getCurrentMapping()['match'])){
				//we need to make sure that we only replace right at the beginning (after the protocol), so we do not destroy subdomains (like img.mydomain.com). that is why we add the :// to the strings
				//and we also need to be sure that we do not replace it in a hyperlink which leads to any page on our original domain or to the home page itelsf. so we add a pregex which needs to have any character, a dot and again any character before the next ". that should do the trick...
				$preg_host = preg_quote(parse_url(get_site_url())['host']);
				//to understand the regex, use https://regexr.com/ :)
				$input = preg_replace_callback('/:\/\/'.$preg_host.'([^\", ]*(\.)+[^\", ]*)([\"\']|$)/', array($this, 'replace_domain_in_url'), $input);
			}
			return $input;
		}
		private function replace_domain_in_url($input){
			//if this is called from preg_replace_callback we will receive an array. we only need the first index, so we can generalize this to be used by other functions as well
			if(is_array($input)){
				$input = $input[0];
			}

			//check if we are on a mapped page and replace original domain with mapped domain
			if(!empty($this->getCurrentMapping()['match'])){
				//we need to make sure that we only replace right at the beginning (after the protocol), so we do not destroy subdomains (like img.mydomain.com). that is why we add the :// to the strings
				return str_ireplace( '://' . parse_url(get_site_url())['host'], '://' . parse_url('dummyprotocol://' . $this->getCurrentMapping()['match']['domain'])['host'], $input);
			}

			return $input;
		}
		public function replace_yoast_xml_sitemap_post_url($url, $post){
			// add home url to the posturl, so YOAST will not handle the post like an external url
			// this is stripped again in the next filter
			if(trailingslashit( get_home_url() ) != trailingslashit( $url) ){
				$url = get_home_url() .'/\\'. $this->replace_uri($url);
			}
			return $url;
		}
		public function replace_yoast_sitemap_entry($url, $type, $post){
			//true for all post types
			if($type === 'post'){
				if(false !== strpos($url['loc'],'\\')){
					$tmp = explode('\\', $url['loc']);
					$url['loc'] = $tmp[1];
				}
			}
			return $url;
		}
		public function replace_elementor_preview_url($preview_url){
			//elementor saves the uri in some escaped format
			$unescaped_preview_url = str_replace( '\/', '/', $preview_url);
			return $this->unreplace_uri( $unescaped_preview_url );
		}

		//hook into some of our own defined actions
		public function hookMDMAction(){
			add_action('falke_mdma_after_mapping_body', array( $this, 'simple_premium_notice'), 10, 2);
		}
		public function simple_premium_notice($cnt, $mapping){
			if($cnt !== 'new')	echo sprintf('<p class="premium-survey-notice">%s <a title="%s" target="_blank" href="https://www.falkemedia.at/multiple-domain-mapping-on-single-site-premium/">%s</a>.</p>', esc_html__('Looking for extra features and settings?', 'falke_mdm'), esc_html__('Survey', 'falke_mdm'), esc_html__('Find out more about our premium plans', 'falke_mdm'));
		}
	}

	$FALKE_MultipleDomainMapping = FALKE_MultipleDomainMapping::get_instance();
}
