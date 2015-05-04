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

	function main( $data ) {

		/* Save */
		if( isset( $_POST['save'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$save_con = $_POST['save_con'];
		
			$this->save_file_array("plugin.transfer", $save_con, "plugin_config");
			$this->T_msg( $this->lang['ok'], $this->lang['save_settings'] );
		
		}
		
		/* Load and Install */
		if( file_exists( MODULE_DATA."/plugin.transfer.php" ) )
			require_once MODULE_DATA . "/plugin.transfer.php";
		else {
			
			$this->save_file_array("plugin.transfer", array('status'=>"0"), "plugin_config");
			
			$this->T_msg( $this->lang['install_plugin'], $this->lang['install_plugin_desc'], $PHP_SELF . "?mod=billing&c=Refund" );
		}
	
		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['transfer_title'] );
		
		$this->T_str_table( $this->lang['settings_status'], $this->lang['refund_status_desc'], $this->T_makeCheckBox("save_con[status]", $plugin_config['status']) );
		$this->T_str_table( $this->lang['paysys_name'], $this->lang['refund_name_desc'], "<input name=\"save_con[name]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['name'] ."\">" );
		$this->T_str_table( $this->lang['transfer_minimum'], $this->lang['transfer_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['minimum'] ."\"> " . $this->pay_api->bf_declOfNum( $plugin_config['minimum'] ) );
		$this->T_str_table( $this->lang['refund_commision'], $this->lang['refund_commision_desc'], "<input name=\"save_con[com]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['com'] ."\">%" );

		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"save\" type=\"submit\" value=\"".$this->lang['save']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		
		$content .= $this->foother();
		
		return $content;
		
	}
 
}
?>