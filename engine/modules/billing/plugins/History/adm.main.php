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

		$data = explode("/", $data);

		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; " . $this->lang['history_title'] . ( ($data[0]) ? " ( ".$this->lang['history_for']." ". $data[0] ." <a href=\"{$PHP_SELF}?mod=billing&c=History\" class=\"tip\" title=\"".$this->lang['remove']."\"><i class=\"icon-remove\"></i></a> )": "" ), "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->lang['history_search']."</a>" );
		
		/* Search Form */
		$content .= "<div style=\"display:none\" name=\"searchhistory\" id=\"searchhistory\">";

		$this->T_str_table( $this->lang['search_pcode'], $this->lang['search_pcode_desc'], "<input name=\"search_plugin\" class=\"edit bk\" value=\"".$_POST['search_plugin']."\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_pid'], $this->lang['search_pcode_desc'], "<input name=\"search_plugin_id\" class=\"edit bk\" value=\"".$_POST['search_plugin_id']."\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_type_summa'], $this->lang['search_type_summa_desc'], $this->T_makeDropDown( array( ''=>$this->lang['search_tsd_1'], 'plus'=> $this->lang['search_tsd_2'], 'minus'=>$this->lang['search_tsd_3'] ), "search_type", $_POST['search_type'] )." ".$this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_logick", $_POST['search_logick'] ) );
		$this->T_str_table( $this->lang['users_summa'], $this->lang['search_summa_desc'], "<input name=\"search_summa\" value=\"".$_POST['search_summa']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_user'], $this->lang['search_user_desc'], "<input name=\"search_login\" value=\"".$_POST['search_login']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_comm'], $this->lang['search_comm_desc'], "<input name=\"search_comment\" value=\"".$_POST['search_comment']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_type_date'], $this->lang['search_type_date_desc'], $this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_date_logick", $_POST['search_date_logick'] ) );
		$this->T_str_table( $this->lang['search_date'], $this->lang['search_date_desc'], "<input data-rel=\"calendar\" type=\"text\" name=\"search_date\" value=\"".$_POST['search_date']."\" class=\"edit bk\" style=\"width: 100%\" >" );
																																	
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-blue\" style=\"margin:7px;\" name=\"search_btn\" type=\"submit\" value=\"".$this->lang['history_search']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= "</div>";
		
		/* List */
		$this->T_set_list( array( 	'<td width="1%">#</td>',
									'<td width="12%">'.$this->lang['history_code'].'</td>',
									'<td>'.$this->lang['history_summa'].'</td>',
									'<td>'.$this->lang['history_date'].'</td>',
									'<td>'.$this->lang['history_user'].'</td>',
									'<td>'.$this->lang['history_balance'].'</td>',
									'<td width="35%">'.$this->lang['history_comment'].'</td>'
								) );

		/* DB | Search */
		if( isset( $_POST['search_btn'] ) ) {
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";
			if( !in_array( $_POST['search_date_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_date_logick'] = "=";
			
			if( $_POST['search_type'] == "plus" )
				$search_type_summa = "history_plus ".$_POST['search_logick']."'{s}' and history_minus='0' "; 
			elseif( $_POST['search_type'] == "minus" )
				$search_type_summa = "history_minus".$_POST['search_logick']."'{s}' and history_plus='0' ";
			else $_POST['search_summa'] = "";

			$search_array = array(
				"history_plugin ='{s}' " => $_POST['search_plugin'],
				"history_plugin_id ='{s}' " => intval( $_POST['search_plugin_id'] ),
				"$search_type_summa" => $_POST['search_summa'],
				"history_date ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] ),
				"history_user_name LIKE '{s}' " => $_POST['search_login'],
				"history_text LIKE '{s}' " => $_POST['search_comment']
			);
			
			$this->db_where( $search_array );

			$per_page = 100;
			$num_history = $this->db_get_log_num();
			$history = $this->db_get_log( 1, $per_page );
			
		} else {
					
			$this->db_where( array( "history_user_name = '{s}' " => $data[0] ) );
					
			$per_page = 30;
			$num_history = $this->db_get_log_num();
			$history = $this->db_get_log( $data[1], $per_page );
			
		}
			
		foreach( $history as $history_id => $history_value ) {

			$this->T_set_list( array( 	$history_id,
										$history_value['history_plugin'].":".$history_value['history_plugin_id'],
										( ($history_value['history_plus']) ? "<font color=\"green\">+".$history_value['history_plus']." ".$history_value['history_currency']."</font>":"<font color=\"red\">-".$history_value['history_minus']." ".$history_value['history_currency']."</font>" ),
										langdate( "j F Y  G:i", $history_value['history_date']),
										$this->T_user( $history_value['history_user_name'] ),
										$this->pay_api->bf_convert( $history_value['history_balance'] ) ." ". $this->pay_api->bf_declOfNum( $history_value['history_balance'] ),
										$history_value['history_text']
									) );
		}
									
		$content .= $this->T_pars_list();
		
		/* Paging */
		if( $num_history > $per_page )
			$content .= $this->T_padded( "<ul class=\"pagination pagination-sm\">".$this->pay_api->bf_paging( $num_history, $data[1], $PHP_SELF . "?mod=billing&c=History&p={$data[0]}/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>", $per_page )."</ul>" );
		
		/* Null */
		if( !$num_history )
			$content .= $this->T_padded( $this->lang['history_no'], '' );

		$content .= $this->header_end();
		$content .= $this->foother();
		
		return $content;
		
	}
	
}
?>