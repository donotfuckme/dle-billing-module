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

		$this->T_str_table("Кошелек продавца:", "Кошелек продавца, на который покупатель должен совершить платеж. Формат – буква и 12 цифр. В настоящее время допускается использование кошельков Z-,R-,E-,U- и D-типа.", "<input name=\"save_con[wm]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['wm'] ."\">" );
		$this->T_str_table("Секретный ключ:", "Задаётся в настройках торгового кошелько WebMoney.", "<input name=\"save_con[key]\" class=\"edit bk\" type=\"password\" value=\"" . $paysys_config['key'] ."\">" );
	
		return $this->T_parse_str_table();
		
	}


	function form( $id, $config, $invoice, $currency, $desc = '' ) {
	
		if( !$desc ) $desc = 'Пополнение баланса пользователем '.$invoice['invoice_user_name'].' на сумму '.$invoice['invoice_get'].' '.$currency;

		return '
			<form method="post" id="paysys_form" accept-charset="windows-1251" action="https://merchant.webmoney.ru/lmi/payment.asp">

				<input name="lmi_payment_desc" value="'.$desc.'" type="hidden">
				<input name="lmi_payment_no" value="'.$id.'" type="hidden">
				<input name="lmi_payment_amount" value="'.$invoice['invoice_pay'].'" type="hidden">
				<input name="lmi_sim_mode" value="0" type="hidden">
				<input name="lmi_payee_purse" value="'.$config['wm'].'" type="hidden">

				<input type="submit" class="bs_button" value="Оплатить">

			</form>';
		
	}
	
	function check_id( $DATA ) {

		return $DATA["LMI_PAYMENT_NO"];
	}
	
	function check_ok( $DATA ) {

		return 'YES';
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) {

		if( $DATA['LMI_PAYMENT_AMOUNT'] != $INVOICE['invoice_pay'] )
			return "Error: PAYMENT_AMOUNT";
		
		if( $DATA['LMI_PAYEE_PURSE'] != $CONFIG['wm'] )
			return "Error: LMI_PAYEE_PURSE";
	
		IF( $DATA['LMI_PREREQUEST']==1 )
			return "YES";
	
		$sign = strtoupper( hash("sha256", $DATA['LMI_PAYEE_PURSE'].$DATA['LMI_PAYMENT_AMOUNT'].$DATA['LMI_PAYMENT_NO'].$DATA['LMI_MODE'].$DATA['LMI_SYS_INVS_NO'].$DATA['LMI_SYS_TRANS_NO'].$DATA['LMI_SYS_TRANS_DATE'].$CONFIG['key'].$DATA['LMI_PAYER_PURSE'].$DATA['LMI_PAYER_WM'] ) );
	
		if( $DATA['LMI_HASH'] == $sign )
			return 200;

		return "bad sign\n";
	}
	
}

$Paysys = new PAYSYS;
?>