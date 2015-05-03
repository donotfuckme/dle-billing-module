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

	function main() {
	
		$content = $this->header();
		
		/* Menu */
		$content = $this->header_start( $this->lang['title'] );
		
			$this->T_section( $this->lang['main_settings'], $this->lang['main_settings_desc'], "engine/modules/billing/theme/icons/configure.png", $PHP_SELF."?mod=billing&m=settings" );
			$this->T_section( $this->lang['main_mail'], $this->lang['main_mail_desc'], "engine/modules/billing/theme/icons/mail.png", $PHP_SELF."?mod=billing&m=mail" );

		$content .= $this->T_parse_section();
		$content .= $this->header_end();
		
		/* Paysys */
		$content .= $this->header_start( $this->lang['main_paysys'] );
		$content .= $this->T_paysys();
		$content .= $this->header_end();
			
		/* Plugins */
		$content .= $this->header_start( $this->lang['main_plugins'] );
		$content .= $this->T_plugins();
		$content .= $this->header_end();
	
		$content .= $this->foother();

		return $content;
	}

	function settings() {
	
		if( isset( $_POST['save'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$save_con = $_POST['save_con'];
			$save_con['version'] = $this->config['version'];
		
			$this->save_file_array("config", $save_con, "billing_config");
			$this->T_msg( $this->lang['ok'], $this->lang['save_settings'] );
		
		}
	
		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['main_settings'] );
		
		$this->T_str_table( $this->lang['settings_status'], $this->lang['settings_status_desc'], $this->T_makeCheckBox("save_con[status]", $this->config['status']) );
		$this->T_str_table( $this->lang['settings_page'], $this->lang['settings_page_desc'], "<input name=\"save_con[page]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['page'] ."\" required>.html" );
		$this->T_str_table( $this->lang['settings_currency'], $this->lang['settings_currency_desc'], "<input name=\"save_con[currency]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['currency'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_redirect'], $this->lang['settings_redirect_desc'], $this->T_makeCheckBox("save_con[redirect]", $this->config['redirect']) );
		$this->T_str_table( $this->lang['settings_paging'], $this->lang['settings_paging_desc'], "<input name=\"save_con[paging]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['paging'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_admin'], $this->lang['settings_admin_desc'], "<input name=\"save_con[admin]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['admin'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_key'], $this->lang['settings_key_desc'], "<input name=\"save_con[secret]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['secret'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_field'], $this->lang['settings_field_desc'], "<input name=\"save_con[fname]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['fname'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_start'], $this->lang['settings_start_desc'], "<input name=\"save_con[start]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['start'] ."\" required>" );
		$this->T_str_table( $this->lang['settings_format'], $this->lang['settings_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $this->config['format'] ."\" required>" );
	
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"save\" type=\"submit\" value=\"".$this->lang['save']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		$content .= $this->foother();

		return $content;
	
	}
	
	function mail() {

		if( isset( $_POST['save'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$save_con = $_POST['save_con'];
			
			$this->save_file_array("mail", $save_con, "billing_mail" );
			$this->T_msg( $this->lang['ok'], $this->lang['save_mail'] );
		
		}
	
		if( file_exists( MODULE_DATA."/mail.php" ) )
			require_once MODULE_DATA . '/mail.php';
		
		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['main_mail'] );
		
			$this->T_set_list( array( '<td width="25%"><b>'.$this->lang['mail_name'].'</b></td>', '<td><b>'.$this->lang['mail_blank'].'</b></td>', '<td width="10%"><b>'.$this->lang['mail_on'].'</b></td>') );
			$this->T_set_list( array( $this->lang['mail_pay_ok'], '<input name="save_con[yespay_title]" type="text" value="' . $billing_mail['yespay_title'] . '" style="width: 100%; margin-bottom: 3px" required><br /><textarea style="width: 100%; height: 140px" name="save_con[yespay]" required>' . html_entity_decode( $billing_mail['yespay'] ) . '</textarea>', '<span style="height: 140px; vertical-align: middle;  display: table-cell; ">PM: '. $this->T_makeCheckBox("save_con[yespay_staus_pm]", $billing_mail['yespay_staus_pm']) .'<br />Email: '. $this->T_makeCheckBox("save_con[yespay_staus_email]", $billing_mail['yespay_staus_email']) .'</span>') );
			$this->T_set_list( array( $this->lang['mail_pay_new'], '<input name="save_con[newpay_title]" type="text" value="' . $billing_mail['newpay_title'] . '" style="width: 100%; margin-bottom: 3px" required><br /><textarea style="width: 100%; height: 140px" name="save_con[newpay]" required>' . html_entity_decode( $billing_mail['newpay'] ) . '</textarea>', '<span style="height: 140px; vertical-align: middle;  display: table-cell; ">PM: '. $this->T_makeCheckBox("save_con[newpay_staus_pm]", $billing_mail['newpay_staus_pm']) .'<br />Email: '. $this->T_makeCheckBox("save_con[newpay_staus_email]", $billing_mail['newpay_staus_email']) .'</span>') );

		$content .= $this->T_pars_list();
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"save\" type=\"submit\" value=\"".$this->lang['save']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		$content .= $this->foother();

		return $content;
	
	}
	
}
?>