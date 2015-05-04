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

Class ADMIN_THEME extends MODEL {

	var $config = false;
	var $lang = false;
	var $config_dle = false;
	var $pay_api = false;
	var $hash = false;
	var $member_id = false;
	var $_TIME = false;

	var $section_num = 0;
	var $section = array();

	var $list_table_num = 0;
	var $list_table = array();
	
	var $str_table_num = 0;
	var $str_table = array();
	
	function genCode( $length = 8 ) {
		
	  $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
	  $numChars = strlen($chars);
	  $string = '';
	  
	  for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
	  }
	  
	  return $string;
	}
	
	function T_makeCheckBox($name, $selected) {
	
		$selected = $selected ? "checked" : "";
		return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
		
	}

    function T_makeDropDown($options, $name, $selected) {
        $output = "<select class=\"uniform\" style=\"min-width:100px;\"  name=\"$name\">\r\n";
        foreach ( $options as $value => $description ) {
            $output .= "<option value=\"$value\"";
            if( $selected == $value ) {
                $output .= " selected ";
            }
            $output .= ">$description</option>\n";
        }
        $output .= "</select>";

        return $output;
    }
	
	function T_msg( $title, $desc, $link = 'javascript:history.back();' ) {
	
		msg( "info", $title, $desc, $link );
		
	}
	
	function save_file_array($file, $save_con, $title_arr ) {

		$handler = fopen( MODULE_DATA . '/'.$file.'.php', "w" );

		fwrite( $handler, "<?PHP \n\n//Settings \n\n\${$title_arr} = array (\n\n" );
		
		foreach ( $save_con as $name => $value ) {

				$value = str_replace( "{", "&#123;", $value );
				$value = str_replace( "}", "&#125;", $value );
				
				$value = htmlspecialchars( $value );
				
				$name = str_replace( "$", "&#036;", $name );
				$name = str_replace( "{", "&#123;", $name );
				$name = str_replace( "}", "&#125;", $name );

			fwrite( $handler, "'{$name}' => \"{$value}\",\n\n" );
		
		}

		fwrite( $handler, ");\n\n?>" );
		fclose( $handler );
		
		return true;
	}
	
	function T_parse_str_table() {
	
		if( !$this->str_table_num ) return FALSE;
	
		$answer = "<table width=\"100%\" class=\"table table-normal\">";
	
		for( $i = 1; $i <= $this->str_table_num; $i++ ) {
		
			$answer .= "<tr>
							<td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>" . $this->str_table[$i]['title'] . "</h6><span class=\"note large\">" . $this->str_table[$i]['desc'] . "</span></td>
							<td class=\"col-xs-2 col-md-5 settingstd\">" . $this->str_table[$i]['field'] . "</td>
						</tr>";
		
		}
	
		$this->str_table = array();
		$this->str_table_num = 0;
	
		$answer .= "</table>";
	
		return $answer;
	}
	
	function T_pars_list( $id = '', $other_tr = '' ) {

		$answer = "<table width=\"100%\" class=\"table table-normal table-hover\" ".( ( $id ) ? 'id="'.$id.'"':'' ).">";
		
		for( $i = 1; $i <= $this->list_table_num; $i++ ) {
		
			$answer .= "<tr>";
			if( $i == 1 ) $answer .= "<thead>";
			
			foreach( $this->list_table[$i] as $width=>$td )	$answer .= ( $i==1 ) ? $td: "<td>" . $td . "</td>";
			
			if( $i == 1 ) $answer .= "</thead>";
			$answer .= "</tr>";
		
		}
		
		if( !$this->list_table_num ) return FALSE;
		
		$answer .= $other_tr;
		$answer .= "</table>";
		
		return $answer;
	}
	
	function T_set_list( $array ) {

		$this->list_table_num ++;
	
		$this->list_table[$this->list_table_num] = $array;
	 
		return TRUE;
	}
	
	function T_str_table($title, $desc, $field) {

		$this->str_table_num++;
	
		$this->str_table[$this->str_table_num] = array(
									'title' => $title, 
									'desc' => $desc, 
									'field' => $field
								);
	 
	}
	
	function T_parse_section( $block = '' ) {
	
		if( !$this->section_num ) return FALSE;
	
		$more = false;

		for( $i = 1; $i <= $this->section_num; $i++ ) {

			if( $i>2 and !$more ) {
				$answer .= "</div>
								<div onClick=\"ShowOrHide('more_".$block."')\" class=\"bt_more\">". $this->lang['more'] ."</div>
									
										<div id=\"more_".$block."\" style=\"display:none;\">
											<div class=\"row box-section\">";
				$more = true;
			}
			
			if( $i%2!=0 ) $answer .= "<div class=\"row box-section\">";

			$answer .= "<div class=\"col-md-6\">
						  <div class=\"news with-icons\">
								<div class=\"avatar\"><img src=\"". $this->section[$i]['icon'] ."\"></div>
								<div class=\"news-content\">
									<div class=\"news-title\"><a href=\"". $this->section[$i]['link'] ."\">". $this->section[$i]['title'] ."</a></div>
									<div class=\"news-text\">
									  <a href=\"". $this->section[$i]['link'] ."\">". $this->section[$i]['desc'] ."</a>
									</div>
								</div>
						  </div>
						</div>";

			if( $i%2==0 OR $i == $this->section_num ) $answer .= "</div>";
	
		}
	
		if( $more ) $answer .= "</div>";
		
		

		$this->section_num = 0;
		$this->section = array();
	
		return $answer;
	}
	
	function T_padded( $text, $box = 'box-footer', $position = 'center' ) {
		
		return "<div class=\"". $box ." padded\" style=\"text-align: ". $position ."\">". $text ."</div>";
		
	}

	function T_section( $title, $desc, $icon, $link ) {
	
		$this->section_num++;
	
		$this->section[$this->section_num] = array(
									'title' => $title, 
									'desc' => $desc, 
									'icon' => $icon, 
									'link' => $link
								);
	
		return TRUE;
	}
	
	function T_plugins() {

		foreach( $this->plugins_array() as $name => $config ) {
			
			$title = ( $config['title'] ) ? $config['title']: $name;
			$title = ( $title==$name and $this->lang[strtolower($name).'_title'] ) ? $this->lang[strtolower($name).'_title']: ucfirst( $title );
	
			$this->T_section( $title, $this->lang['go_plugin'] . "&laquo;" . $title ."&raquo;", "engine/modules/billing/plugins/". $name ."/icon/icon.png", $PHP_SELF."?mod=billing&c=".$name );
			
		}
	
		if( !$this->section_num )
			return "<div style=\"text-align: center; padding: 30px\">".$this->lang['no_plugin']."</div>";
	
		return $this->T_parse_section( "plugins" );
	}
	
	function plugins_array() {
		
		$plugins = array();
		
		$load_list = opendir( MODULE_PATH . "/plugins/" );
	
		while ( $name = readdir($load_list) ) {
		
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;
		
			/* Config */
			if( file_exists( MODULE_DATA."/plugin." . $name . ".php" ) )
				require_once MODULE_DATA."/plugin." . $name . ".php";
			else
				$plugin_config = array();
		
			$plugins[$name] = $plugin_config;
		
		}
		
		return $plugins;
	}	
	function paysys_array() {
		
		$paysys = array();
		
		$load_list = opendir( MODULE_PATH . "/paysys/" );
	
		while ( $name = readdir($load_list) ) {
		
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;
		
			/* Config */
			if( file_exists( MODULE_DATA."/pasys." . $name . ".php" ) )
				require_once MODULE_DATA."/pasys." . $name . ".php";
			else
				$paysys_config = array();
		
			$paysys[$name] = $paysys_config;
			$paysys[$name]['name'] = $name;
			
			if( !$paysys[$name]['title'] ) $paysys[$name]['title'] = $name;
		
		}
		
		return $paysys;
	}
	
	function T_paysys() {

		foreach( $this->paysys_array() as $name => $config ) {
			
			$title = ( $config['title'] ) ? $config['title']: $name;
			
			$status = ( $config['status'] ) ? "<br /><font color=\"green\">".$this->lang['on']."</font>": "<br /><font color=\"red\">".$this->lang['off']."</font>";
			
			$this->T_section( $title, $this->lang['go_paysys'] . "&laquo;" . $title ."&raquo;" .  $status, "engine/modules/billing/paysys/". $name ."/icon/icon.png", $PHP_SELF."?mod=billing&c=paysys&p=".$name );
			
		}
	
		if( !$this->section_num )
			return "<div style=\"text-align: center; padding: 30px\">".$this->lang['no_paysys']."</div>";
	
		return $this->T_parse_section( "paysys" );
	}
	
	function T_user( $login ) {
		
		return "<div class=\"btn-group\">
	
					<a href=\"". $this->config_dle['http_home_url'] ."user/".urldecode( $login )."\" target=\"_blank\"><i class=\"icon-user\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i></a>
	
					<a href=\"#\" target=\"_blank\" data-toggle=\"dropdown\" data-original-title=\"". $this->lang['history_user'] ."\" class=\"status-info tip\"><b>{$login}</b></a>
					
								  <ul class=\"dropdown-menu text-left\">
								   <li><a href=\"". $this->config_dle['http_home_url'] ."user/".urldecode( $login )."\" target=\"_blank\"><i class=\"icon-user\"></i> ". $this->lang['user_profily'] ."</a></li>
								   <li class=\"divider\"></li>
								   <li><a href=\"". $PHP_SELF ."?mod=billing&c=Statistics&m=users&p=".urldecode( $login )."\"><i class=\"icon-bar-chart\"></i> ". $this->lang['user_stats'] ."</a></li>
								   <li><a href=\"". $PHP_SELF ."?mod=billing&c=History&p=".urldecode( $login )."\"><i class=\"icon-money\"></i> ". $this->lang['user_history'] ."</a></li>
								   <li><a href=\"". $PHP_SELF ."?mod=billing&c=Refund&p=".urldecode( $login )."\"><i class=\"icon-credit-card\"></i> ". $this->lang['user_refund'] ."</a></li>
								   <li><a href=\"". $PHP_SELF ."?mod=billing&c=Invoice&p=".urldecode( $login )."\"><i class=\"icon-folder-open-alt\"></i> ". $this->lang['user_invoice'] ."</a></li>
								   <li class=\"divider\"></li>
								   <li><a href=\"". $PHP_SELF ."?mod=billing&c=Users&login=".urldecode( $login )."\"><i class=\"icon-edit\"></i> ". $this->lang['user_balance'] ."</a></li>
								 </ul>
				</div>";
		
	}	
	
	function T_billing( $info = array() ) {
		
		if( !$info['title'] ) return false;
		
		$status = ( $info['status'] ) ? "<a style=\"cursor: default; color: green\"> ". $this->lang['pay_status_on'] ."</a>": "<a style=\"cursor: default; color: red\"> ". $this->lang['pay_status_off'] ."</a>";
		
		return "<div class=\"btn-group\">
	
					".( $info['status'] ? "<span class=\"status-success\"><i class=\"icon-info-sign\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i></span>" : "<i class=\"icon-info-sign\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i>" )."
	
					<a href=\"#\" target=\"_blank\" data-toggle=\"dropdown\" data-original-title=\"". $this->lang['pay_name'] ."\" class=\"status-info tip\"><b>{$info['title']}</b></a>
					
								  <ul class=\"dropdown-menu text-left\">
								   <li>{$status}</li>
								   <li><a style=\"cursor: default\"> 1 ".$this->pay_api->bf_declOfNum( 1 )." = ".$info['convert']." ".$info['currency']."</a></li>
								 </ul>
				</div>";
		
	}
	
	function header() {

		echoheader( $this->lang['title'] . " v." . $this->config['version'], $this->lang['desc'] );

			echo "<link href=\"engine/modules/billing/theme/styles.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />";
		
			echo '<script src="engine/modules/billing/theme/highcharts.js"></script>
				  <script src="engine/modules/billing/theme/exporting.js"></script>';
		
			echo '<script type="text/javascript">
					function checkAll(obj) {
					  var items = obj.form.getElementsByTagName("input"), 
						  len, i;
					  for (i = 0, len = items.length; i < len; i += 1) {
						if (items.item(i).type && items.item(i).type === "checkbox") {          
						  if (obj.checked) {
							items.item(i).checked = true;
						  } else {
							items.item(i).checked = false;
						  }       
						}
					  }
					}

					function selectText(){
					  var oTextBox = document.getElementById("someTextField");
					  oTextBox.focus();
					  oTextBox.select();
					}
					</script>';
		
		return "";
	
	}

	function header_start( $title, $toolbar = '' ) {
	
		return "<div id=\"general\" class=\"box\">
					<div class=\"box-header\">
						<div class=\"title\">{$title}</div>
						<ul class=\"box-toolbar\">
						  <li class=\"toolbar-link\">
							{$toolbar}
						  </li>
						</ul>
					</div>
					
					<div class=\"box-content\">

						<form action=\"\" enctype=\"multipart/form-data\" method=\"post\" name=\"frm_billing\" >";

	}
	
	function header_end() {
	
		SetCookie("bNewsDate", $this->_TIME, $this->_TIME+( 24*60*60 ));
	
		return "		</form>
					</div>
				</div>";

	}
	
	function foother() {
	
		return "<p style=\"text-align:center;\">
					[ <a href=\"http://dle-billing.ru/\">".$this->lang['support']."</a> ]
					<br />
					&copy 2012 <a href=\"mailto:evgeny.tc@gmail.com\">mr_Evgen</a>
				</p>";
		
		echofooter();
	
	}
	
}
?>