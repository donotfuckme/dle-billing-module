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

Class ADMIN extends ADMIN_THEME {

	function main( $name ) {
		
		/* Save */
		if( isset( $_POST['save'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$save_con = $_POST['save_con'];
			$save_con['convert'] = $this->pay_api->bf_convert( $save_con['convert'], $save_con['format'] );
			$save_con['minimum'] = $this->pay_api->bf_convert( $save_con['minimum'], $save_con['format'] );
			
			$this->save_file_array("pasys.".$name, $save_con, "paysys_config");
			$this->T_msg( $this->lang['ok'], $this->lang['paysys_save_ok'] );
		
		}
		
		/* Load */
		if( file_exists( MODULE_PATH."/paysys/" . $name . "/adm.settings.php" ) )
			require_once MODULE_PATH . '/paysys/' . $name . '/adm.settings.php';
		else
			$this->T_msg( $this->lang['error'], $this->lang['paysys_fail_error'] );

		/* Config */
		$paysys = $this->paysys_array();
		$paysys_config = $paysys[$name];
		
		/* Settings */
		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['main_settings']." " . $name );

		$this->T_str_table( $this->lang['paysys_url'], $this->lang['paysys_url_desc'], $this->config_dle['http_home_url'] . $this->config['page'] . ".html/pay/get/". $name ."/". $this->config['secret'] );
		$this->T_str_table( $this->lang['mail_on']." {$name}:", $this->lang['paysys_status_desc'], $this->T_makeCheckBox("save_con[status]", $paysys_config['status']) );
		$this->T_str_table( $this->lang['paysys_name'], $this->lang['paysys_name_desc'], "<input name=\"save_con[title]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['title'] ."\" required>" );
		$this->T_str_table( $this->lang['paysys_convert'], $this->lang['paysys_convert_desc'], "<input name=\"save_con[convert]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['convert'] ."\" required> = 1 " . $this->pay_api->bf_declOfNum( 1 ) );
		$this->T_str_table( $this->lang['paysys_minimum'], $this->lang['paysys_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['minimum'] ."\" required>" );
		$this->T_str_table( $this->lang['paysys_currency'], $this->lang['paysys_currency_desc'], "<input name=\"save_con[currency]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['currency'] ."\" required>" );
		$this->T_str_table( $this->lang['paysys_format'], $this->lang['paysys_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['format'] ."\" required>" );
		$this->T_str_table( $this->lang['paysys_icon'], $this->lang['paysys_icon_desc'], "<input name=\"save_con[icon]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['icon'] ."\" size=\"40\">" );
		$this->T_str_table( $this->lang['paysys_about'], $this->lang['paysys_about_desc'], "<input name=\"save_con[text]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['text'] ."\" size=\"40\">" );	
		$content .= $this->T_parse_str_table();

		$content .= $Paysys->settings( $name, $this->config_dle, $this->config, $paysys_config, $this->hash );
		
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"save\" type=\"submit\" value=\"".$this->lang['save']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		$content .= $this->foother();

		return $content;
	}

}
?>