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

		$this->T_str_table("Идентификатор магазина (ID):", "Можно получить в <a href='https://new.interkassa.com/account/checkout' target='_blank'>личном кабинете</a>.", "<input name=\"save_con[login]\" class=\"edit bk\" type=\"text\" value=\"" . $paysys_config['login'] ."\">" );
		$this->T_str_table("Ваш текущий секретный ключ:", "<a href='https://new.interkassa.com/account/checkout' target='_blank'>Настройка кассы</a> вкладка 'Безопасность'", "<input name=\"save_con[secret]\" class=\"edit bk\" type=\"password\" value=\"" . $paysys_config['secret'] ."\">" );

		return $this->T_parse_str_table();
		
	}

	function form( $id, $config, $invoice, $currency, $desc = '' ) {

		if( !$desc ) $desc = 'Пополнение баланса пользователем '.$invoice['invoice_user_name'].' на сумму '.$invoice['invoice_get'].' '.$currency;
	
		return '
			     <form name="payment" method="post" id="paysys_form" action="https://sci.interkassa.com/"> 
					  <input type="hidden" name="ik_co_id" value="'.$config['login'].'" /> 
					  <input type="hidden" name="ik_pm_no" value="'.$id.'" /> 
					  <input type="hidden" name="ik_cur" value="USD" /> 
					  <input type="hidden" name="ik_am" value="'.$invoice['invoice_pay'].'" /> 
					  <input type="hidden" name="ik_desc" value="'.$desc.'" /> 
					  <input type="submit" class="bs_button" value="Оплатить"> 
				</form> ';
		
	}
	
	function check_id( $DATA ) {

		return $DATA["ik_pm_no"];
	}
	
	function check_ok( $DATA ) {

		return '200';
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) {
	
		if( $DATA['ik_am'] != $INVOICE['invoice_pay'] )
			return "Error: PAYMENT_AMOUNT";
	
		// read parameters
		$save_secret = $DATA['ik_sign'];
		
		unset($DATA['ik_sign']);
		ksort($DATA, SORT_STRING); 
		array_push($DATA, $CONFIG['secret']);
		$signString = implode(':', $DATA);
		$sign = base64_encode(md5($signString, true));

		if( $save_secret == $sign )
			return 200;

		return "bad sign\n";
	}
	
}

$Paysys = new PAYSYS;
?>
