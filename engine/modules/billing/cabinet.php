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

define( 'BILLING_MODULE', TRUE );
define( 'MODULE_PATH', ENGINE_DIR . "/modules/billing" );
define( 'MODULE_DATA', ENGINE_DIR . "/data/billing" );

/* Helpers classes */
require_once ENGINE_DIR . '/classes/parse.class.php';
require_once MODULE_PATH . '/helpers/user.models.php';
require_once MODULE_PATH . '/helpers/user.theme.php';
require_once MODULE_PATH . '/lang/cabinet.php';

require_once MODULE_DATA . '/mail.php';

/* Install */
if( !file_exists( MODULE_DATA . '/config.php' ) ) {
	
	header( 'Refresh: 0; url='.$config['http_home_url'] );
	
	die();
}

require_once MODULE_PATH . '/pay.api.php';

/* Pointer controller */
$parse = new ParseFilter( );

$start = explode("/", $billing_config['start'] );

$c = ( $_GET['c'] ) ? $db->safesql( $parse->process( trim( $_GET['c'] ) ) ) : $start[0];
$m = ( $_GET['m'] ) ? $db->safesql( $parse->process( trim( $_GET['m'] ) ) ) : $start[1];
$p = ( $_GET['p'] ) ? $db->safesql( $parse->process( trim( $_GET['p'] ) ) ) : $start[2];

/* OFF */
if( !$billing_config['status'] and $member_id['user_group']!=1 )

	echo $billing_lang['cabinet_off'];
	
else {

	/* Load controller - Core */
	if( file_exists( MODULE_PATH."/controllers/user.".$c.".php" ) )
		require_once MODULE_PATH . '/controllers/user.'.$c.'.php';

	/* Load controller - Plugins */	
	elseif( file_exists( MODULE_PATH."/plugins/".$c."/user.main.php" ) )
		require_once MODULE_PATH . '/plugins/'.$c.'/user.main.php';
		
	else 
		echo str_replace("{c}", $c, $billing_lang['cabinet_controller_error']);

}
	
/* Load controller function */	
if( class_exists("USER") ) {

	$user = new USER;
	$user->config = $billing_config;
	$user->config_mail = $billing_mail;
	$user->config_dle = $config;
	$user->db = $db;
	$user->member_id = $member_id;
	$user->pay_api = $pay_api;
	$user->_TIME = $_TIME;
	$user->lang = $billing_lang;
	$user->member_id[$billing_config['fname']] = $pay_api->bf_convert( $user->member_id[$billing_config['fname']] );
 
	if( in_array($m, get_class_methods($user) ) ) 
		echo $user->$m( $p );
	else
		echo str_replace("{c}", $c, str_replace("{m}", $m, $billing_lang['cabinet_metod_error']) );

}
		
/* Frees memory */
unset( $parse );
unset( $pay_api );
unset( $user );
?>