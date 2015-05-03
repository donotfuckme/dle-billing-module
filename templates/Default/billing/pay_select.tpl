<script type="text/javascript">
$(document).ready(function() {
	$("#bs_summa").mask("00000");
});
</script>

<div class="bt_pay_center">
	
     <div class="bt_title">1. Способ пополнения:</div>

		[paysys]
    	<div class="bt_paysys" id="{paysys_name}" onClick="bs_paysys('{paysys_name}', '{paysys_convert}', '{paysys_valuta}', '{paysys_minimum}')">
    	
            <p>	
                 <img src="{paysys_icon}" title="{paysys_title}" style="float: left; margin-right: 3px">
    	   		 <p style="padding-top: 10px">
					 <b>{paysys_title}</b>
					 <br />{paysys_convert} {paysys_valuta} = 1.00 {module_valuta}
				 </p>
            </p>
            
    	</div>
    	[/paysys]
</div>

<div class="bt_pay_right">
    
   <form action="" onsubmit="return bs_pay(this)" name="payform" method="post">

		<div class="bt_title">2. Введите сумму:</div>

		<div class="bt_invoice">
			<div class="bt_invoice-header">
			  <div class="bt_invoice-title">
				Пополнить баланс
			  </div>
			</div>
			<table width="100%">
				<tr>
					<td valign="middle">Внести на счёт сумму:</td>
					<td style="text-align: right">
						<input type="text" name="bs_summa" class="bs_input" id="bs_summa" size="3" value="10" onkeyup="bs_topay()" />
					</td>
				</tr>
				<tr>
					<td colspan="2" style="padding-top: 5px"><button type="submit" name="submit" class="bs_button" style="width: 100%">Оплатить <span id="bs_pay">10</span> <span id="bs_pay_currency">{module_valuta}</span></button></td>
				</tr>
			</table>
		
			<div class="bs_error_msg" id="bs_error_msg" style="text-align: center; display: none"></div>
			
		</div>

       <input type="hidden" name="bs_hash" value="{hash}" />
       <input type="hidden" name="bs_paysys" id="bs_paysys" />
	   
   </form>

</div>

