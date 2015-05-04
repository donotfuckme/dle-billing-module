<?php
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

if( !defined( 'BILLING_MODULE' ) ) {
    die( "Hacking attempt!" );
}

Class USER_THEME extends MODEL {

	var $config = false;
	var $config_dle = false;
	var $config_mail = false;
	var $member_id = false;
	var $_TIME = false;
	var $lang = false;

	var $elements = array();
	var $element_block = array();
	
	var $plugins = array();

	function set_element( $field, $value ) {
	
		$this->elements[$field] = $value;
	
		return TRUE;
	}

	function set_element_block( $fields, $value ) {
	
		$this->element_block[$fields] = $value;
	
		return TRUE;
	}
	
	function T_preg_match( $theme, $tag ) {
		
		$answer = "";
		
		preg_match($tag, $theme, $answer);
		
		return $answer[1];
	}
	
	function T_plugins() {

		if( count( $this->plugins ) ) return $this->plugins;
	
		$load_list = opendir( MODULE_PATH . "/plugins/" );
	
		while ( $name = readdir($load_list) ) {

			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;

			/* Config */
			if( file_exists( MODULE_DATA . "/plugin." . mb_strtolower( $name ) . ".php" ) )
				include MODULE_DATA . "/plugin." . mb_strtolower( $name ) . ".php";
			else
				continue;
						
			if( !$plugin_config['status'] ) continue;
		
			$this->plugins[$name] = $plugin_config;
		
		}
	
		return $this->plugins;
	}
	
	function load( $tpl_name, $menu = 'log' ) {
		
		$url = @parse_url ( $tpl_name );

		$file_path = dirname ($this->clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);
	
		$tpl = file_get_contents( ROOT_DIR . "/templates/". $this->config_dle['skin'] ."/billing/". $tpl_name .".tpl");	
		
		if( !$tpl ) return( $this->lang['cabinet_theme_error']."$tpl_name.tpl" );
		
		foreach( $this->elements as $key=>$value ) $tpl = str_replace( $key, $value, $tpl);
		foreach( $this->element_block as $key=>$value ) $tpl = preg_replace("'\\[".$key."\\].*?\\[/".$key."\\]'si", $value, $tpl);
		
		/* Plugins menu */
		$tpl_plugin = $this->T_preg_match( $tpl, '~\[plugin\](.*?)\[/plugin\]~is' );
		
		$plugins_list = $this->T_plugins();
		$plugins = "";
		
		if( count( $plugins_list ) ) 
			foreach( $plugins_list as $name => $pl_config ) {
			
				$time_plugins_theme = $tpl_plugin;
				$time_plugins_theme = str_replace("{plugin_link}", $name, $time_plugins_theme);
				$time_plugins_theme = str_replace("{plugin_name}", $pl_config['name'], $time_plugins_theme);
				$time_plugins_theme = ( $menu == $name ) ? str_replace("{plugin_active}", "_active", $time_plugins_theme) : str_replace("{plugin_active}", "", $time_plugins_theme);
				$time_plugins_theme = str_replace( "{URL_CABINET}", $this->config_dle['http_home_url'] . $this->config['page'] . ".html", $time_plugins_theme);
				
				$plugins .= $time_plugins_theme;
			}
		
		$tpl = str_replace( "{URL_CABINET}", $this->config_dle['http_home_url'] . $this->config['page'] . ".html", $tpl);
		$tpl = str_replace( "{THEME}", $this->config_dle['http_home_url'] . "templates/" . $this->config_dle['skin'] . "/billing", $tpl);
		$tpl = str_replace( "[active]".$menu."[/active]", "_active", $tpl);
		$tpl = str_replace( "{BALANCE}", $this->member_id[$this->config['fname']] . " " . $this->pay_api->bf_declOfNum( $this->config['currency'] ), $tpl);
		
		$tpl = preg_replace("'\\[active\\].*?\\[/active\\]'si", '', $tpl);
		$tpl = preg_replace("'\\[plugin\\].*?\\[/plugin\\]'si", $plugins, $tpl);
		
		$elements = array();
		
		return $tpl;
	}
		
	function T_msg( $title, $errors, $plugin = 'log' ) {

		$this->set_element( "{msg}", $errors );
		$this->set_element( "{title}", $title );
				
		$this->set_element( "{content}", $this->load( "msg" ) );
			
		return $this->load( "cabinet", $plugin );
	}
	
	function send_msg( $type, $user_id, $array ) {
	
		if( !$user_id ) return false;
	
		/* PM */

		if( $this->config_mail[$type."_staus_pm"] ) {
		
			$title = html_entity_decode( $this->config_mail[$type."_title"] );
			$text = html_entity_decode( $this->config_mail[$type] );
			
			$text = str_replace("\r\n", "<br />", $text );

			$text = str_replace( "&#036;", "$", $text );
			$text = str_replace( "&#123;", "{", $text );
			$text = str_replace( "&#125;", "}", $text );

			foreach( $array as $key => $value ) {

				$text = str_replace($key, $value, $text);
				$title = str_replace($key, $value, $title);
			
			}
			
			$this->pay_api->send_pm_to_user( $user_id, $title, $text, $this->config['admin'] );
			
		}
	
		/* Email */

		if( $this->config_mail[$type."_staus_email"] ) {
		
			$get_usert = $this->db_search_user_by_id( $user_id );
			
			if( !$get_usert['email'] )
				return false;
			
			$title = html_entity_decode( $this->config_mail[$type."_title"] );
			$text = html_entity_decode( $this->config_mail[$type] );
			
			$text = str_replace("\r\n", "<br />", $text );

			$text = str_replace( "&#036;", "$", $text );
			$text = str_replace( "&#123;", "{", $text );
			$text = str_replace( "&#125;", "}", $text );

			foreach( $array as $key => $value ) {

				$text = str_replace($key, $value, $text);
				$title = str_replace($key, $value, $title);
			
			}
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
			$mail = new dle_mail( $this->config_dle, true );
			
			$mail->send( $get_usert['email'], $title, $text );
			
			unset( $mail );
		}
		
		return true;
	}
	
	function clear_url_dir($var) {
		
		if ( is_array($var) ) return "";
	
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );

		return $var;
	}
	
	function hash() {
	
		return base64_encode($this->member_id['email'] .'/*\/'. date("H") );
	
	}

}
?>