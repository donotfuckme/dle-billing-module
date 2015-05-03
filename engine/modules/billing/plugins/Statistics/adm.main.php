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

	/* Header menu */
	private function menu() {
		
		return <<<HTML
<div class="box">
  <div class="box-content">
	<div class="row box-section">
		<ul class="settingsb">
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=Statistics" class="tip" title="" data-original-title="{$this->lang['statistics_1']}"><i class="icon-desktop"></i><br />{$this->lang['statistics_1']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=Statistics&m=billings" class="tip" title="" data-original-title="{$this->lang['statistics_2']}"><i class="icon-bar-chart"></i><br />{$this->lang['statistics_2_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=Statistics&m=plugins" class="tip" title="" data-original-title="{$this->lang['statistics_3']}"><i class="icon-list"></i><br />{$this->lang['statistics_3_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=Statistics&m=users&p={$this->member_id['name']}" class="tip" title="" data-original-title="{$this->lang['statistics_4']}"><i class="icon-user"></i><br />{$this->lang['statistics_4_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=Statistics&m=clean" class="tip" title="" data-original-title="{$this->lang['statistics_5']}"><i class="icon-remove-circle"></i><br />{$this->lang['statistics_5']}</a></li>
		 <li style="min-width:90px; margin-left: 50px"><a href="{$PHP_SELF}?mod=billing" class="tip" title="" data-original-title="{$this->lang['statistics_6']}"><i class="icon-reply"></i><br />{$this->lang['statistics_6_title']}</a></li>
		</ul>
     </div>
   </div>
</div>
HTML;
		
	}
	
	function main() {

		$paysys = $this->paysys_array();
	
		$content = $this->header();
		$content .= $this->menu();
		 
		// - get db-
		$balance = $this->db->super_query( "SELECT count(name) as count, SUM({$this->config['fname']}) as summa FROM " . USERPREFIX . "_users where {$this->config['fname']}!='0'" );
		$refund = $this->db->super_query( "SELECT SUM(refund_summa) as summa, SUM(refund_commission) as commission FROM " . USERPREFIX . "_billing_refund where refund_date_return!='0'" );
		$refund_wait = $this->db->super_query( "SELECT SUM(refund_summa) as summa, SUM(refund_commission) as commission FROM " . USERPREFIX . "_billing_refund where refund_date_return='0'" );

		$invoice = $this->db->super_query( "SELECT SUM(invoice_get) as summa FROM " . USERPREFIX . "_billing_invoice where invoice_date_pay!='0'" );
		$invoice_wait = $this->db->super_query( "SELECT SUM(invoice_get) as summa FROM " . USERPREFIX . "_billing_invoice where invoice_date_pay='0'" );
		
		$content .= <<<HTML
<div class="row">
	<div class="col-md-12">
		
		<div class="box">
		
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#statauto" data-toggle="tab"><i class="icon-bar-chart"></i> {$this->lang['statistics_1']}</a></li>
				</ul>
			</div>
		
            <div class="box-content">
                 <div class="tab-content">
                   
                     <div class="tab-pane active" id="statauto" >
					 
						<table class="table table-normal">
							<tr>
								<td class="col-md-4 white-line">{$this->lang['statistics_main_1']}</td>
								<td class="col-md-8 white-line">{$this->pay_api->bf_convert( $balance['summa'] )}{$this->pay_api->bf_declOfNum( $balance['summa'] )}</td>
							</tr>
							<tr>
								<td>{$this->lang['statistics_main_2']}</td>
								<td>{$this->pay_api->bf_convert( $refund['summa'] )}{$this->pay_api->bf_declOfNum( $refund['summa'] )} | {$this->pay_api->bf_convert( $refund['commission'] )}{$this->pay_api->bf_declOfNum( $refund['commission'] )} - комиссия</td>
							</tr>
							<tr>
								<td>{$this->lang['statistics_main_3']}</td>
								<td>{$this->pay_api->bf_convert( $refund_wait['summa'] )} {$this->pay_api->bf_declOfNum( $refund_wait['summa'] )} | {$this->pay_api->bf_convert( $refund_wait['commission'] )}{$this->pay_api->bf_declOfNum( $refund_wait['commission'] )} - комиссия </td>
							</tr>
							<tr>
								<td></td>
								<td>[ <a class="status-info" href="{$PHP_SELF}?mod=billing&c=refund">{$this->lang['statistics_main_4']}</a> ]</td>
							</tr>
							<tr>
								<td>{$this->lang['statistics_main_5']}</td>
								<td>{$this->pay_api->bf_convert( $invoice['summa'] )}{$this->pay_api->bf_declOfNum( $invoice['summa'] )}</td>
							</tr>
							<tr>
								<td>{$this->lang['statistics_main_6']}</td>
								<td>{$this->pay_api->bf_convert( $invoice_wait['summa'] )}{$this->pay_api->bf_declOfNum( $invoice_wait['summa'] )}</td>
							</tr>
							<tr>
								<td></td>
								<td>[ <a class="status-info" href="{$PHP_SELF}?mod=billing&c=invoice">{$this->lang['statistics_main_7']}</a> ]</td>
							</tr>
							<tr>
								<td>{$this->lang['statistics_main_8']}</td>
								<td>{$balance['count']}</td>
							</tr>
							<tr>
								<td></td>
								<td>[ <a class="status-info" href="{$PHP_SELF}?mod=billing&c=users">{$this->lang['statistics_main_9']}</a> ]</td>
							</tr>
						</table>      
						
                     </div>
                 </div>
             </div>

	   </div>
	</div>

</div>

HTML;
					 
		$content .= $this->foother();
		
		return $content;
		
	}
	
	function clean() {

		/* Act */
		if( isset( $_POST['act'] ) ) {
		
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->hash ) {       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			// - plugins
			foreach( $_POST['clean_plugins'] as $plagin_name ) 
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_history WHERE history_plugin='".$this->db->safesql($plagin_name)."'" );
			
			// - invoice
			if( $_POST['clear_invoice'] == "all" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice" );
			elseif( $_POST['clear_invoice'] == "ok" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay!='0'" );
			elseif( $_POST['clear_invoice'] == "no" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay='0'" );
						
			// - refund
			if( $_POST['clear_refund'] == "all" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund" );
			elseif( $_POST['clear_refund'] == "ok" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_date_return!='0'" );
			elseif( $_POST['clear_refund'] == "no" )
				$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_date_return='0'" );
			
			// - balance
			if( $_POST['clear_balance'] )
				$this->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->config['fname']}='0'");
			
			$this->T_msg( $this->lang['ok'], $this->lang['statistics_clean_1_ok'] );
		}
	
		$paysys = $this->paysys_array();
		$plugins = $this->plugins_array();
		
		$content = $this->header();
		$content .= $this->menu();
		
		$content .= "<div class=\"well relative\"><span class=\"triangle-button red\"><i class=\"icon-warning-sign\"></i></span>{$this->lang['statistics_clean_info']}</div>";
		
		$content .= $this->header_start( $this->lang['statistics_5'] );

		/* Plugins */
		$plugins_select = "<div class=\"checkbox\">
									<label>
									  <input type=\"checkbox\" value=\"\" onclick=\"checkAll(this)\" /> {$this->lang['statistics_clean_2']}
									</label>
								</div>";
		
		$this->db->query( "SELECT history_plugin FROM " . USERPREFIX . "_billing_history GROUP BY history_plugin" );
		
		while ( $row = $this->db->get_row() ) {

			$title = $plugins[$row['history_plugin']]['title'] ? $plugins[$row['history_plugin']]['title']: $row['history_plugin'];
			$title = ( $title==$row['history_plugin'] and $this->lang[$row['history_plugin'].'_title'] ) ? $this->lang[$row['history_plugin'].'_title']: ucfirst( $title );
			
			$plugins_select .= "<div class=\"checkbox\">
									<label>
									  <input type=\"checkbox\" name=\"clean_plugins[]\" value=\"{$row['history_plugin']}\"> {$title}
									</label>
								</div>";
		}
		
		$this->T_str_table( $this->lang['statistics_clean_3'], $this->lang['statistics_clean_3d'], $plugins_select );
		$this->T_str_table( $this->lang['statistics_clean_4'], $this->lang['statistics_clean_4d'], $this->T_makeDropDown( array(''=>"",'all'=>$this->lang['statistics_clean_4_s1'], 'ok'=>$this->lang['statistics_clean_4_s2'], 'no'=>$this->lang['statistics_clean_4_s3'] ), "clear_invoice" ) );
		$this->T_str_table( $this->lang['statistics_clean_5'], $this->lang['statistics_clean_5d'], $this->T_makeDropDown( array(''=>"",'all'=>$this->lang['statistics_clean_4_s1'], 'ok'=>$this->lang['statistics_clean_5_s1'], 'no'=>$this->lang['statistics_clean_5_s2'] ), "clear_refund" ) );
		$this->T_str_table( $this->lang['statistics_clean_6'], $this->lang['statistics_clean_6d'], $this->T_makeDropDown( array(''=>"",'1'=>$this->lang['statistics_clean_6d_yep'] ), "clear_balance" ) );
																											
		$content .= $this->T_parse_str_table();
		$content .= $this->T_padded( "<input class=\"btn btn-gold\" style=\"margin:7px;\" name=\"act\" type=\"submit\" onclick=\"return confirm('{$this->lang['statistics_clean_7']}')\" value=\"".$this->lang['act']."\"><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" );			 
		$content .= $this->header_end();
		
		$content .= $this->foother();
		
		return $content;
		
	}
	
	function billings() {

		$paysys = $this->paysys_array();
	
		$content = $this->header();
		$content .= $this->menu();
		
		/* SQL | Pay */
		$this->db->query( "SELECT invoice_paysys, SUM(invoice_pay) FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay!='0' GROUP BY invoice_paysys" );
		
		$paysys_data = array();
		$js_paysys = "";
		$js_pay = "";
		$js_wait = "";
		$js_summ = "";
		
		$all_pay = 0;
		
		while ( $row = $this->db->get_row() ) {
		
			$paysys_data[$row['invoice_paysys']] = array();
			$paysys_data[$row['invoice_paysys']]['summ'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
			$paysys_data[$row['invoice_paysys']]['pay'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );

		}
		
			/* SQL | Wait */
			$this->db->query( "SELECT invoice_paysys, SUM(invoice_pay) FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay='0' GROUP BY invoice_paysys" );
		
			while ( $row = $this->db->get_row() ) {
			
				$paysys_data[$row['invoice_paysys']]['summ'] += $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
				$paysys_data[$row['invoice_paysys']]['wait'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
			
			}
		
			// - answer
			foreach( $paysys_data as $paysys_name => $paysys_info ) {
			
				$paysys_info['summ'] = $this->pay_api->bf_convert( $paysys_info['summ'] );
				$paysys_info['pay'] = $this->pay_api->bf_convert( $paysys_info['pay'] );
				$paysys_info['wait'] = $this->pay_api->bf_convert( $paysys_info['wait'] );
			
				$js_paysys .= "'{$paysys[$paysys_name]['title']}',";
				$js_pay .= "{$paysys_info['pay']},";
				$js_wait .= "{$paysys_info['wait']},";
				$js_summ .= "{$paysys_info['summ']},";

				$all_pay += $paysys_info['pay'];
				
			}
					
		/* All paysys */ 
		$diagrams_1 = $this->billings_diagram( $js_paysys, $js_pay, $js_wait, $js_summ );
		
		/* Percent paysys */ 
		$one_percent = $all_pay / 100;
		$js_percent = "";
		
		foreach( $paysys_data as $paysys_name => $paysys_info ) {
		
			$percent = $this->pay_api->bf_convert( ( $paysys_info['pay'] / $one_percent ) );
		
			$js_percent .= ( $js_percent ) ?
							"['{$paysys[$paysys_name]['title']}',   {$percent}]," 
							:
							"{
                        name: '{$paysys[$paysys_name]['title']}',
                        y: {$percent},
                        sliced: true,
                        selected: true
                    },";

		}
		
		$diagrams_2 = <<<HTML
<script>	
$(function () {

    $(document).ready(function () {

        // Build the chart
        $('#container_2').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '{$this->lang['statistics_billings_1']}'
            },
			subtitle: {
				text: '{$this->lang['statistics_billings_2']}'
			},
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: 'Доля',
                data: [
                    {$js_percent}
                ]
            }]
        });
    });

});
</script>
	
<div id="container_2" style="min-width: 310px; height: 100%; max-width: 100%; margin: 0 auto"></div>	
HTML;
		
		$content .= "<div class=\"well relative\"><span class=\"triangle-button green\"><i class=\"icon-info-sign\"></i></span>{$this->lang['statistics_billings_info']}</div>";
				
		$content .= "<table width=\"100%\">
						<tr>
							<td width=\"68%\" valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$diagrams_1}</div></td>
							<td width=\"2%\"></td>
							<td valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$diagrams_2}</div></td>
						</tr>
					 </table>";
		
		$content .= $this->foother();
		
		return $content;
	
	}
	
	function users( $user ) {
		global $user_group;
	
		$paysys = $this->paysys_array();
	
		if( iconv_strlen( $user ) < 3 ) $user = "";
			
		$content = $this->header();
		$content .= $this->menu();
		
		$result =  array();
		
		/* Search */
		if( isset( $_POST['search_btn'] ) OR $user ) {
			
			$user = ( $_POST['search_user'] ) ? $_POST['search_user'] : $user;
			
			$this->db_where( array( "name LIKE '{s}' or email LIKE '{s}' " => $user  ) );
			
			$result = $this->db_search_users( 1 );
		}

		$content .= "<table width=\"100%\">
						<tr>
							<td colspan=\"3\"><div class=\"well relative\" style=\"white-space: nowrap; text-align: center\"><span class=\"triangle-button green\"><i class=\"icon-user\"></i></span>
												<form action=\"\" method=\"post\" name=\"frm_billing\">
													<input type=\"text\" style=\"width: 90%\" name=\"search_user\" placeholder=\"{$this->lang['statistics_users_1']}\" value=\"{$user}\"> 
													<button type=\"submit\" name=\"search_btn\" class=\"btn btn-blue\"><i class=\"icon-search\"></i></button>
												</form>
											  </div>
							</td>
						</tr>";

		/* Result */
		if( !$result[0]['user_id'] )
			return $content . 	"<tr>
									<td colspan=\"3\">
										<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$this->lang['statistics_users_error']}</div>
									</td>
								</tr></table>".$this->foother();
		
		/* User info */
		// - foto
	        if ( count(explode("@", $result[0]['foto'])) == 2 )
                $result[0]['foto'] = 'http://www.gravatar.com/avatar/' . md5(trim($result[0]['foto'])) . '?s=150';
            elseif( $result[0]['foto'] and ( file_exists( ROOT_DIR . "/uploads/fotos/" . $result[0]['foto'] )) ) 
				$result[0]['foto'] = $config['http_home_url'] . "uploads/fotos/" . $result[0]['foto'];
            else
				$result[0]['foto'] = "{$this->config_dle['http_home_url']}templates/{$this->config_dle['skin']}/dleimages/noavatar.png";

		// - group
		if( $result[0]['banned'] == 'yes' )
			$user_group[$result[0]['user_group']]['group_name'] = $this->lang['statistics_users_2'];
		if( $user_group[$result[0]['user_group']]['time_limit'] )
			if( $result[0]['time_limit'] )
				$user_group[$result[0]['user_group']]['group_name'] .= "<br /> ".$this->lang['statistics_users_21'] . langdate( "j F Y H:i", $result[0]['time_limit'] );
			else
				$user_group[$result[0]['user_group']]['group_name'] .= $this->lang['statistics_users_22'];
			
		// - dates
		$date_reg = langdate( "j F Y  G:i", $result[0]['reg_date'] );
		$date_lats = langdate( "j F Y  G:i", $result[0]['lastdate'] );
			
		$user_info = <<<HTML
		<p>
			<div class="form-group"><center><img src="{$result[0]['foto']}"></center></div>
		</p>
		<p>
			<div class="form-group"><center>{$this->T_user( $result[0]['name'] )}</center></div>
		</p>
		<p>
			<table class="table table-bordered">
			  <tr>
				<td>{$this->lang['statistics_users_3']}</td>
				<td>{$this->pay_api->bf_convert( $result[0][$this->config['fname']] )} {$this->pay_api->bf_declOfNum( $result[0][$this->config['fname']] )}</td>
			  </tr>
			  <tr>
				<td>{$this->lang['statistics_users_4']}</td>
				<td>{$result[0]['fullname']}</td>
			  </tr>
			  <tr>
				<td>{$this->lang['statistics_users_5']}</td>
				<td>{$result[0]['email']}</td>
			  </tr>
			  <tr>
				<td>{$this->lang['statistics_users_6']}</td>
				<td>{$user_group[$result[0]['user_group']]['group_name']}</td>
			  </tr>
			   <tr>
				<td>{$this->lang['statistics_users_7']}</td>
				<td>{$date_reg}</td>
			  </tr>
			  <tr>
				<td>{$this->lang['statistics_users_8']}</td>
				<td>{$date_lats}</td>
			  </tr>
			</table>
		</p>
		
		<p style="text-align: center">
			<a href="{$this->config_dle['http_home_url']}index.php?do=pm&doaction=newpm&user={$result[0]['user_id']}" target="_blank"><i class="icon-comments" style="margin-left: 10px; margin-right: 5px; vertical-align: middle"></i> {$this->lang['statistics_users_9']}</a>
			<a href="{$this->config_dle['http_home_url']}index.php?do=feedback&user={$result[0]['user_id']}" target="_blank"><i class="icon-share-alt" style="margin-left: 10px; margin-right: 5px; vertical-align: middle"></i> {$this->lang['statistics_users_10']}</a>
		</p>
		
	</span>
HTML;
		
		$js_prugins = "";
		$js_plus = "";
		$js_minus = "";
		$js_difference = "";
		
		$params_plugins = "";

		/* SQL */
		$search_array = array(
					"history_plugin!='pay' " => 1,
					"history_user_name='{s}' " => $user
		);

		/* Diagrams */
		$this->db_where( $search_array );

		$this->db->query( "SELECT history_plugin, SUM(history_minus), SUM(history_plus) FROM " . USERPREFIX . "_billing_history {$this->where} GROUP BY history_plugin" );
		
		while ( $row = $this->db->get_row() ) {

			$title = ( $plugins[$row['history_plugin']]['title'] ) ? $plugins[$row['history_plugin']]['title']: $row['history_plugin'];
			$title = ( $title==$row['history_plugin'] and $this->lang[strtolower( $row['history_plugin'] ).'_title'] ) ? $this->lang[strtolower( $row['history_plugin'] ).'_title']: $title;
		
			$row['SUM(history_plus)'] = $this->pay_api->bf_convert( $row['SUM(history_plus)'] );
			$row['SUM(history_minus)'] = $this->pay_api->bf_convert( $row['SUM(history_minus)'] );
			$row['difference'] = $this->pay_api->bf_convert( ($row['SUM(history_plus)']-$row['SUM(history_minus)']) );
		
			$js_prugins .= "'$title',";
			$js_plus .= "{$row['SUM(history_plus)']},";
			$js_minus .= "{$row['SUM(history_minus)']},";
			$js_difference .= "{$row['difference']},";

		}
		
		$diagrams = $this->plugins_diagram( $js_prugins, $js_plus, $js_minus, $js_difference, $this->lang['statistics_users_11'] );
		
		/* Billings */
		/* SQL | Pay */
		$search_array = array(
					"invoice_date_pay!='0' " => 1,
					"invoice_user_name='{s}' " => $user
		);

		/* Diagrams */
		$this->db_where( $search_array );
		
		$this->db->query( "SELECT invoice_paysys, SUM(invoice_pay) FROM " . USERPREFIX . "_billing_invoice {$this->where} GROUP BY invoice_paysys" );
		
		$paysys_data = array();
		$js_paysys = "";
		$js_pay = "";
		$js_wait = "";
		$js_summ = "";
		
		$all_pay = 0;
		
		while ( $row = $this->db->get_row() ) {
		
			$paysys_data[$row['invoice_paysys']] = array();
			$paysys_data[$row['invoice_paysys']]['summ'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
			$paysys_data[$row['invoice_paysys']]['pay'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );

		}
		
			/* SQL | Wait */
			$search_array = array(
						"invoice_date_pay='0' " => 1,
						"invoice_user_name='{s}' " => $user
			);

			/* Diagrams */
			$this->db_where( $search_array );
		
			$this->db->query( "SELECT invoice_paysys, SUM(invoice_pay) FROM " . USERPREFIX . "_billing_invoice {$this->where} GROUP BY invoice_paysys" );
		
			while ( $row = $this->db->get_row() ) {
			
				$paysys_data[$row['invoice_paysys']]['summ'] += $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
				$paysys_data[$row['invoice_paysys']]['wait'] = $this->pay_api->bf_convert( $row['SUM(invoice_pay)'] );
			
			}
		
			// - answer
			foreach( $paysys_data as $paysys_name => $paysys_info ) {
			
				$paysys_info['summ'] = $this->pay_api->bf_convert( $paysys_info['summ'] );
				$paysys_info['pay'] = $this->pay_api->bf_convert( $paysys_info['pay'] );
				$paysys_info['wait'] = $this->pay_api->bf_convert( $paysys_info['wait'] );
			
				$js_paysys .= "'{$paysys[$paysys_name]['title']}',";
				$js_pay .= "{$paysys_info['pay']},";
				$js_wait .= "{$paysys_info['wait']},";
				$js_summ .= "{$paysys_info['summ']},";

				$all_pay += $paysys_info['pay'];
				
			}
					
		/* All paysys */ 
		$diagrams_billing = $this->billings_diagram( $js_paysys, $js_pay, $js_wait, $js_summ );
		
		/* View */
		$content .= "	<tr>
							<td width=\"70%\" valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$diagrams}</div><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$diagrams_billing}</div></td>
							<td width=\"2%\"></td>
							<td valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px\">{$user_info}</div></td>
						</tr>
					 </table>";
					 
		$content .= $this->foother();
		
		return $content;
		
	}
	
	function plugins() {

		$plugins = $this->plugins_array();
		
		$content = $this->header();
		$content .= $this->menu();

		$js_prugins = "";
		$js_plus = "";
		$js_minus = "";
		$js_difference = "";
		
		$params_plugins = "";

		/* SQL */
		$search_array = array(
					"history_plugin!='pay' " => 1,
					"history_date>='{s}' " => strtotime( $_POST['date_start'] ),
					"history_date<='{s}' " => strtotime( $_POST['date_end'] )
		);
		
		$this->db_where( $search_array );

		$this->db->query( "SELECT history_plugin, SUM(history_minus), SUM(history_plus) FROM " . USERPREFIX . "_billing_history {$this->where} GROUP BY history_plugin" );
		
		while ( $row = $this->db->get_row() ) {

			$title = ( $plugins[$row['history_plugin']]['title'] ) ? $plugins[$row['history_plugin']]['title']: $row['history_plugin'];
			$title = ( $title==$row['history_plugin'] and $this->lang[strtolower( $row['history_plugin'] ).'_title'] ) ? $this->lang[strtolower( $row['history_plugin'] ).'_title']: $title;
		
			$row['SUM(history_plus)'] = $this->pay_api->bf_convert( $row['SUM(history_plus)'] );
			$row['SUM(history_minus)'] = $this->pay_api->bf_convert( $row['SUM(history_minus)'] );
			$row['difference'] = $this->pay_api->bf_convert( ($row['SUM(history_minus)']-$row['SUM(history_plus)']) );
		
			if( (isset( $_POST['digrams_btn'] ) and in_array($row['history_plugin'], $_POST['plugins_in'])) or !isset( $_POST['digrams_btn'] ) or !count($_POST['plugins_in']) ) {
			
				$js_prugins .= "'$title',";
				$js_plus .= "{$row['SUM(history_plus)']},";
				$js_minus .= "{$row['SUM(history_minus)']},";
				$js_difference .= "{$row['difference']},";
		
			}
	
			$plugin_checked = ( isset( $_POST['digrams_btn'] ) and !in_array($row['history_plugin'], $_POST['plugins_in']) and count($_POST['plugins_in']) ) ? "": "checked";

			$params_plugins .= "<div class=\"checkbox\">
									<label>
									  <input type=\"checkbox\" name=\"plugins_in[]\" value=\"{$row['history_plugin']}\" {$plugin_checked}> {$title}
									</label>
								</div>";
	
		}

		if( !$params_plugins ) $params_plugins = $this->lang['statistics_plugins_1'];
		
		/* Panel params */
		$params = <<<HTML
<form action="" method="post" name="frm_billing">

	<span style="color:#333333;font-size:18px">{$this->lang['statistics_plugins_2']}</span>
		<p>
			<div class="form-group">
						<label>{$this->lang['statistics_plugins_3']}</label>
						<input data-rel="calendar" type="text" name="date_start" value="{$_POST['date_start']}" class="edit bk" style="width: 100%">
			</div>
		</p>
		<p>
			<div class="form-group">
						<label>{$this->lang['statistics_plugins_4']}</label>
						<input data-rel="calendar" type="text" name="date_end" value="{$_POST['date_end']}" class="edit bk" style="width: 100%">
			</div>
		</p>
		<p>
			<label>{$this->lang['statistics_plugins_5']}</label>
			{$params_plugins}
		</p>
		<p>
			<input class="btn btn-green" style="margin:7px;" name="digrams_btn" type="submit" value="{$this->lang['statistics_plugins_6']}"> <a href="{$PHP_SELF}?mod=billing&c=Statistics&m=plugins" class="btn btn-blue" style="margin:7px;">{$this->lang['statistics_plugins_7']}</a>
		</p>
		
</form>
HTML;
		
		
		$diagrams = $this->plugins_diagram( $js_prugins, $js_plus, $js_minus, $js_difference, $this->lang['statistics_plugins_8'] );

		$content .= "<div class=\"well relative\"><span class=\"triangle-button green\"><i class=\"icon-info-sign\"></i></span>{$this->lang['statistics_plugins_info']}</div>";
		
		$content .= "<table width=\"100%\">
						<tr>
							<td width=\"70%\" valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$diagrams}</div></td>
							<td width=\"2%\"></td>
							<td valign=\"top\"><div id=\"general\" class=\"box\" style=\"padding: 10px; background: #fff\">{$params}</div></td>
						</tr>
					 </table>";
					 
		$content .= $this->foother();
		
		return $content;
	
	}
	
	private function billings_diagram( $js_paysys, $js_pay, $js_wait, $js_summ ) {
		
		return <<<HTML
<script>
$(function () {
    $('#container_3').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '{$this->lang['statistics_bdiagram_1']}'
        },
        xAxis: {
            categories: [
                {$js_paysys}
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '{$this->lang['statistics_bdiagram_2']}'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '{$this->lang['statistics_bdiagram_3']}',
            data: [{$js_pay}]

        }, {
            name: '{$this->lang['statistics_bdiagram_4']}',
            data: [{$js_wait}]

        }, {
            name: '{$this->lang['statistics_bdiagram_5']}',
            data: [{$js_summ}]

        }]
    });
});
</script>
		
<div id="container_3" style="min-width: 310px; height: 100%; max-width: 100%; margin: 0 auto"></div>		
HTML;
		
	}
		
	private function plugins_diagram( $js_prugins, $js_plus, $js_minus, $js_difference, $desc ) {
		
		$diagrams_height = intval( count( explode(",", $js_prugins) ) ) * 70;  if( $diagrams_height<300 ) $diagrams_height = 300; 
		
		return <<<HTML
<script>
$(function () {
    $('#container').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: '{$this->lang['statistics_pdiagram_1']}'
        },
        xAxis: {
            categories: [{$js_prugins}],
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: '{$desc}',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: '{$this->lang['statistics_pdiagram_2']}',
            data: [{$js_plus}]
        }, {
            name: '{$this->lang['statistics_pdiagram_3']}',
            data: [{$js_minus}]
        }, {
            name: '{$this->lang['statistics_pdiagram_4']}',
            data: [{$js_difference}]
        }]
    });
});
</script>
		
<div id="container" style="min-width: 310px; max-width: 100%; height: {$diagrams_height}px; margin: 0 auto"></div>

HTML;
		
	}
	
}
?>