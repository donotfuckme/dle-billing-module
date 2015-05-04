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
	var $paysys = array();
	
	function main() {
		
		/* Login */
		if( !$this->member_id['name'] )
			return $this->lang['pay_need_login'];
		
		/* POST */
		if( isset($_POST['submit']) ) {
		
			$post_paysys = preg_replace('~[^a-z|0-9|\-|.]*~is', '', $_POST['bs_paysys']);

			$post_summa = $this->pay_api->bf_convert( $_POST['bs_summa'] );
			
			$paysys = $this->paysys_list();
			
			$error = "";
	
			$pay = $this->pay_api->bf_convert( ($post_summa*$paysys[$post_paysys]['convert']), $paysys[$post_paysys]['format'] );
		
			if( !$_POST['bs_hash'] OR $_POST['bs_hash'] != $this->hash() )
				$error = $this->lang['pay_hash_error'];
			
			else if( !isset( $post_paysys ) OR !isset( $paysys[$post_paysys]['status'] ) )
				$error = $this->lang['pay_paysys_error'];
		
			else if( !isset( $post_summa ) )
				$error = $this->lang['pay_summa_error'];
	
			else if( $post_summa < $paysys[$post_paysys]['minimum'] )
				$error = $this->lang['pay_minimum_error'] . $paysys[$post_paysys]['title']." - ".$paysys[$post_paysys]['minimum']." ".$this->pay_api->bf_declOfNum( $paysys[$post_paysys]['minimum'] );
	
			if( $error )
				return $this->T_msg( $this->lang['pay_error_title'], $error, "pay" );

				// - Creat pay
				$invoice_id = $this->db_creat_pay( $post_paysys, $this->member_id['name'], $post_summa, $pay );
			
				$array_to_msg = array(
									'{id}' => $invoice_id,
									'{summa}' => $pay ." ". $this->pay_api->bf_declOfNum( $pay, $paysys[$post_paysys]['currency'] ),
									'{summa_get}' => $post_summa ." ". $this->pay_api->bf_declOfNum( $post_summa ),
									'{payments}' => $paysys[$post_paysys]['title'],
									'{link}' => $this->config_dle['http_home_url'].$this->config['page'].'.html/pay/waiting/'.$invoice_id,
									);
			
				$this->send_msg( "newpay", $this->member_id['user_id'], $array_to_msg );
			
			header( 'Location: '.$this->config_dle['http_home_url'].$this->config['page'].'.html/pay/waiting/'.$invoice_id );
			
			return true;

		}
		/* -- END POST -- */
	
		/* PAYSYS LIST */
		$tpl = $this->load( "pay_select" );
		$tpl_paysys = $this->T_preg_match( $tpl, '~\[paysys\](.*?)\[/paysys\]~is' );
		$paysys = "";

		if( count( $this->paysys_list() ) )
			foreach( $this->paysys as $name=>$info ) {
			
				$time_paysys_theme = $tpl_paysys;
				$time_paysys_theme = str_replace("{paysys_name}", $name, $time_paysys_theme);
				$time_paysys_theme = str_replace("{paysys_title}", $info['title'], $time_paysys_theme);
				$time_paysys_theme = str_replace("{paysys_convert}", $info['convert'], $time_paysys_theme);
				$time_paysys_theme = str_replace("{paysys_minimum}", $info['minimum'], $time_paysys_theme);
				$time_paysys_theme = str_replace("{paysys_icon}", $info['icon'], $time_paysys_theme);
				$time_paysys_theme = str_replace("{paysys_text}", $info['text'], $time_paysys_theme);
				
				$time_paysys_theme = str_replace("{paysys_valuta}", $this->pay_api->bf_declOfNum(1, $info['currency'] ), $time_paysys_theme);
				$time_paysys_theme = str_replace("{module_valuta}", $this->pay_api->bf_declOfNum(1, $this->config['currency'] ), $time_paysys_theme);
			
				$paysys .= $time_paysys_theme;
			}
		else
			$paysys = $this->lang['pay_main_error'];
		
		$this->set_element_block( "paysys", $paysys );
		$this->set_element( "{module_valuta}", $this->pay_api->bf_declOfNum( 10 ) );
		$this->set_element( "{hash}", $this->hash() );

		$this->set_element( "{content}", $this->load( "pay_select" ) );
	
		return $this->load( "cabinet", "pay" );
	}

	function ok() {
		
		$this->set_element( "{content}", $this->load( "pay_ok" ) );
	
		return $this->load( "cabinet", "pay" );
		
	}

	function bad() {
		
		$this->set_element( "{content}", $this->load( "pay_bad" ) );
	
		return $this->load( "cabinet", "pay" );
		
	}
	
	function waiting( $id ) {

		$invoice = $this->db_get_invoice_by_id( $id );
		$paysys = $this->paysys_list();
		$msg = "";

		if( !isset( $invoice['invoice_paysys'] ) or $invoice['invoice_user_name'] != $this->member_id['name'] )
			$msg = $this->lang['pay_invoice_error'];
		else {
		
			$this->set_element( "{paysys_title}", $paysys[$invoice['invoice_paysys']]['title'] );
			$this->set_element( "{summa}", $invoice['invoice_pay'] );
			$this->set_element( "{valuta}",  $paysys[$invoice['invoice_paysys']]['currency'] );
			$this->set_element( "{money}", $invoice['invoice_get']." ".$this->pay_api->bf_declOfNum( $invoice['invoice_pay'] ) );
			
			// - was pay
			if( $invoice['invoice_date_pay'] )
				$msg = $this->load( "ok" );
			else {
			
				// - get form pay
				require_once MODULE_PATH . '/helpers/adm.theme.php';
			
				/* Load */
				if( file_exists( MODULE_PATH."/paysys/" . $invoice['invoice_paysys'] . "/adm.settings.php" ) ) {
				
					require_once MODULE_PATH . '/paysys/' . $invoice['invoice_paysys'] . '/adm.settings.php';
					
					// - redirect
					if( $this->config['redirect'] )
						$redirect_form = '	<script type="text/javascript">
												window.onload = function() {
													document.getElementById("paysys_form").submit();
												}
											</script>';
					else 
						$redirect_form = "";
					
					$this->set_element( "{button}", $redirect_form . $Paysys->form( $id, $paysys[$invoice['invoice_paysys']], $invoice, $this->pay_api->bf_declOfNum( $invoice['invoice_pay'] ) ) );
					
				} else
					$this->set_element( "{button}", $this->lang['pay_file_error'] );

				$msg = $this->load( "waiting" );
			
			}
		}

		return $this->T_msg( str_replace("{id}", $id, $this->lang['pay_invoice']), $msg, "pay" );
	}
	
	/* Pay process */
	function get( $getpaysys ) {

		@header( "Content-type: text/html; charset=" . $this->config_dle['charset'] );
	
		$key = $this->db->safesql( $_GET['key'] );
		$paysys = $this->paysys_list();

		if( !$key or $key != $this->config['secret'] )
				die( $this->lang['pay_getErr_key'] );
		if( !$getpaysys or !$paysys[$getpaysys]['status'] )
				die( $this->lang['pay_getErr_paysys'] );
		
		require_once MODULE_PATH . '/helpers/adm.theme.php';
					
				/* Load */
				if( file_exists( MODULE_PATH."/paysys/" . $getpaysys . "/adm.settings.php" ) ) {
				
					require_once MODULE_PATH . '/paysys/' . $getpaysys . '/adm.settings.php';

					$check_id = $Paysys->check_id( $_POST );
					$invoice = $this->db_get_invoice_by_id( $check_id );
					$check_out = $Paysys->check_out( $_POST, $paysys[$getpaysys], $invoice );
					
					if( $check_out == "200" ) {
						
						if( $this->pay_ok( $check_id ) )
							echo $Paysys->check_ok( $_POST );
						else
							echo $this->lang['pay_getErr_invoice'];
						
					} else
						echo $check_out;
					
				} else
					echo $this->lang['pay_file_error'];

		
	
		die();
	}
	
	private function pay_ok( $invoice_id ) {
	
		$invoice = $this->db_get_invoice_by_id( $invoice_id );
		$paysys = $this->paysys_list();
		
		if( $invoice['invoice_date_pay'] or !$invoice['invoice_id'] ) return false;
		
		$this->db_invoice_ok( $invoice_id );

		$get_usert = $this->db_search_user_by_name( $invoice['invoice_user_name'] );
	
		$this->pay_api->plus( $invoice['invoice_user_name'], $this->pay_api->bf_convert( $invoice['invoice_get'] ), str_replace( "{paysys}", $paysys[$invoice['invoice_paysys']]['title'], str_replace( "{money}", "{$invoice['invoice_pay']} {$paysys[$invoice['invoice_paysys']]['currency']}", $this->lang['pay_msgOk'] ) ), $this->pay_api->bf_declOfNum($invoice['invoice_get'] ), "pay", $invoice_id );

				$array_to_msg = array(
									'{id}' => $invoice_id,
									'{summa}' => $invoice['invoice_pay']." ".$paysys[$invoice['invoice_paysys']]['currency'],
									'{summa_get}' => $invoice['invoice_get']." ".$this->pay_api->bf_declOfNum( $invoice['invoice_get'] ),
									'{payments}' => $paysys[$invoice['invoice_paysys']]['title']
									);
			
				$this->send_msg( "yespay", $get_usert['user_id'], $array_to_msg );
	
		return true;
	}
	
	private function paysys_list() {
	
		if( count( $this->paysys ) ) return $this->paysys;

		$load_list = opendir( MODULE_PATH . "/paysys/" );

		while ( $name = readdir($load_list) ) {
		
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;
		
			/* Config */
			if( file_exists( MODULE_DATA."/pasys." . $name . ".php" ) )
				require_once MODULE_DATA."/pasys." . $name . ".php";
			else
				continue;
		
			if( !$paysys_config['status'] ) continue;
		
			$this->paysys[$name] = $paysys_config;
		
		}
	
		return $this->paysys;
	}
	
}
?>