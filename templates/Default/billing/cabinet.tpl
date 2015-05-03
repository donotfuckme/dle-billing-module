<link media="screen" href="{THEME}/css/styles.css" type="text/css" rel="stylesheet" />

<script type="text/javascript" src="{THEME}/js/scripts.js"></script>
<script src="{THEME}/js/jsmask/jquery.inputmask.js" type="text/javascript"></script>

<div class="bt_sidebar">

	<div class="bt_menu[active]pay[/active]" onClick="document.location.href = '{URL_CABINET}/pay/';">Пополнить баланс</div>
	<div class="bt_menu[active]log[/active]" onClick="document.location.href = '{URL_CABINET}/log/';">История движения средств</div>
	<br />
	[plugin] <div class="bt_menu{plugin_active}" onClick="document.location.href = '{URL_CABINET}/{plugin_link}/';">{plugin_name}</div> [/plugin]
	
</div>

<div class="bt_container">{content}</div>