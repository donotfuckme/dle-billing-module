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

if( !defined( 'DATALIFEENGINE' ) OR !LOGED_IN ) {
    die( "Hacking attempt!" );
}

if( $member_id['user_group']!=1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

define( 'BILLING_MODULE', TRUE );
define( 'MODULE_PATH', ENGINE_DIR . "/modules/billing" );
define( 'MODULE_DATA', ENGINE_DIR . "/data/billing" );

/* Need install */
if( !file_exists( MODULE_DATA . '/config.php' ) ) {
	
	require_once MODULE_PATH . '/helpers/install.php';
	
	die();
}

/* Helpers classes */
require_once ENGINE_DIR . '/classes/parse.class.php';
require_once MODULE_PATH . '/helpers/user.models.php';
require_once MODULE_PATH . '/helpers/adm.theme.php';
require_once MODULE_DATA . '/config.php';
require_once MODULE_PATH . '/lang/admin.php';
require_once MODULE_PATH . '/pay.api.php';

/* Pointer controller */
$parse = new ParseFilter( );

$c = ( $_GET['c'] ) ? $db->safesql( $parse->process( trim( $_GET['c'] ) ) ) : "main";
$m = ( $_GET['m'] ) ? $db->safesql( $parse->process( trim( $_GET['m'] ) ) ) : "main";
$p = ( $_GET['p'] ) ? $db->safesql( $parse->process( trim( $_GET['p'] ) ) ) : "";

/* Load controller - Core */
if( file_exists( MODULE_PATH."/controllers/adm.".$c.".php" ) )
	require_once MODULE_PATH . '/controllers/adm.'.$c.'.php';

/* Load controller - Plugins */	
elseif( file_exists( MODULE_PATH."/plugins/".$c."/adm.main.php" ) )
	require_once MODULE_PATH . '/plugins/'.$c.'/adm.main.php';
	
else
	die("Controller Error - 404");

	/* Load controller function */	
	$adm = new ADMIN;
	$adm->config = $billing_config;
	$adm->config_dle = $config;
	$adm->hash = $dle_login_hash;
	$adm->db = $db;
	$adm->member_id = $member_id;
	$adm->_TIME = $_TIME;
	$adm->lang = $billing_lang;
	$adm->pay_api = $pay_api;

	if( in_array($m, get_class_methods($adm) ) )
		echo $adm->$m( $p );
	else
		die("Method controller Error - 404");

/* Frees memory */
unset( $parse );
unset( $pay_api );
unset( $adm );
?>