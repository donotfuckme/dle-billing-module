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

	function remove( $hash ) {
		
		$data = explode("|", base64_decode( $hash ) );
		
		$data[0] = intval( $data[0] );
		
		if( $data[1] != $this->hash or !$data[0] ) 
			$this->T_msg( $this->lang['error'], $this->lang['stats_error_remove'] );
		
			$this->db_invoice_remove( $data[0] );
		
			$this->T_msg( $this->lang['ok'], $this->lang['stats_ok_remove'] );
	}

	function main() {

		global $user_group;

		# Edit
		if( isset( $_POST['edit_btn'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$edit_name = $this->db->safesql( $_POST['edit_name'] );
			$edit_comm = $this->db->safesql( $_POST['edit_comm'] );
			$edit_name = explode(",", $edit_name);
			
			$edit_group = intval( $_POST['edit_group'] );
			$edit_do = intval( $_POST['edit_do'] );
			$edit_summa= $this->pay_api->bf_convert( $_POST['edit_summa'] );
		
			$errors = "";
			
			if( !count( $edit_name ) and !$edit_group )
				$errors = $this->lang['users_er_user'];
			if( !$edit_summa )
				$errors = $this->lang['users_er_summa'];

				if( $errors )
					$this->T_msg( $this->lang['error'], $errors );
				else if( $edit_group ) {
		
					$e_users = array();
					
					$this->db->query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_group='$edit_group'");

					while ( $row = $this->db->get_row() )
						$e_users[] = $row['name'];
						
					// group plus
					if( $edit_do ) {

						foreach( $e_users as $u_num=>$u_name )
							$this->pay_api->plus( $u_name, $this->pay_api->bf_convert( $edit_summa ), $edit_comm, $this->pay_api->bf_declOfNum( $edit_summa ), "users", $this->member_id['user_id'] );
					
					// group minus	
					} else {
						
						foreach( $e_users as $u_num=>$u_name )
							$this->pay_api->minus( $u_name, $edit_summa, $edit_comm, $this->pay_api->bf_declOfNum( $edit_summa ), "users", $this->member_id['user_id'] );
						
					}

					$this->T_msg( $this->lang['ok'], $this->lang['users_ok_group'] );
					
				} else {
					
					if( $edit_do ) {
						
						foreach( $edit_name as $u_name )
							if( trim($u_name) ) $this->pay_api->plus( $u_name, $this->pay_api->bf_convert( $edit_summa ), $edit_comm, $this->pay_api->bf_declOfNum( $edit_summa ), "users", $this->member_id['user_id'] );
					
					} else {
						
						foreach( $edit_name as $u_name )
							if( trim($u_name) ) $this->pay_api->minus( $u_name, $edit_summa, $edit_comm, $this->pay_api->bf_declOfNum( $edit_summa ), "users", $this->member_id['user_id'] );
						
					}
					
					$this->T_msg( $this->lang['ok'], $this->lang['users_ok'], $PHP_SELF . "?mod=billing&c=Users" );	
					
				}
			
		}
		
		# View
		$content = $this->header();
		$content .= $this->header_start( "<a href='{$PHP_SELF}?mod=billing'>".$this->lang['title']."</a> &raquo; ".$this->lang['users_title'], "<a href=\"javascript:ShowOrHide('searchusers');\"><i class=\"icon-search\"></i> ". $this->lang['users_search'] ."</a>" );
		
		# Search form
		$content .= "<div style=\"display:none\" name=\"searchusers\" id=\"searchusers\">";
		
		$this->T_str_table( $this->lang['users_label'], $this->lang['users_label_desc'], "<input name=\"search_name\" value=\"".$_POST['search_name']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\" value=\"". $search_user ."\">" );
		$this->T_str_table( $this->lang['user_se_type_balance'], $this->lang['user_se_type_balance_desc'], $this->T_makeDropDown( array(''=>$this->lang['search_type_oper'],'>'=>">",'<'=>"<",'='=>"=",'!='=>"!="), "search_logick", $_POST['search_logick'] ) );
		$this->T_str_table( $this->lang['user_se_balance'], $this->lang['user_se_balance_desc'], "<input name=\"search_balance\" value=\"".$_POST['search_balance']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
																				
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-blue\" style=\"margin:7px;\" name=\"search_btn\" type=\"submit\" value=\"".$this->lang['history_search']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		
		$content .= "</div>";
		
		# Data list
		$this->T_set_list( array( 	'<td width="15%">'.$this->lang['users_tanle_login'].'</td>',
									'<td>'.$this->lang['users_tanle_email'].'</td>',
									'<td>'.$this->lang['users_tanle_group'].'</td>',
									'<td>'.$this->lang['users_tanle_datereg'].'</td>',
									'<td>'.$this->lang['users_tanle_balance'].'</td>'
								) );

		if( isset( $_POST['search_btn'] ) ) {
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";
			
			$search_array = array( 
							"name LIKE '%{s}%' or email LIKE '%{s}%' " => $_POST['search_name'],
							"{$this->config['fname']} {$_POST['search_logick']}'{s}' " => $_POST['search_balance']
			);
			
			$this->db_where( $search_array );

			$history = $this->db_search_users();

		} else {
			
			$this->db_where( array( "{$this->config['fname']}>0 " => 1 ) );
			
			$history = $this->db_search_users( 10 );

		}
			
		foreach( $history as $history_id => $history_value ) {

			$this->T_set_list( array( 	"<a id=\"add_".$history_value['name']."\" href=\"#\"><span onClick=\"$('#edit_name').val($('#edit_name').val()+',".$history_value['name']."'); $('#add_".$history_value['name']."').html('<span class=\'status-success\'><b><i class=\'icon-plus\' style=\'margin-left: 10px; vertical-align: middle\'></i></b></span>')\"><i class=\"icon-plus\" style=\"margin-left: 10px; vertical-align: middle\"></i></span></a>" . 
											$this->T_user( $history_value['name'] ),
										$history_value['email'],
										$user_group[$history_value['user_group']]['group_name'],
										langdate( "j F Y  G:i", $history_value['reg_date']),
										$this->pay_api->bf_convert( $history_value[$this->config['fname']] ) ." ". $this->pay_api->bf_declOfNum( $history_value[$this->config['fname']] )
									) );
		}
									
		$content .= $this->T_pars_list();

		/* Null */
		if( !count($history) )
			$content .=  $this->T_padded( $this->lang['history_no'], '' );

		$content .= $this->header_end();	

		/* Settings */
		$content .= $this->header_start( $this->lang['users_edit'] );
			
			foreach( $user_group as $group_id => $group )
				$select_group .= "<option value=\"".$group_id."\">".$group['group_name']."</option>";
			
		$this->T_str_table( $this->lang['users_login'], "", "<input name=\"edit_name\" id=\"edit_name\" class=\"edit bk\" value=\"". $_GET['login'] ."\" type=\"text\">" );
		$this->T_str_table( $this->lang['users_group'], "", "<select name=\"edit_group\" class=\"uniform\"><option value=\"\"></option>".$select_group."</select>" );
		$this->T_str_table( $this->lang['users_edit_do'], "", "<select name=\"edit_do\" class=\"uniform\"><option value=\"1\">".$this->lang['users_plus']."</option><option value=\"0\">".$this->lang['users_minus']."</option></select>" );
		$this->T_str_table( $this->lang['users_summa'], "", "<input name=\"edit_summa\" class=\"edit bk\" type=\"text\">" . $this->pay_api->bf_declOfNum( 10 ) );
		$this->T_str_table( $this->lang['users_comm'], "", "<input name=\"edit_comm\" class=\"edit bk\" type=\"text\" style=\"width: 100%\">" );

		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-green\" style=\"margin:7px;\" name=\"edit_btn\" type=\"submit\" value=\"".$this->lang['act']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		$content .= $this->foother();

		return $content;
		
	}
	
}
?>