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
       
		if( file_exists( MODULE_DATA."/plugin.transfer.php" ) )
				include MODULE_DATA."/plugin.transfer.php";
		
		$this->plugin_config = $plugin_config;
	   
	}
	
	function ok( $hash ) {
	
		/* Login */
		if( !$this->member_id['name'] )
			return $this->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] )
			return $this->T_msg( $this->lang['pay_error_title'], $this->lang['cabinet_off'], "Transfer" );
	
		$get = explode("|", base64_decode( urldecode( $hash ) ) );

		if( count($get) != 3 )
				return $this->lang['pay_hash_error'];
		
		return $this->T_msg( $this->lang['transfer_msgOk'], str_replace( "{link}", $this->config_dle['http_home_url']."user/".urlencode( $get[0] ), 
												str_replace( "{user}", $get[0], 
													str_replace( "{com}", $get[1]." ".$get[2], $this->lang['transfer_log_text'] ) ) ), "Transfer" );
	}
	
	function main( $page ) {

		/* Login */
		if( !$this->member_id['name'] )
			return $this->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] )
			return $this->T_msg( $this->lang['pay_error_title'], $this->lang['cabinet_off'], "Transfer" );
	
		/* Post */
		if( isset($_POST['submit']) ) {
		
			$post_user_name = $this->db_search_user_by_name( $this->db->safesql( $_POST['bs_user_name'] ) );
			
			$post_summa = $this->pay_api->bf_convert( $this->db->safesql( $_POST['bs_summa'] ) );
			
			$post_commission = $this->pay_api->bf_convert( ( $post_summa / 100 ) * $this->plugin_config['com'] );
			
			$error = "";

			if( !isset( $_POST['bs_hash'] ) OR $_POST['bs_hash'] != $this->hash() )
				$error = $this->lang['pay_hash_error'];
			
			else if( !$post_summa )
				$error = $this->lang['pay_summa_error'];
	
			else if( !$post_user_name['name'] )
				$error = $this->lang['transfer_error_get'];
	
			else if( $post_summa > $this->member_id[$this->config['fname']] )
				$error = $this->lang['refund_error_balance'];
	
			else if( $post_user_name['name'] == $this->member_id['name'] )
				$error = $this->lang['transfer_error_name_me'];
			
			else if( $post_summa < $this->plugin_config['minimum'] )
				$error = $this->lang['transfer_error_minimum'] . $this->plugin_config['minimum']." ".$this->pay_api->bf_declOfNum($this->plugin_config['minimum'] );	
			
			if( $error ) 
				return $this->T_msg( $this->lang['pay_error_title'], $error, "Transfer" );

			// - Process
			
			$this->pay_api->minus( $this->member_id['name'], $post_summa, str_replace( "{login}", $post_user_name['name'], $this->lang['transfer_log_for'] ), $this->pay_api->bf_declOfNum( $post_summa ), "transfer", $post_user_name['user_id'] );
			$this->pay_api->plus( $post_user_name['name'], ($post_summa-$post_commission), str_replace( "{login}", $this->member_id['name'], $this->lang['transfer_log_from'] ), $this->pay_api->bf_declOfNum( $post_summa ), "transfer", $post_user_name['user_id']);
			
			header( 'Location: '.$this->config_dle['http_home_url'].$this->config['page'].'.html/Transfer/ok/'.urlencode( base64_encode($post_user_name['name']."|".$post_commission ."|".$this->pay_api->bf_declOfNum( $post_commission ) ) ) );

			return TRUE;
		}
	
		/* PAGE */
		$get_summ = $_GET['summ'] ? $this->pay_api->bf_convert( $_GET['summ'] ) : $this->plugin_config['minimum'];
		
		$this->set_element( "{hash}", $this->hash() );
		$this->set_element( "{get_summ}", $get_summ );
		$this->set_element( "{get_summ_valuta}", $this->pay_api->bf_declOfNum( $get_summ ) );
		$this->set_element( "{minimum}", $this->plugin_config['minimum'] );
		$this->set_element( "{minimum_valuta}", $this->pay_api->bf_declOfNum( $this->plugin_config['minimum'] ) );
		$this->set_element( "{commission}", $this->plugin_config['com'] );
		$this->set_element( "{to}", $_GET['to'] );
	
		/* History */
		$tpl = $this->load( "Transfer" );
		$log = "";
			
		$tpl_log = $this->T_preg_match( $tpl, '~\[history\](.*?)\[/history\]~is' );
		$tpl_log_null = $this->T_preg_match( $tpl, '~\[not_history\](.*?)\[/not_history\]~is' );
		$tpl_log_date = $this->T_preg_match( $tpl_log, '~\{date=(.*?)\}~is' );
			
			$this->db_where( array( "history_plugin = '{s} ' "=>'transfer', "history_user_name = '{s}' " => $this->member_id['name'] ) );
			
			$num_history = $this->db_get_log_num();
			$history = $this->db_get_log( $page );

			foreach( $history as $history_id => $history_value ) {

				$time_log_theme = $tpl_log;
				$time_log_theme = str_replace("{date=".$tpl_log_date."}", langdate( $tpl_log_date, $history_value['history_date']), $time_log_theme);
				$time_log_theme = str_replace("{transfer_summa}", ( ($history_value['history_plus']) ? "<font color=\"green\">+".$history_value['history_plus']." ".$history_value['history_currency']."</font>":"<font color=\"red\">-".$history_value['history_minus']." ".$history_value['history_currency']."</font>" ), $time_log_theme);
				$time_log_theme = str_replace("{transfer_user}", $history_value['history_text'], $time_log_theme);

				$log .= $time_log_theme;
			}

				/* Paging */
				if( $num_history > $this->config['paging'] ) {
				
					$tpl_log_page = $this->T_preg_match( $tpl, '~\[paging\](.*?)\[/paging\]~is' );
					$tpl_log_page_link = $this->T_preg_match( $tpl, '~\[page_link\](.*?)\[/page_link\]~is' );
					$tpl_log_page_this = $this->T_preg_match( $tpl, '~\[page_this\](.*?)\[/page_this\]~is' );

					$tpl_log_page = preg_replace("'\\[page_link\\].*?\\[/page_link\\]'si", $this->pay_api->bf_paging( $num_history, $page, $this->config_dle['http_home_url'] . $this->config['page'] . ".html/Transfer/main/{p}", $tpl_log_page_link, $tpl_log_page_this ), $tpl_log_page );
					$tpl_log_page = preg_replace("'\\[page_this\\].*?\\[/page_this\\]'si", "", $tpl_log_page);
					
					$this->set_element_block( "paging", $tpl_log_page );
				
				} else
					$this->set_element_block( "paging", "" );
			
				/* LOG NULL */
				if( $log )	$this->set_element_block( "not_history", "" );
				else 		$this->set_element_block( "not_history", $tpl_log_null );
	
		$this->set_element_block( "history", $log );
		/* History END */
	
		$tpl = $this->load( "Transfer" );

		$this->set_element( "{content}", $tpl );
	
		return $this->load( "cabinet", "Transfer" );
	}

}
?>