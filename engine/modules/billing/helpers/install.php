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

require_once MODULE_PATH . '/lang/admin.php';
require_once MODULE_PATH . '/helpers/user.models.php';
require_once MODULE_PATH . '/helpers/adm.theme.php';

Class INSTALL extends ADMIN_THEME {

	var $db = false;
	var $member_id = false;
	var $lang = false;

	var $blank = array (
		'status' => "0",
		'page' => "billing",
		'currency' => "",
		'paging' => "10",
		'admin' => "",
		'secret' => "",
		'fname' => "user_balance",
		'start' => "log/main/1",
		'format' => "0.00",
		'version' => "0.5.5b"
	);
	
	function main() {
		global $_TIME;
	
		$this->blank['currency'] = $this->lang['currency'];
		$this->blank['admin'] = $this->member_id['name'];
		$this->blank['secret'] = $this->genCode();

		$this->config['version'] = $this->blank['version'];
	
		if( isset( $_POST['agree'] ) ) {
		
			// - config
			$this->save_file_array("config", $this->blank, "billing_config");
		
			// - htaccess
			$htaccess_set = "# billing
RewriteRule ^([^/]+).html/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2 [L]
RewriteRule ^([^/]+).html/([^/]*)/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2&m=$3 [L]
RewriteRule ^([^/]+).html/([^/]*)/([^/]*)/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2&m=$3&p=$4 [L]
RewriteRule ^([^/]+).html/([^/]*)/([^/]*)/([^/]*)/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2&m=$3&p=$4&key=$5 [L]";

			if( is_writable( ".htaccess" ) ) {
				
				if ( !strpos( file_get_contents(".htaccess"), "# billing" ) ) {
					$new_htaccess = fopen(".htaccess", "a");
					fwrite($new_htaccess, "\n".$htaccess_set);
					fclose($new_htaccess); 
				} 		
			
			} else {
				
				$this->lang['install_ok'] = $this->lang['install_bad'];
				$this->lang['install_next'] = "<div style=\"text-align: left\">".$this->lang['install_error']."<pre><code>".$htaccess_set."</code></pre></div>";
		
			}
		
			// - sql
			$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_history";
			$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_invoice";
			$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_refund";
		
			if( !isset( $this->member_id[$this->blank['fname']] ) ) 
				$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD {$this->blank['fname']} float NOT NULL";

			$_admin_sections = $this->db->super_query( "SELECT name FROM " . USERPREFIX . "_admin_sections WHERE name='billing'" );
			
			if( !isset( $_admin_sections['name'] ) ) 
				$tableSchema[] = "INSERT INTO `" . PREFIX . "_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('billing', '".$this->lang['title']."', '".$this->lang['desc']."', 'billing.png', '1')";
			
			$_static = $this->db->super_query( "SELECT name FROM " . USERPREFIX . "_static WHERE name='billing'" );
				
			if( !isset( $_static['name'] ) ) 
				$tableSchema[] = "INSERT INTO `" . PREFIX . "_static` (`name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`, `date`, `metatitle`, `allow_count`, `sitemap`, `disable_index`) VALUES ('billing', '".$this->lang['cabinet']."', 'billing/cabinet', 1, 1, 'all', 'billing', 'billing/cabinet', 'cabinet, billing', 0, '', ".$_TIME.", '', 1, 1, 0);";
			
			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_history` (
								  `history_id` int(11) NOT NULL AUTO_INCREMENT,
								  `history_plugin` varchar(100) NOT NULL,
								  `history_plugin_id` int(11) NOT NULL,
								  `history_user_name` varchar(100) NOT NULL,
								  `history_plus` text NOT NULL,
								  `history_minus` text NOT NULL,
								  `history_balance` text NOT NULL,
								  `history_currency` varchar(100) NOT NULL,
								  `history_text` text NOT NULL,
								  `history_date` int(11) NOT NULL,
								  PRIMARY KEY (`history_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";
	
			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_invoice` (
								  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
								  `invoice_paysys` varchar(100) NOT NULL,
								  `invoice_user_name` varchar(100) NOT NULL,
								  `invoice_get` text NOT NULL,
								  `invoice_pay` text NOT NULL,
								  `invoice_date_creat` int(11) NOT NULL,
								  `invoice_date_pay` int(11) NOT NULL,
								  PRIMARY KEY (`invoice_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";

			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_refund` (
								  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
								  `refund_date` int(11) NOT NULL,
								  `refund_user` varchar(100) NOT NULL,
								  `refund_summa` text NOT NULL,
								  `refund_commission` text NOT NULL,
								  `refund_requisites` text NOT NULL,
								  `refund_date_return` int(11) NOT NULL,
								  PRIMARY KEY (`refund_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";
								
			foreach($tableSchema as $table) {
				$this->db->super_query($table);
			}
	
			$this->T_msg( $this->lang['install_ok'], $this->lang['install_next'], $PHP_SELF . "?mod=billing" );
		}
	
		$content = $this->header();
		$content = $this->header_start( $this->lang['install'] );
		
		$content .= <<<HTML
<form action="{$PHP_SELF}" method="post">
	<div style="margin: 10px; height: 200px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;">
    {$this->lang['license']}
	</div>
HTML;
	$content .= <<<HTML
	<div class="row box-section">	
		<input class="btn btn-green" name="agree" type=submit value="{$this->lang['install_button']}">
	</div>
</form>
HTML;
		
		$content .= $this->header_end();
		$content .= $this->foother();

		return $content;
	
	}

	
}

$Install = new INSTALL;
$Install->lang = $billing_lang;
$Install->db = $db;
$Install->member_id = $member_id;

echo $Install->main();
?>