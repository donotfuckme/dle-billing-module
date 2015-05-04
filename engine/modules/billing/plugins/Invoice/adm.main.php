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
	
		$paysys = $this->paysys_array();
	
		/* Act */
		if( isset( $_POST['act_do'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$mass_list = $_POST['massact_list'];
			$mass_act = $_POST['act'];
		  
			foreach( $mass_list as $mass_id ) {
			  
				$mass_id = intval( $mass_id );
				
				if( !$mass_id ) continue;
				
				// - remove
				if( $mass_act == "remove" )
					$this->db_invoice_remove( $mass_id );
								
				// - status ok
				if( $mass_act == "ok" )
					$this->db_invoice_ok( $mass_id );
											
				// - status no
				if( $mass_act == "no" )
					$this->db_invoice_ok( $mass_id, true );
				
				// - status ok and pay
				if( $mass_act == "ok_pay" ) {

					$invoice = array();
					$invoice = $this->db_get_invoice_by_id( $mass_id );
					
					if( $invoice['invoice_user_name'] and !$invoice['invoice_date_pay'] ) {
						
						$this->db_invoice_ok( $mass_id );
						
						$this->pay_api->plus( $invoice['invoice_user_name'], $this->pay_api->bf_convert( $invoice['invoice_get'] ), str_replace( "{paysys}", $paysys[$invoice['invoice_paysys']]['title'], str_replace( "{money}", "{$invoice['invoice_pay']} {$paysys[$invoice['invoice_paysys']]['currency']}", $this->lang['pay_msgOk'] ) ), $this->pay_api->bf_declOfNum( $invoice['invoice_get'] ), "pay", $mass_id );

					}
				}
			}
			
			$this->T_msg( $this->lang['ok'], $this->lang['invoice_ok'], $PHP_SELF . "?mod=billing&c=Invoice" );
		}

		$data = explode("/", $data);
		
		if( $data[0]==1 ) $data[0] = '';

		$content = $this->header();
		$content .= $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; " . $this->lang['invoice_title'] . ( ($data[0]) ? " ( ".$this->lang['history_for']." ". $data[0] ." <a href=\"{$PHP_SELF}?mod=billing&c=Invoice\" class=\"tip\" title=\"".$this->lang['remove']."\"><i class=\"icon-remove\"></i></a> )": "" ), "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->lang['history_search']."</a>" );

		/* Search Form */
		$content .= "<div style=\"display:none\" name=\"searchhistory\" id=\"searchhistory\">";

		$select_paysys = array();
		$select_paysys[""] = "Все";
		
		foreach( $paysys as $name=>$info )
			$select_paysys[$name] = $info['title'];
		
		$this->T_str_table( $this->lang['invoice_type_payok'], $this->lang['invoice_type_payok_desc'], $this->T_makeDropDown( array(''=>"Тип операции",'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_logick", $_POST['search_logick'] ) );
		$this->T_str_table( $this->lang['invoice_summa'], $this->lang['invoice_summa_desc'], "<input name=\"search_summa\" value=\"".$_POST['search_summa']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['invoice_ps'], $this->lang['invoice_ps_desc'], $this->T_makeDropDown( $select_paysys, "search_paysys", $_POST['search_paysys'] ) );
		$this->T_str_table( $this->lang['search_user'], $this->lang['search_user_desc'], "<input name=\"search_login\" value=\"".$_POST['search_login']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['invoice_status'], $this->lang['invoice_status_desc'], $this->T_makeDropDown( array(''=>$this->lang['invoice_status_1'], 'ok'=>$this->lang['invoice_status_2'], 'no'=>$this->lang['invoice_status_3'] ), "search_status", $_POST['search_status'] ) );
		$this->T_str_table( $this->lang['search_type_date'], $this->lang['search_type_date_desc'], $this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_date_logick", $_POST['search_date_logick'] ) );
		$this->T_str_table( $this->lang['search_date'], $this->lang['search_date_desc'], "<input data-rel=\"calendar\" type=\"text\" name=\"search_date\" value=\"".$_POST['search_date']."\" class=\"edit bk\" style=\"width: 100%\" >" );
																																	
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-blue\" style=\"margin:7px;\" name=\"search_btn\" type=\"submit\" value=\"".$this->lang['history_search']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= "</div>";
		
		$this->T_set_list( array( 	'<td width="1%">#</td>',
									'<td width="5%">'.$this->lang['invoice_str_payok'].'</td>',
									'<td width="15%">'.$this->lang['invoice_str_get'].'</td>',
									'<td>'.$this->lang['history_date'].'</td>',
									'<td>'.$this->lang['invoice_str_ps'].'</td>',
									'<td>'.$this->lang['history_user'].'</td>',
									'<td width="20%">'.$this->lang['invoice_str_status'].'</td>',
									'<td width="5%"><center><input type="checkbox" value="" name="massact_list[]" onclick="checkAll(this)" /></center></td>',
								) );

		/* DB | Search */
		if( isset( $_POST['search_btn'] ) ) {
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";
			if( !in_array( $_POST['search_date_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_date_logick'] = "=";
	
			$search_status_value = 1;
			
			if( $_POST['search_status']=="ok" )
				$search_status = "invoice_date_pay!='0'";			
			elseif( $_POST['search_status']=="no" )
				$search_status = "invoice_date_pay='0'";
			else 
				$search_status_value = 0;
	
				$search_array = array(
					"invoice_pay {$_POST['search_logick']}'{s}' " => $_POST['search_summa'],
					"invoice_paysys='{s}' " => $_POST['search_paysys'],
					"invoice_user_name LIKE '{s}' " => $_POST['search_login'],
					"$search_status " => $search_status_value,
					"invoice_date_creat ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] )
				);
		
			$this->db_where( $search_array );
								
			$per_page = 100;
			$num_history = $this->db_get_invoice_num();
			$history = $this->db_get_invoice( 1, $per_page );

		} else {
					
			$this->db_where( array( "invoice_user_name = '{s}' " => $data[0] ) );
					
			$per_page = 30;
			$num_history = $this->db_get_invoice_num();
			$history = $this->db_get_invoice( $data[1], $per_page );
		
		}

		foreach( $history as $history_id => $history_value ) {

			$this->T_set_list( array( 	$history_id,
										$history_value['invoice_pay'] . " ". $paysys[$history_value['invoice_paysys']]['currency'],
										$history_value['invoice_get'] ." ". $this->pay_api->bf_declOfNum( $history_value['invoice_pay'] ),
										langdate( "j F Y  G:i", $history_value['invoice_date_creat']),
										( $paysys[$history_value['invoice_paysys']]['title'] ? $this->T_billing( $paysys[$history_value['invoice_paysys']] ) : $history_value['invoice_paysys'] ),
										$this->T_user( $history_value['invoice_user_name'] ),
										( ($history_value['invoice_date_pay']) ? "<font color=\"green\">".$this->lang['invoice_summa']." ".langdate( "j F Y  G:i", $history_value['invoice_date_creat'])."</font>" : "<font color=\"red\">".$this->lang['invoice_status_3']."</font>" ),
										"<center><input name=\"massact_list[]\" value=\"".$history_id."\" type=\"checkbox\"></center>"
									) );
		}
									
		$content .= $this->T_pars_list();
		
		/* Act and Paging */
		if( $num_history )	
			$content .= $this->T_padded( '
						<div class="pull-left" style="margin:7px; vertical-align: middle">
							<ul class="pagination pagination-sm">
								'.$this->pay_api->bf_paging( $num_history, $data[1], $PHP_SELF . "?mod=billing&c=Invoice&p={$data[0]}/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>", $per_page ).'
							</ul>
						</div>
						
											<select name="act" class="uniform">
												<option value="ok">'.$this->lang['invoice_edit_1'].'</option>
												<option value="no">'.$this->lang['invoice_edit_2'].'</option>
												<option value="ok_pay">'.$this->lang['invoice_edit_3'].'</option>
												<option value="remove">'.$this->lang['remove'].'</option>
											</select>
											
											<input class="btn btn-gold" style="margin:7px; vertical-align: middle" name="act_do" type="submit" value="'.$this->lang['act'].'">
											<input type="hidden" name="user_hash" value="' . $this->hash . '" />
						', 'box-footer', 'right' );
						
		/* Null */
		if( !$num_history )
			$content .= $this->T_padded( $this->lang['history_no'], '' );

		$content .= $this->header_end();		
		$content .= $this->foother();

		return $content;
		
	}
	
}
?>