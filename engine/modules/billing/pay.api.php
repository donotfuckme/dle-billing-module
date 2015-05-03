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

if( !defined( 'DATALIFEENGINE' ) ) {
    die( "Hacking attempt!" );
}

Class PAY_API {

	var $config = false;
	var $db = false;
	var $member_id = false;
	var $_TIME = false;

	function plus( $user, $money, $desc, $currency, $plugin = '', $plugin_id = '' ) {

			$desc = $this->db->safesql( $desc );
			$user = $this->db->safesql( $user );
	
			$money = $this->bf_convert( $money );
	
			if( $this->member_id['name'] == $user )
				$balance = $this->member_id[$this->config['fname']]+$money;
			else {
				
				$user_info = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE name='$user'" );
				
				$balance = $user_info[$this->config['fname']]+$money;
			}
	
			$this->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->config['fname']}={$this->config['fname']}+'$money' where name='$user'");
			
			$this->set_log($user, $money, 0, $balance, $desc, $currency, $plugin, $plugin_id);

		 return true;
	}

	function minus($user, $money, $desc, $currency, $plugin = '', $plugin_id = '') {

			$desc = $this->db->safesql( $desc );
			$user = $this->db->safesql( $user );
	
			$money = $this->bf_convert( $money );
	
			if( $this->member_id['name'] == $user )
				$balance = $this->member_id[$this->config['fname']]-$money;
			else {
				
				$user_info = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE name='$user'" );
				
				$balance = $user_info[$this->config['fname']]-$money;
			}
	
			$this->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->config['fname']}={$this->config['fname']}-'$money' where name='$user'");
			
			$this->set_log( $user, 0, $money, $balance, $desc, $currency, $plugin, $plugin_id );

		 return true;
	}
	
	function send_pm_to_user($user_id, $subject, $text, $from) {
			
			$user_id = intval( $user_id );

			$subject = $this->db->safesql( $subject );
			$text = $this->db->safesql( $text );
			$from = $this->db->safesql( $from );
			
			$now = time();
			
			$q = $this->db->query( "insert into " . PREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder) VALUES ('$subject', '$text', '$user_id', '$from', '$now', '0', 'inbox')" );
			
			if( ! $q ) return false;

			$this->db->query( "update " . USERPREFIX . "_users set pm_unread = pm_unread + 1, pm_all = pm_all+1  where user_id = '$user_id'" );
		
		return true;
	}
	
	function bf_paging( $all_count, $this_page, $link, $tpl_link, $tpl_this_num, $per_page ) {

		$all_count = intval( $all_count ) > 0 ? intval( $all_count ): 1;
		$this_page = intval( $this_page ) > 0 ? intval( $this_page ): 1;
		$per_page = intval( $per_page ) > 0 ? intval( $per_page ): $this->config['paging'];

		$enpages_count = @ceil( $all_count / $per_page );
		$enpages_start_from = 0;
		$enpages = "";

		if( $enpages_count==1 ) return false;
		
		$min = false;
		
		// left
		if( $this_page > 1 )
			$enpages = $this->bf_paging_form( ($this_page-1), $tpl_link, $link, "&laquo;" );
		
		// center
		for($j = 1; $j <= $enpages_count; $j ++) {
		
			// min limit
			if( $j < ( $this_page - 4 ) ) {
	
				if( !$min ) {
					$j++;
					$min = true;
			
					$enpages .= $this->bf_paging_form( 1, $tpl_link, $link, "1.." );
				} 
				continue;
			}
		
			// max limit
			if( $j > ( $this_page + 5 ) ) {
				
				$enpages .= $this->bf_paging_form( $enpages_count, $tpl_link, $link, "..{$enpages_count}" );
				
				break;
			}
			
			if( $this_page != $j ) {
				$enpages .= $this->bf_paging_form( $j, $tpl_link, $link );
			} else {
				$enpages .= $this->bf_paging_form( $j, $tpl_this_num, $link );
			}
			
			$enpages_start_from += $per_page;
		}

		// right
		if( $this_page < $enpages_count )
			$enpages .= $this->bf_paging_form( ($this_page+1), $tpl_link, $link, "&raquo;" );
		
		return $enpages;
	}

	private function bf_paging_form( $page, $form_link, $link, $title = '' ) {
		
		$link = str_replace( "{p}", $page, $link);
		
		$answer = str_replace( "{page_num}", ( $title ? $title : $page ), $form_link);
		$answer = str_replace( "{page_num_link}", $link, $answer);
		
		return $answer;
	}
	
	function bf_convert( $money, $format = '' ) {

		if( !$format ) $format = $this->config['format'];
		if( !$money ) $money = $format;
	
		$get = explode(".", str_replace(",", ".", $format) );

		if( !iconv_strlen($get[1]) ) return intval( $money );
		
		return number_format( str_replace(",", ".", $money) , iconv_strlen($get[1]), '.', '');

	}

	function bf_declOfNum( $number, $titles = '' ) {

		$number = intval( $number );
	
		if( !$titles ) $titles = $this->config['currency'];
		
		$titles = explode(",", $titles );

		if( count( $titles ) != 3 ) return $titles[0];

		$cases = array (2, 0, 1, 1, 1, 2);

		return $titles[ ($number%100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)] ];
	}
	
	private function set_log($user, $plus, $minus, $balance, $desc, $currency, $plugin = '', $plugin_id = '') {

		if( $plus<=0 and $minus<=0 ) return false;
	
		$balance = $this->bf_convert($balance );
	
		$this->db->query( "INSERT INTO " . PREFIX . "_billing_history (history_plugin, history_plugin_id, history_user_name, history_plus, history_minus, history_balance, history_currency, history_text, history_date) values 
							('$plugin', '$plugin_id', '$user', '$plus', '$minus', '$balance', '$currency', '$desc', '".$this->_TIME."')" );

	 return true;
	}


}

$pay_api = new PAY_API;

	if( !$billing_config['version'] ) 
		include (ENGINE_DIR . '/data/billing/config.php');

	$pay_api->config = $billing_config;
	
	if( !isset( $db ) ) {
	
			include_once (ENGINE_DIR . '/classes/mysql.php');
			include_once (ENGINE_DIR . '/data/dbconfig.php');
			
	}
	
	$pay_api->db = $db;
	$pay_api->_TIME = $_TIME;
	$pay_api->member_id = $member_id;

?>