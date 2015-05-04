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

Class USER extends USER_THEME {

	var $db = false;
	var $plugin_config = false;

	function __construct() {
       
		if( file_exists( MODULE_DATA."/plugin.refund.php" ) )
				include MODULE_DATA."/plugin.refund.php";
		
		$this->plugin_config = $plugin_config;
	   
	}
	
	function main( $page ) {

		/* Login */
		if( !$this->member_id['name'] )
			return $this->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] )
			return $this->T_msg( $this->lang['pay_error_title'], $this->lang['cabinet_off'], "Refund" );

		/* Post */
		if( isset($_POST['submit']) ) {
		
			$post_requisites = $this->db->safesql( $_POST['bs_requisites'] );
			
			$post_summa = $this->pay_api->bf_convert( $_POST['bs_summa'] );
			
			$post_commission = $this->pay_api->bf_convert( ( $post_summa / 100 ) * $this->plugin_config['com'] );
			
			$error = "";

			if( !isset( $_POST['bs_hash'] ) OR $_POST['bs_hash'] != $this->hash() )
				$error = $this->lang['pay_hash_error'];
			
			else if( !$post_summa )
				$error = $this->lang['pay_summa_error'];
	
			else if( !$post_requisites )
				$error = str_replace("{link_to_user}", $this->config_dle['http_home_url']."user/".urlencode( $this->member_id['name'] ), $this->lang['refund_error_requisites'] );
	
			else if( $post_summa > $this->member_id[$this->config['fname']] )
				$error = $this->lang['refund_error_balance'];
			
			else if( $post_summa < $this->plugin_config['minimum'] )
				$error = $this->lang['refund_error_minimum'] . $this->plugin_config['minimum']." ".$this->pay_api->bf_declOfNum( $this->plugin_config['minimum'] );	
			
			if( $error )
				return $this->T_msg( $this->lang['pay_error_title'], $error, "Refund" );

			// - Creat moneyback
			$redund_id = $this->db_creat_refund( $this->member_id['name'], $post_summa, $post_commission, $post_requisites );

			$this->pay_api->minus( $this->member_id['name'], $post_summa, $this->lang['refund_msgOk'], $this->pay_api->bf_declOfNum( $post_summa ), "refund", $redund_id );

				// - email
				if( $this->plugin_config['email'] ) {
					
					include_once ENGINE_DIR . '/classes/mail.class.php';
					
					$mail = new dle_mail( $this->config_dle, true);
					$mail->send( $this->config_dle['admin_mail'], $this->lang['refund_email_title'], $this->lang['refund_email_msg'].$this->config_dle['http_home_url'].$this->config_dle['admin_path']."?mod=billing&c=Refund" );
					
					unset( $mail );
					
				}
	
		}
	
		/* PAGE */
		$this->set_element( "{hash}", $this->hash() );
		$this->set_element( "{requisites}", $this->xfield( $this->plugin_config['requisites'] ) );
		$this->set_element( "{minimum}", $this->plugin_config['minimum'] );
		$this->set_element( "{minimum_valuta}", $this->pay_api->bf_declOfNum( $this->plugin_config['minimum'] ) );
		$this->set_element( "{commission}", $this->plugin_config['com'] );
		$this->set_element( "{mask}", $this->plugin_config['format'] );

		/* History */
		$tpl = $this->load( "Refund" );
		$log = "";
			
		$tpl_log = $this->T_preg_match( $tpl, '~\[history\](.*?)\[/history\]~is' );
		$tpl_log_null = $this->T_preg_match( $tpl, '~\[not_history\](.*?)\[/not_history\]~is' );
		$tpl_log_date = $this->T_preg_match( $tpl_log, '~\{date=(.*?)\}~is' );
			
			$this->db_where( array( "refund_user = '{s}' " => $this->member_id['name'] ) );
			
			$history = $this->db_get_refund( $page, $this->config['paging'] );
			$num_history = $this->db_get_refund_num();

			foreach( $history as $history_id => $history_value ) {

				$time_log_theme = $tpl_log;
				$time_log_theme = str_replace("{date=".$tpl_log_date."}", langdate( $tpl_log_date, $history_value['refund_date']), $time_log_theme);
				$time_log_theme = str_replace("{refund_requisites}", $history_value['refund_requisites'], $time_log_theme);
				
				$time_log_theme = str_replace("{refund_commission}",$history_value['refund_commission'], $time_log_theme);
				$time_log_theme = str_replace("{refund_commission_valuta}", $this->pay_api->bf_declOfNum( $history_value['refund_commission'] ), $time_log_theme);
				
				$time_log_theme = str_replace("{refund_summa}", $history_value['refund_summa'], $time_log_theme);
				$time_log_theme = str_replace("{refund_summa_valuta}",  $this->pay_api->bf_declOfNum( $history_value['refund_summa'] ), $time_log_theme);
				
				$time_log_theme = str_replace("{refund_status}", ( ($history_value['refund_date_return']) ? "<font color=\"green\">".langdate( $tpl_log_date, $history_value['refund_date_return'])."</a>": "<font color=\"red\">".$this->lang['refund_wait']."</a>" ), $time_log_theme);

				$log .= $time_log_theme;
			}

				/* Paging */
				if( $num_history > $this->config['paging'] ) {
						
					$tpl_log_page = $this->T_preg_match( $tpl, '~\[paging\](.*?)\[/paging\]~is' );
					$tpl_log_page_link = $this->T_preg_match( $tpl, '~\[page_link\](.*?)\[/page_link\]~is' );
					$tpl_log_page_this = $this->T_preg_match( $tpl, '~\[page_this\](.*?)\[/page_this\]~is' );

					$tpl_log_page = preg_replace("'\\[page_link\\].*?\\[/page_link\\]'si", $this->pay_api->bf_paging( $num_history, $page, $this->config_dle['http_home_url'] . $this->config['page'] . ".html/Refund/main/{p}", $tpl_log_page_link, $tpl_log_page_this ), $tpl_log_page );
					$tpl_log_page = preg_replace("'\\[page_this\\].*?\\[/page_this\\]'si", "", $tpl_log_page);
					
					$this->set_element_block( "paging", $tpl_log_page );
				
				} else
					$this->set_element_block( "paging", "" );
			
				/* LOG NULL */
				if( $log )	$this->set_element_block( "not_history", "" );
				else 		$this->set_element_block( "not_history", $tpl_log_null );
	
		$this->set_element_block( "history", $log );
		/* History END */
		
		$tpl = $this->load( "Refund" );

		$this->set_element( "{content}", $tpl );
	
		return $this->load( "cabinet", "Refund" );
	}
	
	private function xfield( $key ) {
		
		foreach( explode("||", $this->member_id['xfields']) as $xfield_str ) {
			
				$value = explode("|", $xfield_str);
				
				if( $value[0] == $key ) return $value[1];
			
		}
		
		return '';
	}

}
?>