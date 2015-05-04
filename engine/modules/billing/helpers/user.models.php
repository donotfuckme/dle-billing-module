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

Class MODEL {

	var $where = "";

	function db_search_users( $limit = 100 ) {

		$answer = array();
	
		$this->db->query( "SELECT * FROM " . USERPREFIX . "_users {$this->where} order by  ".$this->config['fname']." desc limit ".$limit );

		while ( $row = $this->db->get_row() )
			$answer[] = $row;
		
		return $answer;
	}

	function db_search_user_by_name( $name ) {
	
		$name = $this->db->safesql( $name );
	
		$user = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE name='$name'" );

		return $user;
	}

	function db_search_user_by_id( $id ) {
	
		$id = intval( $id );
	
		$user = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='$id'" );

		return $user;
	}
	function db_get_refund_by_id( $refund_id ) {

		$refund_id = intval( $refund_id );
	
		$refund = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_billing_refund WHERE refund_id='$refund_id'" );

		return $refund;
	}

	function db_refund_status( $refund_id, $new_status ) {
		
		$refund_id = intval( $refund_id );
		$new_status = $this->db->safesql( $new_status );
		
		$this->db->super_query( "UPDATE " . USERPREFIX . "_billing_refund SET refund_date_return=".$new_status." where refund_id='$refund_id'" );

		return true;
	}

	function db_refund_remove( $refund_id ) {
		
		$refund_id = intval( $refund_id );
		
		$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_id='$refund_id'" );

		return true;
	}

	function db_get_refund_num() {

		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_refund {$this->where}" );
		
        return $result_count['count'];
	}

	function db_get_refund( $start_from = 1, $per_page = 30 ) {
	
		if( intval( $start_from ) < 1 ) $start_from = 1;
	
		$start_from = ( $start_from * $per_page ) - $per_page;
	
		$answer = array();
	
		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_refund {$this->where} ORDER BY refund_id desc LIMIT {$start_from},{$per_page}" );
		
		while ( $row = $this->db->get_row() )
			$answer[$row['refund_id']] = $row;
		
		
		return $answer;
	}
	
	function db_get_invoice_num() {

		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_invoice {$this->where}" );
		
        return $result_count['count'];
	}
	
	function db_get_invoice( $start_from = 1, $per_page = 10 ) {
	
		if( intval( $start_from ) < 1 ) $start_from = 1;
	
		$start_from = ( $start_from * $per_page ) - $per_page;
	
		$answer = array();
	
		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_invoice {$this->where} ORDER BY invoice_id desc LIMIT {$start_from},{$per_page}" );
		
		while ( $row = $this->db->get_row() )
			$answer[$row['invoice_id']] = $row;
		
		return $answer;
	}
	
	function db_get_log_num() {

		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_history {$this->where}" );

        return $result_count['count'];
	}

	function db_get_log( $start_from = 1, $per_page = 30 ) {
	
		if( intval( $start_from ) < 1 ) $start_from = 1;
	
		if( $per_page > 99 ) $per_page = 100;
	
		$start_from = ( $start_from * $per_page ) - $per_page;
	
		$answer = array();
	
		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_history {$this->where} ORDER BY history_id desc LIMIT {$start_from},{$per_page}" );
		
		while ( $row = $this->db->get_row() )
			$answer[$row['history_id']] = $row;
		
		return $answer;
	}

	function db_creat_pay( $paysys, $user_name, $get, $pay ) {
	
		$user_name = $this->db->safesql( $user_name );
		$paysys = $this->db->safesql( $paysys );
		$get = $this->db->safesql( $get );
		$pay = $this->db->safesql( $pay );
		
		$this->db->query( "INSERT INTO " . USERPREFIX . "_billing_invoice (invoice_paysys, invoice_user_name, invoice_get, invoice_pay, invoice_date_creat) values ('$paysys', '$user_name', '$get', '$pay', '".$this->_TIME."')" );
				
		return $this->db->insert_id();
	}

	function db_get_invoice_by_id( $id ) {
	
		$id = intval( $id );
	
		$invoice = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_billing_invoice WHERE invoice_id='$id'" );

		return $invoice;
	}
	
	function db_invoice_ok( $invoice_id, $wait = false ) {

		$invoice_id = intval( $invoice_id );
	
		$time = ( !$wait ) ? $this->_TIME : 0;

		$this->db->super_query( "UPDATE " . USERPREFIX . "_billing_invoice SET invoice_date_pay='$time' where invoice_id='$invoice_id'" );

		return true;
	}
	
	function db_invoice_remove( $invoice_id ) {
	
		$invoice_id = intval( $invoice_id );
	
		$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_id='$invoice_id'" );

		return true;
	}
	
	function db_creat_refund( $user_name, $summa, $commission, $requisites ) {
	
		$user_name = $this->db->safesql( $user_name );
		
		$this->db->query( "INSERT INTO " . USERPREFIX . "_billing_refund (refund_date, refund_user, refund_summa, refund_commission, refund_requisites) values ('".$this->_TIME."', '$user_name', '$summa', '$commission', '$requisites')" );
				
		return $this->db->insert_id();
	}
		
	function db_where( $where_array ) {
	
		$this->where = array();
	
		$first = true;
		$where = "";
	
		foreach( $where_array as $key => $value ) {
			
			$value = $this->db->safesql( $value );
			
			if( !$value ) continue;
			
			if( $first ) {
				
				$where = "WHERE ".str_replace("{s}", $value, $key);
				
				$first = false;
				
			} else 
				$where .= "and ".str_replace("{s}", $value, $key);
			
		}
	
		$this->where = $where;
		
	}
	
}
?>