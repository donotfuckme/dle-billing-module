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

		/* Save */
		if( isset( $_POST['save'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$save_con = $_POST['save_con'];
		
			$this->save_file_array("plugin.refund", $save_con, "plugin_config");
			$this->T_msg( $this->lang['ok'], $this->lang['save_settings'] );
		
		}
		
		/* Act */
		if( isset( $_POST['act_do'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$remove_list = $_POST['remove_list'];
			$remove_act = $_POST['act'];
		  
			foreach($remove_list as $remove_id) {
			  
				$remove_id = intval( $remove_id );
				
				if( !$remove_id ) continue;
				
				if( $remove_act == "ok" )
					$this->db_refund_status( $remove_id, $this->_TIME );
				else if( $remove_act == "wait" )
					$this->db_refund_status( $remove_id, '' );
				else if( $remove_act == "remove" )
					$this->db_refund_remove( $remove_id );
				else if( $remove_act == "back" ) {
					
					$refund_info = $this->db_get_refund_by_id( $remove_id );
					
					$this->pay_api->plus($refund_info['refund_user'], 
									$this->pay_api->bf_convert( $refund_info['refund_summa'] ), 
									str_replace("{remove_id}", $remove_id, $this->lang['refund_back']), 
									$this->pay_api->bf_declOfNum($refund_info['refund_summa'] ), 
									"refund", 
									$remove_id );
					
					$this->db_refund_remove( $remove_id );
					
				}
				
			}
		
			$this->T_msg( $this->lang['ok'], $this->lang['refund_act'], $PHP_SELF . "?mod=billing&c=Refund" );
		}
	
		/* Load and Install */
		if( file_exists( MODULE_DATA."/plugin.refund.php" ) )
			require_once MODULE_DATA . "/plugin.refund.php";
		else {
			
			$this->save_file_array("plugin.refund", array( 'status'=>"0" ), "plugin_config");
			
			$this->T_msg( $this->lang['install_plugin'], $this->lang['install_plugin_desc'], $PHP_SELF . "?mod=billing&c=Refund" );
		}

		$content = $this->header();
		$content = $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['refund_title'] . ( ($data[0]) ? " ( ".$this->lang['history_for']." ". $data[0] ." <a href=\"{$PHP_SELF}?mod=billing&c=Refund\" class=\"tip\" title=\"".$this->lang['remove']."\"><i class=\"icon-remove\"></i></a> )": "" ), "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->lang['history_search']."</a>" ); 
		
		/* Search Form */
		$content .= "<div style=\"display:none\" name=\"searchhistory\" id=\"searchhistory\">";

		$this->T_str_table( $this->lang['search_type_summa'], $this->lang['search_type_summa_desc'], $this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_logick", $_POST['search_logick'] ) );
		$this->T_str_table( $this->lang['refund_se_summa'], $this->lang['refund_se_summa_desc'], "<input name=\"search_summa\" value=\"".$_POST['search_summa']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['refund_se_req'], $this->lang['refund_se_req_desc'], "<input name=\"search_requisites\" value=\"".$_POST['search_requisites']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['search_user'], $this->lang['search_user_desc'], "<input name=\"search_login\" value=\"".$_POST['search_login']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
		$this->T_str_table( $this->lang['refund_se_status'], $this->lang['refund_se_status_desc'], $this->T_makeDropDown( array(''=>$this->lang['refund_se_s_1'], 'wait'=>$this->lang['refund_se_s_2'], 'ok'=>$this->lang['refund_se_s_3'] ), "search_status", $_POST['search_status'] ) );
		$this->T_str_table( $this->lang['search_type_date'], $this->lang['search_type_date_desc'], $this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_date_logick", $_POST['search_date_logick'] ) );
		$this->T_str_table( $this->lang['search_date'], $this->lang['search_date_desc'], "<input data-rel=\"calendar\" type=\"text\" name=\"search_date\" value=\"".$_POST['search_date']."\" class=\"edit bk\" style=\"width: 100%\" >" );
																																	
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-blue\" style=\"margin:7px;\" name=\"search_btn\" type=\"submit\" value=\"".$this->lang['history_search']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= "</div>";
		
		$this->T_set_list( array( 	'<td width="1%"><b>#</b></td>',
									'<td>'.$this->lang['refund_summa'].'</td>',
									'<td>'.$this->lang['refund_commision_list'].'</td>',
									'<td>'.$this->lang['refund_requisites'].'</td>',
									'<td>'.$this->lang['history_date'].'</td>',
									'<td>'.$this->lang['history_user'].'</td>',
									'<td>'.$this->lang['status'].'</td>',
									'<td><center><input type="checkbox" value="" name="remove_list[]" onclick="checkAll(this)" /></center></td>'
								) );

		/* DB | Search */
		if( isset( $_POST['search_btn'] ) ) {
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";
			if( !in_array( $_POST['search_date_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_date_logick'] = "=";
			
			$search_status_value = 1;
			
			if( $_POST['search_status']=="wait" )
				$search_status = "refund_date_return='0'";			
			elseif( $_POST['search_status']=="ok" )
				$search_status = "refund_date_return!='0'";
			else 
				$search_status_value = 0;
			
				$search_array = array(
					"refund_summa-refund_commission {$_POST['search_logick']}'{s}' " => $_POST['search_summa'],
					"refund_requisites LIKE '{s}' " => $_POST['search_requisites'],
					"refund_requisites LIKE '{s}' " => $_POST['search_requisites'],
					"refund_user LIKE '{s}' " => $_POST['search_login'],
					"$search_status " => $search_status_value,
					"refund_date ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] ),
				);
				
			$this->db_where( $search_array );
								
			$per_page = 100;
			$num_history = $this->db_get_refund_num();
			$history = $this->db_get_refund( 1, $per_page );

		} else {
					
			$this->db_where( array( "refund_user = '{s}' " => $data[0] ) );
					
			$per_page = 30;
			$num_history = $this->db_get_refund_num();
			$history = $this->db_get_refund( $data[1], $per_page );
		
		}


		foreach( $history as $history_id => $history_value ) {

			$this->T_set_list( array( 	$history_id,
										$this->pay_api->bf_convert( $history_value['refund_summa']-$history_value['refund_commission'] ).$this->pay_api->bf_declOfNum(($history_value['refund_summa']-$history_value['refund_commission']) ),
										$this->pay_api->bf_convert( $history_value['refund_commission'] ).$this->pay_api->bf_declOfNum( $history_value['refund_commission'] ),
										$history_value['refund_requisites'],
										langdate( "j F Y  G:i", $history_value['refund_date']),
										$this->T_user( $history_value['refund_user'] ),
										( ($history_value['refund_date_return']) ? "<font color=\"green\">".langdate( "j F Y  G:i", $history_value['refund_date_return'])."</a>": "<font color=\"red\">".$this->lang['refund_wait']."</a>" ),
										'<center><input name="remove_list[]" value="'.$history_id.'" type="checkbox"></center>'
									) );
		}
							
		$content .= $this->T_pars_list();

		/* Act and Paging */
		if( $num_history )	
			$content .= $this->T_padded( '
						<div class="pull-left" style="margin:7px; vertical-align: middle"><ul class="pagination pagination-sm">'.$this->pay_api->bf_paging( $num_history, $data[1], $PHP_SELF . "?mod=billing&c=Refund&p={$data[0]}/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>" ).'</ul></div>
						
											<select name="act" class="uniform">
												<option value="ok">'.$this->lang['refund_act_ok'].'</option>
												<option value="wait">'.$this->lang['refund_wait'].'</option>
												<option value="back">'.$this->lang['refund_act_no'].'</option>
												<option value="remove">'.$this->lang['remove'].'</option>
											</select>
											<input class="btn btn-gold" style="margin:7px; vertical-align: middle" name="act_do" type="submit" value="'.$this->lang['act'].'">
											<input type="hidden" name="user_hash" value="' . $this->hash . '" />
						', 'box-footer', 'right' );

		/* Null */
		if( !$num_history )
			$content .= $this->T_padded( $this->lang['history_no'], '' );
		
		$content .= $this->header_end();

		$content .= $this->header_start( $this->lang['main_settings'] );
		$this->T_str_table( $this->lang['settings_status'], $this->lang['refund_status_desc'], $this->T_makeCheckBox("save_con[status]", $plugin_config['status']) );
		$this->T_str_table( $this->lang['refund_email'], $this->lang['refund_email_desc'], $this->T_makeCheckBox("save_con[email]", $plugin_config['email']) );
		$this->T_str_table( $this->lang['paysys_name'], $this->lang['refund_name_desc'], "<input name=\"save_con[name]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['name'] ."\">" );
		$this->T_str_table( $this->lang['refund_minimum'], $this->lang['refund_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['minimum'] ."\"> " . $this->pay_api->bf_declOfNum( $plugin_config['minimum'] ) );
		$this->T_str_table( $this->lang['refund_commision'], $this->lang['refund_commision_desc'], "<input name=\"save_con[com]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['com'] ."\"> %" );
		$this->T_str_table( $this->lang['refund_field'], $this->lang['refund_field_desc'], $this->T_makeDropDown( $this->xfields(), "save_con[requisites]", $plugin_config['requisites'] ) );
		$this->T_str_table( $this->lang['refund_format'], $this->lang['refund_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['format'] ."\"> " );

		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"save\" type=\"submit\" value=\"".$this->lang['save']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		
		$content .= $this->foother();
		
		return $content;
		
	}
	
	private function xfields() {
		
		$answer = array(''=>"");
		
		$xprofile = file("engine/data/xprofile.txt");
		
		foreach($xprofile as $line) {
			
			$xfield = explode("|", $line);
			
			$answer[$xfield[0]] = $xfield[1];
			
		}
		
		return $answer;
	}
	
}
?>