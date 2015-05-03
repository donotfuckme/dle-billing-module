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

Class PAYSYS extends ADMIN_THEME {

	function settings( $name, $config_dle, $config, $paysys_config, $hash ) {

		$this->T_str_table("Идентификатор магазина:", "Ваш идентификатор в системе Робокасса.", "<input name=\"save_con[login]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['login'] ."\">" );
		$this->T_str_table("Пароль #1:", "Используется интерфейсом инициализации оплаты.", "<input name=\"save_con[pass1]\" class=\"edit bk\" type=\"password\" value=\"" . $paysys_config['pass1'] ."\">" );
		$this->T_str_table("Пароль #2:", "Используется интерфейсом оповещения о платеже, XML-интерфейсах.", "<input name=\"save_con[pass2]\" class=\"edit bk\" type=\"password\" value=\"" . $paysys_config['pass2'] ."\">" );

		return $this->T_parse_str_table();
		
	}

	function form( $id, $config, $invoice, $currency, $desc = '' ) {
	
		$sign_hash = md5("$config[login]:$invoice[invoice_pay]:$id:$config[pass1]");
		
		if( !$desc ) $desc = 'Пополнение баланса пользователем '.$invoice['invoice_user_name'].' на сумму '.$invoice['invoice_get'].' '.$currency;
	
		# http://test.robokassa.ru/Index.aspx
		# https://merchant.roboxchange.com/Index.aspx
	
		return '
			<form method="post" id="paysys_form" action="http://test.robokassa.ru/Index.aspx">
				
				<input type=hidden name=MerchantLogin value="'.$config['login'].'">
				<input type=hidden name=OutSum value="'.$invoice['invoice_pay'].'">
				<input type=hidden name=InvId value="'.$id.'">
				<input type=hidden name=Desc value="'.$desc.'">
				<input type=hidden name=SignatureValue value="'.$sign_hash.'">

				<input type="submit" name="process" class="bs_button" value="Оплатить" />
			</form>';
		
	}
	
	function check_id( $DATA ) {
	
		return $DATA["InvId"];
	}
	
	function check_ok( $DATA ) {

		return 'OK'.$DATA["InvId"];
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) {
	
		if( $DATA['OutSum'] != $INVOICE['invoice_pay'] )
			return "Error: PAYMENT_AMOUNT";
	
		// read parameters
		$out_summ = $DATA["OutSum"];
		$inv_id = $DATA["InvId"];
		$shp_item = $DATA["Shp_item"];
		$crc = $DATA["SignatureValue"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$out_summ:$inv_id:$CONFIG[pass2]"));
	
		if ($my_crc !=$crc)
			return "bad sign\n";

		return 200;
		
	}
	
}

$Paysys = new PAYSYS;
?>