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

$login = $db->safesql( $login );

include MODULE_DATA . '/config.php';

if( ( $login == $member_id['name'] and $member_id['name'] ) OR !$login and $member_id['name'] )
	echo ( $member_id[$billing_config['fname']] ) ? $member_id[$billing_config['fname']] : 0;
else if ( $login ) {

	$search = $db->super_query( "SELECT ".$billing_config['fname']." FROM " . USERPREFIX . "_users WHERE name='$login'" );

	echo ( $search[$billing_config['fname']] ) ? $search[$billing_config['fname']] : 0;
}

?>