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

	function main( $page ) {

		/* Login */
		if( !$this->member_id['name'] )
			return $this->lang['pay_need_login'];

		if( intval( $page ) <= 0 ) $page = 1;
		
		$tpl = $this->load( "history" );
		$log = "";

		$tpl_log = $this->T_preg_match( $tpl, '~\[history\](.*?)\[/history\]~is' );
		$tpl_log_null = $this->T_preg_match( $tpl, '~\[not_history\](.*?)\[/not_history\]~is' );
		$tpl_log_date = $this->T_preg_match( $tpl_log, '~\{date=(.*?)\}~is' );
		
		$this->db_where( array( "history_user_name = '{s}' " => $this->member_id['name'] ) );
		
		$history = $this->db_get_log( $page, $this->config['paging'] );
		$num_history = $this->db_get_log_num();

		foreach( $history as $history_id => $history_value ) {

			$time_log_theme = $tpl_log;
			$time_log_theme = str_replace("{date=".$tpl_log_date."}", langdate( $tpl_log_date, $history_value['history_date']), $time_log_theme);
			$time_log_theme = str_replace("{comment}", $history_value['history_text'], $time_log_theme);
			$time_log_theme = str_replace("{balance}", $history_value['history_balance'] ." ". $this->pay_api->bf_declOfNum( $history_value['history_balance'] ), $time_log_theme);
			$time_log_theme = str_replace("{summa}", ( ($history_value['history_plus']) ? "<font color=\"green\">+".$history_value['history_plus']." ".$history_value['history_currency']."</font>":"<font color=\"red\">-".$history_value['history_minus']." ".$history_value['history_currency']."</font>" ), $time_log_theme);

			$log .= $time_log_theme;
		}
	
		/* Paging */
		if( $num_history > $this->config['paging'] ) {
		
			$tpl_log_page = $this->T_preg_match( $tpl, '~\[paging\](.*?)\[/paging\]~is' );
			$tpl_log_page_link = $this->T_preg_match( $tpl, '~\[page_link\](.*?)\[/page_link\]~is' );
			$tpl_log_page_this = $this->T_preg_match( $tpl, '~\[page_this\](.*?)\[/page_this\]~is' );

			$tpl_log_page = preg_replace("'\\[page_link\\].*?\\[/page_link\\]'si", $this->pay_api->bf_paging( $num_history, $page, $this->config_dle['http_home_url'] . $this->config['page'] . ".html/log/main/{p}", $tpl_log_page_link, $tpl_log_page_this ), $tpl_log_page);
			$tpl_log_page = preg_replace("'\\[page_this\\].*?\\[/page_this\\]'si", "", $tpl_log_page);
			
			$this->set_element_block( "paging", $tpl_log_page );
		
		} else
			$this->set_element_block( "paging", "" );
	
		/* LOG NULL */
		if( $log )	$this->set_element_block( "not_history", "" );
		else 		$this->set_element_block( "not_history", $tpl_log_null );
	
		$this->set_element_block( "history", $log );
		$this->set_element( "{content}", $tpl );
	
		return $this->load( "cabinet" );
	}

}
?>