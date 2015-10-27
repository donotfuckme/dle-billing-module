var bs_pay_sys = "";
var bs_pay_convert = "";
var bs_pay_minimum = "";
var bs_pay_currency = "";

/* Select billing - process */
function bs_paysys( name, convert, currency, minimum, valuta ) {

	$("#"+bs_pay_sys).removeClass("bt_paysys_active");
	$("#"+name).addClass("bt_paysys_active");

	bs_pay_sys = name;
	bs_pay_convert = convert;
	bs_pay_minimum = minimum.split(".");
	bs_pay_minimum = bs_pay_minimum[0];
	bs_pay_currency = valuta;

	$("#bs_pay_currency").html( currency );
	$("#bs_paysys").val( name );
	
	$("#bs_invoice_input").focus();
	
	bs_topay();

	return true;
}

/* Convert */
function bs_topay( convert ) {

	if( !convert ) convert = bs_pay_convert;
	if( !convert ) convert = 1;

	$("#bs_pay").html( parseFloat( (convert*$("#bs_summa").val()).toFixed(11) ) );

}

/* Form creat pay */
function bs_pay( form ) {

	error = "";

	if( !bs_pay_sys )
		error = "Выберите способ оплаты";

	if( $("#bs_summa").val() < bs_pay_minimum )
		error = "Минимум для "+bs_pay_sys+" - "+bs_pay_minimum+" "+bs_pay_currency;
		
	if( error ) {
	
		$("#bs_error_msg").html( error ).show();
	
		return false;
	}
	
	return true;
}