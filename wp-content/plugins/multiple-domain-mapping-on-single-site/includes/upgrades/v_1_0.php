<?php
// If this file is called directly, abort.
if( !defined( 'ABSPATH' ) ){
	die('...');
}

//Upgrade DB from previous versions to v1.0+
$oldSettings = get_option('multidomainplugin_tabsettings');
$oldMappings = get_option('multidomainplugin_options');

//var_dump($oldSettings);
//var_dump($oldMappings);
if($oldSettings !== false || $oldMappings !== false){

  //prepare admin notice
  $upgradeAdminNotice = esc_html__( 'Multiple Domain Mapping on single site has performed a database upgrade.', 'falke_mdm' );

  if($oldSettings !== false){
    //option_value
    //a:1:{s:15:"server_variable";s:11:"SERVER_NAME";}
      // array (
      //   'server_variable' => 'SERVER_NAME',
      // )
    //a:1:{s:15:"server_variable";s:9:"HTTP_HOST";}
      // array (
      //   'server_variable' => 'HTTP_HOST',
      // )

    //prepare new options array
    $options = array();
    //store existing value there
    if(isset($oldSettings['server_variable'])) $options['php_server'] = $oldSettings['server_variable'];
    //use sanitize function for proper format and content
    $options = $this->sanitize_settings_group($options);
    //save new option to database
    update_option('falke_mdm_settings', $options);
    //delete old option, so this will never be executed again
    delete_option('multidomainplugin_tabsettings');

    //prepare admin notice
    $upgradeAdminNotice .= ' ' . esc_html__( 'Settings have been updated.', 'falke_mdm' );
  }
  if($oldMappings !== false){
    //option_value
    //a:4:{s:25:"multidomainplugin_domain0";s:19:"http://www.hallo.at";s:30:"multidomainplugin_destination0";s:10:"/hallo/abc";s:25:"multidomainplugin_domain1";s:0:"";s:30:"multidomainplugin_destination1";s:0:"";}
      // array (
      //   'multidomainplugin_domain0' => 'http://www.hallo.at',
      //   'multidomainplugin_destination0' => '/hallo/abc',
      //   'multidomainplugin_domain1' => '',
      //   'multidomainplugin_destination1' => '',
      // )
    //a:5:{s:25:"multidomainplugin_domain0";s:19:"http://www.hallo.at";s:30:"multidomainplugin_destination0";s:10:"/hallo/abc";s:25:"multidomainplugin_domain1";s:22:"https://testdomain.com";s:25:"multidomainplugin_domain2";s:16:"ftp://nochmal.it";s:30:"multidomainplugin_destination2";s:5:"/test";}
      // array (
      //   'multidomainplugin_domain0' => 'http://www.hallo.at',
      //   'multidomainplugin_destination0' => '/hallo/abc',
      //   'multidomainplugin_domain1' => 'https://testdomain.com',
      //   'multidomainplugin_domain2' => 'ftp://nochmal.it',
      //   'multidomainplugin_destination2' => '/test',
      // )

    //realistic option_value
    //a:8:{s:25:"multidomainplugin_domain0";s:15:"http://hallo.at";s:30:"multidomainplugin_destination0";s:6:"/hallo";s:25:"multidomainplugin_domain2";s:19:"http://www.hallo.at";s:30:"multidomainplugin_destination2";s:6:"/hallo";s:25:"multidomainplugin_domain3";s:16:"https://hallo.at";s:30:"multidomainplugin_destination3";s:6:"/hallo";s:25:"multidomainplugin_domain4";s:20:"https://www.hallo.at";s:30:"multidomainplugin_destination4";s:6:"/hallo";}
      // array (
      //   'multidomainplugin_domain0' => 'http://hallo.at',
      //   'multidomainplugin_destination0' => '/hallo',
      //   'multidomainplugin_domain2' => 'http://www.hallo.at',
      //   'multidomainplugin_destination2' => '/hallo',
      //   'multidomainplugin_domain3' => 'https://hallo.at',
      //   'multidomainplugin_destination3' => '/hallo',
      //   'multidomainplugin_domain4' => 'https://www.hallo.at',
      //   'multidomainplugin_destination4' => '/hallo',
      // )

    //prepare new options array
    $options = array();
    //iterate over old options
    if(!empty($oldMappings)){
      foreach($oldMappings as $key => $val){
        //strip last character and create sub-array
        $arrayIndex = substr($key, strlen($key)-1);
        if(!isset($options['cnt_' . $arrayIndex])) $options['cnt_' . $arrayIndex] = array();
        //store values inside this sub-array
        if(stripos( $key, 'multidomainplugin_domain' ) !== false){
          $options['cnt_' . $arrayIndex]['domain'] = $val;
        }else if(stripos( $key, 'multidomainplugin_destination' ) !== false){
          $options['cnt_' . $arrayIndex]['path'] = $val;
        }
      }
    }

    //use sanitize function for proper format and content
    $options = $this->sanitize_mappings_group($options);
    //save new option to database
    update_option('falke_mdm_mappings', $options);
    //delete old option, so this will never be executed again
    delete_option('multidomainplugin_options');

    //prepare admin notice
    $upgradeAdminNotice .= ' ' . esc_html__( 'Mappings have been updated.', 'falke_mdm' );
  }

  //finalize admin notice
  $upgradeAdminNotice .= ' ' . sprintf('%s <a href="'.admin_url( 'tools.php?page=' . plugin_basename('multiple-domain-mapping-on-single-site/multidomainmapping.php') ).'" title="%s">%s</a> %s.', esc_html__( 'Please head over to the', 'falke_mdm' ), esc_html__('Multidomain-Settings', 'falke_mdm'), esc_html__('Multidomain-Settings'), esc_html__('and check if everything is fine', 'falke_mdm'));
  $upgradeAdminNotice = '<p>' . $upgradeAdminNotice . '</p>';
  $upgradeAdminScreenNotice = '<p><strong>'.esc_html__('Please note that some mappings may have been combined, since http/https and www/non-www are now handled in one line. You will need to check if the mappings are still working as you expect them to - do not forget to clear website and browser caches for that purpose.', 'falke-mdm').'</strong></p>';
}
if(isset($upgradeAdminNotice) && !empty($upgradeAdminNotice)){
  update_option('falke_mdm_notice', array(
    'class' => 'notice notice-info',
    'text' => $upgradeAdminNotice,
    'onScreenText' => $upgradeAdminScreenNotice
  ));
}
// update_option('falke_mdm_notice', array(
//   'class' => 'notice notice-info',
//   'text' => '<p>hallo notiz <a href="'.admin_url( 'tools.php?page=' . plugin_basename('multiple-domain-mapping-on-single-site/multidomainmapping.php') ).'">admin</a></p>',
//   'onScreenText' => '<p><strong>watch out for something</strong></p>'
// ));
